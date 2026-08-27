(() => {
  const el = document.querySelector('[data-hijri-today-label]');
  if (!el || !window.ICLive || typeof ICLive.hijriToday !== 'function') return;
  ICLive.hijriToday().then((h) => {
    const hijri = [h.day, h.month_en, h.year ? h.year + ' AH' : ''].filter(Boolean).join(' ');
    el.textContent = h.gregorian_label ? hijri + ' · ' + h.gregorian_label : hijri;
  }).catch(() => {});
})();
