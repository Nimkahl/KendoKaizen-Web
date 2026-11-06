<?php
/**
 * Plugin Name: KendoKaizen Review Reminders
 * Description: Private follow-up queue and calendar export for published KendoKaizen events awaiting details.
 * Version: 0.1.0
 * Author: KendoKaizen
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) exit;

const KK_REVIEW_DIGEST_HOOK = 'kk_review_daily_digest';
const KK_REVIEW_EMAIL_OPTION = 'kk_review_email_enabled';
const KK_REVIEW_EMAIL_LAST_OPTION = 'kk_review_last_digest_date';

/** Reminder days before each event. */
function kk_review_milestones() {
    return array(30, 14, 7, 3, 1);
}

function kk_review_events() {
    $ids = get_posts(array(
        'post_type' => 'tribe_events',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_key' => '_kk_needs_review',
        'meta_value' => '1',
        'suppress_filters' => true,
    ));
    $events = array();
    foreach ($ids as $id) {
        $start = substr((string) get_post_meta($id, '_EventStartDate', true), 0, 10);
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $start, wp_timezone());
        if (!$date || $date->format('Y-m-d') !== $start) continue;
        $events[] = array(
            'id' => (int) $id,
            'title' => get_the_title($id),
            'start' => $date,
            'pipeline_id' => (string) get_post_meta($id, '_kk_pipeline_event_id', true),
            'edit_url' => get_edit_post_link($id, 'raw'),
        );
    }
    usort($events, function ($a, $b) { return $a['start']->getTimestamp() <=> $b['start']->getTimestamp(); });
    return $events;
}

function kk_review_today() {
    return new DateTimeImmutable('today', wp_timezone());
}

function kk_review_reminder_dates($event_start) {
    $dates = array();
    foreach (kk_review_milestones() as $days) {
        $dates[$days] = $event_start->modify('-' . $days . ' days');
    }
    return $dates;
}

function kk_review_next_date($event_start, $today) {
    foreach (kk_review_reminder_dates($event_start) as $date) {
        if ($date >= $today) return $date;
    }
    return $event_start > $today ? $today : null;
}

function kk_review_schedule() {
    if (wp_next_scheduled(KK_REVIEW_DIGEST_HOOK)) return;
    $now = new DateTimeImmutable('now', wp_timezone());
    $next = $now->setTime(8, 0);
    if ($next <= $now) $next = $next->modify('+1 day');
    wp_schedule_event($next->getTimestamp(), 'daily', KK_REVIEW_DIGEST_HOOK);
}
register_activation_hook(__FILE__, 'kk_review_schedule');
register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook(KK_REVIEW_DIGEST_HOOK);
});

// Activation is not called when a WordPress Playground export already contains this plugin.
add_action('init', function () {
    if (get_option(KK_REVIEW_EMAIL_OPTION) === '1') kk_review_schedule();
});

/** The daily digest is opt-in, so restoring a Playground never sends mail by accident. */
function kk_review_send_digest() {
    if (get_option(KK_REVIEW_EMAIL_OPTION) !== '1') return;
    $today = kk_review_today();
    $today_iso = $today->format('Y-m-d');
    if (get_option(KK_REVIEW_EMAIL_LAST_OPTION) === $today_iso) return;

    $lines = array();
    foreach (kk_review_events() as $event) {
        foreach (kk_review_reminder_dates($event['start']) as $days => $date) {
            if ($date->format('Y-m-d') !== $today_iso) continue;
            $lines[] = sprintf('%s — starts %s (in %d day%s) — %s',
                $event['title'], $event['start']->format('Y-m-d'), $days,
                $days === 1 ? '' : 's', $event['edit_url']);
            break;
        }
    }
    if (!$lines) return;
    $subject = '[KendoKaizen] Events needing review — ' . $today_iso;
    $body = "These published events still need additional details.\n\n" . implode("\n", $lines)
          . "\n\nOnce confirmed, change the Excel status to Published and re-import the event (or clear the check box in WordPress).";
    $recipient = sanitize_email((string) get_option('admin_email'));
    if ($recipient && is_email($recipient) && wp_mail($recipient, $subject, $body)) {
        update_option(KK_REVIEW_EMAIL_LAST_OPTION, $today_iso, false);
    }
}
add_action(KK_REVIEW_DIGEST_HOOK, 'kk_review_send_digest');

function kk_review_admin_menu() {
    add_management_page('KendoKaizen Review Queue', 'KendoKaizen Reviews', 'manage_options', 'kendokaizen-reviews', 'kk_review_render_admin');
}
add_action('admin_menu', 'kk_review_admin_menu');

function kk_review_save_settings() {
    if (!current_user_can('manage_options')) wp_die('Forbidden');
    check_admin_referer('kk_review_save');
    update_option(KK_REVIEW_EMAIL_OPTION, isset($_POST['kk_review_email_enabled']) ? '1' : '0', false);
    wp_safe_redirect(add_query_arg(array('page' => 'kendokaizen-reviews', 'saved' => 1), admin_url('tools.php')));
    exit;
}
add_action('admin_post_kk_review_save', 'kk_review_save_settings');

function kk_review_ics_escape($text) {
    return str_replace(array('\\', ';', ',', "\r\n", "\n", "\r"), array('\\\\', '\\;', '\\,', '\\n', '\\n', '\\n'), (string) $text);
}
function kk_review_ics_line($value) {
    // RFC 5545 line folding by octets; avoid splitting a UTF-8 code point.
    $result = '';
    while (strlen($value) > 70) {
        $length = 70;
        while ($length > 1 && (ord($value[$length]) & 0xC0) === 0x80) $length--;
        $result .= substr($value, 0, $length) . "\r\n ";
        $value = substr($value, $length);
    }
    return $result . $value . "\r\n";
}

function kk_review_export_ics() {
    if (!current_user_can('manage_options')) wp_die('Forbidden');
    check_admin_referer('kk_review_calendar');
    $today = kk_review_today();
    $output = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//KendoKaizen//Review Queue//EN\r\nCALSCALE:GREGORIAN\r\nMETHOD:PUBLISH\r\nX-WR-CALNAME:KendoKaizen Event Reviews\r\n";
    foreach (kk_review_events() as $event) {
        $dates = kk_review_reminder_dates($event['start']);
        foreach ($dates as $days => $date) {
            if ($date < $today) continue;
            $start = $date->setTime(9, 0);
            $end = $start->modify('+30 minutes');
            $title = 'Review event details: ' . $event['title'];
            $description = 'Published&Review — follow up with organiser before ' . $event['start']->format('Y-m-d')
                . '. Update the KendoKaizen spreadsheet and WordPress. ' . $event['edit_url'];
            $lines = array(
                'BEGIN:VEVENT',
                'UID:kk-review-' . $event['id'] . '-' . $days . '@kendokaizen.com',
                'DTSTAMP:' . gmdate('Ymd\\THis\\Z'),
                'DTSTART:' . $start->setTimezone(new DateTimeZone('UTC'))->format('Ymd\\THis\\Z'),
                'DTEND:' . $end->setTimezone(new DateTimeZone('UTC'))->format('Ymd\\THis\\Z'),
                'SUMMARY:' . kk_review_ics_escape($title),
                'DESCRIPTION:' . kk_review_ics_escape($description),
                'BEGIN:VALARM', 'TRIGGER:-P1D', 'ACTION:DISPLAY', 'DESCRIPTION:KendoKaizen event review', 'END:VALARM',
                'END:VEVENT',
            );
            foreach ($lines as $line) $output .= kk_review_ics_line($line);
        }
    }
    $output .= "END:VCALENDAR\r\n";
    nocache_headers();
    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="kendokaizen-review-calendar.ics"');
    echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- RFC 5545 serialised calendar
    exit;
}
add_action('admin_post_kk_review_calendar', 'kk_review_export_ics');

function kk_review_render_admin() {
    if (!current_user_can('manage_options')) return;
    $today = kk_review_today();
    $events = kk_review_events();
    $calendar_url = wp_nonce_url(admin_url('admin-post.php?action=kk_review_calendar'), 'kk_review_calendar');
    ?>
    <div class="wrap">
      <h1>KendoKaizen — Event Review Queue</h1>
      <p>Published events still requiring details. This list is derived from the WordPress <code>_kk_needs_review</code> flag set by the Excel status <code>Published&amp;Review</code>. Importing the same <code>event_id</code> with status <code>Published</code> clears it.</p>
      <p><a class="button button-secondary" href="<?php echo esc_url($calendar_url); ?>">Download review calendar (.ics)</a> — import the file into Google Calendar, Apple Calendar or Outlook. This is a snapshot, not a live calendar subscription: download again when the event list changes.</p>
      <h2>Pending events (<?php echo count($events); ?>)</h2>
      <table class="widefat striped" style="max-width:1000px;"><thead><tr><th>Event</th><th>Event date</th><th>Next follow-up</th><th>Pipeline ID</th></tr></thead><tbody>
      <?php foreach ($events as $event) :
          $next = kk_review_next_date($event['start'], $today);
          ?>
          <tr>
            <td><a href="<?php echo esc_url($event['edit_url']); ?>"><?php echo esc_html($event['title']); ?></a></td>
            <td><?php echo esc_html($event['start']->format('j M Y')); ?></td>
            <td><?php echo esc_html($next ? $next->format('j M Y') : 'Event has passed — close review'); ?></td>
            <td><code><?php echo esc_html($event['pipeline_id']); ?></code></td>
          </tr>
      <?php endforeach; ?>
      <?php if (!$events) : ?><tr><td colspan="4">There are no events awaiting review.</td></tr><?php endif; ?>
      </tbody></table>
      <h2>Optional daily email digest</h2>
      <p>Notifications are <strong>off by default</strong> for safe Playground testing. When enabled, WordPress sends a summary to its administrator email at 30, 14, 7, 3 and 1 days before each pending event. WP-Cron depends on traffic and email delivery depends on hosting configuration; use a server cron or an external scheduler for reliable production notifications.</p>
      <?php if (isset($_GET['saved'])) : ?><div class="notice notice-success"><p>Preferences saved.</p></div><?php endif; ?>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('kk_review_save'); ?>
        <input type="hidden" name="action" value="kk_review_save">
        <p><label><input type="checkbox" name="kk_review_email_enabled" value="1" <?php checked(get_option(KK_REVIEW_EMAIL_OPTION), '1'); ?>> Enable email reminders to <strong><?php echo esc_html(get_option('admin_email')); ?></strong></label></p>
        <?php submit_button('Save reminder settings'); ?>
      </form>
    </div>
    <?php
}