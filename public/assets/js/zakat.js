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
    if (date) date.textContent = spot.for_date || '';
    if (note) note.textContent = spot.stale ? 'Last saved rates' : 'Live spot · India (INR)';
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

    const calculate = () => {
    const calcUrl = root.getAttribute('data-calc-url') || '/api/zakat/calculate';
    return fetch(calcUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(payload()),
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
  const loadNisab = () => fetch(nisabUrl, { headers: { Accept: 'application/json' } })
    .then((res) => res.json())
    .then(paintNisab)
    .catch(() => {});

  loadNisab();
  setInterval(() => {
    const shown = (root.querySelector('[data-spot-date]')?.textContent || '').trim();
    const today = new Intl.DateTimeFormat('en-CA', {
      timeZone: 'Asia/Kolkata',
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
    }).format(new Date());
    if (shown && shown !== today) {
      loadNisab();
    }
  }, 60000);
})();
