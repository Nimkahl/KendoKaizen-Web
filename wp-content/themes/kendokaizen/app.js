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
  const resultCount = document.querySelector('.kk-result-count');
  const emptyState = document.querySelector('.kk-empty');
  const modal = document.querySelector('.kk-modal');
  const modalDialog = document.querySelector('.kk-modal-dialog');
  const shareButton = modal.querySelector('.kk-share-button');
  const shareMenu = modal.querySelector('.kk-share-menu');
  const shareStatus = modal.querySelector('.kk-share-status');
  let lastTrigger = null;
  let activeEvent = null;

  const firstDate = events.find(event => event.start)?.start;
  const initial = firstDate ? new Date(`${firstDate}T12:00:00`) : new Date();
  let visibleMonth = new Date(initial.getFullYear(), initial.getMonth(), 1);

  const filteredEvents = () => events.filter(event => {
    return (!countryFilter.value || event.country === countryFilter.value)
      && (!typeFilter.value || event.type === typeFilter.value);
  });

  const updateFilters = () => {
    const allowed = new Set(filteredEvents().map(event => String(event.id)));
    let visible = 0;
    document.querySelectorAll('.kk-card').forEach(card => {
      const button = card.querySelector('[data-event-id]');
      const show = button && allowed.has(button.dataset.eventId);
      card.hidden = !show;
      if (show) visible += 1;
    });
    resultCount.textContent = `${visible} event${visible === 1 ? '' : 's'}`;
    emptyState.hidden = visible !== 0;
    renderCalendar();
  };

  const renderCalendar = () => {
    const year = visibleMonth.getFullYear();
    const month = visibleMonth.getMonth();
    calendarTitle.textContent = visibleMonth.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });
    calendarGrid.innerHTML = '';

    ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].forEach(day => {
      const cell = document.createElement('div');
      cell.className = 'kk-weekday';
      cell.textContent = day;
      calendarGrid.appendChild(cell);
    });

    const firstDay = new Date(year, month, 1);
    const mondayOffset = (firstDay.getDay() + 6) % 7;
    const gridStart = new Date(year, month, 1 - mondayOffset);
    const matchingEvents = filteredEvents();

    for (let i = 0; i < 42; i += 1) {
      const date = new Date(gridStart);
      date.setDate(gridStart.getDate() + i);
      const iso = [date.getFullYear(), String(date.getMonth() + 1).padStart(2, '0'), String(date.getDate()).padStart(2, '0')].join('-');
      const cell = document.createElement('div');
      cell.className = `kk-day${date.getMonth() !== month ? ' is-outside' : ''}`;
      cell.innerHTML = `<span class="kk-day-number">${date.getDate()}</span>`;

      matchingEvents.filter(event => event.start <= iso && event.end >= iso).forEach(event => {
        const button = document.createElement('button');
        button.className = 'kk-calendar-event';
        button.type = 'button';
        button.dataset.eventId = event.id;
        button.textContent = event.title;
        cell.appendChild(button);
      });
      calendarGrid.appendChild(cell);
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
    modal.querySelector('.kk-modal-image img').src = event.poster;
    modal.querySelector('.kk-modal-image img').alt = `Poster for ${event.title}`;
    modal.querySelector('.kk-modal-kicker').textContent = `${event.type} · ${event.dateLabel}`;
    modal.querySelector('#kk-modal-title').textContent = event.title;
    modal.querySelector('.kk-modal-location').textContent = `${event.venue} · ${event.city}, ${event.country}`;
    modal.querySelector('.kk-modal-description').textContent = event.description;
    modal.querySelector('.kk-modal-meta').innerHTML = `
      <div><strong>Approx. price</strong><span>${event.price}</span></div>
      <div><strong>Level</strong><span>${event.level}</span></div>
      <div><strong>Instructors</strong><span>${event.instructors}</span></div>
      <div><strong>Country</strong><span>${event.country}</span></div>`;
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
    const eventButton = event.target.closest('[data-event-id]');
    if (eventButton) openModal(eventButton.dataset.eventId, eventButton);
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

    const monthButton = event.target.closest('[data-month-step]');
    if (monthButton) {
      visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() + Number(monthButton.dataset.monthStep), 1);
      renderCalendar();
    }
  });

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && !shareMenu.hidden) {
      closeShareMenu();
      shareButton.focus();
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
  updateFilters();

  const hashMatch = window.location.hash.match(/^#event=(.+)$/);
  if (hashMatch) {
    const key = decodeURIComponent(hashMatch[1]);
    const linkedEvent = eventsBySlug.get(key) || eventsById.get(key);
    if (linkedEvent) openModal(linkedEvent.id, null, false);
  }
})();
