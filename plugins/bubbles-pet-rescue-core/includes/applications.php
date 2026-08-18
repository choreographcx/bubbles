<?php
/**
 * Applications & Messages admin page.
 *
 * Lists Forminator submissions (adoption / foster / contact forms) together
 * with the legacy theme-form submissions saved as 'application' posts, in one
 * grouped, paginated view — modelled on the Chunkz & Tubz CNT Applications
 * plugin, adapted for Bubbles:
 *   - Forminator forms are auto-detected by title (no hard-coded form IDs).
 *   - "From" / pet-name columns are resolved from each form's field labels.
 *   - Per-group pagination with a 10 / 25 / 50 / All page-size chooser.
 */

if (!defined('ABSPATH')) {
    exit;
}

define('BPR_CORE_APPS_SLUG', 'bpr-applications');

function bpr_core_applications_capability() {
    return 'edit_posts';
}

function bpr_core_application_statuses() {
    return array('Pending Review', 'In Process', 'Approved', 'Denied', 'Banned');
}

/*
 * =========================
 * Forminator DB utilities
 * =========================
 */

function bpr_core_forminator_tables_exist() {
    global $wpdb;
    static $exists = null;
    if ($exists !== null) {
        return $exists;
    }

    $t1 = $wpdb->prefix . 'frmt_form_entry';
    $t2 = $wpdb->prefix . 'frmt_form_entry_meta';
    $exists = (bool) ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $t1))
        && $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $t2)));

    return $exists;
}

/**
 * All published Forminator forms, bucketed by title:
 * 'adoption' / 'foster' / 'contact' (anything else).
 */
function bpr_core_forminator_forms() {
    static $forms = null;
    if ($forms !== null) {
        return $forms;
    }

    $forms = array();
    $posts = get_posts(array(
        'post_type' => 'forminator_forms',
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby' => 'ID',
        'order' => 'ASC',
    ));

    foreach ($posts as $post) {
        $title = (string) $post->post_title;
        if (stripos($title, 'adoption') !== false) {
            $bucket = 'adoption';
        } elseif (stripos($title, 'foster') !== false) {
            $bucket = 'foster';
        } else {
            $bucket = 'contact';
        }

        $forms[] = array(
            'id' => (int) $post->ID,
            'title' => $title,
            'bucket' => $bucket,
        );
    }

    return $forms;
}

/**
 * element_id => label map, read from the stored form definition.
 *
 * Handles both storage layouts Forminator uses for the field list
 * (wrapper-nested and flat), with the form's post_content JSON as a
 * final fallback.
 */
function bpr_core_forminator_field_labels($form_id) {
    static $cache = array();
    $form_id = (int) $form_id;
    if (isset($cache[$form_id])) {
        return $cache[$form_id];
    }

    $labels = array();

    $collect = static function ($field) use (&$labels) {
        if (!is_array($field) || empty($field['element_id'])) {
            return;
        }
        $label = '';
        if (!empty($field['field_label']) && is_string($field['field_label'])) {
            $label = $field['field_label'];
        } elseif (!empty($field['label']) && is_string($field['label'])) {
            $label = $field['label'];
        } elseif (!empty($field['section_title']) && is_string($field['section_title'])) {
            $label = $field['section_title'];
        } elseif (!empty($field['placeholder']) && is_string($field['placeholder'])) {
            $label = $field['placeholder'];
        }
        $label = trim(wp_strip_all_tags(html_entity_decode($label, ENT_QUOTES, 'UTF-8')));
        if ($label !== '' && !isset($labels[(string) $field['element_id']])) {
            $labels[(string) $field['element_id']] = $label;
        }
    };

    // 1) Stored form definition: wrapper-nested or flat field lists.
    $meta = maybe_unserialize(get_post_meta($form_id, 'forminator_form_meta', true));
    if (is_array($meta) && !empty($meta['fields']) && is_array($meta['fields'])) {
        foreach ($meta['fields'] as $item) {
            if (!is_array($item)) {
                continue;
            }
            if (!empty($item['fields']) && is_array($item['fields'])) {
                foreach ($item['fields'] as $field) {
                    $collect($field);
                }
            } else {
                $collect($item);
            }
        }
    }

    // 2) Fallback: recursive walk of the form post_content JSON.
    if (!$labels) {
        $post = get_post($form_id);
        $json = $post ? json_decode((string) $post->post_content, true) : null;
        if (is_array($json)) {
            $walk = static function ($node) use (&$walk, $collect) {
                if (!is_array($node)) {
                    return;
                }
                $collect($node);
                foreach ($node as $child) {
                    $walk($child);
                }
            };
            $walk($json);
        }
    }

    $cache[$form_id] = $labels;
    return $labels;
}

/**
 * Resolve which element ids hold the applicant name / email / phone and the
 * pet name(s), by matching the form's own field labels.
 */
function bpr_core_forminator_field_roles($form_id) {
    static $cache = array();
    $form_id = (int) $form_id;
    if (isset($cache[$form_id])) {
        return $cache[$form_id];
    }

    $roles = array('name' => '', 'email' => '', 'phone' => '', 'pets' => array());

    foreach (bpr_core_forminator_field_labels($form_id) as $element_id => $label) {
        if (!$roles['name'] && preg_match('/full\s*name/i', $label) && stripos($label, 'animal') === false) {
            $roles['name'] = $element_id;
        }
        if (!$roles['email'] && preg_match('/e-?mail/i', $label)) {
            $roles['email'] = $element_id;
        }
        if (!$roles['phone'] && preg_match('/phone|mobile|whatsapp/i', $label) && stripos($label, 'emergency') === false) {
            $roles['phone'] = $element_id;
        }
        if (preg_match('/name\s+of\s+(the\s+)?(rescue|foster\s+animal|animal|dog|cat|pet)/i', $label)) {
            $roles['pets'][] = $element_id;
        }
    }

    $cache[$form_id] = $roles;
    return $roles;
}

function bpr_core_forminator_entry_value($meta, $element_id, $glue = ', ') {
    if ($element_id === '' || !array_key_exists($element_id, (array) $meta)) {
        return '';
    }
    $value = $meta[$element_id];
    if (is_array($value)) {
        // Grouped fields (e.g. Name with first/last parts) store sub-values.
        $value = implode($glue, array_filter(array_map('trim', array_map('strval', array_filter($value, 'is_scalar'))), 'strlen'));
    }
    return trim((string) $value);
}

/**
 * First non-empty value whose meta key starts with a Forminator element-id
 * prefix such as name-, email-, phone-.
 */
function bpr_core_forminator_value_by_prefix($meta, $prefix, $glue = ', ') {
    foreach ((array) $meta as $key => $value) {
        if (preg_match('/^' . preg_quote($prefix, '/') . '\d+$/', (string) $key)) {
            $found = bpr_core_forminator_entry_value($meta, (string) $key, $glue);
            if ($found !== '') {
                return $found;
            }
        }
    }
    return '';
}

function bpr_core_forminator_entry_from($meta, $form_id) {
    $roles = bpr_core_forminator_field_roles($form_id);

    // Names join grouped parts with spaces, not commas.
    $name = bpr_core_forminator_entry_value($meta, $roles['name'], ' ');
    if ($name === '') {
        $name = bpr_core_forminator_value_by_prefix($meta, 'name-', ' ');
    }

    $email = bpr_core_forminator_entry_value($meta, $roles['email']);
    if ($email === '') {
        $email = bpr_core_forminator_value_by_prefix($meta, 'email-');
    }
    if ($email === '') {
        // Last resort: any value in the entry that looks like an email.
        foreach ((array) $meta as $key => $value) {
            if (strpos((string) $key, '_') === 0) {
                continue;
            }
            foreach ((is_array($value) ? $value : array($value)) as $candidate) {
                if (is_string($candidate) && is_email(trim($candidate))) {
                    $email = trim($candidate);
                    break 2;
                }
            }
        }
    }

    $phone = bpr_core_forminator_entry_value($meta, $roles['phone']);
    if ($phone === '') {
        $phone = bpr_core_forminator_value_by_prefix($meta, 'phone-');
    }

    $contact = $email !== '' ? $email : $phone;
    if ($name === '') {
        return $contact !== '' ? $contact : '(unknown)';
    }
    return $contact !== '' ? $name . ' — ' . $contact : $name;
}

function bpr_core_forminator_entry_pets($meta, $form_id) {
    $roles = bpr_core_forminator_field_roles($form_id);
    $pets = array();
    foreach ($roles['pets'] as $element_id) {
        $value = bpr_core_forminator_entry_value($meta, $element_id);
        if ($value !== '') {
            $pets[] = $value;
        }
    }
    return implode(', ', array_unique($pets));
}

function bpr_core_forminator_unpack_meta_value($value) {
    $value = maybe_unserialize($value);
    if (is_string($value)) {
        $trimmed = trim($value);
        if ($trimmed !== '' && ($trimmed[0] === '{' || $trimmed[0] === '[')) {
            $decoded = json_decode($trimmed, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
    }
    return $value;
}

/**
 * Fetch entries + meta for a form, newest first.
 */
function bpr_core_forminator_entries($form_id, $limit = 1000) {
    global $wpdb;
    $form_id = (int) $form_id;

    if (!bpr_core_forminator_tables_exist()) {
        return array();
    }

    $entry_table = $wpdb->prefix . 'frmt_form_entry';
    $meta_table = $wpdb->prefix . 'frmt_form_entry_meta';

    $entries = $wpdb->get_results($wpdb->prepare(
        "SELECT entry_id, date_created FROM {$entry_table}
         WHERE form_id = %d AND is_spam = 0
         ORDER BY date_created DESC, entry_id DESC
         LIMIT %d",
        $form_id,
        (int) $limit
    ), ARRAY_A);

    if (!$entries) {
        return array();
    }

    $entry_ids = wp_list_pluck($entries, 'entry_id');
    $entry_ids = array_map('absint', $entry_ids);
    $placeholders = implode(',', array_fill(0, count($entry_ids), '%d'));
    $meta_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT entry_id, meta_key, meta_value FROM {$meta_table} WHERE entry_id IN ({$placeholders})",
        $entry_ids
    ), ARRAY_A);

    $meta_by_entry = array();
    foreach ((array) $meta_rows as $row) {
        $entry_id = (int) $row['entry_id'];
        $key = (string) $row['meta_key'];
        if ($key === '') {
            continue;
        }
        $meta_by_entry[$entry_id][$key] = bpr_core_forminator_unpack_meta_value($row['meta_value']);
    }

    $out = array();
    foreach ($entries as $entry) {
        $entry_id = (int) $entry['entry_id'];
        $out[] = array(
            'entry_id' => $entry_id,
            'date_created' => (string) $entry['date_created'],
            'meta' => isset($meta_by_entry[$entry_id]) ? $meta_by_entry[$entry_id] : array(),
        );
    }

    return $out;
}

function bpr_core_forminator_entry($form_id, $entry_id) {
    global $wpdb;

    if (!bpr_core_forminator_tables_exist()) {
        return null;
    }

    $entry_table = $wpdb->prefix . 'frmt_form_entry';
    $meta_table = $wpdb->prefix . 'frmt_form_entry_meta';

    $entry = $wpdb->get_row($wpdb->prepare(
        "SELECT entry_id, date_created FROM {$entry_table} WHERE form_id = %d AND entry_id = %d LIMIT 1",
        (int) $form_id,
        (int) $entry_id
    ), ARRAY_A);

    if (!$entry) {
        return null;
    }

    $meta_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT meta_key, meta_value FROM {$meta_table} WHERE entry_id = %d",
        (int) $entry_id
    ), ARRAY_A);

    $meta = array();
    foreach ((array) $meta_rows as $row) {
        $key = (string) $row['meta_key'];
        if ($key !== '') {
            $meta[$key] = bpr_core_forminator_unpack_meta_value($row['meta_value']);
        }
    }

    return array(
        'entry_id' => (int) $entry['entry_id'],
        'date_created' => (string) $entry['date_created'],
        'meta' => $meta,
    );
}

/*
 * Status + notes storage.
 *  - Forminator entries: rows in wp_frmt_form_entry_meta (_bpr_status/_bpr_notes)
 *  - application posts:  postmeta (_bpr_status/_bpr_notes)
 */

function bpr_core_forminator_entry_meta_get($entry_id, $key) {
    global $wpdb;
    $table = $wpdb->prefix . 'frmt_form_entry_meta';
    $value = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_value FROM {$table} WHERE entry_id = %d AND meta_key = %s LIMIT 1",
        (int) $entry_id,
        $key
    ));
    return is_string($value) ? $value : '';
}

function bpr_core_forminator_entry_meta_set($entry_id, $key, $value) {
    global $wpdb;
    $table = $wpdb->prefix . 'frmt_form_entry_meta';
    $now = current_time('mysql');

    $meta_id = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_id FROM {$table} WHERE entry_id = %d AND meta_key = %s LIMIT 1",
        (int) $entry_id,
        $key
    ));

    if ($meta_id) {
        $wpdb->update(
            $table,
            array('meta_value' => (string) $value, 'date_updated' => $now),
            array('meta_id' => (int) $meta_id),
            array('%s', '%s'),
            array('%d')
        );
    } else {
        $wpdb->insert(
            $table,
            array(
                'entry_id' => (int) $entry_id,
                'meta_key' => $key,
                'meta_value' => (string) $value,
                'date_created' => $now,
                'date_updated' => $now,
            ),
            array('%d', '%s', '%s', '%s', '%s')
        );
    }
}

function bpr_core_submission_status($source, $id) {
    $status = ($source === 'forminator')
        ? bpr_core_forminator_entry_meta_get($id, '_bpr_status')
        : (string) get_post_meta((int) $id, '_bpr_status', true);
    return $status !== '' ? $status : 'Pending Review';
}

function bpr_core_set_submission_status($source, $id, $status) {
    if (!in_array($status, bpr_core_application_statuses(), true)) {
        return;
    }
    if ($source === 'forminator') {
        bpr_core_forminator_entry_meta_set($id, '_bpr_status', $status);
    } else {
        update_post_meta((int) $id, '_bpr_status', $status);
    }
}

function bpr_core_submission_notes($source, $id) {
    return ($source === 'forminator')
        ? bpr_core_forminator_entry_meta_get($id, '_bpr_notes')
        : (string) get_post_meta((int) $id, '_bpr_notes', true);
}

function bpr_core_save_submission_notes($source, $id, $notes) {
    if ($source === 'forminator') {
        bpr_core_forminator_entry_meta_set($id, '_bpr_notes', $notes);
    } else {
        update_post_meta((int) $id, '_bpr_notes', $notes);
    }
}

function bpr_core_handle_save_app_notes() {
    if (!current_user_can(bpr_core_applications_capability())) {
        wp_die(esc_html__('Not allowed.', 'bubbles-pet-rescue-core'));
    }
    check_admin_referer('bpr_core_save_app_notes');

    $source = isset($_POST['source']) ? sanitize_key(wp_unslash($_POST['source'])) : '';
    $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
    $notes = isset($_POST['notes']) ? sanitize_textarea_field(wp_unslash($_POST['notes'])) : '';
    $redirect = isset($_POST['redirect']) ? esc_url_raw(wp_unslash($_POST['redirect'])) : admin_url('admin.php?page=' . BPR_CORE_APPS_SLUG);

    if (!in_array($source, array('forminator', 'application'), true) || $id <= 0) {
        wp_die(esc_html__('Invalid note target.', 'bubbles-pet-rescue-core'));
    }

    bpr_core_save_submission_notes($source, $id, $notes);
    wp_safe_redirect(add_query_arg('notes_saved', 1, $redirect));
    exit;
}
add_action('admin_post_bpr_core_save_app_notes', 'bpr_core_handle_save_app_notes');

/*
 * =========================
 * Data collection
 * =========================
 */

/**
 * Build all rows grouped as adoption / foster / contact, newest first.
 */
function bpr_core_collect_submission_groups() {
    $groups = array(
        'adoption' => array(),
        'foster' => array(),
        'contact' => array(),
    );

    // 1) Forminator entries.
    if (bpr_core_forminator_tables_exist()) {
        foreach (bpr_core_forminator_forms() as $form) {
            foreach (bpr_core_forminator_entries($form['id']) as $entry) {
                $ts = $entry['date_created'] !== '' ? (int) strtotime($entry['date_created']) : 0;
                $groups[$form['bucket']][] = array(
                    'source' => 'forminator',
                    'sort_ts' => $ts,
                    'date' => $ts ? date_i18n(get_option('date_format'), $ts) : '—',
                    'from' => bpr_core_forminator_entry_from($entry['meta'], $form['id']),
                    'pets' => bpr_core_forminator_entry_pets($entry['meta'], $form['id']),
                    'origin' => $form['title'],
                    'form_id' => $form['id'],
                    'entry_id' => $entry['entry_id'],
                );
            }
        }
    }

    // 2) Legacy theme submissions saved as 'application' posts.
    $applications = get_posts(array(
        'post_type' => 'application',
        'post_status' => array('private', 'publish', 'draft'),
        'numberposts' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
    ));

    foreach ($applications as $post) {
        $type = (string) get_post_meta($post->ID, '_bpr_application_type', true);
        $bucket = ($type === 'foster') ? 'foster' : (($type === 'adoption') ? 'adoption' : 'contact');

        $from = '';
        if (preg_match('/^Name:\s*(.+)$/mi', (string) $post->post_content, $m)) {
            $from = trim($m[1]);
        }
        if ($from === '') {
            $from = trim(preg_replace('/^(Adoption|Foster)\s+application\s+from\s+/i', '', (string) $post->post_title));
        }
        if (preg_match('/^Email:\s*(.+)$/mi', (string) $post->post_content, $m) && trim($m[1]) !== '') {
            $from .= ' — ' . trim($m[1]);
        }

        $pets = '';
        if (preg_match('/^Pet:\s*(.+)$/mi', (string) $post->post_content, $m)) {
            $pets = trim($m[1]);
        }

        $groups[$bucket][] = array(
            'source' => 'application',
            'sort_ts' => (int) get_post_time('U', true, $post),
            'date' => get_the_date('', $post),
            'from' => $from !== '' ? $from : '(unknown)',
            'pets' => $pets,
            'origin' => __('Website form', 'bubbles-pet-rescue-core'),
            'id' => (int) $post->ID,
        );
    }

    foreach ($groups as $key => $items) {
        usort($items, function ($a, $b) {
            return $b['sort_ts'] <=> $a['sort_ts'];
        });
        $groups[$key] = $items;
    }

    return $groups;
}

/*
 * =========================
 * Rendering
 * =========================
 */

function bpr_core_register_applications_page() {
    add_submenu_page(
        'bubbles-pet-rescue',
        __('Applications & Messages', 'bubbles-pet-rescue-core'),
        __('Applications & Messages', 'bubbles-pet-rescue-core'),
        bpr_core_applications_capability(),
        BPR_CORE_APPS_SLUG,
        'bpr_core_render_applications_page'
    );
}
add_action('admin_menu', 'bpr_core_register_applications_page', 11);

function bpr_core_applications_page_url($args = array()) {
    return add_query_arg($args, admin_url('admin.php?page=' . BPR_CORE_APPS_SLUG));
}

/**
 * Per-group pagination state from the query string.
 */
function bpr_core_group_pagination($group_key, $total) {
    $pp_param = 'bpr_pp_' . $group_key;
    $pg_param = 'bpr_pg_' . $group_key;

    $per_page_raw = isset($_GET[$pp_param]) ? sanitize_key(wp_unslash($_GET[$pp_param])) : '10';
    $per_page = in_array($per_page_raw, array('10', '25', '50', 'all'), true) ? $per_page_raw : '10';

    if ($per_page === 'all') {
        return array(
            'per_page' => 'all',
            'page' => 1,
            'pages' => 1,
            'offset' => 0,
            'length' => $total,
            'pp_param' => $pp_param,
            'pg_param' => $pg_param,
        );
    }

    $size = (int) $per_page;
    $pages = max(1, (int) ceil($total / $size));
    $page = isset($_GET[$pg_param]) ? max(1, absint($_GET[$pg_param])) : 1;
    $page = min($page, $pages);

    return array(
        'per_page' => $per_page,
        'page' => $page,
        'pages' => $pages,
        'offset' => ($page - 1) * $size,
        'length' => $size,
        'pp_param' => $pp_param,
        'pg_param' => $pg_param,
    );
}

function bpr_core_render_group_controls($pagination, $total) {
    $shown_from = $total ? $pagination['offset'] + 1 : 0;
    $shown_to = min($total, $pagination['offset'] + $pagination['length']);

    echo '<div style="display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;margin:6px 0 10px;">';

    // Page-size chooser.
    echo '<span>' . esc_html__('Show:', 'bubbles-pet-rescue-core') . ' ';
    $choices = array('10', '25', '50', 'all');
    $links = array();
    foreach ($choices as $choice) {
        $label = ($choice === 'all') ? __('All', 'bubbles-pet-rescue-core') : $choice;
        if ($choice === $pagination['per_page']) {
            $links[] = '<strong>' . esc_html($label) . '</strong>';
        } else {
            $url = remove_query_arg($pagination['pg_param'], bpr_core_applications_page_url(array($pagination['pp_param'] => $choice)));
            $links[] = '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
        }
    }
    echo implode(' | ', $links) . '</span>';

    // Prev / next.
    if ($pagination['pages'] > 1) {
        echo '<span>';
        if ($pagination['page'] > 1) {
            $url = bpr_core_applications_page_url(array(
                $pagination['pp_param'] => $pagination['per_page'],
                $pagination['pg_param'] => $pagination['page'] - 1,
            ));
            echo '<a href="' . esc_url($url) . '">&laquo; ' . esc_html__('Previous', 'bubbles-pet-rescue-core') . '</a> ';
        }
        printf(
            esc_html__('Page %1$d of %2$d', 'bubbles-pet-rescue-core'),
            (int) $pagination['page'],
            (int) $pagination['pages']
        );
        if ($pagination['page'] < $pagination['pages']) {
            $url = bpr_core_applications_page_url(array(
                $pagination['pp_param'] => $pagination['per_page'],
                $pagination['pg_param'] => $pagination['page'] + 1,
            ));
            echo ' <a href="' . esc_url($url) . '">' . esc_html__('Next', 'bubbles-pet-rescue-core') . ' &raquo;</a>';
        }
        echo '</span>';
    }

    printf(
        '<span style="color:#666;">' . esc_html__('Showing %1$d–%2$d of %3$d', 'bubbles-pet-rescue-core') . '</span>',
        (int) $shown_from,
        (int) $shown_to,
        (int) $total
    );

    echo '</div>';
}

function bpr_core_render_applications_page() {
    if (!current_user_can(bpr_core_applications_capability())) {
        wp_die(esc_html__('You do not have permission to access this page.', 'bubbles-pet-rescue-core'));
    }

    echo '<div class="wrap"><h1>' . esc_html__('Applications & Messages', 'bubbles-pet-rescue-core') . '</h1>';

    if (!bpr_core_forminator_tables_exist()) {
        echo '<div class="notice notice-warning"><p><strong>Bubbles Pet Rescue:</strong> '
            . esc_html__('Forminator submission tables were not found. Forminator entries will not appear until the plugin is active and forms store submissions.', 'bubbles-pet-rescue-core')
            . '</p></div>';
    }

    // Inline single view?
    if (!empty($_GET['view'])) {
        $src = isset($_GET['src']) ? sanitize_key(wp_unslash($_GET['src'])) : '';
        if ($src === 'forminator' && !empty($_GET['form']) && !empty($_GET['entry'])) {
            bpr_core_render_forminator_entry_view(absint($_GET['form']), absint($_GET['entry']));
        } elseif ($src === 'application' && !empty($_GET['id'])) {
            bpr_core_render_application_post_view(absint($_GET['id']));
        }
    }

    $groups = bpr_core_collect_submission_groups();
    $titles = array(
        'adoption' => __('Adoption Applications', 'bubbles-pet-rescue-core'),
        'foster' => __('Foster Applications', 'bubbles-pet-rescue-core'),
        'contact' => __('Contact Messages', 'bubbles-pet-rescue-core'),
    );

    foreach ($titles as $group_key => $title) {
        $items = $groups[$group_key];
        $total = count($items);

        echo '<h2 style="margin-top:1.5rem;">' . esc_html($title) . ' (' . (int) $total . ')</h2>';

        if (!$items) {
            echo '<p>' . esc_html__('No submissions found.', 'bubbles-pet-rescue-core') . '</p>';
            continue;
        }

        $pagination = bpr_core_group_pagination($group_key, $total);
        bpr_core_render_group_controls($pagination, $total);

        $page_items = array_slice($items, $pagination['offset'], $pagination['length']);

        echo '<table class="widefat striped"><thead><tr>'
            . '<th style="width:120px;">' . esc_html__('Date', 'bubbles-pet-rescue-core') . '</th>'
            . '<th style="width:300px;">' . esc_html__('From', 'bubbles-pet-rescue-core') . '</th>'
            . '<th style="width:200px;">' . esc_html__('Pet(s)', 'bubbles-pet-rescue-core') . '</th>'
            . '<th style="width:200px;">' . esc_html__('Form', 'bubbles-pet-rescue-core') . '</th>'
            . '<th style="width:130px;">' . esc_html__('Status', 'bubbles-pet-rescue-core') . '</th>'
            . '<th style="width:240px;">' . esc_html__('Notes', 'bubbles-pet-rescue-core') . '</th>'
            . '<th style="width:80px;">' . esc_html__('View', 'bubbles-pet-rescue-core') . '</th>'
            . '</tr></thead><tbody>';

        foreach ($page_items as $row) {
            if ($row['source'] === 'forminator') {
                $id = (int) $row['entry_id'];
                $view_url = bpr_core_applications_page_url(array(
                    'view' => 1,
                    'src' => 'forminator',
                    'form' => (int) $row['form_id'],
                    'entry' => $id,
                ));
            } else {
                $id = (int) $row['id'];
                $view_url = bpr_core_applications_page_url(array(
                    'view' => 1,
                    'src' => 'application',
                    'id' => $id,
                ));
            }

            $status = bpr_core_submission_status($row['source'], $id);
            $notes = bpr_core_submission_notes($row['source'], $id);
            $notes_short = ($notes !== '') ? mb_strimwidth($notes, 0, 60, '…') : '—';

            echo '<tr>'
                . '<td>' . esc_html($row['date']) . '</td>'
                . '<td>' . esc_html($row['from']) . '</td>'
                . '<td>' . esc_html($row['pets'] !== '' ? $row['pets'] : '—') . '</td>'
                . '<td>' . esc_html($row['origin']) . '</td>'
                . '<td>' . esc_html($status) . '</td>'
                . '<td>' . esc_html($notes_short) . '</td>'
                . '<td><a href="' . esc_url($view_url) . '">' . esc_html__('View', 'bubbles-pet-rescue-core') . '</a></td>'
                . '</tr>';
        }

        echo '</tbody></table>';
    }

    echo '</div>';
}

/*
 * =========================
 * Inline single views
 * =========================
 */

function bpr_core_render_status_form($source, $id, $nonce_action) {
    $status = bpr_core_submission_status($source, $id);

    echo '<form method="post" style="margin:0;display:inline-flex;align-items:center;gap:.5rem;">';
    wp_nonce_field($nonce_action);
    echo '<label><strong>' . esc_html__('Status:', 'bubbles-pet-rescue-core') . '</strong></label> ';
    echo '<select name="bpr_status">';
    foreach (bpr_core_application_statuses() as $choice) {
        printf('<option value="%s" %s>%s</option>', esc_attr($choice), selected($status, $choice, false), esc_html($choice));
    }
    echo '</select> ';
    submit_button(__('Update Status', 'bubbles-pet-rescue-core'), 'primary', '', false);
    echo '</form>';
}

function bpr_core_render_notes_box($source, $id) {
    $notes = bpr_core_submission_notes($source, $id);
    $scheme = is_ssl() ? 'https://' : 'http://';
    $current_url = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    echo '<hr style="margin:16px 0;" />';
    echo '<h3 style="margin:0 0 8px;">' . esc_html__('Notes', 'bubbles-pet-rescue-core') . '</h3>';
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin:0 0 12px;">';
    wp_nonce_field('bpr_core_save_app_notes');
    echo '<input type="hidden" name="action" value="bpr_core_save_app_notes" />';
    echo '<input type="hidden" name="source" value="' . esc_attr($source) . '" />';
    echo '<input type="hidden" name="id" value="' . esc_attr((string) $id) . '" />';
    echo '<input type="hidden" name="redirect" value="' . esc_url(remove_query_arg('notes_saved', $current_url)) . '" />';
    echo '<textarea name="notes" rows="4" style="width:100%;max-width:900px;">' . esc_textarea($notes) . '</textarea>';
    echo '<p style="margin-top:8px;"><button type="submit" class="button button-primary">' . esc_html__('Save / Update Notes', 'bubbles-pet-rescue-core') . '</button>';
    if (!empty($_GET['notes_saved'])) {
        echo ' <span style="margin-left:10px;color:#1d7f2a;font-weight:600;">' . esc_html__('Saved', 'bubbles-pet-rescue-core') . '</span>';
    }
    echo '</p></form>';
}

/**
 * If a Forminator value carries an uploaded file / signature image, return
 * its URL; '' otherwise.
 */
function bpr_core_forminator_value_file_url($value) {
    if (!is_array($value)) {
        return '';
    }
    if (!empty($value['file_url']) && is_string($value['file_url'])) {
        return $value['file_url'];
    }
    if (!empty($value['file']) && is_array($value['file']) && !empty($value['file']['file_url'])) {
        return (string) $value['file']['file_url'];
    }
    if (!empty($value['url']) && is_string($value['url'])) {
        return $value['url'];
    }
    return '';
}

function bpr_core_stringify_submission_value($value) {
    if (is_array($value)) {
        $parts = array();
        foreach ($value as $item) {
            $parts[] = (is_scalar($item) || $item === null) ? (string) $item : wp_json_encode($item);
        }
        return trim(implode(', ', array_filter($parts, static function ($part) {
            return $part !== '';
        })));
    }
    if (is_object($value)) {
        return (string) wp_json_encode($value);
    }
    if (is_bool($value)) {
        return $value ? '1' : '0';
    }
    return trim((string) $value);
}

function bpr_core_render_forminator_entry_view($form_id, $entry_id) {
    // Status update.
    if (!empty($_POST['bpr_status'])) {
        check_admin_referer('bpr_core_fm_status_' . $form_id . '_' . $entry_id);
        bpr_core_set_submission_status('forminator', $entry_id, sanitize_text_field(wp_unslash($_POST['bpr_status'])));
        echo '<div class="notice notice-success"><p>' . esc_html__('Status updated.', 'bubbles-pet-rescue-core') . '</p></div>';
    }

    $entry = bpr_core_forminator_entry($form_id, $entry_id);

    echo '<div class="postbox" style="padding:20px;margin:16px 0;">';
    $form_title = get_the_title($form_id);
    echo '<h2 style="margin-top:0;font-size:1.4em;">' . esc_html($form_title ? $form_title : __('Forminator Form', 'bubbles-pet-rescue-core')) . ' — ' . esc_html__('Entry', 'bubbles-pet-rescue-core') . ' #' . (int) $entry_id . '</h2>';

    if (!$entry) {
        echo '<p><em>' . esc_html__('Entry not found.', 'bubbles-pet-rescue-core') . '</em></p></div>';
        return;
    }

    $ts = $entry['date_created'] !== '' ? (int) strtotime($entry['date_created']) : 0;
    $date = $ts ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $ts) : '—';

    echo '<div style="display:flex;align-items:center;gap:2rem;flex-wrap:wrap;line-height:30px;font-size:1.05em;">';
    echo '<div><strong>' . esc_html__('Date:', 'bubbles-pet-rescue-core') . '</strong> ' . esc_html($date) . '</div>';
    echo '<div><strong>' . esc_html__('From:', 'bubbles-pet-rescue-core') . '</strong> ' . esc_html(bpr_core_forminator_entry_from($entry['meta'], $form_id)) . '</div>';
    bpr_core_render_status_form('forminator', $entry_id, 'bpr_core_fm_status_' . $form_id . '_' . $entry_id);
    echo '</div>';

    bpr_core_render_notes_box('forminator', $entry_id);

    $labels = bpr_core_forminator_field_labels($form_id);

    echo '<table class="widefat striped" style="margin-top:1rem;">';
    echo '<thead><tr><th style="width:340px;">' . esc_html__('Field', 'bubbles-pet-rescue-core') . '</th><th>' . esc_html__('Value', 'bubbles-pet-rescue-core') . '</th></tr></thead><tbody>';

    foreach ($entry['meta'] as $key => $value) {
        // Internal bookkeeping rows.
        if (in_array($key, array('_bpr_status', '_bpr_notes'), true) || strpos($key, '_forminator') === 0 || $key === 'forminator_user_ip') {
            continue;
        }

        $label = isset($labels[$key]) ? $labels[$key] : $key;

        // Signature / upload images.
        $file_url = bpr_core_forminator_value_file_url($value);
        if ($file_url !== '') {
            echo '<tr><td><strong>' . esc_html($label) . '</strong></td><td>'
                . '<a href="' . esc_url($file_url) . '" target="_blank" rel="noopener noreferrer">'
                . '<img src="' . esc_url($file_url) . '" alt="" style="max-width:220px;height:auto;border:1px solid #ddd;background:#fff;padding:6px;" />'
                . '</a></td></tr>';
            continue;
        }

        $text = bpr_core_stringify_submission_value($value);
        $style = ($text === '') ? ' style="color:#999;"' : '';
        echo '<tr><td><strong>' . esc_html($label) . '</strong></td><td' . $style . '>' . esc_html($text !== '' ? $text : __('(empty)', 'bubbles-pet-rescue-core')) . '</td></tr>';
    }

    echo '</tbody></table></div>';
}

function bpr_core_render_application_post_view($post_id) {
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'application') {
        return;
    }

    // Status update.
    if (!empty($_POST['bpr_status'])) {
        check_admin_referer('bpr_core_app_status_' . $post_id);
        bpr_core_set_submission_status('application', $post_id, sanitize_text_field(wp_unslash($_POST['bpr_status'])));
        echo '<div class="notice notice-success"><p>' . esc_html__('Status updated.', 'bubbles-pet-rescue-core') . '</p></div>';
    }

    echo '<div class="postbox" style="padding:20px;margin:16px 0;">';
    echo '<h2 style="margin-top:0;font-size:1.4em;">' . esc_html($post->post_title) . '</h2>';

    echo '<div style="display:flex;align-items:center;gap:2rem;flex-wrap:wrap;line-height:30px;font-size:1.05em;">';
    echo '<div><strong>' . esc_html__('Date:', 'bubbles-pet-rescue-core') . '</strong> ' . esc_html(get_the_date('', $post) . ' ' . get_the_time('', $post)) . '</div>';
    bpr_core_render_status_form('application', $post_id, 'bpr_core_app_status_' . $post_id);
    echo '</div>';

    bpr_core_render_notes_box('application', $post_id);

    echo '<div style="margin-top:1rem;white-space:pre-wrap;background:#fff;border:1px solid #e2e2e2;padding:12px 16px;max-width:900px;">'
        . esc_html((string) $post->post_content)
        . '</div>';

    echo '</div>';
}
