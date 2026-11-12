# Upcoming-event map

Leaflet 1.9.4 and Leaflet.markercluster 1.5.3 are bundled with licenses. Standard OpenStreetMap tiles load on demand with visible attribution, normal browser caching and referrers. Internet is needed for the background map. No paid dependency or API key.

The map sits above the organiser card, starts over Europe, and clusters nearby events by zoom. Counts represent events, not cities. Clicking clusters zooms in; overlapping events can be expanded at maximum zoom. Individual markers and the accessible location list open the shared event popup. The map always displays all upcoming events, independently of the agenda filters.

Saved `_kk_latitude`/`_kk_longitude` or linked TEC venue `_VenueLat`/`_VenueLng` take priority. Otherwise six verified city centres are bundled for the existing seven upcoming events (Brussels, Vienna, Innsbruck, Linz, Bellshill and Cranford/Hounslow). These are labelled approximate. New unmatched cities remain unmapped and are counted explicitly until coordinates are supplied or the lookup is extended. No runtime geocoding or importer changes.

City data retrieved from Open-Meteo/GeoNames on 2026-11-11; Cranford was disambiguated with the event's Hounslow address. Sources: https://open-meteo.com/en/docs/geocoding-api and https://www.geonames.org/.

Map policy: https://operations.osmfoundation.org/policies/tiles/ . Automated browser zoom tests mock tiles to avoid automated tile harvesting. Tests check initial counts, clustered and detailed zoom, popup links, mobile overflow and JS errors. Live background tile availability is not guaranteed or asserted by the mocked test.

Original exports are preserved. No GitHub publication performed.
