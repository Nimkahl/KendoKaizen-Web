# Calendar export preview — v0.8.12

Work is isolated on `codex/calendar-flags-and-export`, based on the preserved v0.8.11 source snapshot. The older local clone has different commit history from GitHub's equivalent snapshot commits; before eventual publication, apply this feature diff onto the verified remote v0.8.11 base rather than pushing unrelated baseline history. Do not update the timeline branch or main.

`functions.php` provides calendarTiming (allDay, timeZone, startUtc, endUtc) in the existing event JSON. Stored event-local times are converted to UTC with DateTimeImmutable; all-day exports use date values with an exclusive end. Invalid timed exports are disabled. The importer and database are unchanged.

`calendar-export.js` builds RFC 5545 files and Google/Outlook compose links. Descriptions include nonempty practical fields and event/registration links. Links exceeding 2,000 characters are hidden in favour of the full ICS file. Downloads are one-off copies, not subscriptions; repeated imports may create duplicates depending on the calendar application.

`app.js` integrates the shared popup menu and calendar flags using the existing timeline country mapping. Menu dismissal and keyboard focus follow the Share control. The additional script is included by the Playground packaging script.

Run unit checks with `node --test tests/*.test.cjs`. Browser checks covered desktop 1440px, mobile 390px, calendar flags/order/accessibility, all three popup entry points, downloads, Escape/focus, mutually exclusive menus, timeline country filters and PDF downloads. Provider links and file data were checked; authenticated provider saves and native calendar app imports were not performed.

Publish only after the user reviews the new ZIP. No server deployment is included.
