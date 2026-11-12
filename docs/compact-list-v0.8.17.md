# Compact upcoming list — v0.8.17

Upcoming list cards use small uncropped poster thumbnails and hide the summary (available in the event popup). Monthly sections include the year, ordered by start date. Events spanning months appear once under their start month.

The All months / month-year selector appears only in List. Its selection is retained on returning to List and combines with existing country/type/audience filters. Calendar, Timeline and the map are not restricted by this selector. Empty monthly sections disappear; the standard empty-state message remains.

Browser checks passed: November/December groups, December subset, switching views without leaking the month filter, returning to the retained selection, popup and 390px mobile overflow. Tiles are mocked/blocked during tests. No GitHub publication.
