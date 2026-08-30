(() => {
  const root = document.querySelector('[data-quran-root]');
  if (!root) return;
  const select = root.querySelector('[data-quran-surah]');
  const search = root.querySelector('[data-quran-search]');
  const list = root.querySelector('[data-quran-ayahs]');
  const errorBox = root.querySelector('[data-quran-error]');
  const surahs = [
    [1, 'Al-Fatihah', 7], [2, 'Al-Baqarah', 286], [3, 'Ali \'Imran', 200], [4, 'An-Nisa', 176],
    [5, 'Al-Ma\'idah', 120], [6, 'Al-An\'am', 165], [7, 'Al-A\'raf', 206], [8, 'Al-Anfal', 75],
    [9, 'At-Tawbah', 129], [10, 'Yunus', 109], [11, 'Hud', 123], [12, 'Yusuf', 111],
    [13, 'Ar-Ra\'d', 43], [14, 'Ibrahim', 52], [15, 'Al-Hijr', 99], [16, 'An-Nahl', 128],
    [17, 'Al-Isra', 111], [18, 'Al-Kahf', 110], [19, 'Maryam', 98], [20, 'Ta-Ha', 135],
    [21, 'Al-Anbiya', 112], [22, 'Al-Hajj', 78], [23, 'Al-Mu\'minun', 118], [24, 'An-Nur', 64],
    [25, 'Al-Furqan', 77], [26, 'Ash-Shu\'ara', 227], [27, 'An-Naml', 93], [28, 'Al-Qasas', 88],
    [29, 'Al-\'Ankabut', 69], [30, 'Ar-Rum', 60], [31, 'Luqman', 34], [32, 'As-Sajdah', 30],
    [33, 'Al-Ahzab', 73], [34, 'Saba', 54], [35, 'Fatir', 45], [36, 'Ya-Sin', 83],
    [37, 'As-Saffat', 182], [38, 'Sad', 88], [39, 'Az-Zumar', 75], [40, 'Ghafir', 85],
    [41, 'Fussilat', 54], [42, 'Ash-Shura', 53], [43, 'Az-Zukhruf', 89], [44, 'Ad-Dukhan', 59],
    [45, 'Al-Jathiyah', 37], [46, 'Al-Ahqaf', 35], [47, 'Muhammad', 38], [48, 'Al-Fath', 29],
    [49, 'Al-Hujurat', 18], [50, 'Qaf', 45], [51, 'Adh-Dhariyat', 60], [52, 'At-Tur', 49],
    [53, 'An-Najm', 62], [54, 'Al-Qamar', 55], [55, 'Ar-Rahman', 78], [56, 'Al-Waqi\'ah', 96],
    [57, 'Al-Hadid', 29], [58, 'Al-Mujadila', 22], [59, 'Al-Hashr', 24], [60, 'Al-Mumtahanah', 13],
    [61, 'As-Saff', 14], [62, 'Al-Jumu\'ah', 11], [63, 'Al-Munafiqun', 11], [64, 'At-Taghabun', 18],
    [65, 'At-Talaq', 12], [66, 'At-Tahrim', 12], [67, 'Al-Mulk', 30], [68, 'Al-Qalam', 52],
    [69, 'Al-Haqqah', 52], [70, 'Al-Ma\'arij', 44], [71, 'Nuh', 28], [72, 'Al-Jinn', 28],
    [73, 'Al-Muzzammil', 20], [74, 'Al-Muddaththir', 56], [75, 'Al-Qiyamah', 40], [76, 'Al-Insan', 31],
    [77, 'Al-Mursalat', 50], [78, 'An-Naba', 40], [79, 'An-Nazi\'at', 46], [80, '\'Abasa', 42],
    [81, 'At-Takwir', 29], [82, 'Al-Infitar', 19], [83, 'Al-Mutaffifin', 36], [84, 'Al-Inshiqaq', 25],
    [85, 'Al-Buruj', 22], [86, 'At-Tariq', 17], [87, 'Al-A\'la', 19], [88, 'Al-Ghashiyah', 26],
    [89, 'Al-Fajr', 30], [90, 'Al-Balad', 20], [91, 'Ash-Shams', 15], [92, 'Al-Layl', 21],
    [93, 'Ad-Duhaa', 11], [94, 'Ash-Sharh', 8], [95, 'At-Tin', 8], [96, 'Al-\'Alaq', 19],
    [97, 'Al-Qadr', 5], [98, 'Al-Bayyinah', 8], [99, 'Az-Zalzalah', 8], [100, 'Al-\'Adiyat', 11],
    [101, 'Al-Qari\'ah', 11], [102, 'At-Takathur', 8], [103, 'Al-\'Asr', 3], [104, 'Al-Humazah', 9],
    [105, 'Al-Fil', 5], [106, 'Quraysh', 4], [107, 'Al-Ma\'un', 7], [108, 'Al-Kawthar', 3],
    [109, 'Al-Kafirun', 6], [110, 'An-Nasr', 3], [111, 'Al-Masad', 5], [112, 'Al-Ikhlas', 4],
    [113, 'Al-Falaq', 5], [114, 'An-Nas', 6],
  ];
  surahs.forEach((row) => {
    const opt = document.createElement('option');
    opt.value = String(row[0]);
    opt.textContent = row[0] + '. ' + row[1] + ' (' + row[2] + ')';
    select.appendChild(opt);
  });

  let ayahs = [];
  let playIndex = -1;
  let nextIndex = -1;
  const player = new Audio();
  player.preload = 'auto';
  const showErr = (msg) => {
    if (!errorBox) return;
    errorBox.hidden = !msg;
    errorBox.textContent = msg || '';
  };

  const urlsFor = (ayah) => {
    const s = String(ayah.surah).padStart(3, '0');
    const a = String(ayah.n).padStart(3, '0');
    return [
      'https://cdn.islamic.network/quran/audio/128/ar.alafasy/' + ayah.global + '.mp3',
      'https://everyayah.com/data/Alafasy_128kbps/' + s + a + '.mp3',
    ];
  };

  const paintPlaying = () => {
    list.querySelectorAll('[data-ayah]').forEach((art) => {
      const idx = Number(art.getAttribute('data-ayah'));
      const on = idx === playIndex;
      art.classList.toggle('is-playing', on);
      const playBtn = art.querySelector('[data-ayah-play]');
      const stopBtn = art.querySelector('[data-ayah-stop]');
      if (playBtn) playBtn.textContent = on ? 'Playing' : 'Play';
      if (playBtn) playBtn.classList.toggle('is-playing', on);
      if (stopBtn) stopBtn.hidden = !on;
    });
  };

  const stopPlay = () => {
    nextIndex = -1;
    playIndex = -1;
    player.onended = null;
    player.onerror = null;
    try { player.pause(); } catch (e) {}
    paintPlaying();
  };

  const startSrc = (ayah, urlI) => {
    const urls = urlsFor(ayah);
    if (urlI >= urls.length) {
      playNext();
      return;
    }
    player.onerror = () => startSrc(ayah, urlI + 1);
    player.src = urls[urlI];
    player.play().catch(() => startSrc(ayah, urlI + 1));
  };

  const playNext = () => {
    if (nextIndex < 0 || nextIndex >= ayahs.length) {
      stopPlay();
      return;
    }
    playIndex = nextIndex;
    nextIndex += 1;
    paintPlaying();
    const art = list.querySelector('[data-ayah="' + playIndex + '"]');
    if (art && typeof art.scrollIntoView === 'function') {
      art.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }
    player.onended = playNext;
    startSrc(ayahs[playIndex], 0);
  };

  const playFrom = (index) => {
    if (index < 0 || index >= ayahs.length) return;
    player.onended = null;
    player.onerror = null;
    try { player.pause(); } catch (e) {}
    nextIndex = index;
    playNext();
  };

  const render = (filter) => {
    const q = (filter || '').trim().toLowerCase();
    list.innerHTML = '';
    ayahs.forEach((ayah, idx) => {
      const hay = (ayah.ar + ' ' + ayah.en + ' ' + ayah.ur + ' ' + ayah.hi + ' ' + ayah.n).toLowerCase();
      if (q && !hay.includes(q) && String(ayah.n) !== q) return;
      const art = document.createElement('article');
      art.className = 'ft-ayah';
      art.setAttribute('data-ayah', String(idx));
      art.innerHTML = '<div class="ft-ayah-top"><span>' + ayah.n + '</span><div class="ft-ayah-actions">'
        + '<button type="button" class="btn btn-outline btn-sm" data-ayah-play>Play</button>'
        + '<button type="button" class="btn btn-gold btn-sm" data-ayah-stop hidden>Stop</button>'
        + '</div></div>'
        + '<p class="ft-ar" lang="ar" dir="rtl"></p>'
        + (ayah.en ? '<div class="ft-lang"><span>English</span><p class="ft-en"></p></div>' : '')
        + (ayah.ur ? '<div class="ft-lang" lang="ur" dir="rtl"><span>اردو</span><p class="ft-ur"></p></div>' : '')
        + (ayah.hi ? '<div class="ft-lang" lang="hi"><span>हिन्दी</span><p class="ft-hi"></p></div>' : '');
      art.querySelector('.ft-ar').textContent = ayah.ar;
      const en = art.querySelector('.ft-en');
      const ur = art.querySelector('.ft-ur');
      const hi = art.querySelector('.ft-hi');
      if (en) en.textContent = ayah.en;
      if (ur) ur.textContent = ayah.ur;
      if (hi) hi.textContent = ayah.hi;
      art.querySelector('[data-ayah-play]').addEventListener('click', () => playFrom(idx));
      art.querySelector('[data-ayah-stop]').addEventListener('click', stopPlay);
      list.appendChild(art);
    });
    if (!list.children.length) {
      list.innerHTML = '<p class="ft-live-note">No ayah matched that search in this surah.</p>';
    }
    paintPlaying();
  };

  const getJson = (url, ms) => {
    const ctrl = new AbortController();
    const timer = window.setTimeout(() => ctrl.abort(), ms || 10000);
    return fetch(url, { signal: ctrl.signal, cache: 'default', headers: { Accept: 'application/json' } })
      .then((res) => {
        if (!res.ok) throw new Error('http');
        return res.json();
      })
      .finally(() => window.clearTimeout(timer));
  };

  const load = (n) => {
    stopPlay();
    showErr('');
    list.innerHTML = '<p class="ft-live-note">Loading surah…</p>';
    const urls = [
      'https://api.alquran.cloud/v1/surah/' + n + '/editions/quran-uthmani,en.sahih,ur.jalandhry,hi.farooq',
      'https://api.alquran.cloud/v1/surah/' + n + '/editions/quran-uthmani,en.sahih,ur.jalandhry,hi.hindi',
      'https://api.alquran.cloud/v1/surah/' + n + '/editions/quran-uthmani,en.sahih,ur.jalandhry',
      'https://api.alquran.cloud/v1/surah/' + n + '/editions/quran-uthmani,en.sahih',
    ];
    const tryUrl = (i) => getJson(urls[i], 10000).then((payload) => {
      const editions = Array.isArray(payload.data) ? payload.data : [];
      if (!editions.length) throw new Error('empty');
      const pick = (id) => editions.find((ed) => ed.edition && ed.edition.identifier === id) || null;
      const ar = pick('quran-uthmani') || editions[0];
      const en = pick('en.sahih');
      const ur = pick('ur.jalandhry');
      const hi = pick('hi.farooq') || pick('hi.hindi');
      ayahs = (ar.ayahs || []).map((ayah, idx) => ({
        n: ayah.numberInSurah,
        global: ayah.number,
        surah: Number(n),
        ar: ayah.text,
        en: en && en.ayahs && en.ayahs[idx] ? en.ayahs[idx].text : '',
        ur: ur && ur.ayahs && ur.ayahs[idx] ? ur.ayahs[idx].text : '',
        hi: hi && hi.ayahs && hi.ayahs[idx] ? hi.ayahs[idx].text : '',
      }));
      render(search.value);
    }).catch(() => {
      if (i + 1 < urls.length) return tryUrl(i + 1);
      throw new Error('empty');
    });
    tryUrl(0).catch(() => {
      showErr('The Qur’an text could not be loaded. Check your connection and try again.');
      list.innerHTML = '';
    });
  };

  select.addEventListener('change', () => load(select.value));
  search.addEventListener('input', () => render(search.value));
  root.querySelector('[data-quran-play]')?.addEventListener('click', () => playFrom(0));
  root.querySelector('[data-quran-stop]')?.addEventListener('click', stopPlay);
  load('1');
})();
