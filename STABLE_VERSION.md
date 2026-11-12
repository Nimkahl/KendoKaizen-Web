# KendoKaizen stable version manifest

## v0.8.30 — current approved application baseline

Declared stable by the project owner on 12 November 2026. This is the current source baseline on `main`; the earlier v1.0.0 entry below remains a historical snapshot under its original numbering.

Export: `wordpress-playground_v0.8.30-quarter-timeline-preview.zip` (stored outside Git).

SHA-256: `c9226334e4fa206c8a3dd08974cc34bbececd219f8177b647694bd05282c34b6`

Timeline rows are grouped by event type, with flags inside bars and names below. Multi-type events appear in each relevant row; uncategorised events remain visible in Other events. Year and calendar-quarter selectors replace Today/All dates; arrows move through complete quarters across year boundaries. Country filters and PDF export are retained.

Validation: 11 existing unit tests pass. Browser checks covered type grouping, country selection, event popup, PDF download, all four quarters, Q4/Q1 transitions in both directions, synced selectors and mobile overflow. This is an approved application snapshot, not a production-server deployment. Calendar-app saving and live map tile availability retain the previously documented limitations. Event data and importer are unchanged.


## v1.0.0

**Declared:** 9 October 2026  
**Status:** First stable baseline

### WordPress application

`wordpress-playground_v6.7-practical-info-complete-v0.7.9-importer-parser-tec-fix.zip`

This is the WordPress Playground state selected as the first stable application baseline.

### Event data

`KendoKaizen_Event_Pipeline_11_refetched_sources.xlsx`

This is the event-data snapshot selected for the first stable baseline. It includes the 9 October source re-fetch, with new events added only after duplicate checks and without overwriting already curated rows.

### Stability definition

For KendoKaizen, “stable” means this pair of artifacts is the approved reference point for production preparation. Later experiments should not replace this baseline until they have been tested and explicitly promoted.

### Known follow-up

Poster URLs and long-form event descriptions have clearer spreadsheet semantics in the v11 data model. Fully automatic, fault-tolerant poster ingestion into the WordPress Media Library and final description mapping remain follow-up importer improvements.
