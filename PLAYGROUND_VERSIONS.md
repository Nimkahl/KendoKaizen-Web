# WordPress Playground version history

The KendoKaizen website was iterated extensively in WordPress Playground on 5 October 2026. This file records the sequence of Playground exports so the repository history remains understandable even when the full Playground ZIPs are stored outside Git.

Latest design state recorded in the project: **v5.9**.

## Export sequence

- v1.3 — imported/starting Playground state for the October iteration.
- v1.4 — subtitle, Events/Contact navigation, Contact form, Submit a Kendo event form, validation, anti-spam and mobile support.
- v1.5 — Kirikaeshi collaborator area.
- v1.6 — Kirikaeshi tab.
- v2.0 — carousel labels corrected.
- v2.1 — header refinement.
- v2.2 — Kirikaeshi typography.
- v2.3 — Contact-style header.
- v2.4 — title hierarchy capped at 42 pt.
- v2.5 — signal-orange buttons and hover treatment.
- v2.6 — mobile responsive pass.
- v2.7 — two-line mobile calendar titles.
- v2.8 — “+N events” overflow and event panel.
- v2.9 — mobile Month / Weekend modes.
- v3.0 — Weekend becomes the default mobile calendar mode.
- v3.1 — mobile Light / Dark / System selector.
- v3.2–v3.7 — Kirikaeshi shop-link cleanup.
- v3.8 — carousel hover removed.
- v3.9 — complete alphabetical/searchable country filtering iteration.
- v4.0 — standardised event types and event-type filtering.
- v4.1 — standard country dropdown restored.
- v4.2 — calendar hover aligned with View details.
- v5.0 — continuous multi-day calendar bars.
- v5.1 — overlapping events stacked with automatic week height.
- v5.2 — event-type icons in calendar bars.
- v5.3 — compact calendar legend.
- v5.4 — Previous events section + grey past events in Calendar.
- v5.5 — vertical header centring.
- v5.6 — popup poster fitting and metadata improvements.
- v5.7 — poster rebuilt to fit; full Playground export rebuilt and verified.
- v5.8 — logo/title clipping and alignment fixes.
- v5.9 — removed orange poster bar; dates and country moved below event type.

## Export storage

Complete WordPress Playground exports contain WordPress core, database state, plugins, uploads and other generated/runtime files. They are intentionally not committed to this repository. Git should contain the reusable custom source plus documentation of the Playground iterations.

The project Library currently contains multiple full exports, including the verified `wordpress-playground_v5.7-fixed.zip`. Later v5.8/v5.9 changes are recorded in the project conversation history and should be incorporated into the next clean source snapshot before the production deployment.
