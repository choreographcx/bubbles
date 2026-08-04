<?php
if (!defined('ABSPATH')) {
    exit;
}

function bpr_core_admin_menu() {
    add_menu_page(
        __('Bubbles Pet Rescue', 'bubbles-pet-rescue-core'),
        __('Bubbles Rescue', 'bubbles-pet-rescue-core'),
        'edit_posts',
        'bubbles-pet-rescue',
        'bpr_core_dashboard_page',
        'dashicons-pets',
        5
    );

    add_submenu_page('bubbles-pet-rescue', __('Overview', 'bubbles-pet-rescue-core'), __('Overview', 'bubbles-pet-rescue-core'), 'edit_posts', 'bubbles-pet-rescue', 'bpr_core_dashboard_page');
    add_submenu_page('bubbles-pet-rescue', __('All Dogs', 'bubbles-pet-rescue-core'), __('All Dogs', 'bubbles-pet-rescue-core'), 'edit_posts', 'edit.php?post_type=dog');
    add_submenu_page('bubbles-pet-rescue', __('Add Dog', 'bubbles-pet-rescue-core'), __('Add Dog', 'bubbles-pet-rescue-core'), 'edit_posts', 'post-new.php?post_type=dog');
    add_submenu_page('bubbles-pet-rescue', __('All Cats', 'bubbles-pet-rescue-core'), __('All Cats', 'bubbles-pet-rescue-core'), 'edit_posts', 'edit.php?post_type=cat');
    add_submenu_page('bubbles-pet-rescue', __('Add Cat', 'bubbles-pet-rescue-core'), __('Add Cat', 'bubbles-pet-rescue-core'), 'edit_posts', 'post-new.php?post_type=cat');
    add_submenu_page('bubbles-pet-rescue', __('Applications', 'bubbles-pet-rescue-core'), __('Applications', 'bubbles-pet-rescue-core'), 'edit_posts', 'edit.php?post_type=application');
    add_submenu_page('bubbles-pet-rescue', __('Pet Statuses', 'bubbles-pet-rescue-core'), __('Pet Statuses', 'bubbles-pet-rescue-core'), 'manage_categories', 'edit-tags.php?taxonomy=pet_status&post_type=dog');
}
add_action('admin_menu', 'bpr_core_admin_menu');

function bpr_core_dashboard_page() {
    $dog_count = wp_count_posts('dog');
    $cat_count = wp_count_posts('cat');
    $application_count = wp_count_posts('application');
    ?>
    <div class="wrap bpr-core-dashboard">
        <h1><?php esc_html_e('Bubbles Pet Rescue', 'bubbles-pet-rescue-core'); ?></h1>
        <p><?php esc_html_e('Manage dogs, cats, photos, adoption details, and saved applications here. Pet records stay available even if the website theme changes.', 'bubbles-pet-rescue-core'); ?></p>
        <div class="bpr-core-dashboard-cards">
            <a href="<?php echo esc_url(admin_url('edit.php?post_type=dog')); ?>"><strong><?php echo esc_html((int) $dog_count->publish); ?></strong><span><?php esc_html_e('Published Dogs', 'bubbles-pet-rescue-core'); ?></span></a>
            <a href="<?php echo esc_url(admin_url('edit.php?post_type=cat')); ?>"><strong><?php echo esc_html((int) $cat_count->publish); ?></strong><span><?php esc_html_e('Published Cats', 'bubbles-pet-rescue-core'); ?></span></a>
            <a href="<?php echo esc_url(admin_url('edit.php?post_type=application')); ?>"><strong><?php echo esc_html((int) $application_count->private); ?></strong><span><?php esc_html_e('Saved Applications', 'bubbles-pet-rescue-core'); ?></span></a>
        </div>
    </div>
    <?php
}

function bpr_core_add_meta_boxes() {
    add_meta_box('bpr_core_pet_details', __('Pet Details', 'bubbles-pet-rescue-core'), 'bpr_core_render_pet_metabox', array('dog', 'cat'), 'normal', 'high');
    remove_meta_box('pet_statusdiv', 'dog', 'side');
    remove_meta_box('pet_statusdiv', 'cat', 'side');
}
add_action('add_meta_boxes', 'bpr_core_add_meta_boxes');

function bpr_core_render_select($name, $current, $options) {
    $normalized = array();
    foreach ($options as $value => $label) {
        if (is_int($value)) {
            $value = $label;
        }
        $normalized[(string) $value] = (string) $label;
    }

    $current = (string) $current;
    if ($current !== '' && !array_key_exists($current, $normalized)) {
        $normalized = array($current => $current) + $normalized;
    }

    echo '<select class="widefat" name="bpr_pet[' . esc_attr($name) . ']">';
    foreach ($normalized as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($current, $value, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
}

function bpr_core_render_tri_state($name, $current) {
    foreach (bpr_core_get_tri_state_options() as $value => $label) {
        echo '<label class="bpr-core-radio"><input type="radio" name="bpr_pet[' . esc_attr($name) . ']" value="' . esc_attr($value) . '" ' . checked($current ?: 'Unknown', $value, false) . '> ' . esc_html($label) . '</label>';
    }
}

function bpr_core_render_pet_metabox($post) {
    wp_nonce_field('bpr_core_save_pet', 'bpr_core_pet_nonce');
    $post_type = get_post_type($post);
    $get = function($key) use ($post) {
        return get_post_meta($post->ID, '_bpr_' . $key, true);
    };

    $saved_breeds = get_post_meta($post->ID, '_bpr_breeds', true);
    $saved_breeds = is_array($saved_breeds) ? $saved_breeds : array();
    $saved_tags = bpr_core_get_personality_tags($post->ID);
    $gallery_ids = bpr_core_get_gallery_ids($post->ID, false);
    $status = bpr_core_get_status_slug($post->ID);
    $status_options = bpr_core_get_status_options();
    $status_terms = get_terms(array('taxonomy' => 'pet_status', 'hide_empty' => false));
    if (!is_wp_error($status_terms)) {
        foreach ($status_terms as $status_term) {
            $status_options[$status_term->slug] = $status_term->name;
        }
    }
    $breed_options = bpr_core_get_breed_options($post_type);
    $legacy_breed = trim((string) get_post_meta($post->ID, '_bpr_breed', true));

    if (!$saved_breeds && $legacy_breed) {
        $saved_breeds = array_map('trim', explode(',', $legacy_breed));
    }
    ?>
    <div class="bpr-core-admin">
        <p class="description bpr-core-intro"><?php esc_html_e('Use the main WordPress editor above for the pet’s story. Use these structured fields for listing cards and the detailed adoption profile.', 'bubbles-pet-rescue-core'); ?></p>

        <section class="bpr-core-section">
            <h3><?php esc_html_e('Basic Information', 'bubbles-pet-rescue-core'); ?></h3>
            <div class="bpr-core-grid bpr-core-grid-3">
                <div class="bpr-core-field"><label><?php esc_html_e('Age', 'bubbles-pet-rescue-core'); ?></label><input class="widefat" type="text" name="bpr_pet[age]" value="<?php echo esc_attr($get('age')); ?>" placeholder="e.g., 1"><span class="description"><?php esc_html_e('Enter just the number (e.g. 1). It shows as “1 year old”.', 'bubbles-pet-rescue-core'); ?></span></div>
                <div class="bpr-core-field"><label><?php esc_html_e('Age Unit', 'bubbles-pet-rescue-core'); ?></label><?php bpr_core_render_select('age_unit', $get('age_unit') ?: 'years', array('years' => __('Years', 'bubbles-pet-rescue-core'), 'months' => __('Months', 'bubbles-pet-rescue-core'))); ?></div>
                <div class="bpr-core-field"><label><?php esc_html_e('Age Range', 'bubbles-pet-rescue-core'); ?></label><?php bpr_core_render_select('age_range', $get('age_range'), bpr_core_get_age_range_options()); ?></div>
                <div class="bpr-core-field"><label><?php esc_html_e('Gender', 'bubbles-pet-rescue-core'); ?></label><?php bpr_core_render_select('gender', $get('gender'), array_merge(array('' => 'Select Gender'), bpr_core_get_gender_options())); ?></div>
                <div class="bpr-core-field"><label><?php esc_html_e('Size', 'bubbles-pet-rescue-core'); ?></label><?php bpr_core_render_select('size', $get('size'), bpr_core_get_size_options()); ?></div>
                <div class="bpr-core-field"><label><?php esc_html_e('Weight (kg)', 'bubbles-pet-rescue-core'); ?></label><input class="widefat" type="number" min="0" step="0.1" name="bpr_pet[weight_kg]" value="<?php echo esc_attr($get('weight_kg')); ?>"></div>
                <div class="bpr-core-field"><label><?php esc_html_e('Color', 'bubbles-pet-rescue-core'); ?></label><input class="widefat" type="text" name="bpr_pet[color]" value="<?php echo esc_attr($get('color')); ?>"></div>
                <div class="bpr-core-field"><label><?php esc_html_e('Coat Length', 'bubbles-pet-rescue-core'); ?></label><?php bpr_core_render_select('coat_length', $get('coat_length'), array_merge(array('' => 'Select Coat Length'), bpr_core_get_coat_options())); ?></div>
                <div class="bpr-core-field"><label><?php esc_html_e('Current Location', 'bubbles-pet-rescue-core'); ?></label><input class="widefat" type="text" name="bpr_pet[location]" value="<?php echo esc_attr($get('location')); ?>" placeholder="e.g., Dubai"></div>
                <div class="bpr-core-field"><label><?php esc_html_e('Status', 'bubbles-pet-rescue-core'); ?></label><?php bpr_core_render_select('status', $status, array_merge(array('' => 'Select Status'), $status_options)); ?></div>
            </div>

            <div class="bpr-core-field bpr-core-field-full">
                <label><?php esc_html_e('Breed(s)', 'bubbles-pet-rescue-core'); ?></label>
                <p class="description"><?php esc_html_e('Select all that apply. Use the field below for a less common breed or a specific mix.', 'bubbles-pet-rescue-core'); ?></p>
                <div class="bpr-core-check-grid">
                    <?php foreach ($breed_options as $breed) : ?>
                        <label><input type="checkbox" name="bpr_breeds[]" value="<?php echo esc_attr($breed); ?>" <?php checked(in_array($breed, $saved_breeds, true)); ?>> <?php echo esc_html($breed); ?></label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="bpr-core-field"><label><?php esc_html_e('Other Breed or Mix', 'bubbles-pet-rescue-core'); ?></label><input class="widefat" type="text" name="bpr_pet[other_breed]" value="<?php echo esc_attr($get('other_breed')); ?>"></div>
        </section>

        <section class="bpr-core-section">
            <h3><?php esc_html_e('Adoption Fit', 'bubbles-pet-rescue-core'); ?></h3>
            <div class="bpr-core-grid bpr-core-grid-3">
                <?php
                $fit_fields = array(
                    'good_with_dogs' => 'Good with Dogs',
                    'good_with_cats' => 'Good with Cats',
                    'good_with_children' => 'Good with Children',
                    'house_trained' => 'House Trained',
                    'crate_trained' => 'Crate Trained',
                    'leash_trained' => 'Leash Trained',
                    'apartment_suitable' => 'Apartment Suitable',
                    'can_be_left_alone' => 'Can Be Left Alone',
                    'spayed_neutered' => 'Spayed/Neutered',
                    'vaccinations_current' => 'Vaccinations Current',
                    'microchipped' => 'Microchipped',
                    'dewormed' => 'Dewormed',
                    'special_needs' => 'Special Needs',
                );
                foreach ($fit_fields as $key => $label) :
                    ?>
                    <div class="bpr-core-field"><label><?php echo esc_html($label); ?></label><div><?php bpr_core_render_tri_state($key, $get($key)); ?></div></div>
                <?php endforeach; ?>
                <div class="bpr-core-field"><label><?php esc_html_e('Energy Level', 'bubbles-pet-rescue-core'); ?></label><?php bpr_core_render_select('energy_level', $get('energy_level'), bpr_core_get_energy_options()); ?></div>
            </div>
            <div class="bpr-core-grid bpr-core-grid-2">
                <div class="bpr-core-field"><label><?php esc_html_e('Additional Compatibility Notes', 'bubbles-pet-rescue-core'); ?></label><textarea class="widefat" rows="4" name="bpr_pet[good_with]"><?php echo esc_textarea($get('good_with')); ?></textarea></div>
                <div class="bpr-core-field"><label><?php esc_html_e('Health and Care Notes', 'bubbles-pet-rescue-core'); ?></label><textarea class="widefat" rows="4" name="bpr_pet[health]"><?php echo esc_textarea($get('health')); ?></textarea></div>
            </div>
        </section>

        <section class="bpr-core-section">
            <h3><?php esc_html_e('Personality', 'bubbles-pet-rescue-core'); ?></h3>
            <div class="bpr-core-check-grid bpr-core-check-grid-small">
                <?php foreach (bpr_core_get_personality_options() as $tag) : ?>
                    <label><input type="checkbox" name="bpr_personality_tags[]" value="<?php echo esc_attr($tag); ?>" <?php checked(in_array($tag, $saved_tags, true)); ?>> <?php echo esc_html($tag); ?></label>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="bpr-core-section">
            <h3><?php esc_html_e('Photos', 'bubbles-pet-rescue-core'); ?></h3>
            <p class="description"><?php esc_html_e('The Featured Image is used as the main listing image. Add more photos here for the profile carousel. Drag photos to reorder them.', 'bubbles-pet-rescue-core'); ?></p>
            <input type="hidden" id="bpr_pet_gallery" name="bpr_pet_gallery" value="<?php echo esc_attr(implode(',', $gallery_ids)); ?>">
            <p><button type="button" class="button button-secondary" id="bpr_pet_gallery_select"><?php esc_html_e('Select Gallery Images', 'bubbles-pet-rescue-core'); ?></button> <button type="button" class="button" id="bpr_pet_gallery_clear"><?php esc_html_e('Clear Gallery', 'bubbles-pet-rescue-core'); ?></button></p>
            <div id="bpr_pet_gallery_preview" class="bpr-core-gallery-preview">
                <?php foreach ($gallery_ids as $image_id) :
                    $thumbnail = wp_get_attachment_image_url($image_id, 'thumbnail');
                    if (!$thumbnail) { continue; }
                    ?>
                    <div class="bpr-core-gallery-item" data-id="<?php echo esc_attr($image_id); ?>"><img src="<?php echo esc_url($thumbnail); ?>" alt=""><button type="button" class="bpr-core-remove-image" aria-label="<?php esc_attr_e('Remove image', 'bubbles-pet-rescue-core'); ?>">&times;</button></div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="bpr-core-section">
            <h3><?php esc_html_e('Application Links', 'bubbles-pet-rescue-core'); ?></h3>
            <div class="bpr-core-grid bpr-core-grid-2">
                <div class="bpr-core-field"><label><?php esc_html_e('Adoption Application URL', 'bubbles-pet-rescue-core'); ?></label><input class="widefat" type="url" name="bpr_pet[application_link]" value="<?php echo esc_attr($get('application_link')); ?>" placeholder="<?php echo esc_attr(home_url('/adoption-application/')); ?>"></div>
                <div class="bpr-core-field"><label><?php esc_html_e('Foster Application URL', 'bubbles-pet-rescue-core'); ?></label><input class="widefat" type="url" name="bpr_pet[foster_link]" value="<?php echo esc_attr($get('foster_link')); ?>" placeholder="<?php echo esc_attr(home_url('/foster-application/')); ?>"></div>
            </div>
        </section>
    </div>
    <?php
}

function bpr_core_save_pet($post_id) {
    if (!isset($_POST['bpr_core_pet_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['bpr_core_pet_nonce'])), 'bpr_core_save_pet')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (wp_is_post_revision($post_id) || !current_user_can('edit_post', $post_id)) {
        return;
    }
    if (!in_array(get_post_type($post_id), array('dog', 'cat'), true)) {
        return;
    }

    $data = isset($_POST['bpr_pet']) && is_array($_POST['bpr_pet']) ? wp_unslash($_POST['bpr_pet']) : array();
    $text_fields = array(
        'age', 'age_unit', 'age_range', 'gender', 'size', 'weight_kg', 'color', 'coat_length', 'location',
        'energy_level', 'good_with', 'health', 'other_breed'
    );
    $tri_fields = array(
        'good_with_dogs', 'good_with_cats', 'good_with_children', 'house_trained', 'crate_trained',
        'leash_trained', 'apartment_suitable', 'can_be_left_alone', 'spayed_neutered',
        'vaccinations_current', 'microchipped', 'dewormed', 'special_needs'
    );

    foreach ($text_fields as $field) {
        $value = isset($data[$field]) ? sanitize_textarea_field($data[$field]) : '';
        update_post_meta($post_id, '_bpr_' . $field, $value);
    }

    foreach ($tri_fields as $field) {
        $value = isset($data[$field]) ? sanitize_text_field($data[$field]) : 'Unknown';
        if (!array_key_exists($value, bpr_core_get_tri_state_options())) {
            $value = 'Unknown';
        }
        update_post_meta($post_id, '_bpr_' . $field, $value);
    }

    foreach (array('application_link', 'foster_link') as $url_field) {
        $value = isset($data[$url_field]) ? esc_url_raw($data[$url_field]) : '';
        update_post_meta($post_id, '_bpr_' . $url_field, $value);
    }

    $allowed_breeds = bpr_core_get_breed_options(get_post_type($post_id));
    $breeds = isset($_POST['bpr_breeds']) ? (array) wp_unslash($_POST['bpr_breeds']) : array();
    $breeds = array_values(array_unique(array_intersect(array_map('sanitize_text_field', $breeds), $allowed_breeds)));
    update_post_meta($post_id, '_bpr_breeds', $breeds);

    $other_breed = isset($data['other_breed']) ? sanitize_text_field($data['other_breed']) : '';
    $breed_display = $breeds;
    if ($other_breed) {
        $breed_display[] = $other_breed;
    }
    update_post_meta($post_id, '_bpr_breed', implode(', ', array_unique(array_filter($breed_display))));

    $allowed_tags = bpr_core_get_personality_options();
    $tags = isset($_POST['bpr_personality_tags']) ? (array) wp_unslash($_POST['bpr_personality_tags']) : array();
    $tags = array_values(array_unique(array_intersect(array_map('sanitize_text_field', $tags), $allowed_tags)));
    update_post_meta($post_id, '_bpr_personality_tags', $tags);

    $gallery_raw = isset($_POST['bpr_pet_gallery']) ? sanitize_text_field(wp_unslash($_POST['bpr_pet_gallery'])) : '';
    $gallery_ids = array_values(array_unique(array_filter(array_map('absint', explode(',', $gallery_raw)))));
    update_post_meta($post_id, '_bpr_gallery', $gallery_ids);

    $status = isset($data['status']) ? sanitize_title($data['status']) : '';
    $status_exists = $status ? term_exists($status, 'pet_status') : false;
    wp_set_object_terms($post_id, $status_exists ? array($status) : array(), 'pet_status', false);
}
add_action('save_post_dog', 'bpr_core_save_pet');
add_action('save_post_cat', 'bpr_core_save_pet');

function bpr_core_admin_assets($hook) {
    global $post;
    if (!in_array($hook, array('post.php', 'post-new.php'), true) || !$post || !in_array($post->post_type, array('dog', 'cat'), true)) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('bpr-core-admin', BPR_CORE_URL . 'assets/js/pet-admin.js', array('jquery', 'jquery-ui-sortable'), BPR_CORE_VERSION, true);
    wp_enqueue_style('bpr-core-admin', BPR_CORE_URL . 'assets/css/admin.css', array(), BPR_CORE_VERSION);
}
add_action('admin_enqueue_scripts', 'bpr_core_admin_assets');

function bpr_core_dashboard_assets($hook) {
    if ($hook === 'toplevel_page_bubbles-pet-rescue') {
        wp_enqueue_style('bpr-core-admin', BPR_CORE_URL . 'assets/css/admin.css', array(), BPR_CORE_VERSION);
    }
}
add_action('admin_enqueue_scripts', 'bpr_core_dashboard_assets');
