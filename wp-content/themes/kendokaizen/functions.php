<?php

if (!defined('ABSPATH')) {
    exit;
}

function kk_enqueue_assets() {
    wp_enqueue_style('kadence-parent', get_template_directory_uri() . '/style.css', [], wp_get_theme('kadence')->get('Version'));
    wp_enqueue_style('kendokaizen', get_stylesheet_uri(), ['kadence-parent'], wp_get_theme()->get('Version'));

    if (is_front_page()) {
        wp_enqueue_script('kendokaizen-app', get_stylesheet_directory_uri() . '/app.js', [], wp_get_theme()->get('Version'), true);
    }
}
add_action('wp_enqueue_scripts', 'kk_enqueue_assets');

function kk_brand_icons() {
    $icon_url = get_stylesheet_directory_uri() . '/assets/kendokaizen-mark.png';
    echo '<link rel="icon" type="image/png" sizes="512x512" href="' . esc_url($icon_url) . '">' . "\n";
    echo '<link rel="apple-touch-icon" href="' . esc_url($icon_url) . '">' . "\n";
}
add_action('wp_head', 'kk_brand_icons');
add_action('admin_head', 'kk_brand_icons');

function kk_event_type_options() {
    return [
        'Seminar' => 'Seminar',
        'Course' => 'Course',
        'Competition' => 'Competition',
        'Training camp' => 'Training camp',
        'Grading' => 'Grading',
        'Other' => 'Other',
    ];
}

function kk_add_event_admin_boxes() {
    add_meta_box(
        'kk-event-details',
        'KendoKaizen event details',
        'kk_render_event_details_box',
        'tribe_events',
        'normal',
        'high'
    );

    add_meta_box(
        'kk-event-checklist',
        'Publishing checklist',
        'kk_render_event_checklist_box',
        'tribe_events',
        'side',
        'high'
    );
}
add_action('add_meta_boxes_tribe_events', 'kk_add_event_admin_boxes');

function kk_render_event_details_box($post) {
    wp_nonce_field('kk_save_event_details', 'kk_event_details_nonce');
    $type = kk_event_meta($post->ID, '_kk_type', 'Seminar');
    $country = kk_event_meta($post->ID, '_kk_country');
    $city = kk_event_meta($post->ID, '_kk_city');
    $venue = kk_event_meta($post->ID, '_kk_venue');
    $price = kk_event_meta($post->ID, '_kk_price');
    $level = kk_event_meta($post->ID, '_kk_level');
    $instructors = kk_event_meta($post->ID, '_kk_instructors');
    $registration_url = kk_event_meta($post->ID, '_kk_registration_url');
    ?>
    <div class="kk-admin-intro">
        Complete these fields to populate the list, calendar and event window automatically.
    </div>
    <div class="kk-admin-grid">
        <p class="kk-admin-field">
            <label for="kk_type"><strong>Event type</strong></label>
            <select id="kk_type" name="kk_type">
                <?php foreach (kk_event_type_options() as $value => $label) : ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php selected($type, $value); ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p class="kk-admin-field">
            <label for="kk_country"><strong>Country</strong></label>
            <input id="kk_country" name="kk_country" type="text" value="<?php echo esc_attr($country); ?>" placeholder="e.g. Spain">
        </p>
        <p class="kk-admin-field">
            <label for="kk_city"><strong>City</strong></label>
            <input id="kk_city" name="kk_city" type="text" value="<?php echo esc_attr($city); ?>" placeholder="e.g. Madrid">
        </p>
        <p class="kk-admin-field">
            <label for="kk_venue"><strong>Venue</strong></label>
            <input id="kk_venue" name="kk_venue" type="text" value="<?php echo esc_attr($venue); ?>" placeholder="e.g. Municipal Sports Hall">
        </p>
        <p class="kk-admin-field">
            <label for="kk_price"><strong>Approximate price</strong></label>
            <input id="kk_price" name="kk_price" type="text" value="<?php echo esc_attr($price); ?>" placeholder="e.g. Approx. €65 or Free">
        </p>
        <p class="kk-admin-field">
            <label for="kk_level"><strong>Level</strong></label>
            <input id="kk_level" name="kk_level" type="text" value="<?php echo esc_attr($level); ?>" placeholder="e.g. All levels">
        </p>
        <p class="kk-admin-field kk-admin-wide">
            <label for="kk_instructors"><strong>Instructors / guests</strong></label>
            <input id="kk_instructors" name="kk_instructors" type="text" value="<?php echo esc_attr($instructors); ?>" placeholder="Names separated by commas">
        </p>
        <p class="kk-admin-field kk-admin-wide">
            <label for="kk_registration_url"><strong>Official page or registration link</strong></label>
            <input id="kk_registration_url" name="kk_registration_url" type="url" value="<?php echo esc_attr($registration_url); ?>" placeholder="https://…">
            <span class="description">This link opens from the event details window in a new tab.</span>
        </p>
    </div>
    <?php
}

function kk_render_event_checklist_box() {
    ?>
    <ol class="kk-admin-checklist">
        <li>Add the event title.</li>
        <li>Set its start and end dates.</li>
        <li>Write the full description.</li>
        <li>Complete the KendoKaizen details.</li>
        <li>Add the poster as the featured image.</li>
        <li>Preview, then publish.</li>
    </ol>
    <p><strong>Poster tip:</strong> use a vertical image where possible. JPG, PNG and WebP are supported.</p>
    <?php
}

function kk_save_event_details($post_id) {
    if (
        !isset($_POST['kk_event_details_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['kk_event_details_nonce'])), 'kk_save_event_details') ||
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
        wp_is_post_revision($post_id) ||
        !current_user_can('edit_post', $post_id)
    ) {
        return;
    }

    $text_fields = [
        'kk_country' => '_kk_country',
        'kk_city' => '_kk_city',
        'kk_venue' => '_kk_venue',
        'kk_price' => '_kk_price',
        'kk_level' => '_kk_level',
        'kk_instructors' => '_kk_instructors',
    ];

    foreach ($text_fields as $form_key => $meta_key) {
        $value = isset($_POST[$form_key]) ? sanitize_text_field(wp_unslash($_POST[$form_key])) : '';
        if ($value === '') {
            delete_post_meta($post_id, $meta_key);
        } else {
            update_post_meta($post_id, $meta_key, $value);
        }
    }

    $type = isset($_POST['kk_type']) ? sanitize_text_field(wp_unslash($_POST['kk_type'])) : '';
    if (array_key_exists($type, kk_event_type_options())) {
        update_post_meta($post_id, '_kk_type', $type);
    }

    $registration_url = isset($_POST['kk_registration_url']) ? esc_url_raw(wp_unslash($_POST['kk_registration_url'])) : '';
    if ($registration_url === '') {
        delete_post_meta($post_id, '_kk_registration_url');
    } else {
        update_post_meta($post_id, '_kk_registration_url', $registration_url);
    }
}
add_action('save_post_tribe_events', 'kk_save_event_details');

function kk_event_admin_styles() {
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'tribe_events') {
        return;
    }
    ?>
    <style>
        #kk-event-details .inside { margin: 0; padding: 0; }
        .kk-admin-intro { padding: 14px 16px; border-bottom: 1px solid #dcdcde; background: #fff7f2; }
        .kk-admin-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 4px 20px; padding: 10px 16px 16px; }
        .kk-admin-field label, .kk-admin-field input, .kk-admin-field select { display: block; width: 100%; }
        .kk-admin-field label { margin-bottom: 7px; }
        .kk-admin-field input, .kk-admin-field select { min-height: 40px; }
        .kk-admin-wide { grid-column: 1 / -1; }
        .kk-admin-field .description { display: block; margin-top: 6px; }
        .kk-admin-checklist { margin: 4px 0 12px 20px; }
        .kk-admin-checklist li { margin-bottom: 7px; }
        @media (max-width: 782px) { .kk-admin-grid { grid-template-columns: 1fr; } .kk-admin-wide { grid-column: auto; } }
    </style>
    <?php
}
add_action('admin_head-post.php', 'kk_event_admin_styles');
add_action('admin_head-post-new.php', 'kk_event_admin_styles');

function kk_event_meta($post_id, $key, $fallback = '') {
    $value = get_post_meta($post_id, $key, true);
    return $value !== '' ? $value : $fallback;
}

function kk_event_date_label($start, $end) {
    $start_ts = strtotime($start);
    $end_ts = strtotime($end);
    if (!$start_ts) {
        return '';
    }
    if (!$end_ts || gmdate('Y-m-d', $start_ts) === gmdate('Y-m-d', $end_ts)) {
        return wp_date('j M Y', $start_ts);
    }
    if (gmdate('Y-m', $start_ts) === gmdate('Y-m', $end_ts)) {
        return wp_date('j', $start_ts) . '–' . wp_date('j M Y', $end_ts);
    }
    return wp_date('j M', $start_ts) . ' – ' . wp_date('j M Y', $end_ts);
}

function kk_collect_events() {
    $query = new WP_Query([
        'post_type' => 'tribe_events',
        'post_status' => 'publish',
        'posts_per_page' => 100,
        'meta_key' => '_EventStartDate',
        'orderby' => 'meta_value',
        'order' => 'ASC',
    ]);

    $events = [];
    foreach ($query->posts as $event) {
        $id = $event->ID;
        $start = kk_event_meta($id, '_EventStartDate');
        $end = kk_event_meta($id, '_EventEndDate', $start);
        $events[] = [
            'id' => $id,
            'slug' => $event->post_name,
            'title' => get_the_title($id),
            'description' => wp_strip_all_tags(apply_filters('the_content', $event->post_content)),
            'summary' => has_excerpt($id) ? get_the_excerpt($id) : wp_trim_words(wp_strip_all_tags($event->post_content), 24),
            'start' => $start ? gmdate('Y-m-d', strtotime($start)) : '',
            'end' => $end ? gmdate('Y-m-d', strtotime($end)) : '',
            'dateLabel' => kk_event_date_label($start, $end),
            'country' => kk_event_meta($id, '_kk_country'),
            'city' => kk_event_meta($id, '_kk_city'),
            'venue' => kk_event_meta($id, '_kk_venue'),
            'type' => kk_event_meta($id, '_kk_type', 'Kendo event'),
            'price' => kk_event_meta($id, '_kk_price', 'See organiser'),
            'level' => kk_event_meta($id, '_kk_level', 'All levels'),
            'instructors' => kk_event_meta($id, '_kk_instructors', 'To be confirmed'),
            'poster' => get_the_post_thumbnail_url($id, 'large') ?: '',
            'url' => get_permalink($id),
            'registration' => kk_event_meta($id, '_kk_registration_url', get_permalink($id)),
        ];
    }
    wp_reset_postdata();
    return $events;
}

function kk_events_shortcode() {
    $events = kk_collect_events();
    $countries = array_values(array_unique(array_filter(array_column($events, 'country'))));
    $types = array_values(array_unique(array_filter(array_column($events, 'type'))));
    sort($countries);
    sort($types);

    ob_start();
    ?>
    <section class="kk-app" aria-label="European kendo events">
        <div class="kk-toolbar">
            <div class="kk-view-switch" aria-label="Choose event view">
                <button class="kk-view-button is-active" type="button" data-view="list">List</button>
                <button class="kk-view-button" type="button" data-view="calendar">Calendar</button>
            </div>
            <div class="kk-filters">
                <select id="kk-country-filter" aria-label="Filter by country">
                    <option value="">All countries</option>
                    <?php foreach ($countries as $country) : ?>
                        <option value="<?php echo esc_attr($country); ?>"><?php echo esc_html($country); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="kk-type-filter" aria-label="Filter by event type">
                    <option value="">All event types</option>
                    <?php foreach ($types as $type) : ?>
                        <option value="<?php echo esc_attr($type); ?>"><?php echo esc_html($type); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <span class="kk-result-count" aria-live="polite"></span>
        </div>

        <div class="kk-panel kk-list-panel">
            <div class="kk-list">
                <?php foreach ($events as $event) : ?>
                    <article class="kk-card" data-country="<?php echo esc_attr($event['country']); ?>" data-type="<?php echo esc_attr($event['type']); ?>">
                        <div class="kk-poster">
                            <?php if ($event['poster']) : ?>
                                <img src="<?php echo esc_url($event['poster']); ?>" alt="Poster for <?php echo esc_attr($event['title']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="kk-card-main">
                            <div class="kk-card-topline">
                                <span class="kk-type"><?php echo esc_html($event['type']); ?></span>
                                <span><?php echo esc_html($event['dateLabel']); ?></span>
                            </div>
                            <h2><?php echo esc_html($event['title']); ?></h2>
                            <p class="kk-card-location"><?php echo esc_html($event['city'] . ', ' . $event['country']); ?></p>
                            <p class="kk-card-summary"><?php echo esc_html($event['summary']); ?></p>
                        </div>
                        <div class="kk-card-side">
                            <span class="kk-price"><?php echo esc_html($event['price']); ?></span>
                            <button class="kk-event-button" type="button" data-event-id="<?php echo esc_attr($event['id']); ?>">View details</button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <p class="kk-empty" hidden>No events match these filters.</p>
        </div>

        <div class="kk-panel kk-calendar-panel" hidden>
            <div class="kk-calendar-head">
                <h2 class="kk-calendar-title"></h2>
                <div class="kk-month-nav">
                    <button class="kk-month-button" type="button" data-month-step="-1" aria-label="Previous month">←</button>
                    <button class="kk-month-button" type="button" data-month-step="1" aria-label="Next month">→</button>
                </div>
            </div>
            <div class="kk-calendar-scroll">
                <div class="kk-calendar-grid" role="grid"></div>
            </div>
        </div>

        <script type="application/json" id="kk-event-data"><?php echo wp_json_encode($events, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
    </section>

    <div class="kk-modal" hidden aria-hidden="true">
        <div class="kk-modal-backdrop" data-close-modal></div>
        <article class="kk-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="kk-modal-title">
            <button class="kk-close" type="button" data-close-modal aria-label="Close event details">×</button>
            <div class="kk-modal-image"><img alt=""></div>
            <div class="kk-modal-content">
                <div class="kk-modal-kicker"></div>
                <h2 id="kk-modal-title"></h2>
                <p class="kk-modal-location"></p>
                <p class="kk-modal-description"></p>
                <div class="kk-modal-meta"></div>
                <div class="kk-modal-actions">
                    <a class="kk-modal-link" href="#" target="_blank" rel="noopener noreferrer">Event page & registration</a>
                    <div class="kk-share">
                        <button class="kk-share-button" type="button" aria-expanded="false" aria-controls="kk-share-menu">Share event</button>
                        <div class="kk-share-menu" id="kk-share-menu" hidden>
                            <a data-share-channel="whatsapp" href="#" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                            <a data-share-channel="telegram" href="#" target="_blank" rel="noopener noreferrer">Telegram</a>
                            <a data-share-channel="email" href="#">Email</a>
                            <button type="button" data-share-copy>Copy link</button>
                            <p class="kk-share-status" aria-live="polite"></p>
                        </div>
                    </div>
                </div>
            </div>
        </article>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('kendokaizen_events', 'kk_events_shortcode');
