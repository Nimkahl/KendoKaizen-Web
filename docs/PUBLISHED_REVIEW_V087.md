# Playground v0.8.7 — Published&Review (testing release)

Development branch: `feature/published-review-indicators` (do not merge into main yet).

The full development Playground is distributed separately as `wordpress-playground_v0.8.7-published-review.zip`. This ZIP includes WordPress database/content and is intentionally excluded from this source repository.

## Implemented inside the Playground ZIP

- Importer v2.5.0 supports `To Upload` in v18 and falls back to legacy `Events`; imports review statuses and upserts by stable event identity.
- Event metadata `_kk_needs_review` controls the public labels in the list, calendar and detail modal.
- Review reminders plugin adds Tools → KendoKaizen Reviews, ICS export and optional daily email digest (off by default).
- A fictional Playground event is flagged for visual testing.

## Verification and release constraints

- PHP lint and JS syntax checks passed; ZIP integrity verified.
- End-to-end UI testing in a running WordPress browser is still required.
- Manual ICS imports are snapshots, not live calendar sync.
- Never deploy the Playground database, demo records, email settings or full ZIP to production.
- The importer and theme code in the ZIP need to be backported into this repository and reviewed in this feature branch before merge. The separate review plugin source is already present on the branch.

## Acceptance tests

1. Verify info marks in list, calendar, legend and modal only for flagged events.
2. Import a v18 workbook and confirm `To Upload` is chosen; verify v17 fallback.
3. Reimport a known event after changing status from `Published&Review` to `Published`; confirm no duplicate and notice disappears.
4. Test both placeholder and real posters on desktop and mobile.
5. Test Tools → KendoKaizen Reviews and downloaded ICS; do not enable emails in Playground.
