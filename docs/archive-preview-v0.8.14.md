# Compact archive — v0.8.14

Archive is now list-only, grouped by event start month and year, newest first. It defaults to all past events. Year and month filters combine with existing country/type filters; month applies across all years unless a year is selected. Multi-month events appear once under their start month and show the complete date range. Events ending today remain upcoming.

Compact rows show the day/date range, event name, country flag, location and type. They are keyboard-accessible buttons opening the existing shared event popup. Colours inherit the theme in light and dark modes. Home keeps its three view options.

Run scripts/check-archive.cjs with Playwright and KK_PREVIEW_URL pointing at a disposable preview. Checks cover default groups, filters, empty state, keyboard popup, responsive layout and browser errors. No GitHub publication.
