(() => {
  const root = document.querySelector('[data-tasbeeh-root]');
  if (!root) return;
  const countEl = root.querySelector('[data-tasbeeh-count]');
  const setsEl = root.querySelector('[data-tasbeeh-sets]');
  const bead = root.querySelector('[data-tasbeeh-tap]');
  const vibrate = root.querySelector('[data-tasbeeh-vibrate]');
  const storeKey = 'ic_tasbeeh_v2';
  let ctx = null;
  let state = { total: 0, vibrate: true };
  try {
    const saved = JSON.parse(localStorage.getItem(storeKey) || localStorage.getItem('ic_tasbeeh') || '{}');
    if (typeof saved.total === 'number') state.total = saved.total;
    else if (typeof saved.count === 'number') state.total = saved.count;
    if (typeof saved.vibrate === 'boolean') state.vibrate = saved.vibrate;
  } catch (e) {}
  if (vibrate) vibrate.checked = !!state.vibrate;

  const inSet = () => {
    if (state.total === 0) return 0;
    const mod = state.total % 100;
    return mod === 0 ? 100 : mod;
  };
  const sets = () => Math.floor(state.total / 100);

  const save = () => {
    try { localStorage.setItem(storeKey, JSON.stringify(state)); } catch (e) {}
  };
  const paint = () => {
    if (countEl) countEl.textContent = String(inSet());
    if (setsEl) setsEl.textContent = String(sets());
  };

  const tickSound = (strong) => {
    try {
      const AC = window.AudioContext || window.webkitAudioContext;
      if (!AC) return;
      if (!ctx) ctx = new AC();
      if (ctx.state === 'suspended') ctx.resume();
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'sine';
      osc.frequency.value = strong ? 620 : 880;
      gain.gain.value = 0.05;
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start();
      gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + (strong ? 0.18 : 0.07));
      osc.stop(ctx.currentTime + (strong ? 0.2 : 0.08));
    } catch (e) {}
  };

  const buzz = (strong) => {
    tickSound(strong);
    if (!state.vibrate) return;
    try {
      if (typeof navigator.vibrate === 'function') {
        navigator.vibrate(0);
        navigator.vibrate(strong ? [40, 30, 70, 30, 90] : 22);
      }
    } catch (e) {}
  };

  const add = (n) => {
    state.total = Math.max(0, state.total + n);
    save();
    paint();
    if (n > 0) {
      const doneSet = state.total > 0 && state.total % 100 === 0;
      buzz(doneSet);
      if (bead) {
        bead.classList.remove('is-set');
        void bead.offsetWidth;
        if (doneSet) bead.classList.add('is-set');
      }
    }
  };

  bead?.addEventListener('click', () => add(1));
  root.querySelector('[data-tasbeeh-undo]')?.addEventListener('click', () => add(-1));
  root.querySelector('[data-tasbeeh-reset]')?.addEventListener('click', () => {
    state.total = 0;
    save();
    paint();
    bead?.classList.remove('is-set');
  });
  vibrate?.addEventListener('change', () => {
    state.vibrate = vibrate.checked;
    save();
    if (state.vibrate) buzz(false);
  });
  paint();
})();
