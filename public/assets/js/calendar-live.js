(() => {
  const el = document.querySelector('[data-hijri-today-label]');
  const refresh = () => {
    if (!el || !window.ICLive || typeof ICLive.hijriToday !== 'function') return;
    ICLive.hijriToday().then((h) => {
      const hijri = [h.day, h.month_en, h.year ? h.year + ' AH' : ''].filter(Boolean).join(' ');
      el.textContent = h.gregorian_label ? hijri + ' · ' + h.gregorian_label : hijri;
    }).catch(() => {});
  };
  refresh();
  if (window.ICLive && typeof ICLive.watchFresh === 'function') {
    ICLive.watchFresh(refresh);
  }

  const box = document.querySelector('[data-date-convert]');
  if (!box || !window.ICLive) return;
  const today = new Date();
  const gtoh = box.querySelector('[data-gtoh]');
  const htog = box.querySelector('[data-htog]');
  if (gtoh) {
    gtoh.gd.value = today.getDate();
    gtoh.gm.value = today.getMonth() + 1;
    gtoh.gy.value = today.getFullYear();
    gtoh.addEventListener('submit', (event) => {
      event.preventDefault();
      const out = box.querySelector('[data-gtoh-out]');
      out.textContent = 'Converting…';
      ICLive.gToH(gtoh.gd.value, gtoh.gm.value, gtoh.gy.value)
        .then((row) => { out.textContent = row.hijri + (row.hijri_ar ? ' · ' + row.hijri_ar : ''); })
        .catch(() => { out.textContent = 'That date could not be converted. Try again.'; });
    });
  }
  if (htog) {
    htog.addEventListener('submit', (event) => {
      event.preventDefault();
      const out = box.querySelector('[data-htog-out]');
      out.textContent = 'Converting…';
      ICLive.hToG(htog.hd.value, htog.hm.value, htog.hy.value)
        .then((row) => { out.textContent = row.gregorian + ' · ' + row.hijri; })
        .catch(() => { out.textContent = 'That Hijri date could not be converted. Try again.'; });
    });
  }
})();
