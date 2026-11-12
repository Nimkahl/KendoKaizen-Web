/* Shared date model and dependency-free timeline renderer. */
(function (root) {
  'use strict';
  const DAY = 86400000;
  const categories = [
    { key: 'competition', label: 'Competitions', color: '#17365D' },
    { key: 'seminar', label: 'Seminars', color: '#287A48' },
    { key: 'grading', label: 'Exams', color: '#454545' },
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
    if (keys.has('course')) keys.add('seminar');
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
  const model = (events, year, month, months = 3) => {
    const start = Date.UTC(year, month, 1) / DAY;
    const end = Date.UTC(year, month + months, 1) / DAY;
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
  const countryFor = event => String(event.country || '').trim() || 'Country not specified';
  const downloadPdf = (panel, events, year, month, months) => {
    const { jsPDF } = root.jspdf;
    const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4', compress: true });
    doc.addFileToVFS('TimelineSans.ttf', root.KendoTimelinePdfFont);
    doc.addFont('TimelineSans.ttf', 'TimelineSans', 'normal');
    doc.setFont('TimelineSans');
    doc.setProperties({ title: 'KendoKaizen — My timeline', subject: 'Selected countries, event filters and dates' });
    const picked = events.filter(event => panel._kkCountries.has(countryFor(event)));
    const typeFilter = document.getElementById('kk-type-filter');
    const audienceFilter = document.getElementById('kk-audience-filter');
    const filters = [typeFilter?.value || 'All event types', audienceFilter?.value || 'Any audience'].join(' · ');
    const period = panel.querySelector('.kk-timeline-title').textContent;
    const colors = { competition: [23,54,93], seminar: [40,122,72], grading: [69,69,69], other: [102,113,122] };
    let first = true;
    let y = 0;
    const pageHead = (date, days) => {
      if (!first) doc.addPage();
      first = false;
      doc.setTextColor(25,30,35); doc.setFontSize(16);
      doc.text('KendoKaizen — My timeline', 12, 14);
      doc.setFontSize(10);
      doc.text(`${date.toLocaleDateString('en-GB', { month: 'long', year: 'numeric', timeZone: 'UTC' })} | ${period}`,12,21);
      doc.setFontSize(8); doc.text(filters,12,27);
      let x=12;
      categories.forEach(type => {
        doc.setFillColor(...colors[type.key]); doc.rect(x,31,5,3,'F');
        doc.text(type.label,x+7,34); x+=55;
      });
      doc.setFontSize(7); doc.setTextColor(85,90,95);
      doc.text('Multi-type events appear in each category. Events spanning months appear in each relevant month.',12,40);
      doc.text('Event / country',12,48);
      for(let i=1;i<=days;i++) doc.text(String(i),94+(i-.5)*190/days,48,{align:'center'});
      y=52;
    };
    for (let index=0;index<months;index++) {
      const current = new Date(Date.UTC(year,month+index,1));
      const days = new Date(Date.UTC(year,month+index+1,0)).getUTCDate();
      const rows = model(picked,current.getUTCFullYear(),current.getUTCMonth(),1).rows;
      if (!rows.length) continue;
      pageHead(current,days);
      const groups = new Map();
      rows.forEach(row=>{
        row.types.forEach(type=>{
          if(!groups.has(type.key)) groups.set(type.key,[]);
          groups.get(type.key).push({...row,types:[type]});
        });
      });
      [...categories,{key:'other',label:'Other events'}].filter(type=>groups.has(type.key)).forEach(type=>{
        const country=type.label, entries=groups.get(type.key);
        const heading = continued => {
          doc.setFillColor(237,240,243); doc.rect(12,y,272,7,'F');
          doc.setFontSize(9); doc.setTextColor(25,30,35);
          doc.text(country+(continued?' (continued)':''),14,y+5); y+=9;
        };
        if(y>174) pageHead(current,days);
        heading(false);
        entries.forEach(row=>{
          doc.setFontSize(8);
          const title=doc.splitTextToSize(`${row.event.title} — ${countryFor(row.event)}`,77);
          const height=Math.max(13,title.length*3.5+6);
          if(y+height>190){pageHead(current,days);heading(true);}
          doc.setTextColor(25,30,35); doc.text(title,13,y+4);
          doc.setFontSize(6); doc.setTextColor(85,90,95);
          doc.text(`${row.event.start} – ${row.event.end||row.event.start}`,13,y+height-2);
          doc.setDrawColor(221,225,229); doc.setLineWidth(.15);
          for(let i=0;i<=days;i++) doc.line(94+i*190/days,y,94+i*190/days,y+height);
          const left=94+row.offset*190/days;
          const width=row.length*190/days;
          row.types.forEach((type,i)=>{
            doc.setFillColor(...(colors[type.key]||colors.other));
            doc.rect(left+i*width/row.types.length,y+2,width/row.types.length,6,'F');
          });
          doc.line(12,y+height,284,y+height); y+=height;
        });
        y+=3;
      });
    }
    for(let i=1;i<=doc.getNumberOfPages();i++){
      doc.setPage(i); doc.setFontSize(7); doc.setTextColor(85,90,95);
      doc.text('KendoKaizen · Confirm event details with the organiser before making plans.',12,202);
      doc.text(`${i} / ${doc.getNumberOfPages()}`,284,202,{align:'right'});
    }
    doc.save(`KendoKaizen-timeline-${iso(Date.UTC(year,month,1)/DAY)}.pdf`);
  };
  const render = (panel, events, year, month, months = 3, allEvents = events) => {
    if (!panel._kkCountries) {
      const countries = [...new Set(allEvents.map(countryFor))].sort((a, b) => a.localeCompare(b));
      panel._kkCountries = new Set(countries);
    }
    panel._kkTimelineArgs = [events, year, month, months, allEvents];
    const toggleAll = panel.querySelector('[data-timeline-toggle-countries]');
    const hasSelected = panel._kkCountries.size > 0;
    toggleAll.textContent = hasSelected ? 'Untick all' : 'Tick all';
    toggleAll.setAttribute('aria-label', hasSelected ? 'Untick all countries' : 'Tick all countries');
    toggleAll.onclick = () => {
      panel._kkCountries = hasSelected ? new Set() : new Set(allEvents.map(countryFor));
      render(panel, ...panel._kkTimelineArgs);
    };
    const data = model(events.filter(event => panel._kkCountries.has(countryFor(event))), year, month, months);
    const pdfButton = panel.querySelector('[data-timeline-pdf]');
    pdfButton.disabled = data.rows.length === 0;
    pdfButton.title = data.rows.length ? 'Download your selected timeline as PDF' : 'Select countries with events to download a PDF';
    pdfButton.onclick = () => downloadPdf(panel, events, year, month, months);
    let chooser = panel.querySelector('.kk-timeline-country-picker');
    if (!chooser) {
      chooser = element('details', 'kk-timeline-country-picker');
      chooser.append(element('summary', '', 'Filter countries'));
      chooser.append(element('div', 'kk-timeline-country-options'));
      panel.querySelector('.kk-timeline-scroll').before(chooser);
    }
    const options = chooser.querySelector('.kk-timeline-country-options'); options.replaceChildren();
    [...new Set(allEvents.map(countryFor))].sort((a,b)=>a.localeCompare(b)).forEach(country => {
      const label = element('label', '');
      const input = document.createElement('input'); input.type='checkbox'; input.className='kk-timeline-country-checkbox'; input.value=country; input.checked=panel._kkCountries.has(country);
      input.addEventListener('change',()=>{
        if(input.checked) panel._kkCountries.add(country); else panel._kkCountries.delete(country);
        render(panel,...panel._kkTimelineArgs);
        [...options.querySelectorAll('input')].find(item=>item.value===country)?.focus({preventScroll:true});
      });
      label.append(input,document.createTextNode(` ${flagFor(country)} ${country}`)); options.append(label);
    });
    const format = (value, options) => new Date(value * DAY).toLocaleDateString('en-GB', { ...options, timeZone: 'UTC' });
    panel.querySelector('.kk-timeline-title').textContent = `${format(data.start, { month: 'short', year: 'numeric' })} – ${format(data.end - 1, { month: 'short', year: 'numeric' })}`;
    const grid = panel.querySelector('.kk-timeline-grid');
    grid.replaceChildren();
    grid.style.setProperty('--kk-timeline-days', data.days);
    const heading = element('div', 'kk-timeline-row kk-timeline-heading');
    heading.append(element('div', 'kk-timeline-label', 'Event type'));
    const scale = element('div', 'kk-timeline-track kk-timeline-scale');
    for (let index = 0; index < months; index++) {
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
    const groups = new Map(categories.map(type=>[type.key, []]));
    data.rows.forEach(row => row.types.forEach(type => {
      if(!groups.has(type.key)) groups.set(type.key, []);
      groups.get(type.key).push(row);
    }));
    const rowTypes = [...categories];
    if(groups.has('other')) rowTypes.push({key:'other',label:'Other events',color:'#66717A'});
    rowTypes.forEach(type => {
      const rows = groups.get(type.key) || [];
      const entry = element('div', 'kk-timeline-row'); entry.dataset.category=type.key;
      const label = element('div', 'kk-timeline-label kk-timeline-category');
      const swatch = element('span','kk-timeline-category-swatch'); swatch.style.backgroundColor=type.color; swatch.setAttribute('aria-hidden','true');
      const copy = element('span','kk-timeline-copy'); copy.append(element('strong','',type.label),element('small','',`${rows.length} events`));
      label.append(swatch,copy);
      const track = element('div', 'kk-timeline-track');
      const laneEnds = [];
      rows.forEach(row => {
      const event = row.event;
      // Reserve enough space for a readable event title as well as its date span.
      const occupiedEnd = row.offset + Math.max(row.length, 9);
      let lane = laneEnds.findIndex(end => end <= row.offset);
      if (lane === -1) lane = laneEnds.length;
      laneEnds[lane] = occupiedEnd + 1;
      const item = element('div', 'kk-timeline-item');
      item.style.left = `${row.offset / data.days * 100}%`;
      item.style.top = `${12 + lane * 62}px`;
      const bar = element('button', 'kk-timeline-bar');
      bar.type = 'button';
      bar.dataset.eventId = event.id;
      bar.style.width = `${row.length * 20}px`;
      if (row.start < data.start) bar.classList.add('continues-before');
      if (row.end >= data.end) bar.classList.add('continues-after');
      const description = `${event.title}, ${event.country || 'Country not specified'}, ${event.start} – ${event.end || event.start}, ${row.types.map(type => type.label).join(' + ')}`;
      bar.setAttribute('aria-label', description);
      bar.title = description;
      [type].forEach(type => {
        const segment = element('span', 'kk-timeline-segment');
        segment.style.backgroundColor = type.color;
        segment.setAttribute('aria-hidden', 'true');
        bar.append(segment);
      });
      const flag = element('span','kk-timeline-bar-flag',flagFor(event.country)); flag.setAttribute('aria-hidden','true'); bar.append(flag);
      const name = element('button', 'kk-timeline-event-name', event.title);
      name.type = 'button';
      name.dataset.eventId = event.id;
      name.title = description;
      name.setAttribute('aria-label', description);
      item.append(bar, name);
      track.append(item);
      });
      entry.style.minHeight = `${Math.max(82, laneEnds.length * 62 + 18)}px`;
      entry.append(label, track);
      grid.append(entry);
    });
    panel.querySelector('.kk-timeline-empty').hidden = data.rows.length > 0;
    const visibleCountries = new Set(data.rows.map(row=>countryFor(row.event))).size;
    panel.querySelector('.kk-timeline-count').textContent = `${data.rows.length} event${data.rows.length === 1 ? '' : 's'} · ${visibleCountries} countr${visibleCountries === 1 ? 'y' : 'ies'} in this period`;
  };
  const api = { day, iso, model, typesFor, flagFor, render };
  if (typeof module !== 'undefined' && module.exports) module.exports = api;
  else root.KendoTimeline = api;
})(typeof window !== 'undefined' ? window : globalThis);
