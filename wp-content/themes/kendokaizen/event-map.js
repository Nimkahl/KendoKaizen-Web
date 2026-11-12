/* City centres verified with Open-Meteo/GeoNames, 2026-11-11.
   These are approximate city locations, never implied venue coordinates. */
(() => {
  const cities = {
    'brussels|belgium': [50.85045, 4.34878],
    'vienna|austria': [48.20849, 16.37208],
    'innsbruck|austria': [47.26266, 11.39454],
    'linz|austria': [48.30639, 14.28611],
    'bellshill|united kingdom': [55.81667, -4.01667],
    'cranford|united kingdom': [51.47695, -0.41345], // Hounslow, confirmed by the event address.
  };
  const locationFor = event => {
    const point = event.mapLocation;
    if (point && Number.isFinite(point.lat) && Number.isFinite(point.lng) && Math.abs(point.lat) <= 90 && Math.abs(point.lng) <= 180) return point;
    const city = cities[`${event.city || ''}|${event.country || ''}`.toLowerCase().trim()];
    return city ? {lat:city[0], lng:city[1], precision:'city'} : null;
  };
  const init = (events, openEvent) => {
    const node = document.getElementById('kk-events-map');
    if (!node) return;
    const status = document.getElementById('kk-map-status');
    const list = document.getElementById('kk-map-location-list');
    const located = events.map(event => ({event, point:locationFor(event)})).filter(row => row.point);
    const missing = events.length - located.length;
    const places = new Set(located.map(({point})=>`${point.lat},${point.lng}`));
    document.getElementById('kk-map-count').textContent = `${located.length} events · ${places.size} locations`;
    const note = `${missing ? `${missing} event${missing === 1 ? '' : 's'} without a mapped location. ` : ''}${located.some(row=>row.point.precision==='city') ? 'City pins are approximate. ' : ''}Zoom to explore nearby events.`;
    status.textContent = events.length ? note : 'No upcoming events yet.';
    const buttonFor = event => {
      const button = document.createElement('button'); button.type = 'button';
      button.textContent = `${event.title} — ${event.city || event.country || 'Location not specified'}`;
      button.addEventListener('click', () => openEvent(event.id, button)); return button;
    };
    events.forEach(event=>list.appendChild(buttonFor(event)));
    if (!window.L || !L.markerClusterGroup) { status.textContent = 'The map is unavailable. Browse event locations below.'; return; }
    const map = L.map(node, {scrollWheelZoom:false, minZoom:2, maxZoom:16}).setView([52, 10], 3);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom:19, attribution:'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).on('tileerror',()=>{status.textContent='The background map could not load. Event pins and the location list remain available. '+note;}).addTo(map);
    const icon = n => L.divIcon({html:String(n), className:'kk-map-pin', iconSize:[36,36], iconAnchor:[18,18]});
    const clusters = L.markerClusterGroup({maxClusterRadius:45, showCoverageOnHover:false, iconCreateFunction:cluster=>icon(cluster.getChildCount())});
    located.forEach(({event,point})=>{
      const popup = document.createElement('div'); popup.className='kk-map-popup';
      const text = document.createElement('p'); text.textContent = `${event.city || event.country}${point.precision==='city' ? ' · Approximate city location' : ''}`;
      popup.append(text,buttonFor(event));
      clusters.addLayer(L.marker([point.lat,point.lng],{icon:icon(1),title:`1 event: ${event.title}`,alt:`1 event in ${event.city || event.country}`,keyboard:true}).bindPopup(popup));
    });
    map.addLayer(clusters);
    // Recalculate after responsive sidebar resizing.
    new ResizeObserver(()=>map.invalidateSize({pan:false})).observe(node);
    node._kkMap = map; node._kkClusters = clusters;
  };
  window.KendoEventMap = {init,locationFor};
})();
