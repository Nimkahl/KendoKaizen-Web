# Home, Archive and Contact — v0.8.13 preview

Home defaults to Calendar and shows current/upcoming events plus the existing Kirikaeshi carousel. Archive defaults to List, showing completed events newest first. Both retain List, Calendar and Timeline views, filters and the shared popup. Existing event hash links can still open completed events on Home.

Contact contains the original message and event-submission forms. Form status redirects now return to Contact. Header/footer are shared templates and navigation highlights the current page.

Pages are provisioned once at the next WordPress load (`kk_site_pages_version`). IDs are stored in options and navigation uses their permalinks, including sites with plain permalinks. Existing unrelated pages are not overwritten; WordPress chooses a unique slug if necessary. The export preserves the source database; the two pages are created on first load.

Past means the event's end date (or start date if end is absent) precedes today's date in the WordPress site timezone. Events ending today remain on Home. Existing full event data is retained for deep links; the displayed views use the appropriate subset.

Validation: 11 existing timeline/calendar export unit tests pass. Browser checks cover desktop (1440px), mobile (390px), all three pages, default views, disjoint event sets, archive ordering, filters, popup, timeline, and no horizontal page overflow or JavaScript exceptions. Date boundary checks cover empty subsets and events ending today. Contact validation uses an invalid submission only; email delivery is not tested.

Run browser checks against a disposable preview with Playwright available: `node scripts/check-pages.cjs` and `node scripts/check-pages-boundaries.cjs`. Set KK_PREVIEW_URL to override localhost:9413.

Package with `python3 scripts/package-timeline.py ORIGINAL.zip OUTPUT.zip`. Original ZIP entries outside the selected theme files are verified byte-for-byte. Do not package the test server database.

Local branch: codex/archive-and-contact-pages. No GitHub push or deployment performed.
