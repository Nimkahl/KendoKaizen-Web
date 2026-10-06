# KendoKaizen

KendoKaizen is a WordPress agenda for European kendo seminars, competitions, gradings, training camps and related events. Events can be viewed as a chronological list or calendar, opened in an event-details window and shared through a direct event link.

## Project status

The Git repository began with the reusable custom WordPress source through **v0.6.0**. Development then continued rapidly in **WordPress Playground**, reaching a recorded design state of **v5.9 on 5 October 2026**.

The complete Playground exports are intentionally kept outside Git because they include WordPress core, plugins, database state and uploads. The full Playground iteration history is documented in [PLAYGROUND_VERSIONS.md](PLAYGROUND_VERSIONS.md) and [CHANGELOG.md](CHANGELOG.md).

Important: until the latest Playground export is converted back into a clean source snapshot, the custom theme files currently present in this repository should be treated as the earlier source baseline rather than as a byte-for-byte representation of v5.9.

## Requirements

- WordPress 6.5 or newer
- PHP 8.1 or newer
- Kadence parent theme
- The Events Calendar plugin

## Repository contents

The reusable custom site code lives in `wp-content/themes/kendokaizen/`.

- `front-page.php`: KendoKaizen homepage shell.
- `functions.php`: event query, administration fields and page markup.
- `app.js`: filters, calendar, event modal, theme selector and sharing.
- `style.css`: light/dark Signal Orange visual system.
- `assets/`: KendoKaizen brand marks for light mode, dark mode and browser icons.
- `PLAYGROUND_VERSIONS.md`: development history of the October 2026 Playground iterations.
- `CHANGELOG.md`: consolidated feature history.

The WordPress database, uploaded posters, third-party plugins and complete Playground exports are intentionally excluded from Git and should be backed up separately.

## Local installation

1. Install WordPress, Kadence and The Events Calendar.
2. Copy `wp-content/themes/kendokaizen/` into the WordPress themes directory.
3. Activate **KendoKaizen** from Appearance → Themes.
4. Create events from Events → Add New.
5. Add posters through the Featured image panel.

## Event administration

The source baseline includes a **KendoKaizen event details** panel for event type, country, city, venue, approximate price, level, instructors and the official registration link. The standard The Events Calendar controls store the dates.

Later Playground versions additionally introduced contact/event-submission flows, mobile calendar modes, improved filtering, continuous/stacked multi-day events, event-type icons and legend, Previous events handling, Kirikaeshi integration, and the redesigned popup poster.

## Sharing

Every event modal in the source baseline contains a **Share event** button. Mobile browsers use the native share sheet when available. Desktop browsers offer WhatsApp, Telegram, Email and Copy link. Shared URLs use `#event=<event-slug>` and reopen the event directly in the KendoKaizen interface.
