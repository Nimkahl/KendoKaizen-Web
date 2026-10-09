# Changelog

This repository tracks the reusable KendoKaizen WordPress code and the development history of the accompanying WordPress Playground builds.

## 1.0.0 — First stable baseline — 9 October 2026

- Declared the first stable KendoKaizen baseline.
- Stable WordPress reference: `wordpress-playground_v6.7-practical-info-complete-v0.7.9-importer-parser-tec-fix.zip`.
- Stable event-data reference: `KendoKaizen_Event_Pipeline_11_refetched_sources.xlsx`.
- Includes the practical-information popup redesign, light/dark refinements, responsive calendar/list views, filtering, sharing, previous-events handling and Kirikaeshi collaborator area developed through the October Playground iterations.
- Includes the Excel-driven event import workflow and the parser/namespace fixes through Playground v0.7.9.
- Event Pipeline v11 adds the latest source re-fetch while preserving already curated event rows and preventing duplicate additions.
- The spreadsheet now clearly separates `description`, `extra_information`, `poster_url`, `poster_source_url` and poster alt-text semantics.
- Known post-v1.0 improvement: make poster URL ingestion into the WordPress Media Library and description mapping fully automatic and fault-tolerant.

## v0.7.9 — Importer parser / The Events Calendar fix

- Latest Playground build used for the first stable baseline.
- Consolidated importer parser fixes and compatibility work with The Events Calendar.

## v0.7.8 — Importer namespace fix

- Corrected importer namespace handling.

## v0.7.7 — Import field fixes

- Corrected field mapping in the Excel import workflow.

## v0.7.6 — Excel import test

- Introduced the first structured Excel-to-WordPress import test workflow.

## v0.7.5 — Light-mode text refinement

- Refined light-mode text treatment following the practical-information popup work.

## 6.8 — Approved Excel import test

- Tested importing approved spreadsheet events into the Playground build.

## 6.7 — Practical information complete

- Consolidated the practical-information event popup and related fields.

## 6.4 — Header map link

- Added/refined map linking in the event header.

## 6.3 — Banner centering

- Refined banner alignment.

## 6.2 — Popup information one-column layout

- Moved popup practical information to a single-column treatment.

## 6.1 — Popup information redesign

- Introduced the revised event-information popup model.

## 5.9 — Event poster header cleanup

- Removed the orange bar at the top of the generated event poster.
- Kept the event type as the first metadata line.
- Moved event dates and country below the event type.
- Preserved the rest of the popup layout.

## 5.8 — Poster alignment fix

- Corrected horizontal displacement and clipping in generated popup posters.
- Improved logo positioning.
- Improved fitting for long event titles.

## 5.7 — Poster rebuilt to fit

- Rebuilt the generated popup poster specifically for its display slot.
- Used a narrow vertical composition.
- Added the KendoKaizen logo, auto-fitting event title, event type/icon, dates and location.
- Rebuilt the WordPress Playground export after the previous ZIP was malformed.

## 5.6 — Popup poster improvements

- Removed the “TEMPORARY POSTER” label.
- Reduced event-title size slightly.
- Added event type on its own line with the corresponding icon.
- Improved poster fitting inside the popup.

## 5.5 — Header vertical alignment

- Refined the header so its content is vertically centred.

## 5.4 — Previous events

- Added a collapsible “Previous events” section in List view.
- Past events remain visible in Calendar view with a light-grey treatment.

## 5.3 — Calendar legend

- Added a compact event-type legend above the calendar grid.
- Included the same event icons used inside calendar event bars.
- Adapted the legend for mobile.

## 5.2 — Event-type icons

- Added minimal icons before event titles in calendar event bars.
- Supported Competition, Grading, Seminar, Professional certifications and Training camp.
- Applied to desktop and mobile.

## 5.1 — Stacked spanning events

- Stacked overlapping calendar events vertically.
- Preserved continuous multi-day event bars.
- Added top spacing and automatic week height so overlapping events remain readable.
- Applied to desktop and mobile.

## 5.0 — Continuous multi-day events

- Rendered multi-day events as one continuous bar across the calendar instead of separate day-by-day labels.
- Applied to desktop and mobile.

## 4.2 — Calendar hover

- Set calendar-event hover to `#ff935c`, matching the View details interaction.

## 4.1 — Country filter simplification

- Returned the country selector to a standard dropdown.

## 4.0 — Event type filters

- Standardised event types to Competition, Seminar, Grading, Training Camp and Professional certifications.
- Added filtering based on those event types.

## 3.9 — Country filtering

- Added a complete alphabetical country list.
- Added searchable country filtering in the Playground iteration.

## 3.8 — Carousel interaction cleanup

- Removed carousel hover behaviour.

## 3.2–3.7 — Kirikaeshi shop links

- Added and refined product/category links across the Kirikaeshi collaborator carousel.

## 3.1 — Mobile theme selector

- Added Light / Dark / System theme controls to the mobile header.

## 3.0 — Mobile Weekend view

- Made Weekend the default mobile calendar mode.
- Added Month as the alternative mobile mode.
- Kept desktop calendar behaviour unchanged.

## 2.8–2.9 — Mobile calendar modes and overflow

- Added Month and Weekend modes.
- Added “+N events” overflow handling and an event panel for hidden events.

## 2.6–2.7 — Responsive mobile pass

- Improved the mobile layout.
- Allowed calendar titles to wrap to two lines on mobile.

## 2.4–2.5 — Typography and buttons

- Refined title hierarchy.
- Set light-theme buttons to signal orange with the matching hover treatment.

## 2.2–2.3 — Kirikaeshi and header styling

- Refined Kirikaeshi typography and the Contact-style header.

## 1.4–1.6 — Navigation, contact and Kirikaeshi

- Added Events and Contact navigation.
- Added Contact and event-submission forms.
- Added Kirikaeshi collaborator content and its dedicated tab.

## 0.6.0 — KendoKaizen identity

- Added the ink-brush K, signal-orange ensō and shinai brand mark.
- Added light- and dark-mode logo variants.
- Added browser/favicon assets.

## 0.5.0 — Shareable events

- Added event-specific direct links and share actions.

## 0.4.0 — Event administration

- Added the KendoKaizen event details panel and secure saving/validation.

## 0.3.0 — Signal Orange interface

- Added the Signal Orange light and dark colour system.
