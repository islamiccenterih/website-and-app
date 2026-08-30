window.ICLive = (() => {

  const istStamp = (type) => {
    const parts = new Intl.DateTimeFormat('en-GB', {
      timeZone: 'Asia/Kolkata',
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    }).formatToParts(new Date());
    const read = (name) => (parts.find((part) => part.type === name) || {}).value || '';
    if (type === 'dmy') return read('day') + '-' + read('month') + '-' + read('year');
    return read('year') + '-' + read('month') + '-' + read('day');
  };

  const getJson = (url, ms) => {
    const ctrl = new AbortController();
    const timer = window.setTimeout(() => ctrl.abort(), ms || 8000);
    return fetch(url, { signal: ctrl.signal, cache: 'no-store', headers: { Accept: 'application/json' } })
      .then((res) => {
        if (!res.ok) throw new Error('Upstream HTTP ' + res.status);
        return res.json();
      })
      .finally(() => window.clearTimeout(timer));
  };

  const watchFresh = (fn, minMs) => {
    let last = 0;
    const gap = minMs || 2500;
    const run = (force) => {
      const now = Date.now();
      if (!force && now - last < gap) return;
      last = now;
      fn();
    };
    document.addEventListener('visibilitychange', () => {
      if (document.visibilityState === 'visible') run(false);
    });
    window.addEventListener('pageshow', (event) => {
      if (event.persisted) run(true);
    });
  };

  const to12 = (raw) => {
    const text = String(raw || '').trim();
    const m = text.match(/^(\d{1,2}):(\d{2})(?::\d{2})?\s*(AM|PM)?/i);
    if (!m) return text;
    let hour = parseInt(m[1], 10);
    const minute = m[2];
    let suffix = (m[3] || '').toUpperCase();
    if (suffix === 'AM' || suffix === 'PM') {
      hour = hour % 12;
      if (hour === 0) hour = 12;
      return hour + ':' + minute + ' ' + suffix;
    }
    suffix = hour >= 12 ? 'PM' : 'AM';
    hour = hour % 12;
    if (hour === 0) hour = 12;
    return hour + ':' + minute + ' ' + suffix;
  };

  const gregIso = (g) => {
    const day = parseInt(g && (g.day || g.date), 10);
    const month = parseInt((g && g.month && (g.month.number || g.month)) || 0, 10);
    const year = parseInt(g && g.year, 10);
    if (g && g.date && /^\d{2}-\d{2}-\d{4}$/.test(g.date)) {
      const p = g.date.split('-');
      return p[2] + '-' + p[1] + '-' + p[0];
    }
    if (!year || !month || !day) return '';
    return year + '-' + String(month).padStart(2, '0') + '-' + String(day).padStart(2, '0');
  };

  const prayerFromAladhan = (payload, city, state) => {
    const times = (payload && payload.data && payload.data.timings) || {};
    const meta = (payload && payload.data && payload.data.date) || {};
    const g = meta.gregorian || {};
    const iso = gregIso(g) || istStamp('iso');
    const weekday = (g.weekday && g.weekday.en) || '';
    return {
      ok: true,
      error: null,
      live: true,
      source: 'aladhan',
      city,
      state,
      date: meta.readable || '',
      weekday,
      for_date: iso,
      timezone: 'Asia/Kolkata',
      prayers: [
        { key: 'fajr', name: 'Fajr', time: to12(times.Fajr) },
        { key: 'zuhr', name: 'Zuhr', time: to12(times.Dhuhr) },
        { key: 'asr', name: 'Asr', time: to12(times.Asr) },
        { key: 'maghrib', name: 'Maghrib', time: to12(times.Maghrib) },
        { key: 'isha', name: 'Isha', time: to12(times.Isha) },
        { key: 'jummah', name: 'Jummah', time: to12(times.Dhuhr) },
      ],
      sunrise: to12(times.Sunrise),
      imsak: to12(times.Imsak),
      fajr: to12(times.Fajr),
      maghrib: to12(times.Maghrib),
      isha: to12(times.Isha),
    };
  };

  const prayerTimes = (city, state) => {
    const q = new URLSearchParams({
      city: city || 'Firozabad',
      country: 'India',
      method: '1',
      school: '1',
    });
    if (state) q.set('state', state);
    return getJson('https://api.aladhan.com/v1/timingsByCity/' + istStamp('dmy') + '?' + q.toString(), 8000)
      .then((payload) => {
        if ((payload.code || 0) !== 200 || !payload.data || !payload.data.timings) {
          throw new Error('Empty prayer times');
        }
        return prayerFromAladhan(payload, city, state);
      });
  };

  const hijriToday = () => getJson('https://api.aladhan.com/v1/gToH?date=' + istStamp('dmy'), 8000)
    .then((payload) => {
      const h = payload.data && payload.data.hijri ? payload.data.hijri : {};
      const g = payload.data && payload.data.gregorian ? payload.data.gregorian : {};
      if (!h.year) throw new Error('Empty hijri');
      const month = h.month || {};
      return {
        day: parseInt(h.day, 10) || 0,
        month: parseInt(month.number, 10) || 0,
        year: parseInt(h.year, 10) || 0,
        month_en: month.en || '',
        month_ar: month.ar || '',
        weekday: (h.weekday && h.weekday.en) || '',
        weekday_ar: (h.weekday && h.weekday.ar) || '',
        holidays: Array.isArray(h.holidays) ? h.holidays : [],
        gregorian_iso: gregIso(g) || istStamp('iso'),
        gregorian_label: [g.weekday && g.weekday.en, (g.day || '') + ' ' + ((g.month && g.month.en) || '') + ' ' + (g.year || '')].filter(Boolean).join(', '),
      };
    });

  const ramadanPage = (city, state) => hijriToday().then((today) => {
    const hy = today.month <= 9 ? today.year : today.year + 1;
    const q = new URLSearchParams({
      city: city || 'Firozabad',
      country: 'India',
      method: '1',
      school: '1',
    });
    if (state) q.set('state', state);
    return getJson('https://api.aladhan.com/v1/hijriCalendarByCity/' + hy + '/9?' + q.toString(), 12000)
      .then((payload) => {
        const rows = Array.isArray(payload.data) ? payload.data : [];
        if (!rows.length) throw new Error('Empty ramadan');
        const days = rows.map((item) => {
          const timings = item.timings || {};
          const date = item.date || {};
          const hijri = date.hijri || {};
          const greg = date.gregorian || {};
          const iso = gregIso(greg);
          return {
            hijri_day: parseInt(hijri.day, 10) || 0,
            hijri_label: (hijri.day || '') + ' Ramadan ' + hy,
            gregorian_iso: iso,
            gregorian_label: date.readable || '',
            weekday: (greg.weekday && greg.weekday.en) || '',
            imsak: to12(timings.Imsak),
            fajr: to12(timings.Fajr),
            maghrib: to12(timings.Maghrib),
            isha: to12(timings.Isha),
            is_today: iso === today.gregorian_iso,
          };
        });
        const todayRow = days.find((day) => day.is_today) || {
          hijri_day: today.day,
          hijri_label: today.day + ' ' + today.month_en + ' ' + today.year,
          gregorian_iso: today.gregorian_iso,
          gregorian_label: today.gregorian_label,
          imsak: '',
          fajr: '',
          maghrib: '',
          isha: '',
          is_today: true,
          ok: true,
        };
        const isRamadan = today.month === 9 && today.year === hy;
        if (!todayRow.fajr) {
          return prayerTimes(city, state).then((prayer) => {
            todayRow.imsak = prayer.imsak;
            todayRow.fajr = prayer.fajr;
            todayRow.maghrib = prayer.maghrib;
            todayRow.isha = prayer.isha;
            todayRow.ok = true;
            return finishRamadan(city, state, hy, isRamadan, today, todayRow, days);
          });
        }
        todayRow.ok = true;
        return finishRamadan(city, state, hy, isRamadan, today, todayRow, days);
      });
  });

  const finishRamadan = (city, state, hy, isRamadan, today, todayRow, days) => {
    const first = days[0] || {};
    return {
      ok: true,
      error: null,
      live: true,
      source: 'aladhan',
      city,
      state,
      hijri_year: hy,
      is_ramadan: isRamadan,
      today,
      today_row: todayRow,
      days,
      gregorian_span: first.gregorian_label && days[days.length - 1]
        ? first.gregorian_label + ' – ' + days[days.length - 1].gregorian_label
        : '',
      next_ramadan_label: isRamadan
        ? 'Ramadan ' + hy + ' AH is here — day ' + today.day + '.'
        : (first.gregorian_label ? 'Ramadan ' + hy + ' AH begins ' + first.gregorian_label : ''),
      ramadan_start: {
        gregorian_iso: first.gregorian_iso || '',
        gregorian_label: first.gregorian_label || '',
        unix: first.gregorian_iso ? Date.parse(first.gregorian_iso + 'T00:00:00+05:30') / 1000 : 0,
      },
    };
  };

  const getText = (url, ms) => {
    const ctrl = new AbortController();
    const timer = window.setTimeout(() => ctrl.abort(), ms || 12000);
    return fetch(url, { signal: ctrl.signal, cache: 'no-store' })
      .then((res) => {
        if (!res.ok) throw new Error('Upstream HTTP ' + res.status);
        return res.text();
      })
      .finally(() => window.clearTimeout(timer));
  };

  const ibjaDateKey = (dmy) => {
    const parts = String(dmy || '').split('/');
    if (parts.length !== 3) return 0;
    return (Number(parts[2]) * 10000) + (Number(parts[1]) * 100) + Number(parts[0]);
  };

  const pickLatestIbja = (rows) => {
    let best = null;
    rows.forEach((row) => {
      if (!row || row.gold10 < 10000 || row.silverKg < 10000) return;
      if (!best || ibjaDateKey(row.date) >= ibjaDateKey(best.date)) best = row;
    });
    return best;
  };

  const parseIbja = (text) => {
    const re = /\|\s*\*{0,2}(\d{2}\/\d{2}\/\d{4})\*{0,2}\s*\|\s*(\d{5,7})\s*\|\s*\d+\s*\|\s*\d+\s*\|\s*\d+\s*\|\s*\d+\s*\|\s*(\d{5,7})\s*\|/g;
    const rows = [];
    let match;
    while ((match = re.exec(text))) {
      rows.push({ date: match[1], gold10: Number(match[2]), silverKg: Number(match[3]) });
    }
    let best = pickLatestIbja(rows);
    if (best) return best;
    const htmlRe = /(\d{2}\/\d{2}\/\d{4})[\s\S]{0,160}?data-label="Gold 999">\s*(\d{5,7})[\s\S]{0,160}?data-label="Silver 999">\s*(\d{5,7})/g;
    const htmlRows = [];
    while ((match = htmlRe.exec(text))) {
      htmlRows.push({ date: match[1], gold10: Number(match[2]), silverKg: Number(match[3]) });
    }
    return pickLatestIbja(htmlRows);
  };

  const packIndiaSpot = (cfg, row) => {
    const goldG = Number((cfg && cfg.gold_nisab_g) || 87.48);
    const silverG = Number((cfg && cfg.silver_nisab_g) || 612.36);
    const rate = Number((cfg && cfg.rate) || 2.5);
    const method = (cfg && cfg.nisab_method) || 'lower';
    const gold10 = row.gold10;
    const silverKg = row.silverKg;
    const goldPerG = gold10 / 10;
    const silverPerG = silverKg / 1000;
    const goldNisab = goldG * goldPerG;
    const silverNisab = silverG * silverPerG;
    const parts = String(row.date || '').split('/');
    const iso = parts.length === 3 ? parts[2] + '-' + parts[1] + '-' + parts[0] : istStamp('iso');
    return {
      ok: true,
      error: null,
      stale: false,
      live: true,
      india: true,
      source: 'IBJA India 24k (999), without GST',
      for_date: iso,
      gold_per_gram_inr: goldPerG,
      silver_per_gram_inr: silverPerG,
      gold_nisab_g: goldG,
      silver_nisab_g: silverG,
      gold_nisab_inr: goldNisab,
      silver_nisab_inr: silverNisab,
      gold_per_10g_inr: gold10,
      silver_per_kg_inr: silverKg,
      rate,
      nisab_method: method,
      nisab: method === 'gold' ? goldNisab : (method === 'silver' ? silverNisab : Math.min(goldNisab, silverNisab) || Math.max(goldNisab, silverNisab)),
    };
  };

  const metalSpot = (cfg) => getText('https://r.jina.ai/https://ibjarates.com/', 12000)
    .then((text) => {
      const row = parseIbja(text);
      if (!row || row.gold10 < 10000 || row.silverKg < 10000) throw new Error('Empty India rates');
      return packIndiaSpot(cfg, row);
    });

  const moonWeek = (lat, lng) => {
    const start = istStamp('iso');
    const endDate = new Date();
    endDate.setDate(endDate.getDate() + 6);
    const end = new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Kolkata', year: 'numeric', month: '2-digit', day: '2-digit' }).format(endDate);
    const skyUrl = 'https://api.sunrisesunset.io/json?lat=' + encodeURIComponent(lat)
      + '&lng=' + encodeURIComponent(lng)
      + '&timezone=' + encodeURIComponent('Asia/Kolkata')
      + '&date_start=' + start + '&date_end=' + end;
    return Promise.all([hijriToday(), getJson(skyUrl, 8000)]).then(([hijri, sky]) => {
      let rows = sky && sky.results;
      if (rows && !Array.isArray(rows) && rows.sunrise) rows = [rows];
      if (!Array.isArray(rows) || !rows.length) throw new Error('Empty moon');
      const week = rows.map((row) => {
        const illumRaw = Number(row.moon_illumination);
        const illum = illumRaw <= 1 ? illumRaw * 100 : illumRaw;
        return {
          date: row.date,
          label: row.date ? new Date(row.date + 'T12:00:00+05:30').toLocaleDateString('en-GB', { weekday: 'short', timeZone: 'Asia/Kolkata' }) : '',
          daynum: row.date ? String(parseInt(row.date.slice(-2), 10)) : '',
          is_today: row.date === start,
          phase: row.moon_phase || '',
          illumination: illum,
          phase_value: row.moon_phase_value,
          moonrise: row.moonrise,
          moonset: row.moonset,
          sunrise: row.sunrise,
          sunset: row.sunset,
          golden_hour: row.golden_hour,
        };
      });
      const todaySky = week.find((row) => row.is_today) || week[0];
      return { ok: true, live: true, hijri, week, moon: todaySky, gregorian: hijri.gregorian_label, for_date: start };
    });
  };

  const pad2 = (n) => String(n).padStart(2, '0');
  const dmy = (d, m, y) => pad2(d) + '-' + pad2(m) + '-' + y;

  const gToH = (day, month, year) => getJson('https://api.aladhan.com/v1/gToH?date=' + dmy(day, month, year), 8000)
    .then((payload) => {
      const h = payload.data && payload.data.hijri ? payload.data.hijri : {};
      const g = payload.data && payload.data.gregorian ? payload.data.gregorian : {};
      if (!h.year) throw new Error('Empty hijri');
      return {
        ok: true,
        hijri: (h.day || '') + ' ' + ((h.month && h.month.en) || '') + ' ' + (h.year || '') + ' AH',
        hijri_ar: (h.day || '') + ' ' + ((h.month && h.month.ar) || '') + ' ' + (h.year || ''),
        gregorian: (g.day || '') + ' ' + ((g.month && g.month.en) || '') + ' ' + (g.year || ''),
      };
    });

  const hToG = (day, month, year) => getJson('https://api.aladhan.com/v1/hToG?date=' + dmy(day, month, year), 8000)
    .then((payload) => {
      const h = payload.data && payload.data.hijri ? payload.data.hijri : {};
      const g = payload.data && payload.data.gregorian ? payload.data.gregorian : {};
      if (!g.year) throw new Error('Empty gregorian');
      return {
        ok: true,
        hijri: (h.day || '') + ' ' + ((h.month && h.month.en) || '') + ' ' + (h.year || '') + ' AH',
        gregorian: (g.weekday && g.weekday.en ? g.weekday.en + ', ' : '') + (g.day || '') + ' ' + ((g.month && g.month.en) || '') + ' ' + (g.year || ''),
      };
    });

  return { prayerTimes, hijriToday, ramadanPage, metalSpot, moonWeek, istStamp, to12, watchFresh, gToH, hToG };
})();
