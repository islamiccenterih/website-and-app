(() => {
  const root = document.querySelector('[data-names-root]');
  if (!root) return;
  const search = root.querySelector('[data-names-search]');
  const empty = root.querySelector('[data-names-empty]');
  const audio = new Audio();
  audio.preload = 'auto';
  let currentBtn = null;
  let failTimer = 0;

  const filter = () => {
    const q = (search && search.value || '').trim().toLowerCase();
    let shown = 0;
    root.querySelectorAll('[data-name-card]').forEach((card) => {
      const hay = (card.getAttribute('data-search') || '') + ' ' + (card.querySelector('.ft-ar')?.textContent || '');
      const on = q === '' || hay.toLowerCase().includes(q);
      card.hidden = !on;
      if (on) shown += 1;
    });
    if (empty) empty.hidden = shown > 0;
  };
  search?.addEventListener('input', filter);

  const resetBtns = () => {
    root.querySelectorAll('[data-name-play]').forEach((btn) => {
      btn.classList.remove('is-playing');
      btn.textContent = 'Play';
    });
    root.querySelectorAll('[data-name-card]').forEach((card) => card.classList.remove('is-playing'));
    currentBtn = null;
  };

  const stopAll = () => {
    window.clearTimeout(failTimer);
    audio.onerror = null;
    audio.onended = null;
    audio.oncanplay = null;
    try { audio.pause(); audio.removeAttribute('src'); audio.load(); } catch (e) {}
    if (window.speechSynthesis) {
      try { window.speechSynthesis.cancel(); } catch (e) {}
    }
    resetBtns();
  };

  const markPlaying = (btn) => {
    resetBtns();
    if (!btn) return;
    btn.classList.add('is-playing');
    btn.textContent = 'Pause';
    btn.closest('[data-name-card]')?.classList.add('is-playing');
    currentBtn = btn;
  };

  const pickVoice = () => {
    const voices = window.speechSynthesis ? window.speechSynthesis.getVoices() : [];
    return voices.find((v) => /^ar/i.test(v.lang)) || voices.find((v) => /arabic/i.test(v.name || '')) || null;
  };

  const speak = (text, btn) => {
    if (!window.speechSynthesis || !text) return false;
    try { window.speechSynthesis.cancel(); } catch (e) {}
    const run = () => {
      if (currentBtn !== btn) return;
      const u = new SpeechSynthesisUtterance(text);
      const voice = pickVoice();
      if (voice) u.voice = voice;
      u.lang = (voice && voice.lang) || 'ar-SA';
      u.rate = 0.75;
      u.onend = () => { if (currentBtn === btn) resetBtns(); };
      u.onerror = () => { if (currentBtn === btn) resetBtns(); };
      window.speechSynthesis.speak(u);
      markPlaying(btn);
    };
    if (pickVoice() || (window.speechSynthesis.getVoices() || []).length) {
      run();
    } else {
      window.speechSynthesis.addEventListener('voiceschanged', run, { once: true });
      window.setTimeout(run, 400);
    }
    return true;
  };

  const sources = (n) => {
    const raw = String(parseInt(n, 10) || 0);
    const pad = raw.padStart(2, '0');
    const urls = [];
    if (raw === '1') urls.push('/assets/audio/names/allah.wav');
    urls.push('/assets/audio/names/' + raw + '.mp3');
    urls.push(
      'https://www.islamcan.com/99names/audio/' + raw + '.mp3',
      'https://www.islamcan.com/99names/audio/' + pad + '.mp3'
    );
    return urls;
  };

  const playList = (urls, i, ar, btn) => {
    if (currentBtn !== btn) return;
    if (i >= urls.length) {
      speak(ar, btn);
      return;
    }
    audio.onerror = () => playList(urls, i + 1, ar, btn);
    audio.onended = () => { if (currentBtn === btn) resetBtns(); };
    audio.oncanplay = () => {
      window.clearTimeout(failTimer);
      audio.play().catch(() => playList(urls, i + 1, ar, btn));
    };
    failTimer = window.setTimeout(() => playList(urls, i + 1, ar, btn), 3500);
    audio.src = urls[i];
    audio.load();
    audio.play().catch(() => {});
  };

  root.querySelectorAll('[data-name-play]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const card = btn.closest('[data-name-card]');
      const n = card ? card.getAttribute('data-n') : '';
      const ar = card ? (card.getAttribute('data-ar') || card.querySelector('.ft-ar')?.textContent || '') : '';
      if (currentBtn === btn && !audio.paused) {
        stopAll();
        return;
      }
      stopAll();
      markPlaying(btn);
      playList(sources(n), 0, ar, btn);
    });
  });
})();
