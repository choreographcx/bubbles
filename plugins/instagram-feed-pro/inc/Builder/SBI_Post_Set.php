<?php

namespace InstagramFeed\Builder;

/**
 * Instagram Feed Feed Post Set
 *
 * @since 6.0
 */
class SBI_Post_Set
{
	/**
	 * Feed id.
	 *
	 * @var int
	 */
	private $feed_id;

	/**
	 * Feed settings.
	 *
	 * @var array
	 */
	private $feed_settings;

	/**
	 * Converted feed settings.
	 *
	 * @var array
	 */
	private $converted_settings;

	/**
	 * Transient name.
	 *
	 * @var string
	 */
	private $transient_name;

	/**
	 * Post data.
	 *
	 * @var array|object
	 */
	private $data;

	/**
	 * Comments data.
	 *
	 * @var array|object
	 */
	private $comments_data;

	/**
	 * Constructor for the SBI_Post_Set class.
	 *
	 * @param string $feed_id The ID of the feed.
	 */
	public function __construct($feed_id)
	{
		$this->feed_id = $feed_id;
		$this->transient_name = '*' . $feed_id;

		$this->data = array();
	}

	/**
	 * Convert settings from 3.x for use in the builder in 6.0+
	 *
	 * @param array $atts Atts to convert legacy to builder.
	 *
	 * @return array
	 * @since 6.0
	 */
	public static function legacy_to_builder_convert($atts = array())
	{
		return array();
	}

	/**
	 * Settings that can include an array of values
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	public static function get_settings_with_multiple()
	{
		return array();
	}

	/**
	 * Used for changing the settings used for general front end feeds
	 *
	 * @param array $builder_settings Builder Settings.
	 *
	 * @return array
	 * @since 6.0
	 */
	public static function filter_general_settings($builder_settings)
	{
		return $builder_settings;
	}

	/**
	 * Used for changing the settings for feeds being edited in the customizer
	 *
	 * @param array $processed_settings Processed settings.
	 *
	 * @return array
	 * @since 6.0
	 */
	public static function filter_builder_settings($processed_settings)
	{
		return $processed_settings;
	}

	/**
	 * Retrieve the data for the feed.
	 *
	 * @return array|object
	 *
	 * @since 6.0
	 */
	public function get_data()
	{
		return $this->data;
	}

	/**
	 * Retrieves the comment data for the posts.
	 *
	 * @return array|object
	 *
	 * @since 6.0
	 */
	public function get_comments_data()
	{
		return $this->comments_data;
	}

	/**
	 * Retrieves the converted feed settings.
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	public function get_converted_settings()
	{
		return $this->converted_settings;
	}

	/**
	 * Initialize the post set.
	 *
	 * @param bool $customizerBuilder Optional. Whether the customizer builder is enabled. Default false.
	 * @param bool $previewSettings Optional. Whether the preview settings are enabled. Default false.
	 */
	public function init($customizerBuilder = false, $previewSettings = false)
	{
		$saver = new SBI_Feed_Saver($this->feed_id);
		if ($customizerBuilder && $previewSettings !== false) {
			$this->feed_settings = $saver->get_feed_settings_preview($previewSettings);
		} else {
			$this->feed_settings = $saver->get_feed_settings();
		}

		$this->converted_settings = self::builder_to_general_settings_convert($this->feed_settings);
	}

	/**
	 * Get the feed settings
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	public function get_feed_settings()
	{
		return $this->feed_settings;
	}

	/**
	 * Converts raw settings from the cff_feed_settings table into the
	 * more general way that the "CFF_Shortcode" class,
	 * "cff_get_processed_options" method does
	 *
	 * @param array $builder_settings Builder settings.
	 *
	 * @return array
	 * @since 6.0
	 */
	public static function builder_to_general_settings_convert($builder_settings)
	{
		if (isset($builder_settings['sources']) && is_array($builder_settings['sources'])) {
			$access_tokens = array();
			$sources_setting = array();
			foreach ($builder_settings['sources'] as $source) {
				$source_array = array();
				if (!is_array($source)) {
					$args = array('id' => $source);
					if (isset($builder_settings['feedtype']) && $builder_settings['feedtype'] === 'events') {
						$args['privilege'] = 'events';
					}
					$source_query = SBI_Db::source_query($args);

					if (isset($source_query[0])) {
						$source_array = $source_query[0];
						$sources_setting[] = $source_query[0];
					}
				} else {
					$source_array = $source;
				}

				if (!empty($source_array)) {
					$access_tokens[] = $source_array['access_token'];
				}
			}

			if (!empty($sources_setting)) {
				$builder_settings['sources'] = $sources_setting;
			}
		}

		return $builder_settings;
	}

	/**
	 * Gathers posts from the API until the minimum number of posts
	 * for the feed are retrieved then stores the results
	 *
	 * @since 6.0
	 */
	public function fetch()
	{
		$post_data = array();
		$this->data = $post_data;
	}

	/**
	 * Gathers comments for posts.
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	public function fetch_comments()
	{
		if (empty($this->data)) {
			return array();
		}

		$comments = array();
		$this->comments_data = $comments;

		return $comments;
	}
}
