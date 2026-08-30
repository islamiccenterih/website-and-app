(() => {
  const LEN = [0, 7, 286, 200, 176, 120, 165, 206, 75, 129, 109, 123, 111, 43, 52, 99, 128, 111, 110, 98, 135, 112, 78, 118, 64, 77, 227, 93, 88, 69, 60, 34, 30, 73, 54, 45, 83, 182, 88, 75, 85, 54, 53, 89, 59, 37, 35, 38, 29, 18, 45, 60, 49, 62, 55, 78, 96, 29, 22, 24, 13, 14, 11, 11, 18, 12, 12, 30, 52, 52, 44, 28, 28, 20, 56, 40, 31, 50, 40, 46, 42, 29, 19, 36, 25, 22, 17, 19, 26, 30, 20, 15, 21, 11, 8, 8, 19, 5, 8, 8, 11, 11, 8, 3, 9, 5, 4, 7, 3, 6, 3, 5, 4, 5, 6];
  const audio = new Audio();
  audio.preload = 'auto';
  let token = 0;
  let queue = [];
  let qi = 0;
  let activeBtn = null;

  const globalOf = (s, a) => {
    let n = 0;
    for (let i = 1; i < s; i += 1) n += LEN[i] || 0;
    return n + a;
  };

  const parseSpec = (spec) => {
    const out = [];
    String(spec || '').split(',').forEach((part) => {
      const chunk = part.trim();
      if (!chunk) return;
      const m = chunk.match(/^(\d+)\s*:\s*(\d+)(?:\s*[-–]\s*(?:(\d+)\s*:)?\s*(\d+))?$/);
      if (!m) return;
      const s1 = Number(m[1]);
      const a1 = Number(m[2]);
      if (!m[4]) {
        out.push({ s: s1, a: a1 });
        return;
      }
      const s2 = m[3] ? Number(m[3]) : s1;
      const a2 = Number(m[4]);
      if (s1 === s2) {
        for (let a = a1; a <= a2; a += 1) out.push({ s: s1, a });
        return;
      }
      const end1 = LEN[s1] || a1;
      for (let a = a1; a <= end1; a += 1) out.push({ s: s1, a });
      for (let a = 1; a <= a2; a += 1) out.push({ s: s2, a });
    });
    return out;
  };

  const urlsFor = (item) => {
    if (item.urls) return item.urls.filter(Boolean);
    if (item.url) return [item.url];
    const g = item.g || globalOf(item.s, item.a);
    const pad = String(item.s).padStart(3, '0') + String(item.a).padStart(3, '0');
    return [
      'https://cdn.islamic.network/quran/audio/128/ar.alafasy/' + g + '.mp3',
      'https://everyayah.com/data/Alafasy_128kbps/' + pad + '.mp3',
    ];
  };

  const speak = (text, my, onDone) => {
    const ar = String(text || '').trim();
    if (!ar || !window.speechSynthesis) {
      onDone();
      return;
    }
    const run = () => {
      if (my !== token) return;
      const u = new SpeechSynthesisUtterance(ar);
      const voices = window.speechSynthesis.getVoices() || [];
      const voice = voices.find((v) => /^ar/i.test(v.lang)) || voices.find((v) => /arabic/i.test(v.name || ''));
      if (voice) u.voice = voice;
      u.lang = (voice && voice.lang) || 'ar-SA';
      u.rate = 0.78;
      u.onend = onDone;
      u.onerror = onDone;
      window.speechSynthesis.speak(u);
    };
    if ((window.speechSynthesis.getVoices() || []).length) run();
    else {
      window.speechSynthesis.addEventListener('voiceschanged', run, { once: true });
      window.setTimeout(run, 400);
    }
  };

  const mark = (on) => {
    if (!activeBtn) return;
    activeBtn.classList.toggle('is-playing', on);
    activeBtn.textContent = on ? 'Pause' : 'Play';
  };

  const stop = () => {
    token += 1;
    window.clearTimeout(failTimer);
    audio.onended = null;
    audio.onerror = null;
    try { audio.pause(); audio.removeAttribute('src'); } catch (e) {}
    if (window.speechSynthesis) {
      try { window.speechSynthesis.cancel(); } catch (e) {}
    }
    mark(false);
    activeBtn = null;
    queue = [];
    qi = 0;
    try { document.dispatchEvent(new CustomEvent('ic-recite-stop')); } catch (e) {}
  };

  let failTimer = 0;
  const startItem = (my, urlI) => {
    if (my !== token) return;
    window.clearTimeout(failTimer);
    const item = queue[qi];
    if (!item) {
      stop();
      return;
    }
    const list = urlsFor(item);
    if (urlI >= list.length) {
      if (item.ar) {
        speak(item.ar, my, () => {
          if (my !== token) return;
          qi += 1;
          startItem(my, 0);
        });
        return;
      }
      qi += 1;
      startItem(my, 0);
      return;
    }
    audio.onerror = () => startItem(my, urlI + 1);
    audio.onended = () => {
      qi += 1;
      startItem(my, 0);
    };
    failTimer = window.setTimeout(() => startItem(my, urlI + 1), 8000);
    audio.src = list[urlI];
    audio.play().catch(() => startItem(my, urlI + 1));
  };

  const playQueue = (items, btn) => {
    if (btn && activeBtn === btn && !audio.paused) {
      stop();
      return false;
    }
    stop();
    queue = (items || []).filter((row) => row && (row.url || row.urls || row.ar || (row.s && row.a)));
    if (!queue.length) return false;
    token += 1;
    const my = token;
    qi = 0;
    activeBtn = btn || null;
    mark(true);
    startItem(my, 0);
    return true;
  };

  const playArabic = (text, btn) => {
    const ar = String(text || '').trim();
    if (!ar || !window.speechSynthesis) return false;
    if (btn && activeBtn === btn) {
      stop();
      return false;
    }
    stop();
    activeBtn = btn || null;
    mark(true);
    const run = () => {
      if (!activeBtn && btn) return;
      const u = new SpeechSynthesisUtterance(ar);
      const voices = window.speechSynthesis.getVoices() || [];
      const voice = voices.find((v) => /^ar/i.test(v.lang)) || voices.find((v) => /arabic/i.test(v.name || ''));
      if (voice) u.voice = voice;
      u.lang = (voice && voice.lang) || 'ar-SA';
      u.rate = 0.78;
      u.onend = () => { if (activeBtn === btn) stop(); };
      u.onerror = () => { if (activeBtn === btn) stop(); };
      window.speechSynthesis.speak(u);
    };
    if ((window.speechSynthesis.getVoices() || []).length) run();
    else {
      window.speechSynthesis.addEventListener('voiceschanged', run, { once: true });
      window.setTimeout(run, 400);
    }
    return true;
  };

  window.IcRecite = {
    stop,
    parseSpec,
    playSpec: (spec, btn) => playQueue(parseSpec(spec), btn),
    playAyah: (s, a, btn) => playQueue([{ s: Number(s), a: Number(a) }], btn),
    playGlobal: (g, s, a, btn) => playQueue([{ s: Number(s), a: Number(a), g: Number(g) }], btn),
    playUrls: (urls, btn, ar) => playQueue([{ urls: (urls || []).filter(Boolean), ar: ar || '' }], btn),
    playArabic,
    isBtn: (btn) => activeBtn === btn,
  };
})();
