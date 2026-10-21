(() => {
  const themeButtons = [...document.querySelectorAll('[data-theme-choice]')];
  const systemTheme = window.matchMedia('(prefers-color-scheme: dark)');
  let themeChoice = localStorage.getItem('kk-theme') || 'system';

  const resolvedTheme = () => themeChoice === 'system'
    ? (systemTheme.matches ? 'dark' : 'light')
    : themeChoice;

  const applyTheme = () => {
    document.documentElement.dataset.kkTheme = resolvedTheme();
    themeButtons.forEach(button => {
      button.setAttribute('aria-pressed', String(button.dataset.themeChoice === themeChoice));
    });
  };

  themeButtons.forEach(button => button.addEventListener('click', () => {
    themeChoice = button.dataset.themeChoice;
    localStorage.setItem('kk-theme', themeChoice);
    applyTheme();
  }));

  systemTheme.addEventListener?.('change', () => {
    if (themeChoice === 'system') applyTheme();
  });

  applyTheme();



  const mobileMenuButton = document.querySelector('.kk-mobile-menu-button');
  const siteNav = document.querySelector('.kk-site-nav');

  if (mobileMenuButton && siteNav) {
    const closeMobileMenu = () => {
      siteNav.classList.remove('is-open');
      mobileMenuButton.setAttribute('aria-expanded', 'false');
      mobileMenuButton.setAttribute('aria-label', 'Open navigation');
    };

    mobileMenuButton.addEventListener('click', () => {
      const opening = !siteNav.classList.contains('is-open');
      siteNav.classList.toggle('is-open', opening);
      mobileMenuButton.setAttribute('aria-expanded', String(opening));
      mobileMenuButton.setAttribute('aria-label', opening ? 'Close navigation' : 'Open navigation');
    });

    siteNav.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMobileMenu));

    document.addEventListener('click', event => {
      if (!event.target.closest('.kk-topbar-actions')) closeMobileMenu();
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth > 720) closeMobileMenu();
    });
  }

  const partnerCarousel = document.querySelector('[data-carousel]');
  const partnerPrev = document.querySelector('[data-carousel-prev]');
  const partnerNext = document.querySelector('[data-carousel-next]');

  if (partnerCarousel && partnerPrev && partnerNext) {
    const scrollByAmount = direction => {
      const firstSlide = partnerCarousel.querySelector('.kk-partner-slide');
      const styles = window.getComputedStyle(partnerCarousel);
      const gap = parseFloat(styles.columnGap || styles.gap || '12') || 12;
      const slideWidth = firstSlide ? firstSlide.getBoundingClientRect().width + gap : partnerCarousel.clientWidth * 0.8;
      partnerCarousel.scrollBy({ left: direction * slideWidth, behavior: 'smooth' });
    };

    partnerPrev.addEventListener('click', () => scrollByAmount(-1));
    partnerNext.addEventListener('click', () => scrollByAmount(1));
  }

  const dataNode = document.getElementById('kk-event-data');
  if (!dataNode) return;

  const events = JSON.parse(dataNode.textContent || '[]');
  const eventsById = new Map(events.map(event => [String(event.id), event]));
  const eventsBySlug = new Map(events.filter(event => event.slug).map(event => [String(event.slug), event]));
  const listPanel = document.querySelector('.kk-list-panel');
  const calendarPanel = document.querySelector('.kk-calendar-panel');
  const calendarGrid = document.querySelector('.kk-calendar-grid');
  const calendarTitle = document.querySelector('.kk-calendar-title');
  const countryFilter = document.getElementById('kk-country-filter');
  const typeFilter = document.getElementById('kk-type-filter');
  const audienceFilter = document.getElementById('kk-audience-filter');
  const resultCount = document.querySelector('.kk-result-count');
  const emptyState = document.querySelector('.kk-empty');
  const mainEventList = document.querySelector('.kk-list');
  const previousEvents = document.querySelector('.kk-previous-events');
  const previousList = document.querySelector('.kk-previous-list');
  const previousCount = document.querySelector('.kk-previous-count');
  const modal = document.querySelector('.kk-modal');
  const modalDialog = document.querySelector('.kk-modal-dialog');
  const shareButton = modal.querySelector('.kk-share-button');
  const modalImage = modal.querySelector('.kk-modal-image');
  const modalPoster = modal.querySelector('.kk-modal-poster');
  const modalPlaceholder = modal.querySelector('.kk-modal-placeholder');
  const modalMapCard = modal.querySelector('.kk-modal-map-card');
  const modalMapFrame = modal.querySelector('.kk-modal-map-frame');
  const modalMapLarge = modal.querySelector('.kk-modal-map-large');
  const shareMenu = modal.querySelector('.kk-share-menu');
  const shareStatus = modal.querySelector('.kk-share-status');
  let lastTrigger = null;
  let activeEvent = null;
  const mobileCalendar = window.matchMedia('(max-width: 560px)');

  const dayModal = document.createElement('div');
  dayModal.className = 'kk-day-modal';
  dayModal.hidden = true;
  dayModal.setAttribute('aria-hidden', 'true');
  dayModal.innerHTML = `
    <div class="kk-day-modal-backdrop" data-close-day-modal></div>
    <section class="kk-day-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="kk-day-modal-title">
      <button class="kk-day-modal-close" type="button" data-close-day-modal aria-label="Close day events">×</button>
      <p class="kk-day-modal-kicker">Events on this day</p>
      <h2 id="kk-day-modal-title"></h2>
      <div class="kk-day-modal-list"></div>
    </section>`;
  document.body.appendChild(dayModal);

  const closeDayModal = () => {
    dayModal.hidden = true;
    dayModal.setAttribute('aria-hidden', 'true');
    if (modal.hidden) document.body.classList.remove('kk-modal-open');
  };

  const openDayModal = (iso, dayEvents) => {
    const date = new Date(`${iso}T12:00:00`);
    dayModal.querySelector('#kk-day-modal-title').textContent = date.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long' });
    dayModal.querySelector('.kk-day-modal-list').innerHTML = dayEvents.map(event => `
      <button class="kk-day-event-row" type="button" data-event-id="${event.id}">
        <span class="kk-day-event-type">${event.type}</span>
        <strong>${event.title}</strong>
        <span>${event.city}, ${event.country}</span>
      </button>`).join('');
    dayModal.hidden = false;
    dayModal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('kk-modal-open');
    dayModal.querySelector('.kk-day-modal-close').focus();
  };

  const firstDate = events.find(event => event.start)?.start;
  const initial = firstDate ? new Date(`${firstDate}T12:00:00`) : new Date();
  let visibleMonth = new Date(initial.getFullYear(), initial.getMonth(), 1);
  let calendarMode = mobileCalendar.matches ? 'weekend' : 'month';


  const today = new Date();
  const todayIso = [
    today.getFullYear(),
    String(today.getMonth() + 1).padStart(2, '0'),
    String(today.getDate()).padStart(2, '0'),
  ].join('-');

  const isPastEvent = event => Boolean(event && event.end && event.end < todayIso);

  const organiseEventLists = () => {
    document.querySelectorAll('.kk-card').forEach(card => {
      const button = card.querySelector('[data-event-id]');
      const event = button ? eventsById.get(String(button.dataset.eventId)) : null;
      if (!event) return;
      card.classList.toggle('is-past', isPastEvent(event));
      (isPastEvent(event) ? previousList : mainEventList).appendChild(card);
    });
  };


  const normaliseEventType = type => String(type || '').trim().toLowerCase();

  const eventTypeIconSvg = type => {
    const key = normaliseEventType(type);
    if (key === 'competition') return '<svg viewBox="0 0 20 20" aria-hidden="true"><path d="M6 3h8v2a4 4 0 0 1-3 3.87V11h3v2H6v-2h3V8.87A4 4 0 0 1 6 5V3Z"/><path d="M6 4H3.5A2.5 2.5 0 0 0 6 7"/><path d="M14 4h2.5A2.5 2.5 0 0 1 14 7"/></svg>';
    if (key === 'grading') return '<svg viewBox="0 0 20 20" aria-hidden="true"><circle cx="10" cy="10" r="7"/><path d="m6.8 10.2 2.1 2.2 4.3-4.7"/></svg>';
    if (key === 'seminar') return '<svg viewBox="0 0 20 20" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H16v11H6.5A2.5 2.5 0 0 0 4 16.5v-11Z"/><path d="M6.5 3A2.5 2.5 0 0 0 4 5.5V17h12"/><path d="M8 7h5"/><path d="M8 10h5"/></svg>';
    if (key === 'professional certifications') return '<svg viewBox="0 0 20 20" aria-hidden="true"><rect x="4" y="3.5" width="12" height="9" rx="1.5"/><path d="M7 16.5 10 13l3 3.5"/><path d="M7.5 7.5h5"/></svg>';
    if (key === 'training camp') return '<svg viewBox="0 0 20 20" aria-hidden="true"><path d="M3.5 15 10 4l6.5 11Z"/><path d="M10 4v11"/><path d="M6.8 9.5h6.4"/></svg>';
    return '<svg viewBox="0 0 20 20" aria-hidden="true"><circle cx="10" cy="10" r="7"/></svg>';
  };

  const buildCalendarEventContent = event => {
    const fragment = document.createDocumentFragment();
    const icon = document.createElement('span');
    icon.className = 'kk-calendar-event-icon';
    icon.setAttribute('aria-hidden', 'true');
    icon.innerHTML = eventTypeIconSvg(event.primaryType || event.type);
    const label = document.createElement('span');
    label.className = 'kk-calendar-event-label';
    label.textContent = event.title;
    fragment.append(icon, label);
    return fragment;
  };


  const escapeHtml = value => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

  const splitValueLines = value => String(value || '')
    .split(/\r?\n+/)
    .map(line => line.trim())
    .filter(Boolean);

  const linesMarkup = value => {
    const lines = Array.isArray(value) ? value.filter(Boolean) : splitValueLines(value);
    if (!lines.length) return '';
    if (lines.length === 1) return `<p class="kk-modal-meta-text">${escapeHtml(lines[0])}</p>`;
    return `<ul class="kk-modal-meta-list">${lines.map(line => `<li>${escapeHtml(line)}</li>`).join('')}</ul>`;
  };

  const detailRowMarkup = (label, value, extraClass = '') => {
    const body = linesMarkup(value);
    if (!body) return '';
    return `
      <div class="kk-modal-info-row ${extraClass}">
        <div class="kk-modal-meta-label">${escapeHtml(label)}</div>
        <div class="kk-modal-info-value">${body}</div>
      </div>`;
  };

  const detailGroupMarkup = (title, rows, className = '') => {
    const content = rows.filter(Boolean).join('');
    if (!content) return '';
    return `
      <section class="kk-modal-info-group ${className}">
        <h3 class="kk-modal-info-heading">${escapeHtml(title)}</h3>
        <div class="kk-modal-info-rows">${content}</div>
      </section>`;
  };

  const buildFullLocation = event => {
    const base = event.address
      ? [event.venue, event.address]
      : [event.venue, event.city, event.country];
    const pieces = base
      .flatMap(value => String(value || '').split(/,|\r?\n/))
      .map(value => value.trim())
      .filter(Boolean);
    return [...new Set(pieces)].join(', ');
  };

  const buildExtraInfo = event => {
    const typeKeys = (Array.isArray(event.types) && event.types.length ? event.types : [event.primaryType || event.type])
      .map(normaliseEventType);
    const lines = splitValueLines(event.extraInformation);

    if (typeKeys.includes('competition') && event.competitionFormat) {
      lines.push(`Competition details: ${event.competitionFormat}`);
    }
    if ((typeKeys.includes('grading') || typeKeys.includes('professional certifications')) && event.examGrades) {
      lines.push(`Exam grades: ${event.examGrades}`);
    }
    if ((typeKeys.includes('seminar') || typeKeys.includes('training camp')) && event.instructors) {
      lines.push(`Instructors: ${event.instructors}`);
    }
    if (event.level) lines.push(`Audience: ${event.level}`);

    return [...new Set(lines.filter(Boolean))];
  };

  const buildModalMeta = event => {
    const priceLines = splitValueLines(event.priceDetails);
    const extraInfo = buildExtraInfo(event);
    const practicalInfo = [];

    if (priceLines.length || event.price) {
      practicalInfo.push(detailRowMarkup('Fees', priceLines.length ? priceLines : [event.price], 'is-fees'));
    }
    if (event.schedule) {
      const scheduleLines = String(event.schedule)
        .split(/;|\r?\n+/)
        .map(line => line.trim())
        .filter(Boolean);
      practicalInfo.push(detailRowMarkup('Schedule', scheduleLines));
    }
    if (event.registrationDeadline) practicalInfo.push(detailRowMarkup('Registration deadline', event.registrationDeadline));
    if (extraInfo.length) practicalInfo.push(detailRowMarkup('Extra information', extraInfo));

    return practicalInfo.length ? detailGroupMarkup('Practical info', practicalInfo, 'is-practical') : '';
  };

  const firstWeekendFridayForMonth = monthDate => {
    const first = new Date(monthDate.getFullYear(), monthDate.getMonth(), 1);
    const offsetBack = (first.getDay() - 5 + 7) % 7;
    first.setDate(first.getDate() - offsetBack);
    return first;
  };

  const filteredEvents = () => events.filter(event => {
    return (!countryFilter.value || event.country === countryFilter.value)
      && (!typeFilter.value || (Array.isArray(event.types) ? event.types.includes(typeFilter.value) : event.type === typeFilter.value))
      && (!audienceFilter.value || (Array.isArray(event.audiences) ? event.audiences.includes(audienceFilter.value) : event.audience === audienceFilter.value));
  });

  const updateFilters = () => {
    const allowed = new Set(filteredEvents().map(event => String(event.id)));
    let visible = 0;
    let visibleUpcoming = 0;
    let visiblePrevious = 0;

    document.querySelectorAll('.kk-card').forEach(card => {
      const button = card.querySelector('[data-event-id]');
      const event = button ? eventsById.get(String(button.dataset.eventId)) : null;
      const show = Boolean(button && event && allowed.has(button.dataset.eventId));
      card.hidden = !show;
      if (show) {
        visible += 1;
        if (isPastEvent(event)) visiblePrevious += 1;
        else visibleUpcoming += 1;
      }
    });

    previousEvents.hidden = visiblePrevious === 0;
    previousCount.textContent = visiblePrevious ? `${visiblePrevious}` : '';
    resultCount.textContent = `${visible} event${visible === 1 ? '' : 's'}`;
    emptyState.hidden = visible !== 0;
    mainEventList.classList.toggle('is-empty', visibleUpcoming === 0);
    renderCalendar();
  };

  const isoForDate = date => [
    date.getFullYear(),
    String(date.getMonth() + 1).padStart(2, '0'),
    String(date.getDate()).padStart(2, '0'),
  ].join('-');

  const assignCalendarLanes = segments => {
    const laneEnds = [];
    return segments
      .sort((a, b) => (a.startCol - b.startCol) || (b.endCol - a.endCol))
      .map(segment => {
        let lane = laneEnds.findIndex(endCol => endCol < segment.startCol);
        if (lane === -1) {
          lane = laneEnds.length;
          laneEnds.push(segment.endCol);
        } else {
          laneEnds[lane] = segment.endCol;
        }
        return { ...segment, lane };
      });
  };

  const buildCalendarWeek = (dates, matchingEvents, month, columns) => {
    const week = document.createElement('div');
    week.className = 'kk-calendar-week';
    week.style.setProperty('--kk-calendar-columns', String(columns));

    const visibleStart = isoForDate(dates[0]);
    const visibleEnd = isoForDate(dates[dates.length - 1]);

    dates.forEach((date, index) => {
      const iso = isoForDate(date);
      const outside = date.getMonth() !== month;
      const cell = document.createElement('div');
      cell.className = `kk-day${columns === 3 ? ' kk-weekend-day' : ''}${outside ? ' is-outside' : ''}`;
      cell.style.gridColumn = String(index + 1);
      cell.innerHTML = `<span class="kk-day-number">${date.getDate()}</span>`;

      const dayEvents = matchingEvents.filter(event => event.start <= iso && event.end >= iso);
      if (mobileCalendar.matches && dayEvents.length) {
        cell.dataset.day = iso;
        cell.dataset.hasEvents = 'true';
      }
      week.appendChild(cell);
    });

    const segments = matchingEvents
      .filter(event => event.start <= visibleEnd && event.end >= visibleStart)
      .map(event => {
        const startIso = event.start < visibleStart ? visibleStart : event.start;
        const endIso = event.end > visibleEnd ? visibleEnd : event.end;
        const startCol = dates.findIndex(date => isoForDate(date) === startIso) + 1;
        const endCol = dates.findIndex(date => isoForDate(date) === endIso) + 1;
        return {
          event,
          startCol,
          endCol,
          continuesBefore: event.start < visibleStart,
          continuesAfter: event.end > visibleEnd,
        };
      })
      .filter(segment => segment.startCol > 0 && segment.endCol > 0);

    const laidOut = assignCalendarLanes(segments);
    const laneCount = laidOut.reduce((max, item) => Math.max(max, item.lane + 1), 0);
    const visibleLaneCount = Math.max(laneCount, 1);
    const firstEventOffset = 50;
    const laneStep = 42;
    week.style.setProperty('--kk-calendar-min-height', `${firstEventOffset + (visibleLaneCount * laneStep) + 12}px`);

    laidOut.forEach(segment => {
      const button = document.createElement('button');
      button.className = 'kk-calendar-event kk-calendar-span-event';
      if (segment.continuesBefore) button.classList.add('continues-before');
      if (segment.continuesAfter) button.classList.add('continues-after');
      if (isPastEvent(segment.event)) button.classList.add('is-past');
      button.type = 'button';
      button.dataset.eventId = segment.event.id;
      button.dataset.eventType = segment.event.type;
      button.appendChild(buildCalendarEventContent(segment.event));
      button.style.gridColumn = `${segment.startCol} / ${segment.endCol + 1}`;
      button.style.marginTop = `${firstEventOffset + (segment.lane * laneStep)}px`;
      week.appendChild(button);
    });

    return week;
  };

  const renderCalendar = () => {
    const matchingEvents = filteredEvents();
    const weekendMode = mobileCalendar.matches && calendarMode === 'weekend';
    calendarGrid.classList.toggle('is-weekend-view', weekendMode);
    calendarGrid.innerHTML = '';

    if (weekendMode) {
      const year = visibleMonth.getFullYear();
      const month = visibleMonth.getMonth();
      const lastDay = new Date(year, month + 1, 0);
      const firstFriday = firstWeekendFridayForMonth(visibleMonth);

      calendarTitle.textContent = visibleMonth.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });

      ['Fri', 'Sat', 'Sun'].forEach(day => {
        const cell = document.createElement('div');
        cell.className = 'kk-weekday';
        cell.textContent = day;
        calendarGrid.appendChild(cell);
      });

      for (let weekendStart = new Date(firstFriday); weekendStart <= lastDay; weekendStart.setDate(weekendStart.getDate() + 7)) {
        const dates = [];
        for (let i = 0; i < 3; i += 1) {
          const date = new Date(weekendStart);
          date.setDate(weekendStart.getDate() + i);
          dates.push(date);
        }
        calendarGrid.appendChild(buildCalendarWeek(dates, matchingEvents, month, 3));
      }
      return;
    }

    const year = visibleMonth.getFullYear();
    const month = visibleMonth.getMonth();
    calendarTitle.textContent = visibleMonth.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });

    ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].forEach(day => {
      const cell = document.createElement('div');
      cell.className = 'kk-weekday';
      cell.textContent = day;
      calendarGrid.appendChild(cell);
    });

    const firstDay = new Date(year, month, 1);
    const mondayOffset = (firstDay.getDay() + 6) % 7;
    const gridStart = new Date(year, month, 1 - mondayOffset);

    for (let weekIndex = 0; weekIndex < 6; weekIndex += 1) {
      const dates = [];
      for (let dayIndex = 0; dayIndex < 7; dayIndex += 1) {
        const date = new Date(gridStart);
        date.setDate(gridStart.getDate() + (weekIndex * 7) + dayIndex);
        dates.push(date);
      }
      calendarGrid.appendChild(buildCalendarWeek(dates, matchingEvents, month, 7));
    }
  };

  const shareUrlFor = event => {
    const url = new URL(window.location.href);
    url.hash = `event=${encodeURIComponent(event.slug || event.id)}`;
    return url.toString();
  };

  const closeShareMenu = () => {
    shareMenu.hidden = true;
    shareButton.setAttribute('aria-expanded', 'false');
  };

  const prepareShareMenu = event => {
    const url = shareUrlFor(event);
    const text = `${event.title} — ${event.dateLabel}`;
    modal.querySelector('[data-share-channel="whatsapp"]').href = `https://wa.me/?text=${encodeURIComponent(`${text} ${url}`)}`;
    modal.querySelector('[data-share-channel="telegram"]').href = `https://t.me/share/url?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}`;
    modal.querySelector('[data-share-channel="email"]').href = `mailto:?subject=${encodeURIComponent(event.title)}&body=${encodeURIComponent(`${text}\n\n${url}`)}`;
    shareStatus.textContent = '';
    closeShareMenu();
  };

  const copyShareLink = async url => {
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(url);
      return;
    }
    const input = document.createElement('textarea');
    input.value = url;
    input.setAttribute('readonly', '');
    input.style.position = 'fixed';
    input.style.opacity = '0';
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    input.remove();
  };

  const openModal = (eventId, trigger, updateUrl = true) => {
    const event = eventsById.get(String(eventId));
    if (!event) return;
    activeEvent = event;
    lastTrigger = trigger || null;

    const isPlaceholderPoster = /kendokaizen-(temp|demo)\//.test(event.poster || '');

    if (isPlaceholderPoster) {
      modalImage.classList.add('has-placeholder');
      modalPoster.hidden = true;
      modalPoster.src = '';
      modalPoster.alt = '';
      modalPlaceholder.hidden = false;
      modalPlaceholder.setAttribute('aria-hidden', 'false');
      const placeholderCard = modalPlaceholder.querySelector('.kk-modal-placeholder-card');
      placeholderCard.classList.toggle('is-long-title', event.title.length > 20);
      placeholderCard.classList.toggle('is-very-long-title', event.title.length > 30);
      modalPlaceholder.querySelector('.kk-modal-placeholder-title').textContent = event.title;
      modalPlaceholder.querySelector('.kk-modal-placeholder-date').textContent = event.dateLabel;
      modalPlaceholder.querySelector('.kk-modal-placeholder-location').textContent = `${event.city} · ${event.country}`.toUpperCase();
      modalPlaceholder.querySelector('.kk-modal-placeholder-type-label').textContent = event.type;
      modalPlaceholder.querySelector('.kk-modal-placeholder-type-icon').innerHTML = eventTypeIconSvg(event.primaryType || event.type);
    } else {
      modalImage.classList.remove('has-placeholder');
      modalPlaceholder.hidden = true;
      modalPlaceholder.setAttribute('aria-hidden', 'true');
      modalPoster.hidden = false;
      modalPoster.src = event.poster;
      modalPoster.alt = `Poster for ${event.title}`;
    }

    modal.querySelector('.kk-modal-kicker').textContent = `${event.type} · ${event.dateLabel}`;
    modal.querySelector('#kk-modal-title').textContent = event.title;
    const fullAddress = buildFullLocation(event);
    const mapQuery = fullAddress || [event.city, event.country].filter(Boolean).join(', ');
    const mapUrl = mapQuery ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(mapQuery)}` : '#';
    const mapEmbedUrl = mapQuery ? `https://www.google.com/maps?q=${encodeURIComponent(mapQuery)}&output=embed` : '';
    modal.querySelector('.kk-modal-location').innerHTML = fullAddress
      ? `<a class="kk-modal-map-link" href="${mapUrl}" target="_blank" rel="noopener noreferrer" aria-label="Open ${escapeHtml(fullAddress)} in maps"><span aria-hidden="true">📍</span><span>${escapeHtml(fullAddress)}</span></a>`
      : '';
    if (mapQuery) {
      modalMapCard.hidden = false;
      modalMapFrame.src = mapEmbedUrl;
      modalMapLarge.href = mapUrl;
    } else {
      modalMapCard.hidden = true;
      modalMapFrame.src = '';
      modalMapLarge.href = '#';
    }
    modal.querySelector('.kk-modal-description').innerHTML = event.description || '';
    modal.querySelector('.kk-modal-meta').innerHTML = buildModalMeta(event);
    modal.querySelector('.kk-modal-link').href = event.registration || event.url;
    prepareShareMenu(event);
    modal.hidden = false;
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('kk-modal-open');
    if (updateUrl) history.replaceState(null, '', shareUrlFor(event));
    modal.querySelector('.kk-close').focus();
  };

  const closeModal = (updateUrl = true) => {
    closeShareMenu();
    modal.hidden = true;
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('kk-modal-open');
    activeEvent = null;
    if (updateUrl && window.location.hash.startsWith('#event=')) {
      const url = new URL(window.location.href);
      url.hash = '';
      history.replaceState(null, '', `${url.pathname}${url.search}`);
    }
    if (lastTrigger) lastTrigger.focus();
  };

  document.addEventListener('click', async event => {
    const moreButton = event.target.closest('.kk-calendar-more');
    if (moreButton) {
      const iso = moreButton.dataset.day;
      const dayEvents = filteredEvents().filter(item => item.start <= iso && item.end >= iso);
      openDayModal(iso, dayEvents);
      return;
    }

    const dayCell = event.target.closest('.kk-day[data-has-events="true"]');
    if (dayCell && !event.target.closest('.kk-calendar-event')) {
      const iso = dayCell.dataset.day;
      const dayEvents = filteredEvents().filter(item => item.start <= iso && item.end >= iso);
      openDayModal(iso, dayEvents);
      return;
    }

    const eventButton = event.target.closest('[data-event-id]');
    if (eventButton) {
      if (eventButton.closest('.kk-day-modal')) closeDayModal();
      openModal(eventButton.dataset.eventId, eventButton);
    }
    if (event.target.closest('[data-close-day-modal]')) closeDayModal();
    if (event.target.closest('[data-close-modal]')) closeModal();

    if (event.target.closest('.kk-share-button') && activeEvent) {
      const url = shareUrlFor(activeEvent);
      const useNativeShare = navigator.share && (navigator.maxTouchPoints > 0 || window.matchMedia('(pointer: coarse)').matches);
      if (useNativeShare) {
        try {
          await navigator.share({
            title: activeEvent.title,
            text: `${activeEvent.title} — ${activeEvent.dateLabel}`,
            url,
          });
        } catch (error) {
          if (error.name !== 'AbortError') shareStatus.textContent = 'Sharing is not available. Please copy the link.';
        }
      } else {
        shareMenu.hidden = !shareMenu.hidden;
        shareButton.setAttribute('aria-expanded', String(!shareMenu.hidden));
      }
    }

    if (event.target.closest('[data-share-copy]') && activeEvent) {
      try {
        await copyShareLink(shareUrlFor(activeEvent));
        shareStatus.textContent = 'Link copied.';
      } catch (error) {
        shareStatus.textContent = 'Could not copy the link.';
      }
    }

    if (!event.target.closest('.kk-share')) closeShareMenu();

    const viewButton = event.target.closest('[data-view]');
    if (viewButton) {
      document.querySelectorAll('[data-view]').forEach(button => button.classList.toggle('is-active', button === viewButton));
      const showCalendar = viewButton.dataset.view === 'calendar';
      listPanel.hidden = showCalendar;
      calendarPanel.hidden = !showCalendar;
      if (showCalendar) renderCalendar();
    }

    const modeButton = event.target.closest('[data-calendar-mode]');
    if (modeButton && mobileCalendar.matches) {
      calendarMode = modeButton.dataset.calendarMode;
      document.querySelectorAll('[data-calendar-mode]').forEach(button => {
        const active = button === modeButton;
        button.classList.toggle('is-active', active);
        button.setAttribute('aria-pressed', String(active));
      });
      renderCalendar();
      return;
    }

    const monthButton = event.target.closest('[data-month-step]');
    if (monthButton) {
      const step = Number(monthButton.dataset.monthStep);
      visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() + step, 1);
      renderCalendar();
    }
  });

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && !shareMenu.hidden) {
      closeShareMenu();
      shareButton.focus();
    } else if (event.key === 'Escape' && !dayModal.hidden) {
      closeDayModal();
    } else if (event.key === 'Escape' && !modal.hidden) {
      closeModal();
    }
    if (event.key === 'Tab' && !modal.hidden) {
      const focusable = [...modalDialog.querySelectorAll('button, a[href]')]
        .filter(element => !element.closest('[hidden]'));
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    }
  });

  countryFilter.addEventListener('change', updateFilters);
  typeFilter.addEventListener('change', updateFilters);
  audienceFilter.addEventListener('change', updateFilters);
  const syncCalendarModeButtons = () => {
    document.querySelectorAll('[data-calendar-mode]').forEach(button => {
      const active = button.dataset.calendarMode === calendarMode;
      button.classList.toggle('is-active', active);
      button.setAttribute('aria-pressed', String(active));
    });
  };

  mobileCalendar.addEventListener?.('change', event => {
    calendarMode = event.matches ? 'weekend' : 'month';
    syncCalendarModeButtons();
    renderCalendar();
  });
  syncCalendarModeButtons();
  organiseEventLists();
  updateFilters();

  const hashMatch = window.location.hash.match(/^#event=(.+)$/);
  if (hashMatch) {
    const key = decodeURIComponent(hashMatch[1]);
    const linkedEvent = eventsBySlug.get(key) || eventsById.get(key);
    if (linkedEvent) openModal(linkedEvent.id, null, false);
  }
})();
