(() => {
  const root = document.querySelector('[data-mirath-root]');
  if (!root) return;
  const form = root.querySelector('[data-mirath-form]');
  const result = root.querySelector('[data-mirath-result]');
  const due = root.querySelector('[data-mirath-due]');
  const lines = root.querySelector('[data-mirath-lines]');
  const rupee = (n) => '₹' + Number(n || 0).toLocaleString('en-IN', { maximumFractionDigits: 2 });
  const money = (v) => Math.max(0, parseFloat(String(v || '0').replace(/[,₹\s]/g, '')) || 0);

  const fracLabel = (portion) => {
    const map = [[0.5, '1/2'], [0.25, '1/4'], [0.125, '1/8'], [1 / 3, '1/3'], [1 / 6, '1/6'], [2 / 3, '2/3']];
    const hit = map.find((row) => Math.abs(portion - row[0]) < 0.01);
    return hit ? hit[1] : (Math.round(portion * 1000) / 10) + '%';
  };

  const num = (name) => Math.max(0, parseInt(form.querySelector('[name="' + name + '"]')?.value || '0', 10) || 0);
  const on = (name) => !!form.querySelector('[name="' + name + '"]')?.checked;

  const calculate = () => {
    const estate = money(form.querySelector('[name="estate"]')?.value);
    const deceased = form.querySelector('[name="deceased"]')?.value === 'female' ? 'female' : 'male';
    const spouse = on('spouse');
    const sons = num('sons');
    const daughters = num('daughters');
    const father = on('father');
    const mother = on('mother');
    const brothers = num('brothers');
    const sisters = num('sisters');
    const kids = sons + daughters;
    const descendants = kids > 0;
    const shares = [];
    if (spouse && deceased === 'female') shares.push({ key: 'husband', label: 'Husband / पति', portion: descendants ? 0.25 : 0.5 });
    if (spouse && deceased === 'male') shares.push({ key: 'wife', label: 'Wife / पत्नी', portion: descendants ? 0.125 : 0.25 });
    const umariyyah = spouse && father && mother && !descendants;
    if (mother) {
      if (descendants || (brothers + sisters) >= 2) shares.push({ key: 'mother', label: 'Mother / माँ', portion: 1 / 6 });
      else if (umariyyah) {
        const spousePart = deceased === 'female' ? 0.5 : 0.25;
        shares.push({ key: 'mother', label: 'Mother / माँ (बाकी का 1/3)', portion: (1 - spousePart) / 3 });
      } else shares.push({ key: 'mother', label: 'Mother / माँ', portion: 1 / 3 });
    }
    if (father) {
      if (descendants) shares.push({ key: 'father', label: 'Father / पिता', portion: 1 / 6 });
      else shares.push({ key: 'father', label: 'Father / पिता (बाकी)', portion: 0, asaba: true });
    }
    if (sons === 0 && daughters === 1) shares.push({ label: 'Daughter / बेटी', portion: 0.5 });
    else if (sons === 0 && daughters >= 2) shares.push({ label: 'Daughters / बेटियाँ (' + daughters + ')', portion: 2 / 3 });
    else if (sons > 0) shares.push({ label: 'children', portion: 0, asaba: true, sons, daughters });
    const asabaSiblings = !descendants && !father && (brothers > 0 || sisters > 0);
    if (asabaSiblings && brothers === 0 && sisters === 1) shares.push({ label: 'Full sister / बहन', portion: 0.5 });
    else if (asabaSiblings && brothers === 0 && sisters >= 2) shares.push({ label: 'Full sisters / बहनें (' + sisters + ')', portion: 2 / 3 });
    else if (asabaSiblings && brothers > 0) shares.push({ label: 'siblings', portion: 0, asaba: true, sons: brothers, daughters: sisters });

    let fixed = 0;
    shares.forEach((row) => { if (!row.asaba) fixed += row.portion; });
    const hasAsaba = shares.some((row) => row.asaba);
    const awl = fixed > 1.0001;
    const radd = fixed < 0.999 && !hasAsaba;
    const scale = awl && fixed > 0 ? 1 / fixed : 1;
    const out = [];
    let assigned = 0;
    shares.forEach((row) => {
      if (row.asaba) return;
      let portion = row.portion * scale;
      if (radd && fixed > 0) portion = row.portion / fixed;
      const amount = Math.round(estate * portion * 100) / 100;
      assigned += amount;
      out.push({ label: row.label, fraction: fracLabel(portion), percent: Math.round(portion * 10000) / 100, amount });
    });
    let residue = Math.max(0, Math.round((estate - assigned) * 100) / 100);
    shares.forEach((row) => {
      if (!row.asaba || residue <= 0) return;
      if (row.key === 'father') {
        out.push({ label: row.label, fraction: fracLabel(estate ? residue / estate : 0), percent: estate ? Math.round(residue / estate * 10000) / 100 : 0, amount: residue });
        residue = 0;
        return;
      }
      const s = row.sons || 0;
      const d = row.daughters || 0;
      const units = (s * 2) + d;
      if (units <= 0) return;
      const sonAmt = Math.round(residue * (2 / units) * 100) / 100;
      const dauAmt = Math.round(residue * (1 / units) * 100) / 100;
      if (s) out.push({ label: s === 1 ? 'Son / बेटा' : s + ' sons / बेटे', fraction: '2:1 बाकी', percent: estate ? Math.round((sonAmt * s) / estate * 10000) / 100 : 0, amount: Math.round(sonAmt * s * 100) / 100 });
      if (d) out.push({ label: d === 1 ? 'Daughter / बेटी' : d + ' daughters / बेटियाँ', fraction: '2:1 बाकी', percent: estate ? Math.round((dauAmt * d) / estate * 10000) / 100 : 0, amount: Math.round(dauAmt * d * 100) / 100 });
      residue = 0;
    });
    if (residue > 0) out.push({ label: 'Unassigned — ask a teacher / बाकी — उस्ताद से पूछें', fraction: '—', percent: estate ? Math.round(residue / estate * 10000) / 100 : 0, amount: residue });
    return { estate, awl, radd, lines: out };
  };

  const paintChoice = () => {
    const val = form.querySelector('[name="deceased"]')?.value;
    root.querySelectorAll('[data-deceased]').forEach((btn) => {
      btn.classList.toggle('is-on', btn.getAttribute('data-deceased') === val);
    });
  };
  const paintSwitch = (name) => {
    const box = form.querySelector('[name="' + name + '"]');
    root.querySelector('[data-switch="' + name + '"]')?.classList.toggle('is-on', !!box?.checked);
  };

  const paint = () => {
    const out = calculate();
    if (result) result.hidden = false;
    const flags = [out.awl ? 'Shares were reduced (awl). / हिस्सा घटाया गया।' : '', out.radd ? 'Remainder returned to heirs (radd). / बाकी वारिसों को लौटा।' : ''].filter(Boolean).join(' ');
    if (due) due.textContent = 'Estate / जायदाद ' + rupee(out.estate) + (flags ? ' · ' + flags : '');
    if (!lines) return;
    lines.innerHTML = '';
    out.lines.forEach((line) => {
      const row = document.createElement('div');
      row.className = 'ft-share-row';
      row.innerHTML = '<div><strong></strong><p></p></div><div class="ft-share-amt"></div>';
      row.querySelector('strong').textContent = line.label;
      row.querySelector('p').textContent = line.fraction + ' · ' + line.percent + '%';
      row.querySelector('.ft-share-amt').textContent = rupee(line.amount);
      lines.appendChild(row);
    });
  };

  root.querySelectorAll('[data-deceased]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const input = form.querySelector('[name="deceased"]');
      if (input) input.value = btn.getAttribute('data-deceased') || 'male';
      paintChoice();
      paint();
    });
  });
  root.querySelectorAll('[data-switch]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const name = btn.getAttribute('data-switch');
      const box = form.querySelector('[name="' + name + '"]');
      if (box) box.checked = !box.checked;
      paintSwitch(name);
      paint();
    });
  });
  root.querySelectorAll('[data-step]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const name = btn.getAttribute('data-step');
      const dir = Number(btn.getAttribute('data-dir') || '1');
      const input = form.querySelector('[name="' + name + '"]');
      if (!input) return;
      input.value = String(Math.max(0, (parseInt(input.value || '0', 10) || 0) + dir));
      paint();
    });
  });

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    paint();
  });
  form.addEventListener('input', () => {
    window.clearTimeout(form._t);
    form._t = window.setTimeout(paint, 200);
  });
  paintChoice();
  paintSwitch('spouse');
  paintSwitch('father');
  paintSwitch('mother');
})();
