(() => {
  const root = document.querySelector('[data-moon-live]');
  if (!root || !window.ICLive || typeof ICLive.moonWeek !== 'function') return;

  const lat = parseFloat(root.getAttribute('data-lat') || '27.1591');
  const lng = parseFloat(root.getAttribute('data-lng') || '78.3957');
  const fmt = (value) => {
    const raw = String(value || '').trim();
    if (!raw || raw.toLowerCase() === 'null' || raw.toLowerCase() === 'none') return '—';
    const ts = Date.parse(raw);
    if (!isNaN(ts)) {
      return new Date(ts).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', timeZone: 'Asia/Kolkata' });
    }
    return ICLive.to12(raw);
  };

  const setText = (sel, value) => {
    const el = root.querySelector(sel);
    if (el && value) el.textContent = value;
  };

  ICLive.moonWeek(lat, lng).then((data) => {
    const hijri = data.hijri || {};
    const sky = data.moon || {};
    const title = [hijri.day, hijri.month_en, hijri.year ? hijri.year + ' AH' : ''].filter(Boolean).join(' ');
    setText('.sec-head:not(.is-light) h2', title);
    setText('.sec-head:not(.is-light) .sec-lead', data.gregorian || hijri.gregorian_label);
    setText('[data-moon-hijri-line]', [hijri.weekday, hijri.month_ar].filter(Boolean).join(' · '));
    setText('[data-moon-phase]', sky.phase);
    if (sky.illumination != null) {
      setText('[data-moon-illum]', Number(sky.illumination).toFixed(1).replace(/\.0$/, '') + '% illuminated');
    }
    root.querySelectorAll('[data-moon-sky]').forEach((el) => {
      const key = el.getAttribute('data-moon-sky');
      el.textContent = fmt(sky[key]);
    });
    const err = root.querySelector('.alert-error');
    if (err) err.hidden = true;
    const empty = root.querySelector('.empty-state');
    if (empty) empty.hidden = true;
    const weekDays = root.querySelectorAll('[data-moon-week-phase]');
    (data.week || []).forEach((day, i) => {
      if (weekDays[i]) weekDays[i].textContent = day.phase || '';
    });
  }).catch(() => {});

  setInterval(() => {
    const shown = root.getAttribute('data-for-date') || root.querySelector('[data-moon-date]')?.getAttribute('data-moon-date');
    if (shown && shown !== ICLive.istStamp('iso')) {
      window.location.reload();
    }
  }, 1000);
})();
