(() => {
  const root = document.querySelector('[data-daily-quran]');
  if (!root) return;
  const playBtn = root.querySelector('[data-dq-play]');
  const istDay = () => {
    const parts = new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Kolkata', year: 'numeric', month: '2-digit', day: '2-digit' }).formatToParts(new Date());
    const read = (t) => (parts.find((p) => p.type === t) || {}).value || '';
    return { iso: read('year') + '-' + read('month') + '-' + read('day'), y: Number(read('year')), m: Number(read('month')), d: Number(read('day')) };
  };
  const dayIndex = () => {
    const t = istDay();
    return Math.floor(Date.UTC(t.y, t.m - 1, t.d) / 86400000);
  };
  const set = (sel, text) => {
    const el = root.querySelector(sel);
    if (el) el.textContent = text || '';
  };
  const showBox = (sel, on) => {
    const el = root.querySelector(sel);
    if (el) el.hidden = !on;
  };
  const share = (text) => window.open('https://wa.me/?text=' + encodeURIComponent(text + '\n' + window.location.href), '_blank', 'noopener');

  let hadith = [];
  const hadithNode = document.querySelector('[data-dq-hadith]');
  try {
    hadith = JSON.parse(hadithNode ? hadithNode.textContent : '[]');
  } catch (e) {
    hadith = [];
  }
  const paintHadith = () => {
    if (!hadith.length) return;
    const row = hadith[dayIndex() % hadith.length];
    set('[data-dq-hadith-ar]', row.ar || '');
    set('[data-dq-hadith-en]', row.en || '');
    set('[data-dq-hadith-src]', row.src || '');
  };
  paintHadith();

  const hiStart = root.querySelector('[data-dq-hi]');
  showBox('[data-dq-hi-box]', !!(hiStart && hiStart.textContent.trim()));

  root._ayahRef = (playBtn && playBtn.getAttribute('data-ayah')) || '';
  root._ayahMeta = null;
  playBtn?.addEventListener('click', () => {
    if (!window.IcRecite) return;
    if (root._ayahMeta) {
      window.IcRecite.playGlobal(root._ayahMeta.g, root._ayahMeta.s, root._ayahMeta.a, playBtn);
      return;
    }
    window.IcRecite.playSpec(root._ayahRef, playBtn);
  });

  const day = istDay();
  const ayahNo = (dayIndex() % 6236) + 1;
  const packs = [
    'quran-uthmani,en.sahih,ur.jalandhry,hi.farooq,en.maududi,ur.maududi',
    'quran-uthmani,en.sahih,ur.jalandhry,hi.hindi,en.maududi,ur.junagarhi',
    'quran-uthmani,en.sahih,ur.jalandhry,hi.farooq,en.maududi',
    'quran-uthmani,en.sahih,ur.jalandhry,en.maududi',
  ];
  const load = (i) => fetch('https://api.alquran.cloud/v1/ayah/' + ayahNo + '/editions/' + packs[i] + '?d=' + day.iso, { cache: 'no-store' })
    .then((res) => res.json())
    .then((payload) => {
      const rows = Array.isArray(payload.data) ? payload.data : [];
      if (!rows.length) {
        if (i + 1 < packs.length) return load(i + 1);
        throw new Error('empty');
      }
      const by = {};
      rows.forEach((row) => {
        by[(row.edition && row.edition.identifier) || ''] = row;
      });
      const ar = by['quran-uthmani'] || rows[0];
      const en = by['en.sahih'] || {};
      const ur = by['ur.jalandhry'] || {};
      const hi = by['hi.farooq'] || by['hi.hindi'] || {};
      const tfEn = by['en.maududi'] || {};
      const tfUr = by['ur.maududi'] || by['ur.junagarhi'] || {};
      set('[data-dq-ar]', ar.text || '');
      set('[data-dq-en]', en.text || '');
      set('[data-dq-ur]', ur.text || '');
      set('[data-dq-hi]', hi.text || '');
      showBox('[data-dq-en-box]', !!en.text);
      showBox('[data-dq-ur-box]', !!ur.text);
      showBox('[data-dq-hi-box]', !!hi.text);
      set('[data-dq-tafsir-en]', tfEn.text || '');
      set('[data-dq-tafsir-ur]', tfUr.text || '');
      showBox('[data-dq-tf-en-box]', !!tfEn.text);
      showBox('[data-dq-tf-ur-box]', !!tfUr.text);
      const ref = ar.surah ? (ar.surah.englishName + ' · ' + ar.surah.number + ':' + ar.numberInSurah) : '';
      set('[data-dq-ref]', ref);
      set('[data-dq-date]', 'India · ' + day.iso + ' · ayah ' + (ar.surah ? ar.surah.number + ':' + ar.numberInSurah : ayahNo));
      if (ar.surah) {
        root._ayahMeta = { g: ar.number, s: ar.surah.number, a: ar.numberInSurah };
        root._ayahRef = ar.surah.number + ':' + ar.numberInSurah;
        if (playBtn) playBtn.setAttribute('data-ayah', root._ayahRef);
      }
      root._ayahShare = [ar.text || '', en.text || '', ur.text || '', hi.text || '', ref].filter(Boolean).join('\n');
    });
  load(0).catch(() => {
    set('[data-dq-date]', 'Today (saved ayah — live Qur’an feed unavailable)');
    const ar = root.querySelector('[data-dq-ar]');
    const ref = root.querySelector('[data-dq-ref]');
    root._ayahShare = ((ar && ar.textContent) || '') + '\n' + ((ref && ref.textContent) || '');
  });

  root.querySelector('[data-dq-share-ayah]')?.addEventListener('click', () => share(root._ayahShare || root.querySelector('[data-dq-ar]')?.textContent || ''));
  root.querySelector('[data-dq-share-hadith]')?.addEventListener('click', () => {
    share([
      root.querySelector('[data-dq-hadith-ar]')?.textContent,
      root.querySelector('[data-dq-hadith-en]')?.textContent,
      root.querySelector('[data-dq-hadith-src]')?.textContent,
    ].filter(Boolean).join('\n'));
  });
})();
