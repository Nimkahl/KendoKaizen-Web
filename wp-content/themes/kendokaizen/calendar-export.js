/* One-off calendar exports; no account access or subscription. */
(function (root) {
  'use strict';
  const escapeText = value => String(value).replace(/\\/g, '\\\\').replace(/\r?\n/g, '\\n').replace(/;/g, '\\;').replace(/,/g, '\\,');
  const fold = line => {
    let result = '', current = '', size = 0;
    for (const char of line) {
      const bytes = new TextEncoder().encode(char).length;
      if (size + bytes > 75) { result += current + '\r\n'; current = ' '; size = 1; }
      current += char; size += bytes;
    }
    return result + current;
  };
  const date = value => {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) throw new Error('Invalid date');
    const d = new Date(value + 'T00:00:00Z');
    if (!Number.isFinite(d.getTime()) || d.toISOString().slice(0, 10) !== value) throw new Error('Invalid date');
    return d;
  };
  const compact = value => value.replace(/[-:]/g, '').replace(/\.\d{3}Z$/, 'Z');
  const fields = [
    ['type', 'Event type'], ['audiences', 'Eligibility'], ['price', 'Fees'], ['priceDetails', 'Fee details'],
    ['schedule', 'Schedule'], ['level', 'Level'], ['competitionFormat', 'Competition format'],
    ['examGrades', 'Exam grades'], ['instructors', 'Instructors'], ['registrationDeadline', 'Registration deadline'],
    ['extraInformation', 'Extra information'],
  ];
  function build(event, plainDescription, eventUrl, now = new Date()) {
    const timing = event.calendarTiming || {allDay: true};
    let start, end, dtstart, dtend;
    if (timing.allDay) {
      start = date(event.start).toISOString().slice(0, 10);
      const finalDay = date(event.end || event.start);
      if (finalDay < date(start)) throw new Error('End before start');
      finalDay.setUTCDate(finalDay.getUTCDate() + 1);
      end = finalDay.toISOString().slice(0, 10);
      dtstart = 'DTSTART;VALUE=DATE:' + compact(start);
      dtend = 'DTEND;VALUE=DATE:' + compact(end);
    } else {
      if (!timing.startUtc || !timing.endUtc) throw new Error('Missing timed dates');
      start = new Date(timing.startUtc).toISOString();
      end = new Date(timing.endUtc).toISOString();
      if (end <= start) throw new Error('Invalid duration');
      dtstart = 'DTSTART:' + compact(start);
      dtend = 'DTEND:' + compact(end);
    }
    const location = [...new Set([event.venue, event.address, event.city, event.country].filter(Boolean))].join(', ');
    const details = [String(plainDescription || '').trim()];
    for (const [key, label] of fields) {
      const value = Array.isArray(event[key]) ? event[key].join(', ') : event[key];
      if (value) details.push(`${label}: ${value}`);
    }
    if (!timing.allDay && timing.timeZone) details.push(`Event time zone: ${timing.timeZone}`);
    if (event.registration) details.push(`Event page & registration: ${event.registration}`);
    details.push(`KendoKaizen event: ${eventUrl}`, 'Details may change. Please confirm them with the organiser.');
    const description = details.filter(Boolean).join('\n\n');
    const uid = `kendokaizen-event-${event.id}@kendokaizen`;
    const lines = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//KendoKaizen//Event Export//EN', 'CALSCALE:GREGORIAN',
      'BEGIN:VEVENT', `UID:${uid}`, `DTSTAMP:${compact(now.toISOString())}`, dtstart, dtend,
      `SUMMARY:${escapeText(event.title)}`, `LOCATION:${escapeText(location)}`, `DESCRIPTION:${escapeText(description)}`,
      `URL:${eventUrl.replace(/[\r\n]/g, '')}`, 'END:VEVENT', 'END:VCALENDAR'];
    const google = new URL('https://calendar.google.com/calendar/render');
    google.search = new URLSearchParams({action: 'TEMPLATE', text: event.title, dates: `${compact(start)}/${compact(end)}`, details: description, location}).toString();
    const outlook = base => {
      const url = new URL(base);
      url.search = new URLSearchParams({path: '/calendar/action/compose', rru: 'addevent', subject: event.title,
        startdt: start, enddt: end, allday: String(timing.allDay), body: description, location}).toString();
      return url.toString();
    };
    return {ics: lines.map(fold).join('\r\n') + '\r\n', description, links: {google: google.toString(),
      outlook: outlook('https://outlook.live.com/calendar/0/deeplink/compose'),
      office: outlook('https://outlook.office.com/calendar/0/deeplink/compose')}};
  }
  const api = {build};
  if (typeof module !== 'undefined' && module.exports) module.exports = api;
  else root.KendoCalendarExport = api;
})(typeof window !== 'undefined' ? window : globalThis);
