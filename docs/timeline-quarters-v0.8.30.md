# Calendar-quarter navigation

Timeline defaults to the current quarter in the WordPress site date. Year and quarter selectors replace Today and All dates. Q1 is Jan–Mar, Q2 Apr–Jun, Q3 Jul–Sep, Q4 Oct–Dec. Arrows move exactly one quarter, synchronizing the selectors across years and resetting horizontal scroll. Years initially span event years and current year ±5; arrow navigation extends the year options as needed. Empty quarters remain selectable. Country and event filters persist; PDF uses the selected quarter.

Validation: browser checks pass for all quarters, Q4 2026 → Q1 2027 and back, selector synchronization and mobile overflow. Eleven existing unit tests pass. No GitHub push.
