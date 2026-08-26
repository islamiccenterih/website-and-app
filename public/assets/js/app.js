(() => {
  const nav = document.getElementById('site-nav');
  const toggle = document.querySelector('[data-nav-toggle]');
  const closeBtn = document.querySelector('[data-nav-close]');

  const setOpen = (open) => {
    if (!nav || !toggle) return;
    nav.classList.toggle('is-open', open);
    document.body.classList.toggle('nav-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    nav.setAttribute('aria-hidden', open ? 'false' : 'true');
  };

  if (nav && toggle) {
    toggle.addEventListener('click', () => setOpen(!nav.classList.contains('is-open')));
    if (closeBtn) closeBtn.addEventListener('click', () => setOpen(false));
    nav.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => setOpen(false));
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') setOpen(false);
    });
  }

  const closeMore = () => {
    document.querySelectorAll('[data-nav-more]').forEach((wrap) => {
      wrap.classList.remove('is-open');
      const btn = wrap.querySelector('[data-nav-more-btn]');
      if (btn) btn.setAttribute('aria-expanded', 'false');
    });
  };
  document.querySelectorAll('[data-nav-more]').forEach((wrap) => {
    const btn = wrap.querySelector('[data-nav-more-btn]');
    if (!btn) return;
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      const open = !wrap.classList.contains('is-open');
      closeMore();
      wrap.classList.toggle('is-open', open);
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  });
  document.addEventListener('click', closeMore);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeMore();
  });

  const side = document.getElementById('dash-side');
  const dashToggle = document.querySelector('[data-dash-toggle]');
  const dashBackdrop = document.querySelector('[data-dash-backdrop]');
  const dashClose = document.querySelector('[data-dash-close]');
  const setDashOpen = (open) => {
    if (!side) return;
    side.classList.toggle('is-open', open);
    if (dashBackdrop) {
      dashBackdrop.hidden = !open;
      dashBackdrop.classList.toggle('is-open', open);
    }
    document.body.classList.toggle('dash-nav-open', open);
    if (dashToggle) dashToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  };
  if (side && dashToggle) {
    dashToggle.addEventListener('click', () => setDashOpen(!side.classList.contains('is-open')));
  }
  if (dashClose) dashClose.addEventListener('click', () => setDashOpen(false));
  if (dashBackdrop) dashBackdrop.addEventListener('click', () => setDashOpen(false));
  if (side) {
    side.querySelectorAll('nav a').forEach((link) => {
      link.addEventListener('click', () => setDashOpen(false));
    });
  }
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') setDashOpen(false);
  });

  const dashFilter = document.querySelector('[data-dash-filter]');
    if (dashFilter) {
      const links = document.querySelectorAll('.dash-side nav a');
      const labels = document.querySelectorAll('.dash-nav-label');
      dashFilter.addEventListener('input', () => {
        const q = dashFilter.value.trim().toLowerCase();
        links.forEach((a) => {
          const show = !q || (a.textContent || '').toLowerCase().includes(q);
          a.hidden = !show;
        });
        labels.forEach((label) => {
          let node = label.nextElementSibling;
          let any = false;
          while (node && !node.classList.contains('dash-nav-label')) {
            if (node.tagName === 'A' && !node.hidden) any = true;
            node = node.nextElementSibling;
          }
          label.hidden = q !== '' && !any;
        });
      });
    }

  const lightbox = document.querySelector('[data-lightbox]');
  if (lightbox) {
    const img = lightbox.querySelector('img');
    document.querySelectorAll('[data-gallery-item]').forEach((el) => {
      el.addEventListener('click', (e) => {
        e.preventDefault();
        img.src = el.getAttribute('href');
        img.alt = el.getAttribute('data-alt') || '';
        lightbox.classList.add('is-open');
      });
    });
    lightbox.addEventListener('click', () => lightbox.classList.remove('is-open'));
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') lightbox.classList.remove('is-open');
    });
  }

  document.querySelectorAll('[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (e) => {
      if (!window.confirm(form.getAttribute('data-confirm') || 'Are you sure?')) {
        e.preventDefault();
      }
    });
  });

  const bar = document.querySelector('[data-scroll-bar]');
  const ring = document.querySelector('[data-scroll-ring]');
  const pct = document.querySelector('[data-scroll-pct]');
  const topBtn = document.querySelector('[data-scroll-top]');
  const onScroll = () => {
    const max = document.documentElement.scrollHeight - window.innerHeight;
    const value = max > 0 ? Math.min(100, Math.round((window.scrollY / max) * 100)) : 0;
    if (bar) bar.style.width = value + '%';
    if (ring) ring.style.strokeDasharray = value + ' 100';
    if (pct) pct.textContent = value + '%';
    if (topBtn) topBtn.classList.toggle('is-visible', window.scrollY > 180);
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
  if (topBtn) {
    topBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  const heroVideo = document.querySelector('.hero-video');
  if (heroVideo && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    heroVideo.muted = true;
    heroVideo.playsInline = true;
    heroVideo.play().catch(() => {});
  }
})();
