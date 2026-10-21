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
    $icon_url = get_stylesheet_directory_uri() . '/assets/kendokaizen-mark-black.png';
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

function kk_event_types($post_id) {
    $stored = get_post_meta($post_id, '_kk_types', true);
    if (is_array($stored)) {
        $types = $stored;
    } elseif (is_string($stored) && $stored !== '') {
        $types = preg_split('/[\r\n|,;]+/', $stored);
    } else {
        $legacy = kk_event_meta($post_id, '_kk_type', 'Kendo event');
        $types = [$legacy];
    }

    $allowed = kk_event_type_options();
    $types = array_values(array_unique(array_filter(array_map('trim', $types), static function ($type) use ($allowed) {
        return $type !== '' && (array_key_exists($type, $allowed) || $type === 'Kendo event' || $type === 'Professional certifications');
    })));
    return $types ?: ['Kendo event'];
}

function kk_event_type_label($post_id) {
    return implode(' · ', kk_event_types($post_id));
}

function kk_event_audience_options() {
    return [
        'All' => 'All',
        'Junior' => 'Junior',
        'Ladies' => 'Ladies',
        'Kyus' => 'Kyus',
    ];
}

function kk_event_audiences($post_id) {
    $stored = get_post_meta($post_id, '_kk_audiences', true);
    if (is_array($stored)) {
        $audiences = $stored;
    } elseif (is_string($stored) && $stored !== '') {
        $audiences = preg_split('/(?:\s*\+\s*)|[\r\n|,;]+/', $stored);
    } else {
        $legacy = get_post_meta($post_id, '_kk_audience', true);
        $audiences = $legacy !== '' ? preg_split('/(?:\s*\+\s*)|[\r\n|,;]+/', $legacy) : [];
    }

    $allowed = kk_event_audience_options();
    $audiences = array_values(array_unique(array_filter(array_map('trim', $audiences), static function ($audience) use ($allowed) {
        return $audience !== '' && array_key_exists($audience, $allowed);
    })));

    if ($audiences) {
        return $audiences;
    }

    // Backwards-compatible fallback for events created before the audience field existed.
    // It keeps the new filter useful without rewriting any existing event metadata.
    $level = strtolower((string) kk_event_meta($post_id, '_kk_level'));
    $title = strtolower((string) get_the_title($post_id));
    $format = strtolower((string) kk_event_meta($post_id, '_kk_competition_format'));
    $text = $title . ' ' . $level . ' ' . $format;
    $inferred = [];

    if (preg_match('/\b(junior|juniors|youth|children|child|jugend)\b/u', $text)) $inferred[] = 'Junior';
    if (preg_match('/\b(ladies|lady|women|woman|female)\b/u', $text)) $inferred[] = 'Ladies';
    if (preg_match('/\b(kyu|kyus|kyusha|ikkyu)\b/u', $text)) $inferred[] = 'Kyus';

    if (!$inferred || preg_match('/\b(all levels|open to all|kyu and dan|kyu, dan|dan grades|teams and juniors)\b/u', $text)) {
        array_unshift($inferred, 'All');
    }

    return array_values(array_unique($inferred));
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
    $types = kk_event_types($post->ID);
    $audiences = kk_event_audiences($post->ID);
    $country = kk_event_meta($post->ID, '_kk_country');
    $city = kk_event_meta($post->ID, '_kk_city');
    $venue = kk_event_meta($post->ID, '_kk_venue');
    $price = kk_event_meta($post->ID, '_kk_price');
    $price_details = kk_event_meta($post->ID, '_kk_price_details');
    $schedule = kk_event_meta($post->ID, '_kk_schedule');
    $level = kk_event_meta($post->ID, '_kk_level');
    $competition_format = kk_event_meta($post->ID, '_kk_competition_format');
    $exam_grades = kk_event_meta($post->ID, '_kk_exam_grades');
    $instructors = kk_event_meta($post->ID, '_kk_instructors');
    $registration_deadline = kk_event_meta($post->ID, '_kk_registration_deadline');
    $address = kk_event_meta($post->ID, '_kk_address');
    $registration_url = kk_event_meta($post->ID, '_kk_registration_url');
    ?>
    <div class="kk-admin-intro">
        Complete these fields to populate the list, calendar and event window automatically.
    </div>
    <div class="kk-admin-grid">
        <p class="kk-admin-field">
            <label for="kk_types"><strong>Event type(s)</strong></label>
            <select id="kk_types" name="kk_types[]" multiple size="6">
                <?php foreach (kk_event_type_options() as $value => $label) : ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php echo in_array($value, $types, true) ? 'selected' : ''; ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
            <span class="description">Select more than one when an event combines, for example, Competition + Grading.</span>
        </p>
        <p class="kk-admin-field">
            <label for="kk_audiences"><strong>Audience(s)</strong></label>
            <select id="kk_audiences" name="kk_audiences[]" multiple size="4">
                <?php foreach (kk_event_audience_options() as $value => $label) : ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php echo in_array($value, $audiences, true) ? 'selected' : ''; ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
            <span class="description">Select one or more: All, Junior, Ladies or Kyus. This powers the public audience filter.</span>
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
            <label for="kk_price"><strong>Main price summary</strong></label>
            <input id="kk_price" name="kk_price" type="text" value="<?php echo esc_attr($price); ?>" placeholder="e.g. Approx. €65 or Free">
        </p>
        <p class="kk-admin-field">
            <label for="kk_schedule"><strong>Schedule / hours</strong></label>
            <input id="kk_schedule" name="kk_schedule" type="text" value="<?php echo esc_attr($schedule); ?>" placeholder="e.g. Sat 09:00–18:00 · Sun 09:30–14:00">
        </p>
        <p class="kk-admin-field kk-admin-wide">
            <label for="kk_price_details"><strong>Price details</strong></label>
            <textarea id="kk_price_details" name="kk_price_details" rows="3" placeholder="One price option per line&#10;Full seminar — €65&#10;One day — €40&#10;Sayonara — €25"><?php echo esc_textarea($price_details); ?></textarea>
            <span class="description">Use one line per option. This can include seminar / one-day / sayonara prices.</span>
        </p>
        <p class="kk-admin-field">
            <label for="kk_level"><strong>Level / audience</strong></label>
            <input id="kk_level" name="kk_level" type="text" value="<?php echo esc_attr($level); ?>" placeholder="e.g. All levels, 2nd kyu and above">
        </p>
        <p class="kk-admin-field">
            <label for="kk_registration_deadline"><strong>Registration deadline</strong></label>
            <input id="kk_registration_deadline" name="kk_registration_deadline" type="text" value="<?php echo esc_attr($registration_deadline); ?>" placeholder="e.g. 15 Oct 2026">
        </p>
        <p class="kk-admin-field kk-admin-wide">
            <label for="kk_instructors"><strong>Instructors / guests</strong></label>
            <input id="kk_instructors" name="kk_instructors" type="text" value="<?php echo esc_attr($instructors); ?>" placeholder="Names separated by commas">
        </p>
        <p class="kk-admin-field">
            <label for="kk_competition_format"><strong>Competition format</strong></label>
            <input id="kk_competition_format" name="kk_competition_format" type="text" value="<?php echo esc_attr($competition_format); ?>" placeholder="e.g. Individual and teams · Dan and kyu categories">
        </p>
        <p class="kk-admin-field">
            <label for="kk_exam_grades"><strong>Exam grades</strong></label>
            <input id="kk_exam_grades" name="kk_exam_grades" type="text" value="<?php echo esc_attr($exam_grades); ?>" placeholder="e.g. 1st–4th Dan">
        </p>
        <p class="kk-admin-field kk-admin-wide">
            <label for="kk_address"><strong>Event address</strong></label>
            <textarea id="kk_address" name="kk_address" rows="3" placeholder="Full venue address"><?php echo esc_textarea($address); ?></textarea>
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
        'kk_schedule' => '_kk_schedule',
        'kk_level' => '_kk_level',
        'kk_instructors' => '_kk_instructors',
        'kk_registration_deadline' => '_kk_registration_deadline',
        'kk_competition_format' => '_kk_competition_format',
        'kk_exam_grades' => '_kk_exam_grades',
    ];

    foreach ($text_fields as $form_key => $meta_key) {
        $value = isset($_POST[$form_key]) ? sanitize_text_field(wp_unslash($_POST[$form_key])) : '';
        if ($value === '') {
            delete_post_meta($post_id, $meta_key);
        } else {
            update_post_meta($post_id, $meta_key, $value);
        }
    }

    $textarea_fields = [
        'kk_price_details' => '_kk_price_details',
        'kk_address' => '_kk_address',
    ];

    foreach ($textarea_fields as $form_key => $meta_key) {
        $value = isset($_POST[$form_key]) ? sanitize_textarea_field(wp_unslash($_POST[$form_key])) : '';
        if ($value === '') {
            delete_post_meta($post_id, $meta_key);
        } else {
            update_post_meta($post_id, $meta_key, $value);
        }
    }

    $posted_types = isset($_POST['kk_types']) ? (array) wp_unslash($_POST['kk_types']) : [];
    $allowed_types = kk_event_type_options();
    $types = [];
    foreach ($posted_types as $posted_type) {
        $type = sanitize_text_field($posted_type);
        if (array_key_exists($type, $allowed_types)) {
            $types[] = $type;
        }
    }
    $types = array_values(array_unique($types));
    if ($types) {
        update_post_meta($post_id, '_kk_types', $types);
        update_post_meta($post_id, '_kk_type', $types[0]);
    }

    $posted_audiences = isset($_POST['kk_audiences']) ? (array) wp_unslash($_POST['kk_audiences']) : [];
    $allowed_audiences = kk_event_audience_options();
    $audiences = [];
    foreach ($posted_audiences as $posted_audience) {
        $audience = sanitize_text_field($posted_audience);
        if (array_key_exists($audience, $allowed_audiences)) $audiences[] = $audience;
    }
    $audiences = array_values(array_unique($audiences));
    if ($audiences) {
        update_post_meta($post_id, '_kk_audiences', $audiences);
        update_post_meta($post_id, '_kk_audience', $audiences[0]);
    } else {
        delete_post_meta($post_id, '_kk_audiences');
        delete_post_meta($post_id, '_kk_audience');
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
        .kk-admin-field label, .kk-admin-field input, .kk-admin-field select, .kk-admin-field textarea { display: block; width: 100%; }
        .kk-admin-field label { margin-bottom: 7px; }
        .kk-admin-field input, .kk-admin-field select { min-height: 40px; }
        .kk-admin-field textarea { min-height: 86px; resize: vertical; }
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
        'posts_per_page' => -1,
        'suppress_filters' => true,
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
            'description' => wp_kses_post(apply_filters('the_content', $event->post_content)),
            'summary' => has_excerpt($id) ? get_the_excerpt($id) : wp_trim_words(wp_strip_all_tags($event->post_content), 24),
            'start' => $start ? gmdate('Y-m-d', strtotime($start)) : '',
            'end' => $end ? gmdate('Y-m-d', strtotime($end)) : '',
            'dateLabel' => kk_event_date_label($start, $end),
            'country' => kk_event_meta($id, '_kk_country'),
            'city' => kk_event_meta($id, '_kk_city'),
            'venue' => kk_event_meta($id, '_kk_venue'),
            'types' => kk_event_types($id),
            'type' => kk_event_type_label($id),
            'primaryType' => kk_event_types($id)[0],
            'audiences' => kk_event_audiences($id),
            'price' => kk_event_meta($id, '_kk_price', 'See organiser'),
            'priceDetails' => kk_event_meta($id, '_kk_price_details'),
            'schedule' => kk_event_meta($id, '_kk_schedule'),
            'level' => kk_event_meta($id, '_kk_level'),
            'competitionFormat' => kk_event_meta($id, '_kk_competition_format'),
            'examGrades' => kk_event_meta($id, '_kk_exam_grades'),
            'extraInformation' => kk_event_meta($id, '_kk_extra_information'),
            'instructors' => kk_event_meta($id, '_kk_instructors'),
            'registrationDeadline' => kk_event_meta($id, '_kk_registration_deadline'),
            'address' => kk_event_meta($id, '_kk_address'),
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
    // Keep the country filter useful even when the current event subset does not
    // contain every country monitored by the KendoKaizen source pipeline.
    $pipeline_countries = [
        'Austria',
        'Belgium',
        'Bulgaria',
        'Czech Republic',
        'Denmark',
        'Finland',
        'France',
        'Georgia',
        'Germany',
        'Hungary',
        'Italy',
        'Latvia',
        'Poland',
        'Portugal',
        'Serbia',
        'Slovenia',
        'Spain',
        'Sweden',
        'Switzerland',
        'United Kingdom',
    ];
    $countries = array_values(array_unique(array_merge(
        $pipeline_countries,
        array_filter(array_column($events, 'country'))
    )));
    $types = [
        'Competition' => 'Competition',
        'Seminar' => 'Seminar',
        'Grading' => 'Grading',
        'Training camp' => 'Training Camp',
        'Professional certifications' => 'Professional certifications',
    ];
    $audiences = kk_event_audience_options();
    natcasesort($countries);
    $countries = array_values($countries);

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
                    <?php foreach ($types as $type_value => $type_label) : ?>
                        <option value="<?php echo esc_attr($type_value); ?>"><?php echo esc_html($type_label); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="kk-audience-filter" aria-label="Filter by audience">
                    <option value="">Any audience</option>
                    <?php foreach ($audiences as $audience_value => $audience_label) : ?>
                        <option value="<?php echo esc_attr($audience_value); ?>"><?php echo esc_html($audience_label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <span class="kk-result-count" aria-live="polite"></span>
        </div>

        <div class="kk-panel kk-list-panel">
            <div class="kk-list">
                <?php foreach ($events as $event) : ?>
                    <article class="kk-card" data-country="<?php echo esc_attr($event['country']); ?>" data-type="<?php echo esc_attr($event['type']); ?>" data-audience="<?php echo esc_attr(implode(' | ', $event['audiences'])); ?>">
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
            <details class="kk-previous-events" hidden>
                <summary>
                    <span>Previous events</span>
                    <span class="kk-previous-count" aria-live="polite"></span>
                </summary>
                <div class="kk-previous-list"></div>
            </details>
            <p class="kk-empty" hidden>No events match these filters.</p>
        </div>

        <div class="kk-panel kk-calendar-panel" hidden>
            <div class="kk-calendar-head">
                <div class="kk-calendar-heading-group">
                    <h2 class="kk-calendar-title"></h2>
                    <div class="kk-mobile-calendar-mode" role="group" aria-label="Calendar display">
                        <button class="kk-calendar-mode-button" type="button" data-calendar-mode="month" aria-pressed="false">Month</button>
                        <button class="kk-calendar-mode-button is-active" type="button" data-calendar-mode="weekend" aria-pressed="true">Weekend</button>
                    </div>
                </div>
                <div class="kk-month-nav">
                    <button class="kk-month-button" type="button" data-month-step="-1" aria-label="Previous period">←</button>
                    <button class="kk-month-button" type="button" data-month-step="1" aria-label="Next period">→</button>
                </div>
            </div>
            <div class="kk-calendar-legend" aria-label="Event type legend">
                <span class="kk-calendar-legend-label">Legend</span>
                <div class="kk-calendar-legend-items">
                    <span class="kk-calendar-legend-item"><span class="kk-calendar-event-icon" aria-hidden="true"><svg viewBox="0 0 20 20"><path d="M6 3h8v2a4 4 0 0 1-3 3.87V11h3v2H6v-2h3V8.87A4 4 0 0 1 6 5V3Z"/><path d="M6 4H3.5A2.5 2.5 0 0 0 6 7"/><path d="M14 4h2.5A2.5 2.5 0 0 1 14 7"/></svg></span><span>Competition</span></span>
                    <span class="kk-calendar-legend-item"><span class="kk-calendar-event-icon" aria-hidden="true"><svg viewBox="0 0 20 20"><circle cx="10" cy="10" r="7"/><path d="m6.8 10.2 2.1 2.2 4.3-4.7"/></svg></span><span>Grading</span></span>
                    <span class="kk-calendar-legend-item"><span class="kk-calendar-event-icon" aria-hidden="true"><svg viewBox="0 0 20 20"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H16v11H6.5A2.5 2.5 0 0 0 4 16.5v-11Z"/><path d="M6.5 3A2.5 2.5 0 0 0 4 5.5V17h12"/><path d="M8 7h5"/><path d="M8 10h5"/></svg></span><span>Seminar</span></span>
                    <span class="kk-calendar-legend-item"><span class="kk-calendar-event-icon" aria-hidden="true"><svg viewBox="0 0 20 20"><rect x="4" y="3.5" width="12" height="9" rx="1.5"/><path d="M7 16.5 10 13l3 3.5"/><path d="M7.5 7.5h5"/></svg></span><span>Professional certifications</span></span>
                    <span class="kk-calendar-legend-item"><span class="kk-calendar-event-icon" aria-hidden="true"><svg viewBox="0 0 20 20"><path d="M3.5 15 10 4l6.5 11Z"/><path d="M10 4v11"/><path d="M6.8 9.5h6.4"/></svg></span><span>Training camp</span></span>
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
            <div class="kk-modal-image">
                <div class="kk-modal-side">
                    <div class="kk-modal-side-brand">KendoKaizen</div>
                    <div class="kk-modal-poster-frame">
                        <img class="kk-modal-poster" alt="">
                        <div class="kk-modal-placeholder" hidden aria-hidden="true">
                            <div class="kk-modal-placeholder-card">
                                <div class="kk-modal-placeholder-stripe"></div>
                                <div class="kk-modal-placeholder-header"></div>
                                <div class="kk-modal-placeholder-body">
                                    <div class="kk-modal-placeholder-title"></div>
                                    <div class="kk-modal-placeholder-type">
                                        <span class="kk-modal-placeholder-type-icon" aria-hidden="true"></span>
                                        <span class="kk-modal-placeholder-type-label"></span>
                                    </div>
                                </div>
                                <div class="kk-modal-placeholder-meta">
                                    <div class="kk-modal-placeholder-divider"></div>
                                    <div class="kk-modal-placeholder-date"></div>
                                    <div class="kk-modal-placeholder-location"></div>
                                    <div class="kk-modal-placeholder-note">Placeholder artwork while the official event poster is being sourced.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="kk-modal-map-card" hidden>
                        <div class="kk-modal-map-head">Event map</div>
                        <iframe class="kk-modal-map-frame" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Event location map"></iframe>
                        <a class="kk-modal-map-large" href="#" target="_blank" rel="noopener noreferrer">View larger map ↗</a>
                    </div>
                </div>
            </div>
            <div class="kk-modal-content">
                <div class="kk-modal-kicker"></div>
                <h2 id="kk-modal-title"></h2>
                <p class="kk-modal-location"></p>
                <div class="kk-modal-description"></div>
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

function kk_redirect_home_with_status($status, $anchor = 'contact') {
    $url = add_query_arg('kk_status', sanitize_key($status), home_url('/')) . '#' . rawurlencode($anchor);
    wp_safe_redirect($url);
    exit;
}

function kk_handle_contact_message() {
    if (
        !isset($_POST['kk_contact_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['kk_contact_nonce'])), 'kk_contact_message') ||
        !empty($_POST['kk_website'])
    ) {
        kk_redirect_home_with_status('error');
    }

    $name = isset($_POST['kk_name']) ? sanitize_text_field(wp_unslash($_POST['kk_name'])) : '';
    $email = isset($_POST['kk_email']) ? sanitize_email(wp_unslash($_POST['kk_email'])) : '';
    $message = isset($_POST['kk_message']) ? sanitize_textarea_field(wp_unslash($_POST['kk_message'])) : '';

    if ($name === '' || !is_email($email) || $message === '') {
        kk_redirect_home_with_status('error');
    }

    $to = get_option('admin_email');
    $subject = sprintf('KendoKaizen contact — %s', $name);
    $body = "Name: {$name}\nEmail: {$email}\n\n{$message}";
    $headers = ['Reply-To: ' . $name . ' <' . $email . '>'];

    if (!wp_mail($to, $subject, $body, $headers)) {
        kk_redirect_home_with_status('error');
    }

    kk_redirect_home_with_status('message-sent');
}
add_action('admin_post_nopriv_kk_contact_message', 'kk_handle_contact_message');
add_action('admin_post_kk_contact_message', 'kk_handle_contact_message');

function kk_handle_event_submission() {
    if (
        !isset($_POST['kk_event_submit_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['kk_event_submit_nonce'])), 'kk_submit_event') ||
        !empty($_POST['kk_website'])
    ) {
        kk_redirect_home_with_status('error');
    }

    $title = isset($_POST['kk_event_name']) ? sanitize_text_field(wp_unslash($_POST['kk_event_name'])) : '';
    $type = isset($_POST['kk_event_type']) ? sanitize_text_field(wp_unslash($_POST['kk_event_type'])) : '';
    $country = isset($_POST['kk_country']) ? sanitize_text_field(wp_unslash($_POST['kk_country'])) : '';
    $city = isset($_POST['kk_city']) ? sanitize_text_field(wp_unslash($_POST['kk_city'])) : '';
    $venue = isset($_POST['kk_venue']) ? sanitize_text_field(wp_unslash($_POST['kk_venue'])) : '';
    $start = isset($_POST['kk_start_date']) ? sanitize_text_field(wp_unslash($_POST['kk_start_date'])) : '';
    $end = isset($_POST['kk_end_date']) ? sanitize_text_field(wp_unslash($_POST['kk_end_date'])) : '';
    $organiser = isset($_POST['kk_organiser']) ? sanitize_text_field(wp_unslash($_POST['kk_organiser'])) : '';
    $price = isset($_POST['kk_price']) ? sanitize_text_field(wp_unslash($_POST['kk_price'])) : '';
    $event_url = isset($_POST['kk_event_url']) ? esc_url_raw(wp_unslash($_POST['kk_event_url'])) : '';
    $submitter_email = isset($_POST['kk_submitter_email']) ? sanitize_email(wp_unslash($_POST['kk_submitter_email'])) : '';
    $notes = isset($_POST['kk_event_notes']) ? sanitize_textarea_field(wp_unslash($_POST['kk_event_notes'])) : '';

    $valid_date = static function ($date) {
        $parsed = DateTime::createFromFormat('Y-m-d', $date);
        return $parsed && $parsed->format('Y-m-d') === $date;
    };

    if (
        $title === '' ||
        !array_key_exists($type, kk_event_type_options()) ||
        $country === '' ||
        $city === '' ||
        !$valid_date($start) ||
        ($end !== '' && !$valid_date($end)) ||
        ($end !== '' && $end < $start) ||
        !is_email($submitter_email)
    ) {
        kk_redirect_home_with_status('error');
    }

    if ($end === '') {
        $end = $start;
    }

    $content_parts = [];
    if ($notes !== '') {
        $content_parts[] = $notes;
    }
    if ($organiser !== '') {
        $content_parts[] = 'Organiser: ' . $organiser;
    }
    $content_parts[] = 'Submitted via KendoKaizen by: ' . $submitter_email;

    $post_id = wp_insert_post([
        'post_type' => 'tribe_events',
        'post_status' => 'pending',
        'post_title' => $title,
        'post_content' => implode("\n\n", $content_parts),
    ], true);

    if (is_wp_error($post_id)) {
        kk_redirect_home_with_status('error');
    }

    update_post_meta($post_id, '_kk_type', $type);
    update_post_meta($post_id, '_kk_types', [$type]);
    update_post_meta($post_id, '_kk_country', $country);
    update_post_meta($post_id, '_kk_city', $city);
    if ($venue !== '') update_post_meta($post_id, '_kk_venue', $venue);
    if ($price !== '') update_post_meta($post_id, '_kk_price', $price);
    if ($organiser !== '') update_post_meta($post_id, '_kk_organiser', $organiser);
    if ($event_url !== '') update_post_meta($post_id, '_kk_registration_url', $event_url);
    update_post_meta($post_id, '_kk_submitter_email', $submitter_email);
    update_post_meta($post_id, '_EventStartDate', $start . ' 00:00:00');
    update_post_meta($post_id, '_EventEndDate', $end . ' 23:59:59');
    update_post_meta($post_id, '_EventStartDateUTC', $start . ' 00:00:00');
    update_post_meta($post_id, '_EventEndDateUTC', $end . ' 23:59:59');
    update_post_meta($post_id, '_EventAllDay', 'yes');

    if (!empty($_FILES['kk_event_poster']['name']) && isset($_FILES['kk_event_poster']['tmp_name'])) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attachment_id = media_handle_upload('kk_event_poster', $post_id);
        if (!is_wp_error($attachment_id)) {
            set_post_thumbnail($post_id, $attachment_id);
        }
    }

    $admin_email = get_option('admin_email');
    wp_mail(
        $admin_email,
        'New KendoKaizen event submission — ' . $title,
        "A new event has been submitted and is waiting for review.\n\n{$title}\n{$city}, {$country}\n{$start} — {$end}\n\nReview it in WordPress under Events."
    );

    kk_redirect_home_with_status('event-submitted');
}
add_action('admin_post_nopriv_kk_submit_event', 'kk_handle_event_submission');
add_action('admin_post_kk_submit_event', 'kk_handle_event_submission');
