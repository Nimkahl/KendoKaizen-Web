# Timeline by event type preview

Three primary rows: Competitions (navy), Seminars (green), Exams (dark grey). Each event appears once per matching category, with that row's colour, country flag inside its bar and title below. Counts report unique events. Unknown categories appear in an additional Other events row rather than disappearing. Training camps and courses map to Seminars; professional certifications map to Exams.

Country selection remains available in a collapsible control above the grid, including tick/untick all. Existing filters and date navigation remain. PDF export groups by the same categories, using country names instead of emoji flags for font compatibility.

Validation: 11 unit tests pass. Browser checks cover grouping, 9 bars for 7 upcoming events (including two multi-category events), flag placement, event popup, tick/untick, PDF download and mobile overflow. Vienna Winter Keiko currently has the generic Kendo event type, so appears in Other events. No event data was reclassified. Branch codex/timeline-by-event-type; no GitHub push.
