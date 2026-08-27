(() => {
  const root = document.querySelector('[data-prayer-root]');
  if (!root) return;

  const toggle = root.querySelector('[data-city-toggle]');
  const panel = root.querySelector('[data-city-panel]');
  const search = root.querySelector('[data-city-search]');
  const list = root.querySelector('[data-city-list]');
  const empty = root.querySelector('[data-city-empty]');
  const label = root.querySelector('[data-city-label]');
  const meta = root.querySelector('[data-prayer-meta]');
  const errorBox = root.querySelector('[data-prayer-error]');
  const storeKey = 'ic_prayer_city';
  let cities = [];
  let open = false;
  let activeCity = {
    name: root.getAttribute('data-city') || 'Firozabad',
    state: root.getAttribute('data-state') || 'Uttar Pradesh',
  };

  const currentCity = () => ({
    name: root.getAttribute('data-city') || 'Firozabad',
    state: root.getAttribute('data-state') || 'Uttar Pradesh',
  });

  const setOpen = (next) => {
    open = next;
    if (!panel || !toggle) return;
    panel.hidden = !open;
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open && search) {
      search.focus();
      renderList(search.value);
    }
  };

  const renderList = (query) => {
    if (!list) return;
    const q = (query || '').trim().toLowerCase();
    const matches = cities.filter((city) => {
      const hay = (city.name + ' ' + city.state).toLowerCase();
      return q === '' || hay.includes(q);
    }).slice(0, 80);
    list.innerHTML = '';
    matches.forEach((city) => {
      const item = document.createElement('li');
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.textContent = city.name + ', ' + city.state;
      btn.addEventListener('click', () => selectCity(city));
      item.appendChild(btn);
      list.appendChild(item);
    });
    if (empty) empty.hidden = matches.length > 0;
  };

  const paint = (data) => {
    if (!data) return;
    root.setAttribute('data-city', data.city || '');
    root.setAttribute('data-state', data.state || '');
    root.setAttribute('data-date', data.for_date || '');
    if (label) label.textContent = (data.city || '') + (data.state ? ', ' + data.state : '');
    if (meta) {
      meta.textContent = [data.weekday, data.date, data.live ? 'Live AlAdhan' : 'Calculated · confirming live…'].filter(Boolean).join(' · ');
    }
    if (errorBox) {
      if (!data.ok && data.error) {
        errorBox.hidden = false;
        errorBox.textContent = data.error;
      } else {
        errorBox.hidden = true;
        errorBox.textContent = '';
      }
    }
    (data.prayers || []).forEach((prayer) => {
      const card = root.querySelector('[data-prayer-card="' + prayer.key + '"]');
      if (!card) return;
      const time = card.querySelector('[data-prayer-time]');
      if (time) time.textContent = prayer.time || '—';
    });
    markNow();
  };

  const minutesFromLabel = (text) => {
    const raw = (text || '').trim();
    const twelve = raw.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
    if (twelve) {
      let hour = parseInt(twelve[1], 10);
      const minute = parseInt(twelve[2], 10);
      const suffix = twelve[3].toUpperCase();
      if (suffix === 'AM') {
        if (hour === 12) hour = 0;
      } else if (hour !== 12) {
        hour += 12;
      }
      return hour * 60 + minute;
    }
    const twentyFour = raw.match(/^(\d{1,2}):(\d{2})$/);
    if (!twentyFour) return null;
    return parseInt(twentyFour[1], 10) * 60 + parseInt(twentyFour[2], 10);
  };

  const istNow = () => {
    const parts = new Intl.DateTimeFormat('en-GB', {
      timeZone: 'Asia/Kolkata',
      hour: 'numeric',
      minute: 'numeric',
      weekday: 'short',
      hourCycle: 'h23',
    }).formatToParts(new Date());
    const read = (type) => (parts.find((part) => part.type === type) || {}).value || '';
    return {
      minutes: parseInt(read('hour'), 10) * 60 + parseInt(read('minute'), 10),
      friday: read('weekday') === 'Fri',
    };
  };

  const markNow = () => {
    const clock = istNow();
    const order = clock.friday
      ? ['fajr', 'jummah', 'asr', 'maghrib', 'isha']
      : ['fajr', 'zuhr', 'asr', 'maghrib', 'isha'];
    let current = 'isha';
    order.forEach((key) => {
      const card = root.querySelector('[data-prayer-card="' + key + '"]');
      const label = card ? card.querySelector('[data-prayer-time]') : null;
      const value = minutesFromLabel(label ? label.textContent : '');
      if (value !== null && clock.minutes >= value) {
        current = key;
      }
    });
    root.querySelectorAll('[data-prayer-card]').forEach((card) => {
      card.classList.toggle('is-now', card.getAttribute('data-prayer-card') === current);
    });
  };

  const loadTimes = (city) => {
    const apply = (data) => {
      paint(data);
      return data;
    };
    const localApi = () => {
      const api = root.getAttribute('data-api') || '/api/prayer-times';
      const params = new URLSearchParams({ city: city.name, state: city.state || '', _: String(Date.now()) });
      return fetch(api + (api.includes('?') ? '&' : '?') + params.toString(), { cache: 'no-store', headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then(apply);
    };
    if (window.ICLive && typeof ICLive.prayerTimes === 'function') {
      return ICLive.prayerTimes(city.name, city.state).then(apply).catch(localApi);
    }
    return localApi();
  };

  const selectCity = (city) => {
    try {
      localStorage.setItem(storeKey, JSON.stringify(city));
    } catch (e) {}
    setOpen(false);
    activeCity = city;
    loadTimes(city);
  };

  const todayStamp = () => new Intl.DateTimeFormat('en-CA', {
    timeZone: 'Asia/Kolkata',
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  }).format(new Date());

  if (toggle) {
    toggle.addEventListener('click', () => setOpen(!open));
  }
  if (search) {
    search.addEventListener('input', () => renderList(search.value));
  }
  document.addEventListener('click', (event) => {
    if (open && !root.querySelector('[data-city-picker]')?.contains(event.target)) {
      setOpen(false);
    }
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') setOpen(false);
  });

  fetch(root.getAttribute('data-cities') || '/assets/data/india-cities.json')
    .then((res) => res.json())
    .then((rows) => {
      cities = Array.isArray(rows) ? rows : [];
    })
    .catch(() => {
      cities = [{ name: 'Firozabad', state: 'Uttar Pradesh' }];
    });

  let saved = null;
  try {
    saved = JSON.parse(localStorage.getItem(storeKey) || 'null');
  } catch (e) {
    saved = null;
  }
  const start = saved && saved.name ? saved : currentCity();
  activeCity = start;
  loadTimes(start);

  markNow();
  setInterval(() => {
    const shown = root.getAttribute('data-date');
    if (shown && shown !== todayStamp()) {
      loadTimes(activeCity);
      return;
    }
    markNow();
  }, 1000);
  if (window.ICLive && typeof ICLive.watchFresh === 'function') {
    ICLive.watchFresh(() => loadTimes(activeCity));
  }
})();
