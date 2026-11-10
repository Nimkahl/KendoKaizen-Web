<?php
/**
 * Plugin Name: KendoKaizen Event Importer
 * Description: Imports Approved rows from the KendoKaizen Excel pipeline into The Events Calendar.
 * Version: 2.4.0
 * Author: KendoKaizen
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

const KK_IMPORTER_RESULT_OPTION = 'kk_event_importer_last_result';

function kk_importer_column_index($letters) {
    $letters = strtoupper($letters);
    $index = 0;
    for ($i = 0, $len = strlen($letters); $i < $len; $i++) {
        $index = ($index * 26) + (ord($letters[$i]) - 64);
    }
    return $index - 1;
}

function kk_importer_xml_decode($value) {
    return html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_XML1, 'UTF-8');
}

function kk_importer_xml_attrs($tag) {
    $attrs = array();
    if (preg_match_all('/([A-Za-z_:][A-Za-z0-9_.:-]*)="([^"]*)"/', $tag, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) $attrs[$match[1]] = kk_importer_xml_decode($match[2]);
    }
    return $attrs;
}

function kk_importer_xml_text_runs($xml) {
    $text = '';
    if (preg_match_all('/<(?:[A-Za-z0-9_]+:)?t\b[^>]*>(.*?)<\/(?:[A-Za-z0-9_]+:)?t>/s', (string) $xml, $matches)) {
        foreach ($matches[1] as $part) $text .= kk_importer_xml_decode($part);
    }
    return $text;
}

function kk_importer_open_excel_archive($path) {
    if (!class_exists('PharData')) {
        return new WP_Error('kk_phar_missing', 'PHP PharData is required to read the Excel workbook.');
    }
    $temporary = trailingslashit(sys_get_temp_dir()) . 'kendokaizen-' . wp_generate_uuid4() . '.zip';
    if (!@copy($path, $temporary)) {
        return new WP_Error('kk_excel_copy', 'The uploaded workbook could not be prepared for reading.');
    }
    try {
        $archive = new PharData($temporary);
    } catch (Exception $e) {
        @unlink($temporary);
        return new WP_Error('kk_excel_open', 'The uploaded Excel workbook could not be opened: ' . $e->getMessage());
    }
    return array($archive, $temporary);
}

function kk_importer_archive_get($archive, $entry) {
    try {
        if (!isset($archive[$entry])) return false;
        return $archive[$entry]->getContent();
    } catch (Exception $e) {
        return false;
    }
}

function kk_importer_parse_excel($path) {
    $opened = kk_importer_open_excel_archive($path);
    if (is_wp_error($opened)) return $opened;
    list($archive, $temporary) = $opened;

    $shared = array();
    $shared_xml = kk_importer_archive_get($archive, 'xl/sharedStrings.xml');
    // Preserve *all* shared-string indexes, including empty self-closing <si />
    // entries. Skipping those entries shifts every later string to the wrong
    // Excel column, which was the cause of the Lidstone structured-field mix-up.
    if ($shared_xml !== false && preg_match_all('/<(?:[A-Za-z0-9_]+:)?si\b[^>]*(?:\/>|>(.*?)<\/(?:[A-Za-z0-9_]+:)?si>)/s', $shared_xml, $string_matches, PREG_SET_ORDER)) {
        foreach ($string_matches as $match) {
            $shared[] = isset($match[1]) ? kk_importer_xml_text_runs($match[1]) : '';
        }
    }

    $workbook_xml = kk_importer_archive_get($archive, 'xl/workbook.xml');
    $rels_xml = kk_importer_archive_get($archive, 'xl/_rels/workbook.xml.rels');
    if ($workbook_xml === false || $rels_xml === false) {
        unset($archive); @unlink($temporary);
        return new WP_Error('kk_excel_structure', 'The workbook structure is incomplete.');
    }

    $relationship_targets = array();
    if (preg_match_all('/<Relationship\b[^>]*\/?>/s', $rels_xml, $rel_matches)) {
        foreach ($rel_matches[0] as $rel_tag) {
            $attrs = kk_importer_xml_attrs($rel_tag);
            if (!empty($attrs['Id']) && !empty($attrs['Target'])) $relationship_targets[$attrs['Id']] = $attrs['Target'];
        }
    }

    $sheet_path = '';
    if (preg_match_all('/<(?:[A-Za-z0-9_]+:)?sheet\b[^>]*\/?>/s', $workbook_xml, $sheet_matches)) {
        foreach ($sheet_matches[0] as $sheet_tag) {
            $attrs = kk_importer_xml_attrs($sheet_tag);
            if (strcasecmp($attrs['name'] ?? '', 'Events') !== 0) continue;
            $rid = $attrs['r:id'] ?? '';
            if ($rid && isset($relationship_targets[$rid])) {
                $target = ltrim($relationship_targets[$rid], '/');
                $sheet_path = strpos($target, 'xl/') === 0 ? $target : 'xl/' . $target;
            }
            break;
        }
    }

    if ($sheet_path === '') {
        unset($archive); @unlink($temporary);
        return new WP_Error('kk_events_sheet', 'No sheet named “Events” was found.');
    }

    $sheet_xml = kk_importer_archive_get($archive, $sheet_path);
    unset($archive); @unlink($temporary);
    if ($sheet_xml === false) return new WP_Error('kk_events_sheet_read', 'The Events sheet could not be read.');

    $rows = array();
    if (preg_match_all('/<(?:[A-Za-z0-9_]+:)?row\b[^>]*>(.*?)<\/(?:[A-Za-z0-9_]+:)?row>/s', $sheet_xml, $row_matches)) {
        foreach ($row_matches[1] as $row_xml) {
            $values = array();
            // Match both populated cells (<c>...</c>) and empty self-closing
            // cells (<c />) as separate cells. Treating a self-closing cell as
            // an opening tag makes the regex swallow the next populated cell,
            // shifting values into the wrong headers.
            if (preg_match_all('/<(?:[A-Za-z0-9_]+:)?c\b([^>]*?)(?:\/>|>(.*?)<\/(?:[A-Za-z0-9_]+:)?c>)/s', $row_xml, $cell_matches, PREG_SET_ORDER)) {
                foreach ($cell_matches as $cell_match) {
                    $attrs = kk_importer_xml_attrs('<c ' . $cell_match[1] . '>');
                    $ref = $attrs['r'] ?? '';
                    if (!preg_match('/^([A-Z]+)\d+$/i', $ref, $reference_match)) continue;
                    $column = kk_importer_column_index($reference_match[1]);
                    $type = $attrs['t'] ?? '';
                    $cell_xml = $cell_match[2] ?? '';
                    $raw = '';
                    if (preg_match('/<(?:[A-Za-z0-9_]+:)?v\b[^>]*>(.*?)<\/(?:[A-Za-z0-9_]+:)?v>/s', $cell_xml, $value_match)) $raw = kk_importer_xml_decode($value_match[1]);

                    if ($type === 's') {
                        $value = isset($shared[(int) $raw]) ? $shared[(int) $raw] : '';
                    } elseif ($type === 'inlineStr') {
                        $value = kk_importer_xml_text_runs($cell_xml);
                    } elseif ($type === 'b') {
                        $value = $raw === '1' ? '1' : '0';
                    } elseif ($type === 'str' || $type === 'd') {
                        $value = $raw;
                    } else {
                        $value = $raw;
                    }
                    $values[$column] = $value;
                }
            }
            if ($values) $rows[] = $values;
        }
    }

    $header_row_index = null;
    $headers = array();
    foreach ($rows as $i => $row) {
        $candidate = array_map('trim', $row);
        if (in_array('event_id', $candidate, true) && in_array('status', $candidate, true) && in_array('title', $candidate, true)) {
            $header_row_index = $i;
            foreach ($row as $column => $header) $headers[$column] = trim((string) $header);
            break;
        }
    }
    if ($header_row_index === null) return new WP_Error('kk_headers', 'The Events sheet header row could not be identified.');

    $records = array();
    for ($i = $header_row_index + 1, $count = count($rows); $i < $count; $i++) {
        $record = array();
        foreach ($headers as $column => $header) {
            if ($header === '') continue;
            $record[$header] = isset($rows[$i][$column]) ? trim((string) $rows[$i][$column]) : '';
        }
        if (!empty($record['event_id']) || !empty($record['title'])) $records[] = $record;
    }
    return $records;
}

function kk_importer_excel_date($value) {
    if ($value === '') return '';
    if (is_numeric($value)) {
        $serial = (float) $value;
        $days = (int) floor($serial);
        $timestamp = strtotime('1899-12-30 +' . $days . ' days');
        return $timestamp ? gmdate('Y-m-d', $timestamp) : '';
    }
    $timestamp = strtotime($value);
    return $timestamp ? gmdate('Y-m-d', $timestamp) : '';
}

function kk_importer_excel_time($value, $fallback) {
    if ($value === '') return $fallback;
    if (is_numeric($value)) {
        $fraction = (float) $value - floor((float) $value);
        $seconds = (int) round($fraction * 86400);
        if ($seconds >= 86400) $seconds = 86399;
        return sprintf('%02d:%02d:%02d', floor($seconds / 3600), floor(($seconds % 3600) / 60), $seconds % 60);
    }
    $timestamp = strtotime($value);
    return $timestamp ? date('H:i:s', $timestamp) : $fallback;
}

function kk_importer_types($value) {
    $allowed = array('Seminar', 'Course', 'Competition', 'Training camp', 'Grading', 'Other', 'Professional certifications');
    $parts = preg_split('/(?:\s*\+\s*)|[\r\n|,;]+/', (string) $value);
    $types = array();
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part !== '' && in_array($part, $allowed, true)) $types[] = $part;
    }
    return array_values(array_unique($types));
}

function kk_importer_audiences($value) {
    $allowed = array('All', 'Junior', 'Ladies', 'Kyus');
    $parts = preg_split('/(?:\s*\+\s*)|[\r\n|,;]+/', (string) $value);
    $audiences = array();
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part !== '' && in_array($part, $allowed, true)) $audiences[] = $part;
    }
    return array_values(array_unique($audiences));
}

function kk_importer_money_value($value, $currency) {
    $value = trim((string) $value);
    if ($value === '') return '';
    if ($currency && is_numeric($value)) return $value . ' ' . $currency;
    return $value;
}

function kk_importer_flexible_fee_items($row) {
    $currency = isset($row['currency']) ? trim($row['currency']) : '';
    $items = array();
    for ($i = 1; $i <= 4; $i++) {
        $label = trim((string) ($row['fee_' . $i . '_label'] ?? ''));
        $amount = trim((string) ($row['fee_' . $i . '_amount'] ?? ''));
        if ($label === '' && $amount === '') continue;
        if ($label === '') $label = 'Fee';
        $value = kk_importer_money_value($amount, $currency);
        $line = $label . ($value !== '' ? ' — ' . $value : '');
        if (!in_array($line, $items, true)) $items[] = $line;
    }
    return $items;
}

function kk_importer_price_details($row) {
    $items = kk_importer_flexible_fee_items($row);
    if ($items) return implode("\n", $items);

    // Backwards compatibility with Pipeline 8 and earlier workbooks.
    $currency = isset($row['currency']) ? trim($row['currency']) : '';
    $legacy = array();
    $map = array(
        'fee_one_day' => 'One day',
        'fee_full_event' => 'Full event',
        'fee_sayonara' => 'Sayonara',
        'fee_other' => 'Other',
    );
    foreach ($map as $key => $label) {
        if (empty($row[$key])) continue;
        $value = kk_importer_money_value($row[$key], $currency);
        $line = $label . ' — ' . $value;
        if (!in_array($line, $legacy, true)) $legacy[] = $line;
    }
    return implode("\n", $legacy);
}

function kk_importer_main_price($row) {
    $currency = isset($row['currency']) ? trim($row['currency']) : '';
    if (!empty($row['price'])) return kk_importer_money_value($row['price'], $currency);

    for ($i = 1; $i <= 4; $i++) {
        $amount = trim((string) ($row['fee_' . $i . '_amount'] ?? ''));
        if ($amount !== '') return kk_importer_money_value($amount, $currency);
    }

    foreach (array('fee_full_event', 'fee_one_day', 'fee_other') as $key) {
        if (!empty($row[$key])) return kk_importer_money_value($row[$key], $currency);
    }
    return 'See organiser';
}

function kk_importer_find_event($pipeline_id, $source_event_id = '', $title = '') {
    foreach (array(
        array('_kk_pipeline_event_id', $pipeline_id),
        array('_kk_source_event_id', $pipeline_id),
        array('_kk_source_event_id', $source_event_id),
    ) as $lookup) {
        if ($lookup[1] === '') continue;
        $posts = get_posts(array(
            'post_type' => 'tribe_events', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids',
            'meta_key' => $lookup[0], 'meta_value' => $lookup[1], 'suppress_filters' => true,
        ));
        if ($posts) return (int) $posts[0];
    }

    // Older test imports may not yet have the pipeline metadata. Fall back to
    // an exact title match so the next import repairs the existing event
    // instead of creating another copy.
    if ($title !== '') {
        global $wpdb;
        $match = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_title = %s AND post_status NOT IN ('trash','auto-draft') ORDER BY ID ASC LIMIT 1",
            'tribe_events', $title
        ));
        if ($match) return (int) $match;
    }
    return 0;
}

function kk_importer_svg($event) {
    $title = esc_html(strtoupper($event['title']));
    $date = esc_html(strtoupper($event['date_label']));
    $place = esc_html(strtoupper(trim($event['city'] . ' · ' . $event['country'], ' ·')));
    $type = esc_html(strtoupper($event['type_label']));
    return '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="800" viewBox="0 0 600 800">'
        . '<rect width="600" height="800" rx="28" fill="#1b2026"/><rect x="48" y="44" width="504" height="5" rx="2.5" fill="#ff935c"/>'
        . '<text x="48" y="88" font-family="Arial,sans-serif" font-size="18" font-weight="800" letter-spacing="4" fill="#ff935c">KENDOKAIZEN</text>'
        . '<rect x="374" y="58" width="178" height="36" rx="18" fill="#ff935c"/><text x="463" y="82" text-anchor="middle" font-family="Arial,sans-serif" font-size="12" font-weight="800" fill="#14181c">TEMPORARY POSTER</text>'
        . '<text x="48" y="155" font-family="Arial,sans-serif" font-size="18" font-weight="800" letter-spacing="2" fill="#ff935c">' . $type . '</text>'
        . '<foreignObject x="48" y="205" width="504" height="300"><div xmlns="http://www.w3.org/1999/xhtml" style="font-family:Arial,sans-serif;font-size:48px;font-weight:800;line-height:1.08;color:#fff9f5;overflow-wrap:anywhere">' . $title . '</div></foreignObject>'
        . '<rect x="48" y="620" width="504" height="1" fill="#fff9f5" opacity=".22"/>'
        . '<text x="48" y="670" font-family="Arial,sans-serif" font-size="22" font-weight="800" fill="#ff935c">' . $date . '</text>'
        . '<text x="48" y="708" font-family="Arial,sans-serif" font-size="18" fill="#d8dbdf">' . $place . '</text>'
        . '<text x="48" y="758" font-family="Arial,sans-serif" font-size="12" fill="#9ea5ad">PLACEHOLDER ARTWORK · NOT THE OFFICIAL EVENT POSTER</text></svg>';
}

function kk_importer_create_placeholder($event, $event_id) {
    $existing = get_posts(array(
        'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1, 'fields' => 'ids',
        'meta_key' => '_kk_temporary_poster_source', 'meta_value' => $event['pipeline_id'],
    ));
    if ($existing) return (int) $existing[0];

    $uploads = wp_upload_dir();
    if (!empty($uploads['error'])) return new WP_Error('kk_upload_error', $uploads['error']);
    $folder = trailingslashit($uploads['basedir']) . 'kendokaizen-temp';
    if (!wp_mkdir_p($folder)) return new WP_Error('kk_folder_error', 'Could not create the temporary poster folder.');

    $filename = sanitize_title($event['title']) . '-' . sanitize_title($event['pipeline_id']) . '.svg';
    $path = trailingslashit($folder) . $filename;
    if (false === file_put_contents($path, kk_importer_svg($event))) return new WP_Error('kk_file_error', 'Could not save the temporary poster.');

    $attachment_id = wp_insert_attachment(array(
        'post_mime_type' => 'image/svg+xml',
        'post_title' => 'Temporary poster – ' . $event['title'],
        'post_status' => 'inherit',
        'guid' => trailingslashit($uploads['baseurl']) . 'kendokaizen-temp/' . $filename,
    ), $path, $event_id);
    if (is_wp_error($attachment_id)) return $attachment_id;
    update_post_meta($attachment_id, '_wp_attached_file', 'kendokaizen-temp/' . $filename);
    update_post_meta($attachment_id, '_wp_attachment_image_alt', 'Temporary poster for ' . $event['title']);
    update_post_meta($attachment_id, '_kk_temporary_poster_source', $event['pipeline_id']);
    return (int) $attachment_id;
}

function kk_importer_set_poster($row, $event, $event_id) {
    $poster_url = !empty($row['poster_url']) ? esc_url_raw($row['poster_url']) : '';
    if ($poster_url) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attachment_id = media_sideload_image($poster_url, $event_id, $event['title'], 'id');
        if (!is_wp_error($attachment_id)) {
            set_post_thumbnail($event_id, $attachment_id);
            update_post_meta($event_id, '_kk_temporary_poster', '0');
            return true;
        }
    }

    if (!has_post_thumbnail($event_id)) {
        $placeholder_id = kk_importer_create_placeholder($event, $event_id);
        if (!is_wp_error($placeholder_id)) {
            update_post_meta($event_id, '_thumbnail_id', $placeholder_id);
            update_post_meta($event_id, '_kk_temporary_poster', '1');
        }
    }
    return true;
}

function kk_importer_import_row($row) {
    $pipeline_id = sanitize_text_field($row['event_id'] ?? '');
    $title = sanitize_text_field($row['title'] ?? '');
    $types = kk_importer_types($row['event_type'] ?? '');
    $audiences = kk_importer_audiences($row['audience'] ?? '');
    $start_date = kk_importer_excel_date($row['start_date'] ?? '');
    $end_date = kk_importer_excel_date($row['end_date'] ?? '') ?: $start_date;
    $timezone = sanitize_text_field($row['time_zone'] ?? '') ?: wp_timezone_string();

    if ($pipeline_id === '' || $title === '' || !$types || $start_date === '') {
        return new WP_Error('kk_required_fields', 'Missing event_id, title, event_type or start_date.');
    }

    $has_time = !empty($row['start_time']) || !empty($row['end_time']);
    $start_time = kk_importer_excel_time($row['start_time'] ?? '', $has_time ? '00:00:00' : '00:00:00');
    $end_time = kk_importer_excel_time($row['end_time'] ?? '', $has_time ? '23:59:59' : '23:59:59');

    try {
        $zone = new DateTimeZone($timezone);
        $start = new DateTime($start_date . ' ' . $start_time, $zone);
        $end = new DateTime($end_date . ' ' . $end_time, $zone);
        $start_utc = clone $start; $start_utc->setTimezone(new DateTimeZone('UTC'));
        $end_utc = clone $end; $end_utc->setTimezone(new DateTimeZone('UTC'));
    } catch (Exception $e) {
        return new WP_Error('kk_time_error', $e->getMessage());
    }

    $description = sanitize_textarea_field($row['description'] ?? '');
    $organizer = sanitize_text_field($row['organizer'] ?? '');
    $content = $description;
    if ($organizer !== '' && stripos($content, $organizer) === false) $content .= ($content ? "\n\n" : '') . 'Organiser: ' . $organizer . '.';

    $source_event_id = sanitize_text_field($row['source_event_id'] ?? '');
    $existing_id = kk_importer_find_event($pipeline_id, $source_event_id, $title);
    $price = kk_importer_main_price($row);
    $registration = esc_url_raw($row['registration_url'] ?? '');
    $source = esc_url_raw($row['source_url'] ?? '');

    $event_args = array(
        'post_status' => 'publish',
        'post_title' => $title,
        'post_name' => sanitize_title($title),
        'post_content' => $content,
        'post_excerpt' => wp_trim_words(wp_strip_all_tags($description), 30),
        'EventStartDate' => $start_date,
        'EventEndDate' => $end_date,
        'EventAllDay' => $has_time ? false : true,
        'EventTimezone' => $timezone,
        'EventCost' => $price,
    );
    if ($has_time) {
        $event_args['EventStartTime'] = substr($start_time, 0, 5);
        $event_args['EventEndTime'] = substr($end_time, 0, 5);
    }
    if ($registration) $event_args['EventURL'] = $registration;
    elseif ($source) $event_args['EventURL'] = $source;

    // Use The Events Calendar API so its own event/occurrence data is kept in
    // sync. This fixes events that were reported as imported but did not appear
    // in the public calendar/list.
    if ($existing_id && function_exists('tribe_update_event')) {
        $saved_id = tribe_update_event($existing_id, wp_slash($event_args));
    } elseif (!$existing_id && function_exists('tribe_create_event')) {
        $saved_id = tribe_create_event(wp_slash($event_args));
    } else {
        $postarr = array(
            'post_type' => 'tribe_events', 'post_status' => 'publish', 'post_title' => $title,
            'post_name' => sanitize_title($title), 'post_content' => $content,
            'post_excerpt' => wp_trim_words(wp_strip_all_tags($description), 30),
        );
        if ($existing_id) $postarr['ID'] = $existing_id;
        $saved_id = $existing_id ? wp_update_post(wp_slash($postarr), true) : wp_insert_post(wp_slash($postarr), true);
    }
    if (!$saved_id || is_wp_error($saved_id)) return new WP_Error('kk_event_save', 'The event could not be saved through The Events Calendar.');
    $event_id = (int) $saved_id;

    if (!$registration) $registration = $source ?: get_permalink($event_id);
    $venue = sanitize_textarea_field($row['venue'] ?? '');
    $address = sanitize_textarea_field($row['full_address'] ?? '');
    if ($address === '') $address = $venue;

    $registration_deadline = kk_importer_excel_date($row['registration_deadline'] ?? '');
    if ($registration_deadline !== '') {
        $registration_deadline = date_i18n('j M Y', strtotime($registration_deadline));
    }

    $meta = array(
        '_EventOrigin' => 'events-calendar',
        '_EventStartDate' => $start->format('Y-m-d H:i:s'), '_EventEndDate' => $end->format('Y-m-d H:i:s'),
        '_EventStartDateUTC' => $start_utc->format('Y-m-d H:i:s'), '_EventEndDateUTC' => $end_utc->format('Y-m-d H:i:s'),
        '_EventTimezone' => $timezone, '_EventAllDay' => $has_time ? 'no' : 'yes',
        '_EventDuration' => (string) max(0, $end_utc->getTimestamp() - $start_utc->getTimestamp()),
        '_EventCost' => $price, '_EventURL' => $registration,
        '_kk_pipeline_event_id' => $pipeline_id, '_kk_source_event_id' => $source_event_id ?: $pipeline_id,
        '_kk_source_url' => $source, '_kk_approved' => '1',
        '_kk_country' => sanitize_text_field($row['country'] ?? ''), '_kk_city' => sanitize_text_field($row['city'] ?? ''),
        '_kk_venue' => $venue, '_kk_address' => $address, '_kk_organiser' => $organizer,
        '_kk_type' => $types[0], '_kk_types' => $types,
        '_kk_audience' => $audiences ? $audiences[0] : '', '_kk_audiences' => $audiences,
        '_kk_price' => $price, '_kk_price_details' => kk_importer_price_details($row),
        '_kk_schedule' => sanitize_textarea_field($row['schedule'] ?? ''), '_kk_level' => sanitize_text_field($row['level'] ?? ''),
        '_kk_instructors' => sanitize_text_field($row['instructors'] ?? ''),
        '_kk_registration_deadline' => $registration_deadline,
        '_kk_competition_format' => sanitize_textarea_field($row['competition_format'] ?? ''),
        '_kk_exam_grades' => sanitize_text_field($row['exam_grades'] ?? ''),
        '_kk_extra_information' => sanitize_textarea_field($row['extra_information'] ?? ''),
        '_kk_registration_url' => $registration,
        '_kk_poster_status' => sanitize_text_field($row['poster_status'] ?? ''),
        '_kk_poster_source_url' => esc_url_raw($row['poster_source_url'] ?? ''),
    );
    // Replace imported fields completely, including clearing values left by an earlier test import.
    foreach (array_keys($meta) as $key) delete_post_meta($event_id, $key);
    foreach ($meta as $key => $value) {
        if ($value === '' || $value === array()) delete_post_meta($event_id, $key);
        else update_post_meta($event_id, $key, $value);
    }

    $event = array(
        'pipeline_id' => $pipeline_id, 'title' => $title, 'city' => $meta['_kk_city'], 'country' => $meta['_kk_country'],
        'type_label' => implode(' · ', $types),
        'date_label' => $start_date === $end_date ? date_i18n('j M Y', strtotime($start_date)) : date_i18n('j M', strtotime($start_date)) . ' – ' . date_i18n('j M Y', strtotime($end_date)),
    );
    kk_importer_set_poster($row, $event, $event_id);
    clean_post_cache($event_id);
    $public_ready = get_post_status($event_id) === 'publish' && (bool) get_post_meta($event_id, '_EventStartDate', true);
    return array(
        'id' => $event_id, 'updated' => (bool) $existing_id, 'title' => $title, 'types' => $types,
        'start' => get_post_meta($event_id, '_EventStartDate', true),
        'status' => get_post_status($event_id),
        'public_ready' => $public_ready,
    );
}

function kk_importer_run_file($path) {
    if (!post_type_exists('tribe_events')) return new WP_Error('kk_tec_missing', 'The Events Calendar must be active before importing.');
    $records = kk_importer_parse_excel($path);
    if (is_wp_error($records)) return $records;

    $result = array('approved' => 0, 'created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => array(), 'events' => array());
    foreach ($records as $row) {
        if (strcasecmp(trim($row['status'] ?? ''), 'Approved') !== 0) {
            $result['skipped']++;
            continue;
        }
        $result['approved']++;
        $imported = kk_importer_import_row($row);
        if (is_wp_error($imported)) {
            $result['errors'][] = ($row['title'] ?? $row['event_id'] ?? 'Unknown event') . ': ' . $imported->get_error_message();
            continue;
        }
        if ($imported['updated']) $result['updated']++; else $result['created']++;
        $result['events'][] = $imported;
    }
    update_option(KK_IMPORTER_RESULT_OPTION, $result, false);
    return $result;
}

function kk_importer_tools_page() {
    add_management_page('KendoKaizen Excel Import', 'KendoKaizen Import', 'manage_options', 'kendokaizen-event-importer', 'kk_importer_render_page');
}
add_action('admin_menu', 'kk_importer_tools_page');

function kk_importer_render_page() {
    if (!current_user_can('manage_options')) return;

    $error = '';
    $result = null;
    if (isset($_POST['kk_import_excel'])) {
        check_admin_referer('kk_import_excel_action');
        if (empty($_FILES['kk_excel_file']['tmp_name'])) {
            $error = 'Choose the KendoKaizen .xlsx or .xlsm workbook first.';
        } else {
            $name = sanitize_file_name($_FILES['kk_excel_file']['name']);
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($extension, array('xlsx', 'xlsm'), true)) {
                $error = 'Please upload an .xlsx or .xlsm workbook.';
            } else {
                $result = kk_importer_run_file($_FILES['kk_excel_file']['tmp_name']);
                if (is_wp_error($result)) {
                    $error = $result->get_error_message();
                    $result = null;
                }
            }
        }
    }
    if (!isset($_POST['kk_import_excel'])) {
        $result = get_option(KK_IMPORTER_RESULT_OPTION, array());
    }
    ?>
    <div class="wrap">
        <h1>KendoKaizen Excel Import <small style="font-size:13px;font-weight:600;color:#646970;">v2.4.0</small></h1>
        <p>Upload the KendoKaizen event pipeline workbook. Only rows whose <strong>status is Approved</strong> are imported. Existing events are matched by <code>event_id</code>, source ID or exact title and updated instead of duplicated.</p>
        <p>Multiple event types are supported. For example, <code>Competition + Grading</code> appears as both types in the list and pop-up, while the normal Competition and Grading filters remain separate.</p>
        <p>The optional <code>audience</code> field supports <code>All</code>, <code>Junior</code>, <code>Ladies</code> and <code>Kyus</code>, including combinations such as <code>Junior + Ladies</code>. Blank audience values remain backwards compatible: no audience meta is written, so the theme falls back to existing title/level information and otherwise treats the event as <code>All</code>.</p>
        <?php if ($error) : ?><div class="notice notice-error inline"><p><?php echo esc_html($error); ?></p></div><?php endif; ?>
        <?php if ($result && !is_wp_error($result)) : ?>
            <div class="notice notice-<?php echo empty($result['errors']) ? 'success' : 'warning'; ?> inline"><p>
                <?php echo esc_html(sprintf('Approved rows: %d. Created: %d. Updated: %d. Errors: %d.', $result['approved'] ?? 0, $result['created'] ?? 0, $result['updated'] ?? 0, count($result['errors'] ?? array()))); ?>
            </p></div>
            <?php if (!empty($result['events'])) : ?>
                <ul>
                    <?php foreach ($result['events'] as $event) : ?>
                        <li><strong><?php echo esc_html($event['title']); ?></strong> — <?php echo esc_html(implode(' + ', $event['types'])); ?> — <?php echo $event['updated'] ? 'updated' : 'created'; ?> — ID <?php echo (int) $event['id']; ?> — <?php echo esc_html($event['start'] ?: 'no start date'); ?> — <strong><?php echo !empty($event['public_ready']) ? 'public-ready' : 'CHECK REQUIRED'; ?></strong></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <?php if (!empty($result['errors'])) : ?><ul><?php foreach ($result['errors'] as $item) : ?><li><?php echo esc_html($item); ?></li><?php endforeach; ?></ul><?php endif; ?>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" style="margin-top:24px; padding:20px; max-width:720px; background:#fff; border:1px solid #dcdcde; border-radius:8px;">
            <?php wp_nonce_field('kk_import_excel_action'); ?>
            <p><label for="kk_excel_file"><strong>KendoKaizen workbook (.xlsx or .xlsm)</strong></label></p>
            <p><input id="kk_excel_file" name="kk_excel_file" type="file" accept=".xlsx,.xlsm" required></p>
            <input type="hidden" name="kk_import_excel" value="1">
            <?php submit_button('Import Approved events', 'primary', 'submit', false); ?>
        </form>
        <p><strong>Posters:</strong> if <code>poster_url</code> contains an image URL, the importer uses it. Otherwise it keeps an existing poster or creates a temporary KendoKaizen placeholder.</p>
    </div>
    <?php
}
