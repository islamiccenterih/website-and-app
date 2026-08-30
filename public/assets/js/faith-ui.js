(() => {
  const share = (text) => {
    const url = 'https://wa.me/?text=' + encodeURIComponent(text);
    window.open(url, '_blank', 'noopener');
  };
  document.addEventListener('click', (event) => {
    const btn = event.target.closest('[data-share]');
    if (btn) {
      share(String(btn.getAttribute('data-share') || '') + '\n' + window.location.href);
    }
  });
  const root = document.querySelector('[data-duas-root]');
  if (root) {
    root.querySelectorAll('[data-dua-tab]').forEach((tab) => {
      tab.addEventListener('click', () => {
        const id = tab.getAttribute('data-dua-tab');
        root.querySelectorAll('[data-dua-tab]').forEach((el) => el.classList.toggle('is-on', el === tab));
        root.querySelectorAll('[data-dua-panel]').forEach((panel) => {
          panel.hidden = panel.getAttribute('data-dua-panel') !== id;
        });
      });
    });
  }
  const hajj = document.querySelector('[data-hajj-root]');
  if (hajj) {
    hajj.querySelectorAll('[data-check-item]').forEach((box) => {
      const key = 'ic_hajj_' + box.getAttribute('data-check-item');
      try {
        box.checked = localStorage.getItem(key) === '1';
      } catch (e) {}
      box.addEventListener('change', () => {
        try {
          localStorage.setItem(key, box.checked ? '1' : '0');
        } catch (e) {}
      });
    });
  }

})();
