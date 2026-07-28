<?php
/**
 * Turn a Meta "Download Your Information" export into a draft dogs CSV.
 *
 * This runs on its own, outside WordPress — you can run it on your laptop.
 * It reads the Instagram/Facebook posts JSON out of the export, groups the
 * posts it thinks belong to the same dog, guesses what it can from the
 * captions, and writes a CSV for you to check.
 *
 * The guesses are genuinely guesses. Captions are prose, so expect to correct
 * roughly half the rows — that is normal and it is still far quicker than
 * typing thirty dogs in by hand. Every row is marked `needs_review` so you can
 * see what the script was unsure about.
 *
 * Usage:
 *   php parse-social-export.php --export=/path/to/unzipped-export --out=dogs.csv
 *
 * Options:
 *   --export=PATH   Folder you unzipped the Meta export into (required).
 *   --out=PATH      CSV to write (default: dogs-draft.csv).
 *   --since=YYYY-MM Ignore posts older than this (default: 18 months ago).
 *   --min-words=N   Skip captions shorter than N words (default: 12) — these
 *                   are usually reshares or thank-you posts, not dog profiles.
 *
 * @package BubblesDogs
 */

// This is a CLI tool; refuse to run over the web.
if ( PHP_SAPI !== 'cli' ) {
	http_response_code( 403 );
	exit( "This script can only be run from the command line.\n" );
}

/**
 * Words that are never a dog's name, so the name guesser skips them.
 */
const BPR_STOPWORDS = array(
	// Grammar and sentence openers.
	'the', 'this', 'that', 'these', 'those', 'meet', 'our', 'we', 'she', 'he',
	'they', 'her', 'his', 'their', 'is', 'was', 'are', 'a', 'an', 'and', 'but',
	'it', 'if', 'for', 'with', 'from', 'about', 'us', 'you', 'your', 'so',
	'after', 'every', 'each', 'both', 'all', 'some', 'there', 'here', 'when',
	// Words that commonly open a rescue caption.
	'update', 'urgent', 'adopted', 'adopt', 'adoption', 'available', 'please',
	'help', 'today', 'tonight', 'still', 'looking', 'home', 'foster', 'rescue',
	'donate', 'donation', 'thank', 'thanks', 'happy', 'good', 'great', 'news',
	'new', 'sponsor', 'volunteer', 'link', 'bio', 'dm', 'wonderful',
	'amazing', 'incredible', 'exciting', 'sadly', 'unfortunately', 'finally',
	'introducing', 'attention', 'important', 'reminder', 'congratulations',
	// Places. "Al" matters because Al Quoz / Al Barsha appear constantly and
	// would otherwise be picked up as a dog's name.
	'dubai', 'sharjah', 'abu', 'ajman', 'uae', 'emirates', 'al',
);

/**
 * Words suggesting a caption really is about an individual dog.
 *
 * Used to hold back the weakest name guess, so fundraising and thank-you posts
 * don't turn into dog rows.
 */
const BPR_DOG_SIGNALS = '/(adopt|foster|rehome|forever home|\bhome\b|puppy|puppies|neuter|spay|steril|vaccinat|microchip|\bkg\b|years? old|months? old|house trained|good with|\bwalks?\b|leash|kennel|\bpaws?\b|\btail\b|rescued|\bstray\b|\bboy\b|\bgirl\b|gentle|playful|cuddl)/';

/**
 * Parse command line options.
 *
 * @return array<string,string>
 */
function bpr_parse_args() {
	$defaults = array(
		'export'    => '',
		'out'       => 'dogs-draft.csv',
		'since'     => gmdate( 'Y-m', strtotime( '-18 months' ) ),
		'min-words' => '12',
	);

	$opts = getopt( '', array( 'export:', 'out:', 'since:', 'min-words:' ) );
	$opts = is_array( $opts ) ? $opts : array();

	return array_merge( $defaults, array_map( 'strval', $opts ) );
}

/**
 * Recursively find the post JSON files inside a Meta export.
 *
 * Meta has changed this layout several times, so rather than hardcoding a path
 * we look for any JSON file whose name looks like a posts file.
 *
 * @param string $root Export root folder.
 * @return string[] Absolute paths.
 */
function bpr_find_post_files( $root ) {
	$found = array();

	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ),
		RecursiveIteratorIterator::LEAVES_ONLY
	);

	foreach ( $iterator as $file ) {
		if ( ! $file->isFile() || 'json' !== strtolower( $file->getExtension() ) ) {
			continue;
		}

		$name = strtolower( $file->getFilename() );

		// Instagram: posts_1.json. Facebook: your_posts_1.json / your_posts.json.
		if ( preg_match( '/^(your_)?posts(_\d+)?\.json$/', $name ) ) {
			$found[] = $file->getPathname();
		}
	}

	sort( $found );
	return $found;
}

/**
 * Pull a flat list of posts out of a Meta export JSON file.
 *
 * The shapes differ between Instagram and Facebook exports and between export
 * vintages, so this walks the structure looking for the parts it recognises
 * rather than assuming one layout.
 *
 * @param string $path JSON file path.
 * @return array<int,array{caption:string,timestamp:int,media:string[]}>
 */
function bpr_extract_posts( $path ) {
	$raw = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- standalone CLI script, no WP available.
	if ( false === $raw ) {
		fwrite( STDERR, "  ! could not read {$path}\n" );
		return array();
	}

	$data = json_decode( $raw, true );
	if ( null === $data ) {
		fwrite( STDERR, "  ! not valid JSON: {$path}\n" );
		return array();
	}

	// Facebook wraps posts in a keyed object in some exports.
	if ( isset( $data['status_updates'] ) ) {
		$data = $data['status_updates'];
	}
	if ( ! is_array( $data ) ) {
		return array();
	}
	// A single-post export is an object, not a list.
	if ( isset( $data['media'] ) || isset( $data['data'] ) ) {
		$data = array( $data );
	}

	$posts = array();

	foreach ( $data as $entry ) {
		if ( ! is_array( $entry ) ) {
			continue;
		}

		$caption   = '';
		$timestamp = 0;
		$media     = array();

		// Timestamp can sit at the top level or on the first media item.
		if ( isset( $entry['creation_timestamp'] ) ) {
			$timestamp = (int) $entry['creation_timestamp'];
		} elseif ( isset( $entry['timestamp'] ) ) {
			$timestamp = (int) $entry['timestamp'];
		}

		// Instagram: title on the post, or on the media item for single posts.
		if ( ! empty( $entry['title'] ) && is_string( $entry['title'] ) ) {
			$caption = $entry['title'];
		}

		// Facebook: the caption lives in data[].post.
		if ( ! empty( $entry['data'] ) && is_array( $entry['data'] ) ) {
			foreach ( $entry['data'] as $chunk ) {
				if ( is_array( $chunk ) && ! empty( $chunk['post'] ) && is_string( $chunk['post'] ) ) {
					$caption = $chunk['post'];
					break;
				}
			}
		}

		// Media: Instagram uses `media`, Facebook nests under attachments.
		if ( ! empty( $entry['media'] ) && is_array( $entry['media'] ) ) {
			foreach ( $entry['media'] as $item ) {
				if ( ! is_array( $item ) ) {
					continue;
				}
				if ( ! empty( $item['uri'] ) && is_string( $item['uri'] ) ) {
					$media[] = $item['uri'];
				}
				// Fall back to the media item's own caption/timestamp.
				if ( '' === $caption && ! empty( $item['title'] ) && is_string( $item['title'] ) ) {
					$caption = $item['title'];
				}
				if ( 0 === $timestamp && ! empty( $item['creation_timestamp'] ) ) {
					$timestamp = (int) $item['creation_timestamp'];
				}
			}
		}

		if ( ! empty( $entry['attachments'] ) && is_array( $entry['attachments'] ) ) {
			foreach ( $entry['attachments'] as $attachment ) {
				if ( empty( $attachment['data'] ) || ! is_array( $attachment['data'] ) ) {
					continue;
				}
				foreach ( $attachment['data'] as $item ) {
					if ( is_array( $item ) && ! empty( $item['media']['uri'] ) ) {
						$media[] = (string) $item['media']['uri'];
					}
				}
			}
		}

		$caption = bpr_decode_meta_text( $caption );

		if ( '' === trim( $caption ) && empty( $media ) ) {
			continue;
		}

		$posts[] = array(
			'caption'   => $caption,
			'timestamp' => $timestamp,
			'media'     => array_values( array_unique( $media ) ),
		);
	}

	return $posts;
}

/**
 * Fix Meta's mojibake.
 *
 * Meta exports UTF-8 text that has been run through a Latin-1 encoder, so
 * emoji and accented characters arrive mangled. This puts them back.
 *
 * @param string $text Raw text from the export.
 * @return string
 */
function bpr_decode_meta_text( $text ) {
	if ( '' === $text ) {
		return '';
	}

	$fixed = @iconv( 'UTF-8', 'ISO-8859-1', $text ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- fails on already-clean text, which is fine.

	if ( false !== $fixed && '' !== $fixed ) {
		// Only accept the conversion if it produced valid UTF-8.
		if ( function_exists( 'mb_check_encoding' ) && mb_check_encoding( $fixed, 'UTF-8' ) ) {
			return $fixed;
		}
	}

	return $text;
}

/**
 * Strip emoji, hashtags, mentions and URLs from a caption.
 *
 * @param string $caption Raw caption.
 * @return string
 */
function bpr_clean_caption( $caption ) {
	// Drop URLs, @mentions and #hashtags.
	$caption = preg_replace( '~https?://\S+~u', '', $caption );
	$caption = preg_replace( '/[@#][\w.]+/u', '', (string) $caption );

	// Drop emoji and pictographs, keeping ordinary punctuation.
	$caption = preg_replace(
		'/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE0F}\x{2B00}-\x{2BFF}\x{1F1E6}-\x{1F1FF}]/u',
		'',
		(string) $caption
	);

	// Collapse whitespace.
	$caption = preg_replace( '/\s+/u', ' ', (string) $caption );

	return trim( (string) $caption );
}

/**
 * Guess a dog's name from a caption.
 *
 * Looks for the patterns rescues actually use — "Meet Bubbles!", "This is
 * Bubbles", "BUBBLES is looking for..." — and falls back to the first
 * capitalised word that isn't a stopword.
 *
 * @param string $caption Cleaned caption.
 * @return string Name, or an empty string when nothing looks like one.
 */
function bpr_guess_name( $caption ) {
	// Strong patterns: the caption explicitly introduces or names the dog.
	//
	// The introducing words are wrapped in (?i:...) so "Meet", "MEET" and
	// "meet" all match, while the captured name stays case-sensitive — it has
	// to start with a capital to be a name at all.
	$patterns = array(
		'/(?i:\bmeet)\s+([A-Z][a-z]{1,15})\b/u',
		'/(?i:\bthis\s+is)\s+([A-Z][a-z]{1,15})\b/u',
		'/(?i:\bsay\s+hello\s+to)\s+([A-Z][a-z]{1,15})\b/u',
		'/(?i:\bintroducing)\s+([A-Z][a-z]{1,15})\b/u',
		// "Luna has been adopted", "Rocky is looking for a home".
		'/\b([A-Z][a-z]{1,15})\s+(?i:(?:has|have)\s+been\s+(?:adopted|rehomed|reserved))\b/u',
		'/\b([A-Z][a-z]{1,15})\s+(?i:is|was|has|needs|loves|came|arrived|found)\b/u',
		// All-caps name at the very start, e.g. "BUBBLES needs a home".
		'/^([A-Z]{2,15})\b/u',
	);

	foreach ( $patterns as $pattern ) {
		if ( ! preg_match_all( $pattern, $caption, $matches ) ) {
			continue;
		}
		foreach ( $matches[1] as $raw ) {
			$candidate = ucfirst( strtolower( $raw ) );
			if ( ! in_array( strtolower( $candidate ), BPR_STOPWORDS, true ) ) {
				return $candidate;
			}
		}
	}

	// Weakest guess: the first capitalised word that isn't a stopword. Only
	// trusted when the caption otherwise reads like a dog profile, because
	// this is what turns fundraising posts into phantom dogs.
	if ( ! preg_match( BPR_DOG_SIGNALS, strtolower( $caption ) ) ) {
		return '';
	}

	if ( preg_match_all( '/\b([A-Z][a-z]{1,15})\b/u', $caption, $all ) ) {
		foreach ( $all[1] as $word ) {
			if ( ! in_array( strtolower( $word ), BPR_STOPWORDS, true ) ) {
				// Marked with a trailing marker the caller strips, so it can
				// flag the row for review.
				return $word . "\x00weak";
			}
		}
	}

	return '';
}

/**
 * Guess sex from pronouns in the caption.
 *
 * @param string $caption Cleaned caption.
 * @return string 'male', 'female' or ''.
 */
function bpr_guess_sex( $caption ) {
	$lower = ' ' . strtolower( $caption ) . ' ';

	$female = preg_match_all( '/\b(she|her|hers|girl|female|bitch)\b/', $lower );
	$male   = preg_match_all( '/\b(he|him|his|boy|male)\b/', $lower );

	if ( $female > $male ) {
		return 'female';
	}
	if ( $male > $female ) {
		return 'male';
	}
	return '';
}

/**
 * Guess an approximate age phrase from the caption.
 *
 * @param string $caption Cleaned caption.
 * @return string
 */
function bpr_guess_age( $caption ) {
	$lower = strtolower( $caption );

	$units = '(year|yr|month|mo|week)';

	// Tried in order of confidence. The loose pattern is last and explicitly
	// refuses durations that describe time in rescue rather than age — "14
	// months with us" is not a 14-month-old dog.
	$patterns = array(
		// "3 years old", "18-month-old".
		'/(\d+(?:\.\d+)?)\s*[-\s]?\s*' . $units . 's?[-\s]+old\b/',
		// "aged about 2 years", "approximately 6 months".
		'/(?:aged?|approx(?:imately)?|about|around|roughly|est(?:imated)?)\s+(\d+(?:\.\d+)?)\s*[-\s]?\s*' . $units . 's?\b/',
		// Bare duration, but not one that is followed by a time-in-rescue phrase.
		'/(\d+(?:\.\d+)?)\s*[-\s]?\s*' . $units . 's?\b(?!\s+(?:with|in|at|of|since|ago|waiting|already))/',
	);

	foreach ( $patterns as $pattern ) {
		if ( ! preg_match( $pattern, $lower, $m ) ) {
			continue;
		}

		$number = $m[1];
		$unit   = $m[2];

		$unit = ( 'yr' === $unit ) ? 'year' : $unit;
		$unit = ( 'mo' === $unit ) ? 'month' : $unit;

		// Sanity check: no dog is 40 years old, that will be a weight or a year.
		$max = ( 'year' === $unit ) ? 20 : 120;
		if ( (float) $number <= 0 || (float) $number > $max ) {
			continue;
		}

		$plural = ( 1.0 === (float) $number ) ? '' : 's';
		return "about {$number} {$unit}{$plural}";
	}

	if ( preg_match( '/\bpupp(?:y|ies)\b/', $lower ) ) {
		return 'puppy';
	}
	if ( preg_match( '/\bsenior\b/', $lower ) ) {
		return 'senior';
	}

	return '';
}

/**
 * Guess the age taxonomy band from an age phrase.
 *
 * @param string $age_text Age phrase.
 * @return string Term slug, or ''.
 */
function bpr_guess_age_band( $age_text ) {
	if ( '' === $age_text ) {
		return '';
	}
	if ( 'puppy' === $age_text ) {
		return 'puppy';
	}
	if ( 'senior' === $age_text ) {
		return 'senior';
	}

	if ( preg_match( '/([\d.]+)\s*month/', $age_text, $m ) ) {
		return ( (float) $m[1] < 12 ) ? 'puppy' : 'young';
	}
	if ( preg_match( '/([\d.]+)\s*week/', $age_text ) ) {
		return 'puppy';
	}
	if ( preg_match( '/([\d.]+)\s*year/', $age_text, $m ) ) {
		$years = (float) $m[1];
		if ( $years < 1 ) {
			return 'puppy';
		}
		if ( $years <= 3 ) {
			return 'young';
		}
		if ( $years <= 8 ) {
			return 'adult';
		}
		return 'senior';
	}

	return '';
}

/**
 * Guess boolean-ish health flags mentioned in the caption.
 *
 * @param string $caption Cleaned caption.
 * @return array{vaccinated:string,sterilised:string,microchipped:string}
 */
function bpr_guess_health( $caption ) {
	$lower = strtolower( $caption );

	$has = static function ( $pattern ) use ( $lower ) {
		// The alternation must stay inside a group — without it the `|` would
		// split the whole expression and the negation check below would match
		// the bare term, silently dropping every flag.
		$group = '(?:' . $pattern . ')';

		if ( ! preg_match( '/' . $group . '/', $lower ) ) {
			return '';
		}

		// A nearby negation flips the meaning.
		if ( preg_match( '/\b(?:not|isn.t|no|never|awaiting|needs|due)\s+(?:\w+\s+){0,2}' . $group . '/', $lower ) ) {
			return '';
		}

		return '1';
	};

	return array(
		'vaccinated'   => $has( 'vaccinat|vacc\b|jabs?\b' ),
		'sterilised'   => $has( 'steril|neuter|spay|castrat|fixed\b' ),
		'microchipped' => $has( 'microchip|chipped\b' ),
	);
}

/**
 * Guess "good with" tri-states.
 *
 * @param string $caption Cleaned caption.
 * @return array{kids:string,dogs:string,cats:string}
 */
function bpr_guess_good_with( $caption ) {
	$lower = strtolower( $caption );

	$check = static function ( $subject ) use ( $lower ) {
		$group = '(?:' . $subject . ')';

		// Negatives are checked first: "not good with cats" also contains
		// "good with cats", so testing for the positive first would be wrong.
		if ( preg_match( '/\b(?:not|isn.t)\s+(?:good|great|ok|fine|suitable|safe)\s+(?:with\s+)?(?:other\s+)?' . $group . '/', $lower ) ) {
			return 'no';
		}
		if ( preg_match( '/\bno\s+' . $group . '\b/', $lower ) ) {
			return 'no';
		}
		if ( preg_match( '/\b(?:good|great|fine|ok|loves|adores|gets on)\s+(?:with\s+)?(?:other\s+)?' . $group . '/', $lower ) ) {
			return 'yes';
		}
		if ( preg_match( '/\bwith\s+(?:slow\s+)?introductions?\b/', $lower ) ) {
			return '';
		}
		return '';
	};

	return array(
		'kids' => $check( '(?:kids?|children|child)' ),
		'dogs' => $check( 'dogs?' ),
		'cats' => $check( 'cats?' ),
	);
}

/**
 * Guess a size band from any weight mentioned.
 *
 * @param string $caption Cleaned caption.
 * @return array{weight:string,size:string}
 */
function bpr_guess_size( $caption ) {
	$lower = strtolower( $caption );

	if ( preg_match( '/(\d+(?:\.\d+)?)\s*(?:kg|kilos?|kgs)\b/', $lower, $m ) ) {
		$weight = (float) $m[1];
		if ( $weight <= 0 || $weight > 90 ) {
			return array(
				'weight' => '',
				'size'   => '',
			);
		}
		if ( $weight <= 10 ) {
			$size = 'small';
		} elseif ( $weight <= 25 ) {
			$size = 'medium';
		} else {
			$size = 'large';
		}
		return array(
			'weight' => rtrim( rtrim( number_format( $weight, 1, '.', '' ), '0' ), '.' ),
			'size'   => $size,
		);
	}

	foreach ( array( 'small', 'medium', 'large' ) as $word ) {
		if ( preg_match( '/\b' . $word . '(?:\s+sized?)?\b/', $lower ) ) {
			return array(
				'weight' => '',
				'size'   => $word,
			);
		}
	}

	return array(
		'weight' => '',
		'size'   => '',
	);
}

/**
 * Does this caption look like it is already announcing an adoption?
 *
 * @param string $caption Cleaned caption.
 * @return bool
 */
function bpr_looks_adopted( $caption ) {
	// Note what is deliberately absent: a bare "forever home". Rescues use
	// that phrase constantly about dogs who are still waiting ("looking for
	// her forever home"), so matching it would mark half the listing adopted.
	// The signal has to be a completed adoption.
	return (bool) preg_match(
		'/(has been adopted|have been adopted|now adopted|been adopted|adopted!|'
		. '(?:found|got|has)\s+(?:her|his|their)\s+forever\s+home|'
		. 'happy ending|gotcha day|went home (?:today|yesterday)|'
		. 'settled (?:in|into) (?:her|his|their) new home)/i',
		$caption
	);
}

/**
 * Does the caption mention overseas rehoming or a flight volunteer?
 *
 * Common for UAE rescues, so worth picking up automatically.
 *
 * @param string $caption Cleaned caption.
 * @return string '1' or ''.
 */
function bpr_guess_travel( $caption ) {
	return preg_match(
		'/\b(flight\s+(?:buddy|buddies|volunteer|angel)|can\s+fly|fly\s+to\b|'
		. 'travel\s+to\b|relocat|rehomed?\s+(?:to|in)\s+(?:europe|the\s+uk|canada|germany)|'
		. 'overseas|abroad)\b/i',
		$caption
	) ? '1' : '';
}

/**
 * The CSV columns, in order. Must match the importer's expectations.
 *
 * @return string[]
 */
function bpr_csv_columns() {
	return array(
		'name',
		'status',
		'bio',
		'sex',
		'age_text',
		'dob',
		'weight_kg',
		'colour',
		'location',
		'breed',
		'size',
		'age_group',
		'vaccinated',
		'sterilised',
		'microchipped',
		'health_notes',
		'good_with_kids',
		'good_with_dogs',
		'good_with_cats',
		'house_trained',
		'energy',
		'travel_ready',
		'intake_date',
		'adopted_date',
		'source_url',
		'photos',
		'needs_review',
	);
}

// ---------------------------------------------------------------------------
// Main
// ---------------------------------------------------------------------------

$args = bpr_parse_args();

if ( '' === $args['export'] ) {
	fwrite( STDERR, "Usage: php parse-social-export.php --export=/path/to/unzipped-export [--out=dogs-draft.csv]\n" );
	exit( 1 );
}

$export_root = rtrim( $args['export'], '/' );

if ( ! is_dir( $export_root ) ) {
	fwrite( STDERR, "Not a folder: {$export_root}\n" );
	exit( 1 );
}

$since_ts  = strtotime( $args['since'] . '-01' );
$min_words = max( 0, (int) $args['min-words'] );

echo "Looking for post files in {$export_root} ...\n";

$files = bpr_find_post_files( $export_root );

if ( empty( $files ) ) {
	fwrite( STDERR, "No posts JSON found. Make sure you unzipped the export and chose JSON (not HTML) when you requested it.\n" );
	exit( 1 );
}

foreach ( $files as $file ) {
	echo '  found ' . basename( $file ) . "\n";
}

$posts = array();
foreach ( $files as $file ) {
	$posts = array_merge( $posts, bpr_extract_posts( $file ) );
}

echo 'Read ' . count( $posts ) . " posts.\n";

// Group posts by guessed dog name. The same dog usually has several posts, and
// the longest caption is normally the original profile post — the one worth
// keeping as the bio.
$dogs    = array();
$skipped = 0;

foreach ( $posts as $post ) {
	if ( $since_ts && $post['timestamp'] && $post['timestamp'] < $since_ts ) {
		++$skipped;
		continue;
	}

	$clean = bpr_clean_caption( $post['caption'] );

	if ( str_word_count( $clean ) < $min_words ) {
		++$skipped;
		continue;
	}

	$name = bpr_guess_name( $clean );
	if ( '' === $name ) {
		++$skipped;
		continue;
	}

	// A weak guess still gets a row, but flagged so it is checked by a human.
	$weak = false;
	if ( str_contains( $name, "\x00weak" ) ) {
		$weak = true;
		$name = str_replace( "\x00weak", '', $name );
	}

	$key = strtolower( $name );

	if ( ! isset( $dogs[ $key ] ) ) {
		$dogs[ $key ] = array(
			'name'       => $name,
			'captions'   => array(),
			'media'      => array(),
			'timestamps' => array(),
			'weak_name'  => true,
		);
	}

	// Confident on any post is confident enough.
	if ( ! $weak ) {
		$dogs[ $key ]['weak_name'] = false;
	}

	$dogs[ $key ]['captions'][]   = $clean;
	$dogs[ $key ]['timestamps'][] = $post['timestamp'];
	$dogs[ $key ]['media']        = array_merge( $dogs[ $key ]['media'], $post['media'] );
}

echo 'Grouped into ' . count( $dogs ) . " possible dogs (skipped {$skipped} posts that didn't look like dog profiles).\n";

if ( empty( $dogs ) ) {
	fwrite( STDERR, "Nothing to write. Try lowering --min-words or widening --since.\n" );
	exit( 1 );
}

$handle = fopen( $args['out'], 'w' );
if ( false === $handle ) {
	fwrite( STDERR, "Could not open {$args['out']} for writing.\n" );
	exit( 1 );
}

// Excel needs a BOM to read UTF-8 correctly, and these captions have accents.
fwrite( $handle, "\xEF\xBB\xBF" );
fputcsv( $handle, bpr_csv_columns() );

// Sort by most recent post first — the current dogs float to the top.
uasort(
	$dogs,
	static function ( $a, $b ) {
		return max( $b['timestamps'] ) <=> max( $a['timestamps'] );
	}
);

$written = 0;

foreach ( $dogs as $dog ) {
	// Longest caption is most likely the full profile post.
	$captions = $dog['captions'];
	usort(
		$captions,
		static function ( $a, $b ) {
			return strlen( $b ) <=> strlen( $a );
		}
	);
	$bio = $captions[0];
	$all = implode( ' ', $captions );

	$age_text = bpr_guess_age( $all );
	$health   = bpr_guess_health( $all );
	$good     = bpr_guess_good_with( $all );
	$size     = bpr_guess_size( $all );
	$sex      = bpr_guess_sex( $all );

	$review = array();
	if ( ! empty( $dog['weak_name'] ) ) {
		$review[] = 'check-name-may-not-be-a-dog';
	}
	if ( '' === $sex ) {
		$review[] = 'sex';
	}
	if ( '' === $age_text ) {
		$review[] = 'age';
	}
	if ( '' === $size['size'] ) {
		$review[] = 'size';
	}
	if ( count( $dog['captions'] ) > 3 ) {
		$review[] = 'many-posts-check-not-two-dogs';
	}
	if ( empty( $dog['media'] ) ) {
		$review[] = 'no-photos';
	}

	$row = array(
		'name'           => $dog['name'],
		'status'         => bpr_looks_adopted( $all ) ? 'adopted' : 'available',
		'bio'            => $bio,
		'sex'            => $sex,
		'age_text'       => $age_text,
		'dob'            => '',
		'weight_kg'      => $size['weight'],
		'colour'         => '',
		'location'       => '',
		'breed'          => '',
		'size'           => $size['size'],
		'age_group'      => bpr_guess_age_band( $age_text ),
		'vaccinated'     => $health['vaccinated'],
		'sterilised'     => $health['sterilised'],
		'microchipped'   => $health['microchipped'],
		'health_notes'   => '',
		'good_with_kids' => $good['kids'],
		'good_with_dogs' => $good['dogs'],
		'good_with_cats' => $good['cats'],
		'house_trained'  => '',
		'energy'         => '',
		'travel_ready'   => bpr_guess_travel( $all ),
		'intake_date'    => $dog['timestamps'] ? gmdate( 'Y-m-d', min( array_filter( $dog['timestamps'] ) ?: array( time() ) ) ) : '',
		'adopted_date'   => '',
		'source_url'     => '',
		// Photos are paths relative to the export root; the importer resolves
		// them with --media-root.
		'photos'         => implode( '|', array_slice( array_unique( $dog['media'] ), 0, 8 ) ),
		'needs_review'   => implode( ' ', $review ),
	);

	fputcsv( $handle, array_values( $row ) );
	++$written;
}

fclose( $handle );

echo "\nWrote {$written} rows to {$args['out']}\n\n";
echo "Next steps:\n";
echo "  1. Open the CSV in Excel or Google Sheets.\n";
echo "  2. Work down the needs_review column — those are the fields the script\n";
echo "     could not work out. Delete rows that are not dogs.\n";
echo "  3. Watch for the same dog appearing twice under different spellings,\n";
echo "     and for one row that is actually two dogs from a litter post.\n";
echo "  4. Clear the needs_review column when you are done, then import with:\n";
echo "     wp bubbles-dogs import dogs.csv --media-root={$export_root}\n";
