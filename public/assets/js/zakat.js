(() => {
  const root = document.querySelector('[data-zakat-root]');
  if (!root) return;
  const form = root.querySelector('[data-zakat-form]');
  const result = root.querySelector('[data-zakat-result]');
  const due = root.querySelector('[data-zakat-due]');
  const lines = root.querySelector('[data-zakat-lines]');
  const errorBox = root.querySelector('[data-zakat-error]');
  const rupee = (n) => '₹' + Number(n || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const ibjaNote = (spot) => {
    const iso = String((spot && spot.for_date) || '');
    const parts = iso.split('-');
    const when = parts.length === 3
      ? new Date(iso + 'T12:00:00+05:30').toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric', timeZone: 'Asia/Kolkata' })
      : '';
    return when
      ? 'IBJA 24k (999) · last close ' + when + ' · without GST (same as ibjarates.com)'
      : 'IBJA 24k (999) · without GST (same as ibjarates.com)';
  };

  const payload = () => {
    const data = {};
    if (!form) return data;
    new FormData(form).forEach((value, key) => {
      data[key] = value;
    });
    return data;
  };

  const isLiveSpot = (spot) => !!(spot && spot.ok && spot.live && !spot.stale);

  const paintNisab = (spot) => {
    if (!isLiveSpot(spot)) return;
    const gold = root.querySelector('[data-gold-nisab]');
    const silver = root.querySelector('[data-silver-nisab]');
    const g10 = root.querySelector('[data-gold-10g]');
    const sKg = root.querySelector('[data-silver-kg]');
    const date = root.querySelector('[data-spot-date]');
    const note = root.querySelector('[data-spot-note]');
    if (gold) gold.textContent = rupee(spot.gold_nisab_inr);
    if (silver) silver.textContent = rupee(spot.silver_nisab_inr);
    if (g10) g10.textContent = rupee(spot.gold_per_10g_inr) + ' / 10g 24k';
    if (sKg) sKg.textContent = rupee(spot.silver_per_kg_inr) + ' / kg';
    if (date) date.textContent = lastFetchAt ? istClock(lastFetchAt) : (spot.for_date || '');
    if (note) note.textContent = ibjaNote(spot);
    if (errorBox) {
      errorBox.hidden = true;
      errorBox.textContent = '';
    }
  };

  const showWait = (message) => {
    if (!errorBox) return;
    errorBox.hidden = false;
    errorBox.textContent = message;
  };

  const paintCalc = (data) => {
    if (!result || !due || !lines) return;
    result.hidden = false;
    if (data.above_nisab) {
      due.innerHTML = 'Zakat due this year: <strong>' + rupee(data.zakat) + '</strong> · net wealth ' + rupee(data.net) + ' is above nisab ' + rupee(data.nisab) + '.';
    } else {
      due.innerHTML = 'No zakat is due on this figure. Net wealth ' + rupee(data.net) + ' is below nisab ' + rupee(data.nisab) + '.';
    }
    lines.innerHTML = '';
    (data.lines || []).forEach((line) => {
      if (!line.amount) return;
      const li = document.createElement('li');
      li.innerHTML = '<div class="when">' + rupee(line.amount) + '</div><div><strong>' + line.label + '</strong></div>';
      lines.appendChild(li);
    });
    const total = document.createElement('li');
    total.innerHTML = '<div class="when">' + rupee(data.zakat) + '</div><div><strong>Zakat at ' + data.rate + '%</strong></div>';
    lines.appendChild(total);
  };

  let liveSpot = null;
  let lastFetchAt = 0;
  let fetching = false;
  const goldG = Number(root.getAttribute('data-gold-nisab-g') || 87.48);
  const silverG = Number(root.getAttribute('data-silver-nisab-g') || 612.36);
  const rate = Number(root.getAttribute('data-zakat-rate') || 2.5);
  const method = root.getAttribute('data-nisab-method') || 'lower';
  const clockEl = root.querySelector('[data-spot-clock]');
  const noteEl = root.querySelector('[data-spot-note]');

  const istClock = (ms) => new Intl.DateTimeFormat('en-IN', {
    timeZone: 'Asia/Kolkata',
    hour: 'numeric',
    minute: '2-digit',
    second: '2-digit',
    hour12: true,
  }).format(new Date(ms));

  const paintClock = () => {
    if (!clockEl || !lastFetchAt) return;
    clockEl.textContent = istClock(lastFetchAt);
    if (noteEl && isLiveSpot(liveSpot)) {
      noteEl.textContent = ibjaNote(liveSpot);
    }
  };

  const money = (value) => {
    const n = parseFloat(String(value || '0').replace(/[,₹\s]/g, '')) || 0;
    return n < 0 ? 0 : n;
  };

  const calcLocal = (spot, input) => {
    const goldValue = money(input.gold_grams) * (Math.min(24, Math.max(9, money(input.gold_karat) || 24)) / 24) * (spot.gold_per_gram_inr || 0);
    const silverValue = money(input.silver_grams) * (spot.silver_per_gram_inr || 0);
    const cash = money(input.cash) + money(input.bank) + money(input.business) + money(input.receivables) + money(input.investments) + money(input.crypto) + money(input.other);
    const debts = money(input.debts);
    const assets = goldValue + silverValue + cash;
    const net = Math.max(0, assets - debts);
    const goldNisab = spot.gold_nisab_inr || 0;
    const silverNisab = spot.silver_nisab_inr || 0;
    const nisab = method === 'gold' ? goldNisab : (method === 'silver' ? silverNisab : Math.min(goldNisab, silverNisab) || Math.max(goldNisab, silverNisab));
    const dueNow = net >= nisab && nisab > 0;
    const zakat = dueNow ? Math.round(net * (rate / 100) * 100) / 100 : 0;
    return {
      ok: true,
      assets,
      debts,
      net,
      nisab,
      above_nisab: dueNow,
      rate,
      zakat,
      lines: [
        { label: 'Gold', amount: goldValue },
        { label: 'Silver', amount: silverValue },
        { label: 'Cash in hand', amount: money(input.cash) },
        { label: 'Bank and savings', amount: money(input.bank) },
        { label: 'Business stock', amount: money(input.business) },
        { label: 'Money owed to you', amount: money(input.receivables) },
        { label: 'Shares and funds', amount: money(input.investments) },
        { label: 'Crypto and digital', amount: money(input.crypto) },
        { label: 'Other zakatable wealth', amount: money(input.other) },
        { label: 'Debts due now', amount: -debts },
      ],
    };
  };

  const hasInput = () => form && [...form.querySelectorAll('input,select')].some((el) => el.value && el.value !== '0' && el.value !== '24');

  const calculate = () => {
    if (!isLiveSpot(liveSpot)) {
      if (result) result.hidden = true;
      showWait('Live gold and silver prices are still loading. Zakat will be calculated only after today’s rates arrive — old rates are not used.');
      return;
    }
    paintCalc(calcLocal(liveSpot, payload()));
  };

  form?.addEventListener('submit', (event) => {
    event.preventDefault();
    calculate();
  });
  form?.addEventListener('input', () => {
    window.clearTimeout(form._t);
    form._t = window.setTimeout(calculate, 350);
  });

  const loadNisab = () => {
    if (fetching) return Promise.resolve();
    fetching = true;
    const apply = (spot) => {
      fetching = false;
      if (!isLiveSpot(spot)) {
        showWait('Live gold and silver could not be confirmed. Zakat will not use a saved or yesterday’s rate.');
        return;
      }
      const changed = !liveSpot || Number(liveSpot.gold_per_gram_inr) !== Number(spot.gold_per_gram_inr)
        || Number(liveSpot.silver_per_gram_inr) !== Number(spot.silver_per_gram_inr);
      liveSpot = spot;
      lastFetchAt = Date.now();
      paintNisab(spot);
      paintClock();
      if (changed && hasInput()) calculate();
    };
    const fail = () => {
      fetching = false;
      if (!isLiveSpot(liveSpot)) {
        showWait('Live gold and silver could not be loaded. Check your connection — zakat will not be shown from old rates.');
      }
    };
    if (window.ICLive && typeof ICLive.metalSpot === 'function') {
      return ICLive.metalSpot({ gold_nisab_g: goldG, silver_nisab_g: silverG, rate, nisab_method: method })
        .then(apply)
        .catch(() => {
          const nisabUrl = root.getAttribute('data-nisab-url') || '/api/zakat/nisab';
          return fetch(nisabUrl, { cache: 'no-store', headers: { Accept: 'application/json' } })
            .then((res) => res.json())
            .then(apply)
            .catch(fail);
        });
    }
    fail();
    return Promise.resolve();
  };

  loadNisab();
  setInterval(loadNisab, 60000);
  setInterval(paintClock, 1000);
  if (window.ICLive && typeof ICLive.watchFresh === 'function') {
    ICLive.watchFresh(loadNisab, 8000);
  }
})();
