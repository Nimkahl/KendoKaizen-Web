# Timeline preview — theme v0.8.11

Current work lives on `codex/event-collection-review`. It is a preview, not a stable promotion. The supplied v0.8.6 export remains the underlying application/data baseline.

## Behaviour

Each country has a row, with its flag and checkbox fixed on the left. All countries start selected. Active countries appear alphabetically; unticked countries move to a compact alphabetical section at the bottom. Ticking restores their active position. Untick all switches to Tick all once no countries are selected.

The initial date range covers all loaded events. All dates restores that range; Today switches to three months starting with the current month. Previous/next moves three months. Type and audience filters are shared with the other views; the timeline uses its own multi-country selection in place of the single-country dropdown. Bars and event titles open the existing event modal. Overlapping bars/titles use separate lanes within a country.

Competition uses #17365D, seminar #287A48 and grading/exam #454545. Multi-type bars have equal segments in this order. Camps share seminar green; professional certifications share exam grey. Colours indicate categories, not session times. Unknown countries use a globe.

## PDF download

Download PDF exports the selected countries, current event filters and date window directly in the browser. Hidden countries are excluded. Monthly landscape pages have day grids, full event titles and inclusive date spans; events crossing months appear in each relevant month. The button is disabled when no selected events are available. jsPDF and Liberation Sans are bundled locally under assets/pdf with their license notices.

## Source structure

- `timeline.js`: date model, country selection, ordering, timeline rendering and PDF generation.
- `app.js`: view switching, filters and date navigation.
- `functions.php`: WordPress markup and script loading.
- `style.css`: responsive timeline styling and rectangular controls.
- `assets/pdf/`: PDF library, embedded font and licenses.

## Validation and package

Date/category model tests cover inclusive spans, clipping, leap years, invalid dates and flags. Local WordPress Playground browser checks covered 22 events, 14 country entries, checkbox ordering/restoration, bulk selection, view switching and mobile layout. PDF downloads were checked on desktop and mobile; extracted text confirmed excluded countries are absent, and the generated layout was visually reviewed.

Run `python3 scripts/package-timeline.py /absolute/path/original.zip /absolute/path/new.zip`. The packager replaces the four timeline theme files and includes PDF assets, verifying every other original ZIP entry remains byte-identical. Full Playground ZIPs and spreadsheet data remain outside Git. Current ZIP: `wordpress-playground_v0.8.11-timeline-pdf-preview.zip`.
