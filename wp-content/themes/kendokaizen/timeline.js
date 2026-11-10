/* Shared date model and dependency-free timeline renderer. */
(function (root) {
  'use strict';
  const DAY = 86400000;
  const categories = [
    { key: 'competition', label: 'Competition', color: '#17365D' },
    { key: 'seminar', label: 'Seminar', color: '#287A48' },
    { key: 'grading', label: 'Grading / exam', color: '#454545' },
  ];
  const day = iso => {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(String(iso))) return NaN;
    const value = Date.parse(`${iso}T00:00:00Z`);
    return Number.isFinite(value) && new Date(value).toISOString().slice(0, 10) === iso ? value / DAY : NaN;
  };
  const iso = value => new Date(value * DAY).toISOString().slice(0, 10);
  const typesFor = event => {
    const raw = Array.isArray(event.types) && event.types.length ? event.types : String(event.type || '').split(/[|,·]/);
    const keys = new Set(raw.map(value => String(value).trim().toLowerCase()));
    // Existing site categories also include camps and professional certifications.
    if (keys.has('training camp')) keys.add('seminar');
    if (keys.has('exam') || keys.has('professional certifications')) keys.add('grading');
    const matched = categories.filter(category => keys.has(category.key));
    return matched.length ? matched : [{ key: 'other', label: event.type || 'Other', color: '#66717A' }];
  };
  const countryCodes = 'AD AL AT AX BA BE BG BY CH CY CZ DE DK EE ES FI FO FR GB GE GG GI GR HR HU IE IM IS IT JE LI LT LU LV MC MD ME MK MT NL NO PL PT RO RS RU SE SI SK SM TR UA VA XK AM AZ IL US CA JP AU NZ KR CN BR MX ZA'.split(' ');
  const countryNames = new Intl.DisplayNames(['en'], { type: 'region' });
  const regionFor = value => {
    const name = String(value || '').trim().toLowerCase();
    const aliases = { uk: 'GB', 'united kingdom': 'GB', england: 'GB', scotland: 'GB', wales: 'GB', 'northern ireland': 'GB', czechia: 'CZ', 'czech republic': 'CZ', turkey: 'TR', türkiye: 'TR', russia: 'RU', españa: 'ES' };
    return aliases[name] || countryCodes.find(code => code.toLowerCase() === name || countryNames.of(code).toLowerCase() === name) || '';
  };
  const flagFor = country => {
    const code = regionFor(country);
    return code ? String.fromCodePoint(...[...code].map(letter => 127397 + letter.charCodeAt(0))) : '🌐';
  };
  const model = (events, year, month) => {
    const start = Date.UTC(year, month, 1) / DAY;
    const end = Date.UTC(year, month + 3, 1) / DAY;
    const rows = events.map(event => ({ event, start: day(event.start), end: day(event.end || event.start) }))
      .filter(row => Number.isFinite(row.start) && Number.isFinite(row.end) && row.end >= row.start && row.start < end && row.end >= start)
      .sort((a, b) => a.start - b.start || String(a.event.title).localeCompare(String(b.event.title)))
      .map(row => ({ ...row, offset: Math.max(row.start, start) - start, length: Math.min(row.end + 1, end) - Math.max(row.start, start), types: typesFor(row.event) }));
    return { start, end, days: end - start, rows };
  };
  const element = (tag, className, text) => {
    const node = document.createElement(tag);
    node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
  };
  const render = (panel, events, year, month) => {
    const data = model(events, year, month);
    const format = (value, options) => new Date(value * DAY).toLocaleDateString('en-GB', { ...options, timeZone: 'UTC' });
    panel.querySelector('.kk-timeline-title').textContent = `${format(data.start, { month: 'short', year: 'numeric' })} – ${format(data.end - 1, { month: 'short', year: 'numeric' })}`;
    const grid = panel.querySelector('.kk-timeline-grid');
    grid.replaceChildren();
    grid.style.setProperty('--kk-timeline-days', data.days);
    const heading = element('div', 'kk-timeline-row kk-timeline-heading');
    heading.append(element('div', 'kk-timeline-label', 'Event / country'));
    const scale = element('div', 'kk-timeline-track kk-timeline-scale');
    for (let index = 0; index < 3; index++) {
      const begin = Date.UTC(year, month + index, 1) / DAY;
      const finish = Date.UTC(year, month + index + 1, 1) / DAY;
      const label = element('span', 'kk-timeline-month', format(begin, { month: 'long', year: 'numeric' }));
      label.style.left = `${(begin - data.start) / data.days * 100}%`;
      label.style.width = `${(finish - begin) / data.days * 100}%`;
      scale.append(label);
    }
    for (let value = data.start; value < data.end; value++) {
      if (new Date(value * DAY).getUTCDay() === 1) {
        const tick = element('span', 'kk-timeline-week', format(value, { day: 'numeric', month: 'short' }));
        tick.style.left = `${(value - data.start) / data.days * 100}%`;
        scale.append(tick);
      }
    }
    heading.append(scale);
    grid.append(heading);
    data.rows.forEach(row => {
      const event = row.event;
      const entry = element('div', 'kk-timeline-row');
      const label = element('button', 'kk-timeline-label kk-timeline-name');
      label.type = 'button';
      label.dataset.eventId = event.id;
      const flag = element('span', 'kk-timeline-flag', flagFor(event.country));
      flag.setAttribute('aria-hidden', 'true');
      const copy = element('span', 'kk-timeline-copy');
      copy.append(element('strong', '', event.title), element('small', '', `${event.country || 'Country not specified'} · ${row.types.map(type => type.label).join(' + ')}`));
      label.append(flag, copy);
      const track = element('div', 'kk-timeline-track');
      const bar = element('button', 'kk-timeline-bar');
      bar.type = 'button';
      bar.dataset.eventId = event.id;
      bar.style.left = `${row.offset / data.days * 100}%`;
      bar.style.width = `${row.length / data.days * 100}%`;
      if (row.start < data.start) bar.classList.add('continues-before');
      if (row.end >= data.end) bar.classList.add('continues-after');
      const description = `${event.title}, ${event.country || 'Country not specified'}, ${event.start} – ${event.end || event.start}, ${row.types.map(type => type.label).join(' + ')}`;
      bar.setAttribute('aria-label', description);
      bar.title = description;
      row.types.forEach(type => {
        const segment = element('span', 'kk-timeline-segment');
        segment.style.backgroundColor = type.color;
        segment.setAttribute('aria-hidden', 'true');
        bar.append(segment);
      });
      track.append(bar);
      entry.append(label, track);
      grid.append(entry);
    });
    panel.querySelector('.kk-timeline-empty').hidden = data.rows.length > 0;
    panel.querySelector('.kk-timeline-count').textContent = `${data.rows.length} event${data.rows.length === 1 ? '' : 's'} in this period`;
  };
  const api = { day, iso, model, typesFor, flagFor, render };
  if (typeof module !== 'undefined' && module.exports) module.exports = api;
  else root.KendoTimeline = api;
})(typeof window !== 'undefined' ? window : globalThis);
