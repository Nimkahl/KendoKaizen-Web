# Timeline preview — theme v0.8.7

The branch first aligns the theme with the supplied v0.8.6 Playground export, then adds Timeline alongside List and Calendar. No database or importer migration is needed.

Each row is an event; country flags precede its name. The date axis covers three months starting with the current month. Previous/next move three months, Today resets the period. End dates are inclusive, and events crossing the window are clipped. Names stay fixed during horizontal scrolling. Names and bars open the existing event modal; all three views share country, event-type and audience filters.

Competition uses #17365D, seminar #287A48 and grading/exam #454545. Multi-type events have equal colour segments in that order; these represent categories rather than session dates. Training camps share seminar green; professional certifications share exam grey. Unknown countries use a globe. The surrounding UI keeps the website's existing English labels and theme switch.

## Validation

Run `node --test tests/timeline.test.cjs` for inclusive spans, window clipping, leap years, year changes, invalid dates, category order and country flags. Browser integration was checked against an independent local WordPress Playground with the supplied export's database and plugins, in desktop (1440px), mobile (390px) and dark mode. Navigation, shared filters, empty periods, fixed labels, horizontal keyboard scrolling and event modals were exercised. Temporary multi-type fixtures were rendered only in browser memory, never written to the database.

## Package

Run `python3 scripts/package-timeline.py /absolute/path/original.zip /absolute/path/new.zip`. Only app.js, functions.php, style.css and the new timeline.js are replaced. The script verifies every other archive entry is byte-identical, including the database, importer, uploads and export metadata. Import the resulting ZIP using WordPress Playground's Import option. Keep the original export to revert.
