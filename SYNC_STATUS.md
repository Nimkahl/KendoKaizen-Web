# Synchronization status — 2026-10-21

**Status: SOURCE SYNC COMPLETE on development branch; WordPress runtime regression testing remains pending.**

This document records independently verified differences between the repository and the latest known KendoKaizen WordPress Playground exports. It is **not** an approval of a new stable release.

## GitHub state at review
- Main branch head at review: `b196b38f5d13ffee52f5eadfbecade588413cf6c` (documentation commit, 2026-10-09).
- Stable branch: `stable/v1.0.0` (same head at review).
- Last known commit affecting `wp-content/themes/kendokaizen/functions.php`: `c72447029822cab49a5c8d97fe8c3ce93f22a808` (2026-10-01).
- The checked-in theme `style.css` declares **Version: 0.6.0**.
- `STABLE_VERSION.md` declares the original 2026-10-09 *Playground* reference v0.7.9. This does **not** establish that GitHub contains that Playground's source files.
- Consequently, the GitHub theme source is not verified equivalent to v0.7.9 or later.

## Latest separately stored project artifacts
The following names and timestamps were verified in the Kendo Web - EC project library:

| Artifact | Project library filename | Date recorded |
| --- | --- | --- |
| Most recent known isolated custom theme | `KendoKaizen-theme-v0.8.5-schedule-lines.zip` | 2026-10-09 |
| Most recent known full WordPress Playground export | `wordpress-playground_v0.8.5-schedule-lines.zip` | 2026-10-09 |
| Custom importer candidate | `KendoKaizen-event-importer-v2.4.0.zip` | 2026-10-09 |
| Latest known event pipeline as of review | `KendoKaizen_Event_Pipeline_16_New_events_2026-10-21.xlsm` | 2026-10-21 |

**IMPORTANT:** On 21 October 2026, the user supplied the v0.8.5 theme and complete Playground ZIP exports. All 23 theme files in the standalone archive matched the corresponding Playground ZIP entries byte-for-byte. The custom importer plugin was extracted from the Playground export (v2.4.0). The 25 reusable custom source files (including graphics) were committed to this branch with Git blob SHA verification. Third-party dependencies and runtime data remain excluded.

## Synchronization checklist
- [x] Inspect GitHub branches, repository tree, code commit history and version manifest.
- [x] Identify latest known theme, Playground and importer export filenames.
- [x] Create dedicated development branch without altering the stable branch.
- [x] Obtain downloadable raw bytes of the latest isolated theme and full Playground ZIP.
- [x] Inspect ZIP structure, exclude WP core, third-party plugins, WordPress database, uploads and secrets.
- [x] Compare `wp-content/themes/kendokaizen/` (PHP, JS, CSS, bundled custom assets) against current GitHub using Git blob SHA hashes.
- [x] Locate the custom event importer v2.4.0 inside the latest Playground; independent ZIP identity has not been separately compared.
- [x] Commit the **actual** reusable custom theme and importer sources to the development branch (commit `1a1bd8843581ef764ba068c3a4e34015cdb629f2`).
- [x] Run `php -l` on the 3 custom PHP files and `node --check` on `app.js` (passed).
- [ ] Run WordPress / The Events Calendar integration and compatibility tests in an actual runtime.
- [ ] Exercise event list, calendar (desktop and mobile), filters, share links, event popup, schedule, description formatting, posters and spreadsheet import with `Approved` / `To update` workflows.
- [ ] Compare important screens and event field mappings with the latest Playground.
- [ ] Review the diff and promote to `main` only after code equivalence and regression checks; preserve `stable/v1.0.0` as historical reference unless a separate stability promotion is authorized.

## Later requested ideas — backlog only (not implemented here)
- Strengthen security / anti-bot defenses before publicity.
- Add individual event calendar export for Google Calendar / Apple Calendar / Outlook (.ics).
- Explore Instagram content preparation based on published events.
- Add a collapsible year-organized Archive for past events.
- Explore an additional events viewing mode and refinements to list aesthetics.

## Storage rules
Only reusable custom code, non-sensitive design assets and documentation should go into the public repository. Keep event records, full Playground runtime exports, WordPress database, uploaded media, credentials and user information out of source control. Maintain separate backups of the latest Excel pipeline and full Playground export.

The development branch now contains the complete extracted custom theme v0.8.5 and importer v2.4.0, verified against supplied files; **do not treat it as production-deployable until runtime regression testing is completed**.
