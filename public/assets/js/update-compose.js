(() => {
  const root = document.querySelector('[data-compose-root]');
  if (!root) return;
  const canvas = root.querySelector('[data-compose]');
  const hidden = root.querySelector('[data-compose-html]');
  const form = document.querySelector('[data-update-form]');
  const uploadUrl = root.getAttribute('data-upload') || '';
  const embedUrl = root.getAttribute('data-embed') || '';
  const csrf = root.getAttribute('data-csrf') || '';
  const imageInput = root.querySelector('[data-image-file]');
  const videoInput = root.querySelector('[data-video-file]');

  const insertHtml = (html) => {
    canvas.focus();
    try {
      document.execCommand('insertHTML', false, html);
    } catch (err) {
      canvas.insertAdjacentHTML('beforeend', html);
    }
    window.requestAnimationFrame(bindImageResize);
  };

  root.querySelectorAll('[data-cmd]').forEach((btn) => {
    btn.addEventListener('click', () => {
      canvas.focus();
      document.execCommand(btn.getAttribute('data-cmd'), false, null);
    });
  });
  root.querySelectorAll('[data-block]').forEach((btn) => {
    btn.addEventListener('click', () => {
      canvas.focus();
      document.execCommand('formatBlock', false, btn.getAttribute('data-block'));
    });
  });

  const postForm = (url, extra, file) => {
    const data = new FormData();
    data.append('_csrf', csrf);
    Object.keys(extra).forEach((key) => data.append(key, extra[key]));
    if (file) data.append('file', file);
    return fetch(url, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrf },
      body: data,
    }).then((res) => res.json());
  };

  const wrapBareImages = () => {
    canvas.querySelectorAll('img').forEach((img) => {
      if (img.closest('figure.update-media')) return;
      const fig = document.createElement('figure');
      fig.className = 'update-media';
      fig.style.width = '100%';
      img.parentNode.insertBefore(fig, img);
      fig.appendChild(img);
    });
  };

  const bindOneFigure = (figure) => {
    if (!figure || figure.classList.contains('update-video') || figure.classList.contains('update-embed')) {
      return;
    }
    if (!figure.querySelector('img')) return;
    figure.classList.add('is-resizable');
    if (!figure.style.width) {
      figure.style.width = '100%';
    }
    if (figure.querySelector('.compose-resize-handle')) return;
    const handle = document.createElement('span');
    handle.className = 'compose-resize-handle';
    handle.contentEditable = 'false';
    handle.setAttribute('role', 'slider');
    handle.setAttribute('aria-label', 'Drag to resize this picture');
    handle.title = 'Drag to resize';
    figure.appendChild(handle);
    handle.addEventListener('pointerdown', (ev) => {
      ev.preventDefault();
      ev.stopPropagation();
      const rtl = document.documentElement.getAttribute('dir') === 'rtl';
      const startX = ev.clientX;
      const startW = figure.getBoundingClientRect().width;
      const parentW = canvas.getBoundingClientRect().width || 1;
      handle.classList.add('is-dragging');
      const onMove = (e) => {
        const dx = rtl ? (startX - e.clientX) : (e.clientX - startX);
        const pct = Math.max(20, Math.min(100, ((startW + dx) / parentW) * 100));
        figure.style.width = (Math.round(pct * 10) / 10) + '%';
        figure.style.height = '';
      };
      const onUp = () => {
        handle.classList.remove('is-dragging');
        handle.removeEventListener('pointermove', onMove);
        handle.removeEventListener('pointerup', onUp);
        handle.removeEventListener('pointercancel', onUp);
        try { handle.releasePointerCapture(ev.pointerId); } catch (err) { /* ignore */ }
      };
      try { handle.setPointerCapture(ev.pointerId); } catch (err) { /* ignore */ }
      handle.addEventListener('pointermove', onMove);
      handle.addEventListener('pointerup', onUp);
      handle.addEventListener('pointercancel', onUp);
    });
  };

  let binding = false;
  const bindImageResize = () => {
    if (binding) return;
    binding = true;
    observer.disconnect();
    wrapBareImages();
    canvas.querySelectorAll('figure.update-media').forEach(bindOneFigure);
    observer.observe(canvas, { childList: true, subtree: true });
    binding = false;
  };

  const observer = new MutationObserver(() => bindImageResize());

  const stripEditorChrome = () => {
    canvas.querySelectorAll('.compose-resize-handle').forEach((el) => el.remove());
    canvas.querySelectorAll('figure.update-media.is-resizable').forEach((fig) => {
      fig.classList.remove('is-resizable');
    });
  };

  const persistImageWidths = () => {
    const parentW = canvas.getBoundingClientRect().width || 1;
    canvas.querySelectorAll('figure.update-media').forEach((fig) => {
      if (fig.classList.contains('update-video') || fig.classList.contains('update-embed')) return;
      if (!fig.querySelector('img')) return;
      const w = fig.getBoundingClientRect().width;
      const pct = Math.max(15, Math.min(100, (w / parentW) * 100));
      fig.style.width = (Math.round(pct * 10) / 10) + '%';
      fig.style.height = '';
    });
  };

  const imageBtn = root.querySelector('[data-insert-image]');
  if (imageBtn && imageInput) {
    imageBtn.addEventListener('click', () => imageInput.click());
    imageInput.addEventListener('change', () => {
      const file = imageInput.files && imageInput.files[0];
      imageInput.value = '';
      if (!file) return;
      postForm(uploadUrl, { kind: 'image' }, file).then((json) => {
        if (!json.ok) {
          window.alert(json.error || 'The picture could not be uploaded.');
          return;
        }
        insertHtml('<figure class="update-media" style="width: 100%;"><img src="' + json.url + '" alt=""></figure><p></p>');
      }).catch(() => window.alert('The picture could not be uploaded.'));
    });
  }

  const videoBtn = root.querySelector('[data-insert-video]');
  if (videoBtn && videoInput) {
    videoBtn.addEventListener('click', () => videoInput.click());
    videoInput.addEventListener('change', () => {
      const file = videoInput.files && videoInput.files[0];
      videoInput.value = '';
      if (!file) return;
      postForm(uploadUrl, { kind: 'video' }, file).then((json) => {
        if (!json.ok) {
          window.alert(json.error || 'The video could not be uploaded.');
          return;
        }
        insertHtml('<figure class="update-media update-video"><video src="' + json.url + '" controls playsinline></video></figure><p></p>');
      }).catch(() => window.alert('The video could not be uploaded. Try a smaller MP4, or paste a YouTube link.'));
    });
  }

  const embedBtn = root.querySelector('[data-insert-embed]');
  if (embedBtn) {
    embedBtn.addEventListener('click', () => {
      const url = window.prompt('Paste a YouTube or Vimeo link');
      if (!url) return;
      postForm(embedUrl, { url: url }).then((json) => {
        if (!json.ok) {
          window.alert(json.error || 'That link could not be used.');
          return;
        }
        insertHtml('<figure class="update-media update-embed"><iframe src="' + json.src + '" title="Video" allowfullscreen loading="lazy"></iframe></figure><p></p>');
      }).catch(() => window.alert('That link could not be used.'));
    });
  }

  if (form && hidden && canvas) {
    form.addEventListener('submit', () => {
      persistImageWidths();
      stripEditorChrome();
      hidden.value = canvas.innerHTML;
      window.requestAnimationFrame(bindImageResize);
    });
  }

  bindImageResize();
})();
