# KendoKaizen

KendoKaizen is a WordPress agenda for European kendo seminars, competitions, gradings, training camps and related events. Events can be viewed as a chronological list or calendar, opened in an event-details window and shared through a direct event link.

## Project status

**First stable baseline: v1.0.0 — 9 October 2026.**

The stable baseline is defined by the following paired project artifacts:

- WordPress Playground: `wordpress-playground_v6.7-practical-info-complete-v0.7.9-importer-parser-tec-fix.zip`
- Event database: `KendoKaizen_Event_Pipeline_11_refetched_sources.xlsx`

This is the reference point for the first production deployment. Further development should branch from this state rather than from earlier Playground experiments.

The complete Playground exports are intentionally kept outside Git because they include WordPress core, plugins, database state and uploads. Their iteration history is documented in [PLAYGROUND_VERSIONS.md](PLAYGROUND_VERSIONS.md) and [CHANGELOG.md](CHANGELOG.md).

The reusable custom WordPress source in this repository remains the clean source representation. When a change is developed in Playground, it should be back-ported into this source tree before a production release whenever practical.

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
- `PLAYGROUND_VERSIONS.md`: development history of the Playground iterations.
- `CHANGELOG.md`: consolidated feature history.
- `STABLE_VERSION.md`: manifest for the current stable baseline.

The WordPress database, uploaded posters, third-party plugins and complete Playground exports are intentionally excluded from Git and should be backed up separately.

## Local installation

1. Install WordPress, Kadence and The Events Calendar.
2. Copy `wp-content/themes/kendokaizen/` into the WordPress themes directory.
3. Activate **KendoKaizen** from Appearance → Themes.
4. Create or import events.
5. Add posters through the Featured image panel when required.

## Event data workflow

The current event workflow uses the KendoKaizen Event Pipeline spreadsheet as the structured source of truth for event collection and review. The stable v1.0.0 baseline uses Event Pipeline v11.

The spreadsheet distinguishes event description, practical information, registration details, fees, source URLs and poster metadata. Approved event rows can then be imported into WordPress through the KendoKaizen import workflow.

## Sharing

Every event modal contains a **Share event** action. Mobile browsers use the native share sheet when available. Desktop browsers offer WhatsApp, Telegram, Email and Copy link. Shared URLs reopen the event directly in the KendoKaizen interface.
