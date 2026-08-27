(() => {
  const root = document.querySelector('[data-zakat-root]');
  if (!root) return;
  const form = root.querySelector('[data-zakat-form]');
  const result = root.querySelector('[data-zakat-result]');
  const due = root.querySelector('[data-zakat-due]');
  const lines = root.querySelector('[data-zakat-lines]');
  const errorBox = root.querySelector('[data-zakat-error]');
  const rupee = (n) => '₹' + Number(n || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  const payload = () => {
    const data = {};
    if (!form) return data;
    new FormData(form).forEach((value, key) => {
      data[key] = value;
    });
    return data;
  };

  const paintNisab = (spot) => {
    if (!spot || !spot.ok) return;
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
    if (note) note.textContent = spot.stale ? 'Saved rates · live feed reconnecting…' : 'Live gold & silver · INR · refreshes every 10s';
    if (errorBox) {
      if (spot.error) {
        errorBox.hidden = false;
        errorBox.textContent = spot.error;
      } else {
        errorBox.hidden = true;
      }
    }
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
    if (noteEl && liveSpot && liveSpot.ok && !liveSpot.stale) {
      noteEl.textContent = 'Live gold & silver · INR · refreshes every 10s';
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
    const due = net >= nisab && nisab > 0;
    const zakat = due ? Math.round(net * (rate / 100) * 100) / 100 : 0;
    return {
      ok: true,
      assets,
      debts,
      net,
      nisab,
      above_nisab: due,
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

  const calculate = () => {
    const input = payload();
    if (liveSpot && liveSpot.ok) {
      paintCalc(calcLocal(liveSpot, input));
      return Promise.resolve();
    }
    const calcUrl = root.getAttribute('data-calc-url') || '/api/zakat/calculate';
    return fetch(calcUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(input),
    }).then((res) => res.json()).then(paintCalc);
  };

  form?.addEventListener('submit', (event) => {
    event.preventDefault();
    calculate();
  });
  form?.addEventListener('input', () => {
    window.clearTimeout(form._t);
    form._t = window.setTimeout(calculate, 350);
  });

  const nisabUrl = root.getAttribute('data-nisab-url') || '/api/zakat/nisab';
  const loadNisab = () => {
    if (fetching) return Promise.resolve();
    fetching = true;
    const apply = (spot) => {
      fetching = false;
      if (!spot || !spot.ok) return;
      const changed = !liveSpot || Number(liveSpot.gold_per_gram_inr) !== Number(spot.gold_per_gram_inr)
        || Number(liveSpot.silver_per_gram_inr) !== Number(spot.silver_per_gram_inr);
      liveSpot = spot;
      lastFetchAt = Date.now();
      paintNisab(spot);
      paintClock();
      if (changed && form && [...form.querySelectorAll('input,select')].some((el) => el.value)) {
        calculate();
      }
    };
    const fail = () => { fetching = false; };
    const localApi = () => fetch(nisabUrl, { headers: { Accept: 'application/json' } })
      .then((res) => res.json())
      .then(apply)
      .catch(fail);
    if (window.ICLive && typeof ICLive.metalSpot === 'function') {
      return ICLive.metalSpot({ gold_nisab_g: goldG, silver_nisab_g: silverG, rate, nisab_method: method })
        .then(apply)
        .catch(localApi);
    }
    return localApi();
  };

  loadNisab();
  setInterval(loadNisab, 10000);
  setInterval(paintClock, 1000);
})();
