(() => {
  const root = document.querySelector('[data-ramadan-root]');
  if (!root) return;

  const toggle = root.querySelector('[data-city-toggle]');
  const panel = root.querySelector('[data-city-panel]');
  const search = root.querySelector('[data-city-search]');
  const list = root.querySelector('[data-city-list]');
  const empty = root.querySelector('[data-city-empty]');
  const label = root.querySelector('[data-city-label]');
  const meta = root.querySelector('[data-ramadan-meta]');
  const errorBox = root.querySelector('[data-ramadan-error]');
  const cal = root.querySelector('[data-roza-cal]');
  const countPanel = root.querySelector('[data-ramadan-count]');
  const flag = document.querySelector('[data-ramadan-flag]');
  const storeKey = 'ic_ramadan_city';
  let cities = [];
  let open = false;
  let page = null;

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

  const setText = (sel, value) => {
    const el = root.querySelector(sel);
    if (el) el.textContent = value || '—';
  };

  const paint = (data) => {
    page = data;
    if (!data) return;
    root.setAttribute('data-city', data.city || '');
    root.setAttribute('data-state', data.state || '');
    root.setAttribute('data-is-ramadan', data.is_ramadan ? '1' : '0');
    if (label) label.textContent = (data.city || '') + (data.state ? ', ' + data.state : '');
    const row = data.today_row || {};
    const greg = row.gregorian_label || '';
    const hijri = row.hijri_label || '';
    if (meta) meta.textContent = ['Today', greg, hijri].filter(Boolean).join(' · ');
    setText('[data-today-dates]', [greg, hijri].filter(Boolean).join(' · '));
    if (flag && data.next_ramadan_label) flag.textContent = data.next_ramadan_label;
    if (errorBox) {
      if (!data.ok && data.error) {
        errorBox.hidden = false;
        errorBox.textContent = data.error;
      } else {
        errorBox.hidden = true;
        errorBox.textContent = '';
      }
    }
    setText('[data-sehri-time]', row.fajr || '—');
    setText('[data-iftar-time]', row.maghrib || '—');
    setText('[data-imsak-time]', 'Imsak ' + (row.imsak || '—'));
    setText('[data-greg-date]', greg);
    setText('[data-hijri-date]', hijri);
    setText('[data-iftar-greg]', greg);
    setText('[data-iftar-place]', 'Sunset in ' + (data.city || 'Firozabad'));

    const start = data.ramadan_start || {};
    if (countPanel) {
      countPanel.setAttribute('data-start-unix', String(start.unix || 0));
      countPanel.setAttribute('data-is-ramadan', data.is_ramadan ? '1' : '0');
      countPanel.setAttribute('data-remaining', String(data.remaining_rozas || 0));
      countPanel.classList.toggle('is-live', !!data.is_ramadan);
      setText('[data-start-greg]', start.gregorian_label || '—');
      setText('[data-start-hijri]', start.hijri_label || '—');
      const kicker = countPanel.querySelector('[data-count-kicker]');
      const title = countPanel.querySelector('[data-count-title]');
      const units = countPanel.querySelector('[data-count-units]');
      const note = countPanel.querySelector('[data-count-note]');
      if (data.is_ramadan) {
        if (kicker) kicker.textContent = 'The blessed month';
        if (title) title.textContent = 'Ramadan is here';
        if (units) units.hidden = true;
        if (note) {
          note.hidden = false;
          note.textContent = 'Day ' + (row.hijri_day || '') + ' of Ramadan · ' + (data.remaining_rozas || 0) + ' fasts remain, including today.';
        }
      } else {
        if (kicker) kicker.textContent = 'Until Ramadan';
        if (title) title.textContent = 'Ramadan begins in';
        if (units) units.hidden = false;
        if (note) note.hidden = true;
      }
    }
    if (cal) {
      cal.innerHTML = '';
      const days = data.days || [];
      if (!days.length) {
        const emptyCal = document.createElement('div');
        emptyCal.className = 'empty-state';
        emptyCal.style.gridColumn = '1 / -1';
        emptyCal.innerHTML = '<h3>Roza calendar will appear here</h3><p>The Ramadan timetable for this city is still loading, or could not be fetched just now.</p>';
        cal.appendChild(emptyCal);
      }
      days.forEach((day) => {
        const card = document.createElement('article');
        card.className = 'roza-day' + (day.is_today ? ' is-today' : '');
        card.innerHTML = '<span class="roza-num">' + day.hijri_day + '</span>'
          + '<strong>' + (day.weekday || '') + '</strong>'
          + '<em>' + (day.gregorian_label || '') + '</em>'
          + '<p><span>Sehri</span> ' + (day.fajr || '') + '</p>'
          + '<p><span>Iftar</span> ' + (day.maghrib || '') + '</p>';
        cal.appendChild(card);
      });
    }
    tickAll();
  };

  const parseStamp = (label) => {
    if (!label || !page || !page.today_row) return null;
    const m = String(label).match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
    if (!m) return null;
    let hour = parseInt(m[1], 10);
    const minute = parseInt(m[2], 10);
    const ap = m[3].toUpperCase();
    if (ap === 'PM' && hour !== 12) hour += 12;
    if (ap === 'AM' && hour === 12) hour = 0;
    const iso = page.today_row.gregorian_iso;
    if (!iso) return null;
    return new Date(iso + 'T' + String(hour).padStart(2, '0') + ':' + String(minute).padStart(2, '0') + ':00+05:30');
  };

  const formatRemain = (ms) => {
    if (ms <= 0) return '';
    const h = Math.floor(ms / 3600000);
    const m = Math.floor((ms % 3600000) / 60000);
    const s = Math.floor((ms % 60000) / 1000);
    const parts = [];
    if (h) parts.push(h + 'h');
    parts.push(m + 'm');
    parts.push(s + 's');
    return parts.join(' ');
  };

  const tickDaily = () => {
    if (!page) return;
    const row = page.today_row || {};
    const now = new Date();
    const sehri = parseStamp(row.fajr);
    const iftar = parseStamp(row.maghrib);
    const sehriLive = root.querySelector('[data-sehri-live]');
    const iftarLive = root.querySelector('[data-iftar-live]');
    if (sehriLive) {
      if (sehri && now < sehri) sehriLive.textContent = 'Ends in ' + formatRemain(sehri.getTime() - now.getTime());
      else sehriLive.textContent = 'Sehri has ended for today.';
    }
    if (iftarLive) {
      if (iftar && now < iftar) iftarLive.textContent = 'Iftar in ' + formatRemain(iftar.getTime() - now.getTime());
      else iftarLive.textContent = 'Iftar has passed for today.';
    }
  };

  const tickCountdown = () => {
    if (!countPanel) return;
    const live = page ? !!page.is_ramadan : countPanel.getAttribute('data-is-ramadan') === '1';
    if (live) return;
    const unix = Number(countPanel.getAttribute('data-start-unix') || 0);
    const daysEl = countPanel.querySelector('[data-c-days]');
    const hoursEl = countPanel.querySelector('[data-c-hours]');
    const minsEl = countPanel.querySelector('[data-c-mins]');
    const secsEl = countPanel.querySelector('[data-c-secs]');
    if (!unix) {
      if (daysEl) daysEl.textContent = '—';
      return;
    }
    let ms = unix * 1000 - Date.now();
    if (ms < 0) ms = 0;
    const days = Math.floor(ms / 86400000);
    const hours = Math.floor((ms % 86400000) / 3600000);
    const mins = Math.floor((ms % 3600000) / 60000);
    const secs = Math.floor((ms % 60000) / 1000);
    if (daysEl) daysEl.textContent = String(days);
    if (hoursEl) hoursEl.textContent = String(hours);
    if (minsEl) minsEl.textContent = String(mins);
    if (secsEl) secsEl.textContent = String(secs);
  };

  const tickAll = () => {
    tickDaily();
    tickCountdown();
  };

  const load = (city) => {
    const localApi = () => {
      const api = root.getAttribute('data-api') || '/api/ramadan';
      const params = new URLSearchParams({ city: city.name, state: city.state || '' });
      return fetch(api + (api.includes('?') ? '&' : '?') + params.toString(), { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then(paint);
    };
    if (window.ICLive && typeof ICLive.ramadanPage === 'function') {
      return ICLive.ramadanPage(city.name, city.state).then((data) => {
        data.duas = data.duas || (page && page.duas);
        paint(data);
      }).catch(localApi);
    }
    return localApi();
  };

  const selectCity = (city) => {
    try { localStorage.setItem(storeKey, JSON.stringify(city)); } catch (e) {}
    setOpen(false);
    load(city);
  };

  if (toggle) toggle.addEventListener('click', () => setOpen(!open));
  if (search) search.addEventListener('input', () => renderList(search.value));
  document.addEventListener('click', (event) => {
    if (open && !root.querySelector('[data-city-picker]')?.contains(event.target)) setOpen(false);
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') setOpen(false);
  });

  fetch(root.getAttribute('data-cities') || '/assets/data/india-cities.json')
    .then((res) => res.json())
    .then((rows) => { cities = Array.isArray(rows) ? rows : []; })
    .catch(() => { cities = [{ name: 'Firozabad', state: 'Uttar Pradesh' }]; });

  let saved = null;
  try { saved = JSON.parse(localStorage.getItem(storeKey) || 'null'); } catch (e) { saved = null; }
  const start = saved && saved.name ? saved : currentCity();
  if (start.name !== currentCity().name || start.state !== currentCity().state) {
    load(start);
  } else {
    load(currentCity());
  }

  setInterval(tickAll, 1000);
  tickAll();
})();
