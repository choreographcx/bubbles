<?php
if (!defined('ABSPATH')) {
    exit;
}

function bpr_core_get_age_range_options() {
    return array(
        '' => 'Select Age Range',
        'baby' => 'Baby: Under 1 Year',
        'young' => 'Young: 1 to 3 Years',
        'adult' => 'Adult: 4 to 7 Years',
        'senior' => 'Senior: 8 to 10 Years',
        'super-senior' => 'Super Senior: 11+ Years',
        'unknown' => 'Age Unknown',
    );
}

function bpr_core_get_size_options() {
    return array(
        '' => 'Select Size',
        'Small' => 'Small',
        'Medium' => 'Medium',
        'Large' => 'Large',
        'Extra Large' => 'Extra Large',
    );
}

function bpr_core_get_gender_options() {
    return array('Male', 'Female', 'Unknown');
}

function bpr_core_get_coat_options() {
    return array('Hairless', 'Short', 'Medium', 'Long', 'Wire', 'Curly', 'Double Coat', 'Unknown');
}

function bpr_core_get_energy_options() {
    return array(
        '' => 'Select Energy Level',
        'Low' => 'Low',
        'Moderate' => 'Moderate',
        'High' => 'High',
        'Unknown' => 'Unknown',
    );
}

function bpr_core_get_tri_state_options() {
    return array('Unknown' => 'Unknown', 'Yes' => 'Yes', 'No' => 'No');
}

function bpr_core_get_status_options() {
    return array(
        'available' => 'Available',
        'foster-needed' => 'Foster Needed',
        'adoption-pending' => 'Adoption Pending',
        'adopted' => 'Adopted',
        'medical-care' => 'Medical Care',
        'coming-soon' => 'Coming Soon',
    );
}

function bpr_core_get_dog_breed_options() {
    return array(
        'Mixed Breed', 'Unknown', 'Afghan Hound', 'Akita', 'Alaskan Malamute', 'American Bulldog',
        'American Bully', 'American Staffordshire Terrier', 'Australian Cattle Dog', 'Australian Shepherd',
        'Basenji', 'Beagle', 'Belgian Malinois', 'Bichon Frise', 'Border Collie', 'Boston Terrier',
        'Boxer', 'Bulldog', 'Bull Terrier', 'Bullmastiff', 'Cane Corso', 'Cavalier King Charles Spaniel',
        'Chihuahua', 'Chinese Crested', 'Cocker Spaniel', 'Corgi', 'Dachshund', 'Dalmatian',
        'Doberman Pinscher', 'English Bulldog', 'French Bulldog', 'German Shepherd Dog',
        'German Shorthaired Pointer', 'Golden Retriever', 'Great Dane', 'Great Pyrenees', 'Greyhound',
        'Havanese', 'Husky', 'Jack Russell Terrier', 'Jindo', 'Labrador Retriever', 'Lhasa Apso',
        'Maltese', 'Mastiff', 'Miniature Pinscher', 'Miniature Schnauzer', 'Pekingese', 'Pomeranian',
        'Poodle', 'Pug', 'Rhodesian Ridgeback', 'Rottweiler', 'Saluki', 'Samoyed', 'Shar-Pei',
        'Shih Tzu', 'Staffordshire Bull Terrier', 'Terrier Mix', 'Whippet', 'Yorkshire Terrier'
    );
}

function bpr_core_get_cat_breed_options() {
    return array(
        'Domestic Shorthair', 'Domestic Medium Hair', 'Domestic Longhair', 'Mixed Breed', 'Unknown',
        'Abyssinian', 'American Shorthair', 'Bengal', 'Birman', 'Bombay', 'British Shorthair',
        'Burmese', 'Egyptian Mau', 'Exotic Shorthair', 'Maine Coon', 'Norwegian Forest Cat',
        'Persian', 'Ragdoll', 'Russian Blue', 'Scottish Fold', 'Siamese', 'Sphynx', 'Turkish Angora'
    );
}

function bpr_core_get_breed_options($post_type) {
    return $post_type === 'cat' ? bpr_core_get_cat_breed_options() : bpr_core_get_dog_breed_options();
}

function bpr_core_get_personality_options() {
    return array(
        'Affectionate', 'Calm', 'Confident', 'Couch Potato', 'Curious', 'Easygoing', 'Friendly',
        'Gentle', 'Independent', 'Loyal', 'Playful', 'Quiet', 'Sensitive', 'Shy', 'Silly',
        'Smart', 'Snuggly', 'Social', 'Talkative', 'Velcro Pet'
    );
}

function bpr_core_clean_display_value($value) {
    if (is_array($value)) {
        return $value;
    }

    $value = trim((string) $value);
    if ($value === '' || stripos($value, 'select ') === 0 || strtolower($value) === 'unknown') {
        return '';
    }

    return $value;
}

function bpr_core_get_age_display($post_id) {
    $exact = bpr_core_clean_display_value(get_post_meta($post_id, '_bpr_age', true));
    if ($exact) {
        return bpr_core_format_age_value($post_id, $exact);
    }

    $range = sanitize_key((string) get_post_meta($post_id, '_bpr_age_range', true));
    $options = bpr_core_get_age_range_options();
    return isset($options[$range]) ? bpr_core_clean_display_value($options[$range]) : '';
}

/*
 * Turn a bare number into a friendly age with its unit, e.g. "1 year old" or
 * "6 months old". The unit comes from the _bpr_age_unit field (default: years).
 * Values that already contain words (e.g. "2 years", "Puppy") are returned
 * unchanged, so existing entries are never double-labelled.
 */
function bpr_core_format_age_value($post_id, $exact) {
    $exact = trim((string) $exact);
    if ($exact === '' || !preg_match('/^\d+(?:\.\d+)?$/', $exact)) {
        return $exact;
    }

    $unit = strtolower(trim((string) get_post_meta($post_id, '_bpr_age_unit', true)));
    $unit = ($unit === 'months') ? 'months' : 'years';
    $is_one = ((float) $exact === 1.0);

    if ($unit === 'months') {
        $word = $is_one ? 'month' : 'months';
    } else {
        $word = $is_one ? 'year' : 'years';
    }

    return $exact . ' ' . $word . ' old';
}

function bpr_core_get_breed_display($post_id) {
    $breeds = get_post_meta($post_id, '_bpr_breeds', true);
    $breeds = is_array($breeds) ? $breeds : array();
    $other = trim((string) get_post_meta($post_id, '_bpr_other_breed', true));

    if ($other) {
        $breeds[] = $other;
    }

    $breeds = array_values(array_unique(array_filter(array_map('trim', $breeds))));
    if ($breeds) {
        return implode(', ', $breeds);
    }

    return bpr_core_clean_display_value(get_post_meta($post_id, '_bpr_breed', true));
}

function bpr_core_get_personality_tags($post_id) {
    $tags = get_post_meta($post_id, '_bpr_personality_tags', true);
    return is_array($tags) ? array_values(array_filter(array_map('sanitize_text_field', $tags))) : array();
}

function bpr_core_get_gallery_ids($post_id, $include_featured = true) {
    $ids = get_post_meta($post_id, '_bpr_gallery', true);

    if (!is_array($ids)) {
        $ids = array_filter(array_map('absint', explode(',', (string) $ids)));
    }

    $ids = array_values(array_filter(array_map('absint', $ids)));
    if ($include_featured) {
        $featured = get_post_thumbnail_id($post_id);
        if ($featured) {
            array_unshift($ids, (int) $featured);
        }
    }

    return array_values(array_unique($ids));
}

function bpr_core_get_primary_image_id($post_id) {
    $featured = get_post_thumbnail_id($post_id);
    if ($featured) {
        return (int) $featured;
    }

    $gallery = bpr_core_get_gallery_ids($post_id, false);
    return $gallery ? (int) $gallery[0] : 0;
}

function bpr_core_get_status_slug($post_id) {
    $terms = wp_get_post_terms($post_id, 'pet_status', array('fields' => 'slugs'));
    return (!is_wp_error($terms) && !empty($terms)) ? (string) $terms[0] : '';
}

function bpr_core_get_meta_display($post_id, $meta_key) {
    return bpr_core_clean_display_value(get_post_meta($post_id, $meta_key, true));
}

function bpr_core_get_detail_groups($post_id) {
    $weight = bpr_core_get_meta_display($post_id, '_bpr_weight_kg');
    if ($weight) {
        $weight .= ' kg';
    }

    $quick = array_filter(array(
        'Breed' => bpr_core_get_breed_display($post_id),
        'Age' => bpr_core_get_age_display($post_id),
        'Gender' => bpr_core_get_meta_display($post_id, '_bpr_gender'),
        'Size' => bpr_core_get_meta_display($post_id, '_bpr_size'),
        'Weight' => $weight,
        'Color' => bpr_core_get_meta_display($post_id, '_bpr_color'),
        'Coat' => bpr_core_get_meta_display($post_id, '_bpr_coat_length'),
        'Location' => bpr_core_get_meta_display($post_id, '_bpr_location'),
    ));

    $compatibility = array_filter(array(
        'Good with Dogs' => bpr_core_get_meta_display($post_id, '_bpr_good_with_dogs'),
        'Good with Cats' => bpr_core_get_meta_display($post_id, '_bpr_good_with_cats'),
        'Good with Children' => bpr_core_get_meta_display($post_id, '_bpr_good_with_children'),
        'Additional Notes' => bpr_core_get_meta_display($post_id, '_bpr_good_with'),
    ));

    $home = array_filter(array(
        'House Trained' => bpr_core_get_meta_display($post_id, '_bpr_house_trained'),
        'Crate Trained' => bpr_core_get_meta_display($post_id, '_bpr_crate_trained'),
        'Leash Trained' => bpr_core_get_meta_display($post_id, '_bpr_leash_trained'),
        'Apartment Suitable' => bpr_core_get_meta_display($post_id, '_bpr_apartment_suitable'),
        'Can Be Left Alone' => bpr_core_get_meta_display($post_id, '_bpr_can_be_left_alone'),
        'Energy Level' => bpr_core_get_meta_display($post_id, '_bpr_energy_level'),
    ));

    $health = array_filter(array(
        'Spayed/Neutered' => bpr_core_get_meta_display($post_id, '_bpr_spayed_neutered'),
        'Vaccinations Current' => bpr_core_get_meta_display($post_id, '_bpr_vaccinations_current'),
        'Microchipped' => bpr_core_get_meta_display($post_id, '_bpr_microchipped'),
        'Dewormed' => bpr_core_get_meta_display($post_id, '_bpr_dewormed'),
        'Special Needs' => bpr_core_get_meta_display($post_id, '_bpr_special_needs'),
    ));

    return apply_filters('bpr_core_pet_detail_groups', array(
        'Quick Details' => $quick,
        'Compatibility' => $compatibility,
        'Home & Lifestyle' => $home,
        'Health & Care' => $health,
    ), $post_id);
}
