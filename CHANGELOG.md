# Changelog

This repository tracks the custom KendoKaizen WordPress code. The project was initially versioned as source releases (0.x) and then iterated rapidly in WordPress Playground (1.x–5.x). The Playground history below is reconstructed from the project work completed on 5 October 2026.

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
- Verified the export structure included `wp-config.php`, `playground-export.json`, `wp-content`, database, plugins and theme.

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

- Standardised event types to:
  - Competition
  - Seminar
  - Grading
  - Training Camp
  - Professional certifications
- Added filtering based on those event types.

## 3.9 — Country filtering

- Added a complete alphabetical country list.
- Added searchable country filtering in the Playground iteration.

## 3.8 — Carousel interaction cleanup

- Removed carousel hover behaviour.

## 3.7 — Kirikaeshi shop links

- Added links for Shinai accessories.
- Added links for Keikogi and Hakama.

## 3.6 — Kirikaeshi shop links

- Added the Kendo Accessories Bag link.

## 3.5 — Kirikaeshi shop links

- Added “Tsuba and Tsuba Dome”.
- Added the Tsuba/Tsubadome link.
- Added the Tenugui link.

## 3.4 — Mobile calendar behaviour / shop-link iteration

- Continued the mobile Weekend/Month calendar work.
- Weekend mode displays the Friday–Saturday–Sunday blocks for the selected month.
- Month mode restores the full-month layout.
- Desktop calendar remained unchanged.
- This iteration also formed part of the Kirikaeshi shop-link cleanup sequence.

## 3.3 — Kirikaeshi shop links

- Linked T-shirts to Clothing.
- Linked Kendo Accessories to Kendo Bottles.

## 3.2 — Kirikaeshi shop links

- Linked Mugs to the mugs-and-jars category.

## 3.1 — Mobile theme selector

- Added Light / Dark / System theme controls to the mobile header.
- Desktop behaviour remained unchanged.

## 3.0 — Mobile Weekend view

- Made Weekend the default mobile calendar mode.
- Displays all Friday–Saturday–Sunday groups for the selected month.
- Added Month as the alternative mobile mode.
- Kept desktop calendar behaviour unchanged.

## 2.9 — Mobile calendar modes

- Added Month and Weekend mobile calendar modes.

## 2.8 — Mobile calendar overflow

- Added “+N events” overflow handling.
- Added an event panel for hidden events.

## 2.7 — Mobile calendar titles

- Allowed calendar titles to wrap to two lines on mobile.

## 2.6 — Responsive mobile pass

- Improved the mobile layout across the KendoKaizen interface.

## 2.5 — Button colours

- Set light-theme buttons to signal orange `#ff935c`.
- Set hover state to `#ffb27f`.

## 2.4 — Title hierarchy

- Limited large headings to a maximum of 42 pt in the relevant page/header treatment.

## 2.3 — Header style

- Refined the site header to match the Contact-page treatment.

## 2.2 — Kirikaeshi typography

- Refined typography for the Kirikaeshi section/page.

## 2.1 — Header refinement

- Continued visual refinement of the site header.

## 2.0 — Carousel labels

- Corrected carousel labels.

## 1.6 — Kirikaeshi tab

- Added a dedicated Kirikaeshi tab.

## 1.5 — Kirikaeshi collaborator

- Added Kirikaeshi as a collaborator/partner area.

## 1.4 — Navigation, contact and event submission

- Updated the site subtitle.
- Added Events and Contact navigation.
- Added a Contact form.
- Added a “Submit a Kendo event” form.
- Contact messages are sent to the WordPress `admin_email`.
- Submitted events are created as Pending for review.
- Added validation and basic anti-spam handling.
- Added mobile adaptations for these pages/forms.

## 0.6.0 — KendoKaizen identity

- Added the new ink-brush K, signal-orange ensō and shinai brand mark.
- Added dedicated light- and dark-mode logo variants.
- Added the new mark as the browser favicon and Apple touch icon.

## 0.5.0 — Shareable events

- Added event-specific direct links.
- Added native mobile sharing.
- Added WhatsApp, Telegram, Email and Copy link options on desktop.
- Preserved the KendoKaizen interface when opening a shared event.

## 0.4.0 — Event administration

- Added the KendoKaizen event details panel to the WordPress editor.
- Added secure saving and validation for custom event metadata.
- Added a publishing checklist and poster guidance.

## 0.3.0 — Signal Orange interface

- Added the Signal Orange light and dark colour system.
- Refined list cards, calendar events, modal controls and hover states.
- Added separate List and Calendar controls.
