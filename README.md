# KendoKaizen

KendoKaizen is a WordPress agenda for European kendo seminars, courses,
competitions, gradings and training camps. Events can be viewed as a
chronological list or calendar, opened in an accessible details window and
shared through a direct event link.

## Requirements

- WordPress 6.5 or newer
- PHP 8.1 or newer
- [Kadence](https://wordpress.org/themes/kadence/) parent theme
- [The Events Calendar](https://wordpress.org/plugins/the-events-calendar/) plugin

## Repository contents

The custom site code lives in `wp-content/themes/kendokaizen/`.

- `front-page.php`: KendoKaizen homepage shell.
- `functions.php`: event query, administration fields and page markup.
- `app.js`: filters, calendar, event modal, theme selector and sharing.
- `style.css`: light/dark Signal Orange visual system.

The WordPress database, uploaded posters, third-party plugins and complete
Playground exports are intentionally excluded from Git. They should be backed
up separately.

## Local installation

1. Install WordPress, Kadence and The Events Calendar.
2. Copy `wp-content/themes/kendokaizen/` into the WordPress themes directory.
3. Activate **KendoKaizen** from Appearance → Themes.
4. Create events from Events → Add New.
5. Add posters through the Featured image panel.

## Event administration

The editor includes a **KendoKaizen event details** panel for event type,
country, city, venue, approximate price, level, instructors and the official
registration link. The standard The Events Calendar controls store the dates.

## Sharing

Every event modal contains a **Share event** button. Mobile browsers use the
native share sheet when available. Desktop browsers offer WhatsApp, Telegram,
Email and Copy link. Shared URLs use `#event=<event-slug>` and reopen the event
directly in the KendoKaizen interface.

