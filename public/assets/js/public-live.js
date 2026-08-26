(() => {
  const iceServers = {
    iceCandidatePoolSize: 4,
    iceServers: [
      { urls: 'stun:stun.l.google.com:19302' },
      { urls: 'stun:stun1.l.google.com:19302' },
      { urls: 'stun:stun2.l.google.com:19302' },
      { urls: 'stun:stun.cloudflare.com:3478' },
    ],
  };

  const post = (url, body, csrf) => {
    const payload = Object.assign({}, body || {});
    const headers = {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    };
    if (csrf) {
      payload._csrf = csrf;
      headers['X-CSRF-TOKEN'] = csrf;
    }
    return fetch(url, {
      method: 'POST',
      headers,
      credentials: 'same-origin',
      body: JSON.stringify(payload),
    }).then(async (res) => {
      let data = {};
      try {
        data = await res.json();
      } catch (e) {
        data = { ok: false, error: 'Could not reach the live server.' };
      }
      return { okHttp: res.ok, data: data || {} };
    });
  };

  const isSecure = window.isSecureContext || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
  const isPhone = () => window.matchMedia('(max-width: 900px), (pointer: coarse)').matches;
  const isiOS = /iPhone|iPad|iPod/i.test(navigator.userAgent || '')
    || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
  const canShareScreen = !!(navigator.mediaDevices && typeof navigator.mediaDevices.getDisplayMedia === 'function');
  const AUDIO = { echoCancellation: true, noiseSuppression: true, autoGainControl: true, channelCount: 1 };
  const POLL_MS = 280;
  const FRAME_MS = 120;
  const AUDIO_RATE = 16000;

  const packSdp = (desc) => {
    if (!desc) return null;
    return { type: desc.type, sdp: desc.sdp };
  };
  const packIce = (cand) => {
    if (!cand) return null;
    return {
      candidate: cand.candidate || '',
      sdpMid: cand.sdpMid,
      sdpMLineIndex: cand.sdpMLineIndex,
      usernameFragment: cand.usernameFragment,
    };
  };

  const chatItem = (row) => {
    const wrap = document.createElement('div');
    wrap.className = 'plive-chat-item';
    wrap.setAttribute('data-id', String(row.id));
    const who = document.createElement('strong');
    who.textContent = row.name || 'Viewer';
    const text = document.createElement('span');
    text.textContent = row.body || '';
    wrap.appendChild(who);
    wrap.appendChild(text);
    return wrap;
  };

  const bindChat = (root) => {
    const box = root.querySelector('[data-plive-chat]');
    const list = root.querySelector('[data-plive-chat-list]');
    const form = root.querySelector('[data-plive-chat-form]');
    const nameEl = root.querySelector('[data-plive-chat-name]');
    const bodyEl = root.querySelector('[data-plive-chat-body]');
    const seen = {};
    let lastId = 0;
    if (nameEl) {
      try {
        const saved = sessionStorage.getItem('plive-chat-name');
        if (saved) nameEl.value = saved;
      } catch (e) {}
    }
    const show = (on) => {
      if (box) box.hidden = !on;
      if (!on && list) {
        list.querySelectorAll('.plive-chat-item').forEach((el) => el.remove());
        Object.keys(seen).forEach((k) => { delete seen[k]; });
        lastId = 0;
        if (!list.querySelector('.plive-chat-empty')) {
          const empty = document.createElement('p');
          empty.className = 'plive-chat-empty';
          empty.textContent = box && box.getAttribute('data-empty')
            ? box.getAttribute('data-empty')
            : 'Comments appear here while the center is live.';
          list.appendChild(empty);
        }
      }
    };
    const add = (rows) => {
      if (!list) return;
      (rows || []).forEach((row) => {
        const id = Number(row.id || 0);
        if (!id || seen[id]) return;
        seen[id] = true;
        if (id > lastId) lastId = id;
        const empty = list.querySelector('.plive-chat-empty');
        if (empty) empty.remove();
        list.appendChild(chatItem(row));
      });
      if (list.querySelector('.plive-chat-item')) {
        list.scrollTop = list.scrollHeight;
      }
    };
    return {
      show,
      add,
      form,
      nameEl,
      bodyEl,
      lastId: () => lastId,
    };
  };

  const bitrateFor = (count, screen) => {
    if (screen) return 1100000;
    if (count > 20) return 250000;
    if (count > 6) return 400000;
    return 550000;
  };

  const hintTrack = (track, hint) => {
    if (!track) return;
    try { track.contentHint = hint; } catch (e) {}
  };

  const applyBitrate = (pc, count, screen) => {
    const max = bitrateFor(count, screen);
    const scale = screen ? 1 : (count > 12 ? 1.5 : 1);
    pc.getSenders().forEach((sender) => {
      if (!sender.track || sender.track.kind !== 'video') return;
      const params = sender.getParameters();
      if (!params.encodings || !params.encodings.length) params.encodings = [{}];
      params.encodings[0].maxBitrate = max;
      params.encodings[0].maxFramerate = screen ? 24 : 24;
      if (scale > 1) params.encodings[0].scaleResolutionDownBy = scale;
      else delete params.encodings[0].scaleResolutionDownBy;
      try { params.degradationPreference = 'maintain-framerate'; } catch (e) {}
      sender.setParameters(params).catch(() => {});
    });
  };

  const shareHint = () => {
    const ua = navigator.userAgent || '';
    if (/iPhone|iPad|iPod/i.test(ua)) return 'On iPhone/iPad use Safari 17 or newer for screen share.';
    if (/Android/i.test(ua)) return 'On Android use Chrome and pick a tab or screen.';
    return 'Choose a window, a tab, or the whole screen.';
  };

  const landscapeConstraints = () => {
    const phone = isPhone();
    return {
      width: { ideal: phone ? 1280 : 1920 },
      height: { ideal: phone ? 720 : 1080 },
      aspectRatio: { ideal: 16 / 9 },
      frameRate: { ideal: 24, max: 30 },
    };
  };

  const pickCameraId = async (facing, excludeId) => {
    try {
      const devices = await navigator.mediaDevices.enumerateDevices();
      const cams = devices.filter((d) => d.kind === 'videoinput' && d.deviceId);
      if (!cams.length) return '';
      const wantBack = facing === 'environment';
      const labeled = cams.find((d) => {
        const label = (d.label || '').toLowerCase();
        return wantBack
          ? /back|rear|environment|world|facing back/.test(label)
          : /front|user|face|facing front/.test(label);
      });
      if (labeled && labeled.deviceId !== excludeId) return labeled.deviceId;
      const others = cams.filter((d) => d.deviceId !== excludeId);
      const pool = others.length ? others : cams;
      if (pool.length > 1) {
        return wantBack ? pool[pool.length - 1].deviceId : pool[0].deviceId;
      }
      return pool[0].deviceId || '';
    } catch (e) {}
    return '';
  };

  const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

  const fsElement = () => document.fullscreenElement || document.webkitFullscreenElement || null;

  const unlockOrientation = (box) => {
    if (box) box.classList.remove('is-fs-rotate');
    try {
      if (screen.orientation && screen.orientation.unlock) screen.orientation.unlock();
    } catch (e) {}
  };

  const lockLandscape = async (box) => {
    try {
      if (screen.orientation && screen.orientation.lock) {
        await screen.orientation.lock('landscape');
        if (box) box.classList.remove('is-fs-rotate');
        return;
      }
    } catch (e) {}
    if (isPhone() && window.matchMedia('(orientation: portrait)').matches && box) {
      box.classList.add('is-fs-rotate');
    }
  };

  const tryFullscreen = async (el) => {
    if (!el) return false;
    try {
      if (el.requestFullscreen) {
        await el.requestFullscreen();
        return true;
      }
      if (el.webkitRequestFullscreen) {
        el.webkitRequestFullscreen();
        return true;
      }
    } catch (e) {}
    return false;
  };

  const enterLiveFullscreen = async (box, video) => {
    if (isiOS && video && typeof video.webkitEnterFullscreen === 'function') {
      try {
        video.webkitEnterFullscreen();
        return;
      } catch (e) {}
    }
    if (await tryFullscreen(video)) {
      await lockLandscape(box);
      return;
    }
    if (await tryFullscreen(box)) {
      await lockLandscape(box);
      return;
    }
    if (video && typeof video.webkitEnterFullscreen === 'function') {
      try {
        video.webkitEnterFullscreen();
        return;
      } catch (e) {}
    }
    if (box) box.classList.add('is-fs-rotate');
  };

  const exitLiveFullscreen = async (box, video) => {
    try {
      if (fsElement() && document.exitFullscreen) await document.exitFullscreen();
      else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
    } catch (e) {}
    try {
      if (video && typeof video.webkitExitFullscreen === 'function') video.webkitExitFullscreen();
    } catch (e) {}
    unlockOrientation(box);
  };

  const grabMicStream = async () => {
    try {
      return await navigator.mediaDevices.getUserMedia({ audio: AUDIO, video: false });
    } catch (e1) {
      try {
        return await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
      } catch (e2) {
        return null;
      }
    }
  };

  const mergeMic = async (stream) => {
    if (!stream) return stream;
    if (stream.getAudioTracks().length) {
      stream.getAudioTracks().forEach((t) => {
        t.enabled = true;
        hintTrack(t, 'speech');
      });
      return stream;
    }
    const mic = await grabMicStream();
    if (mic) {
      mic.getAudioTracks().forEach((track) => {
        track.enabled = true;
        hintTrack(track, 'speech');
        stream.addTrack(track);
      });
    }
    return stream;
  };

  const downsample = (input, fromRate, toRate) => {
    if (!input || !input.length) return new Float32Array(0);
    if (fromRate === toRate) return input;
    const ratio = fromRate / toRate;
    const outLen = Math.max(1, Math.floor(input.length / ratio));
    const out = new Float32Array(outLen);
    for (let i = 0; i < outLen; i += 1) {
      const src = i * ratio;
      const i0 = Math.floor(src);
      const i1 = Math.min(input.length - 1, i0 + 1);
      const f = src - i0;
      out[i] = (input[i0] || 0) * (1 - f) + (input[i1] || 0) * f;
    }
    return out;
  };

  const floatToInt16 = (input) => {
    const out = new Int16Array(input.length);
    for (let i = 0; i < input.length; i += 1) {
      const s = Math.max(-1, Math.min(1, input[i] || 0));
      out[i] = s < 0 ? s * 0x8000 : s * 0x7fff;
    }
    return out;
  };

  const bootHost = (root) => {
    const csrf = root.getAttribute('data-csrf') || '';
    const preview = root.querySelector('[data-plive-preview]');
    const stage = root.querySelector('[data-plive-stage]') || root.querySelector('.plive-studio-stage');
    const note = root.querySelector('[data-plive-note]');
    const viewersEl = root.querySelector('[data-plive-viewers]');
    const idle = root.querySelector('[data-plive-idle]');
    const studio = root.querySelector('[data-plive-studio]');
    const busy = root.querySelector('[data-plive-busy]');
    const muteBtn = root.querySelector('[data-plive-mute]');
    const camBtn = root.querySelector('[data-plive-cam]');
    const modeBtn = root.querySelector('[data-plive-mode]');
    const fsBtn = root.querySelector('[data-plive-host-fs]');
    const titleInput = root.querySelector('[data-plive-title]');
    const micPill = root.querySelector('[data-plive-mic]');
    const chat = bindChat(root);

    const state = {
      sessionId: 0,
      peerId: '',
      token: '',
      stream: null,
      source: 'camera',
      facing: 'user',
      camId: '',
      muted: false,
      pcs: {},
      pending: {},
      polling: false,
      timer: null,
      starting: false,
      audioCtx: null,
      audioSrc: null,
      audioProc: null,
      audioGain: null,
      audioBuf: [],
      pushing: false,
      pushTimer: null,
      canvas: null,
      canvasCtx: null,
    };

    const setNote = (text) => {
      if (note) note.textContent = text || '';
    };

    const showStudio = () => {
      if (idle) idle.hidden = true;
      if (busy) busy.hidden = true;
      if (studio) studio.hidden = false;
      chat.show(true);
      document.body.classList.add('plive-hosting');
    };

    let wakeLock = null;
    const keepAwake = async () => {
      try {
        if (navigator.wakeLock) wakeLock = await navigator.wakeLock.request('screen');
      } catch (e) {}
    };
    const releaseAwake = async () => {
      if (!wakeLock) return;
      try { await wakeLock.release(); } catch (e) {}
      wakeLock = null;
    };

    const attachPreview = () => {
      if (preview && state.stream) {
        preview.srcObject = state.stream;
        preview.muted = true;
        preview.setAttribute('playsinline', '');
        preview.setAttribute('webkit-playsinline', '');
        preview.play().catch(() => {});
      }
    };

    const stopTracks = (stream) => {
      if (!stream) return;
      stream.getTracks().forEach((track) => {
        try { track.stop(); } catch (e) {}
      });
    };

    const stopStream = () => {
      stopTracks(state.stream);
      state.stream = null;
    };

    const applyMicState = () => {
      if (state.stream) {
        state.stream.getAudioTracks().forEach((t) => {
          t.enabled = !state.muted;
        });
      }
      if (muteBtn) muteBtn.textContent = state.muted ? 'Unmute mic' : 'Mute mic';
      if (micPill) micPill.hidden = !state.muted;
    };

    const sendSignal = (to, kind, payload) =>
      post(root.getAttribute('data-signal-url'), {
        session_id: state.sessionId,
        peer_id: state.peerId,
        token: state.token,
        to,
        kind,
        payload,
      }, csrf);

    const attachTracks = (pc) => {
      if (!state.stream) return;
      const senders = pc.getSenders();
      state.stream.getTracks().forEach((track) => {
        const existing = senders.find((s) => s.track && s.track.kind === track.kind);
        if (existing) {
          if (existing.track !== track) existing.replaceTrack(track).catch(() => {});
        } else {
          pc.addTrack(track, state.stream);
        }
      });
    };

    const replaceTracks = async (needOffer) => {
      applyMicState();
      const ids = Object.keys(state.pcs);
      for (const id of ids) {
        const pc = state.pcs[id];
        const before = pc.getSenders().length;
        attachTracks(pc);
        applyBitrate(pc, ids.length, state.source === 'screen');
        if (needOffer || pc.getSenders().length !== before) {
          if (pc.signalingState !== 'stable') continue;
          try {
            const offer = await pc.createOffer();
            await pc.setLocalDescription(offer);
            await sendSignal(id, 'offer', packSdp(pc.localDescription));
          } catch (e) {}
        }
      }
    };

    const onHostAudio = (ev) => {
      if (state.muted || !state.sessionId) return;
      const input = ev.inputBuffer.getChannelData(0);
      const fromRate = ev.inputBuffer.sampleRate || (state.audioCtx && state.audioCtx.sampleRate) || 48000;
      const down = downsample(input, fromRate, AUDIO_RATE);
      if (!down.length) return;
      state.audioBuf.push(floatToInt16(down));
      let total = 0;
      for (let i = 0; i < state.audioBuf.length; i += 1) total += state.audioBuf[i].length;
      if (total > AUDIO_RATE * 0.8) state.audioBuf.shift();
    };

    const hookAudio = (stream) => {
      if (!state.audioCtx || !stream) return;
      try {
        if (state.audioSrc) state.audioSrc.disconnect();
      } catch (e) {}
      state.audioSrc = null;
      const tracks = stream.getAudioTracks();
      if (!tracks.length) return;
      try {
        const micStream = new MediaStream(tracks);
        state.audioSrc = state.audioCtx.createMediaStreamSource(micStream);
        if (!state.audioProc) {
          state.audioProc = state.audioCtx.createScriptProcessor(4096, 1, 1);
          state.audioGain = state.audioCtx.createGain();
          state.audioGain.gain.value = 0;
          state.audioProc.onaudioprocess = onHostAudio;
          state.audioProc.connect(state.audioGain);
          state.audioGain.connect(state.audioCtx.destination);
        }
        state.audioSrc.connect(state.audioProc);
      } catch (e) {}
    };

    const ensureAudioCtx = () => {
      if (state.audioCtx) {
        try { state.audioCtx.resume(); } catch (e) {}
        return;
      }
      const AC = window.AudioContext || window.webkitAudioContext;
      if (!AC) return;
      try {
        state.audioCtx = new AC();
      } catch (e) {}
    };

    const drainAudio = () => {
      if (!state.audioBuf.length) return null;
      let total = 0;
      for (let i = 0; i < state.audioBuf.length; i += 1) total += state.audioBuf[i].length;
      const merged = new Int16Array(total);
      let off = 0;
      state.audioBuf.forEach((chunk) => {
        merged.set(chunk, off);
        off += chunk.length;
      });
      state.audioBuf = [];
      return new Blob([merged.buffer], { type: 'application/octet-stream' });
    };

    const captureFrame = () => new Promise((resolve) => {
      if (!preview || preview.readyState < 2 || !preview.videoWidth) {
        resolve(null);
        return;
      }
      if (!state.canvas) {
        state.canvas = document.createElement('canvas');
        state.canvas.width = 960;
        state.canvas.height = 540;
        state.canvasCtx = state.canvas.getContext('2d', { alpha: false });
      }
      const ctx = state.canvasCtx;
      const vw = preview.videoWidth;
      const vh = preview.videoHeight;
      const cw = state.canvas.width;
      const ch = state.canvas.height;
      ctx.fillStyle = '#071510';
      ctx.fillRect(0, 0, cw, ch);
      const scale = Math.min(cw / vw, ch / vh);
      const w = vw * scale;
      const h = vh * scale;
      try {
        ctx.drawImage(preview, (cw - w) / 2, (ch - h) / 2, w, h);
      } catch (e) {
        resolve(null);
        return;
      }
      state.canvas.toBlob((blob) => resolve(blob || null), 'image/jpeg', 0.58);
    });

    const pushMedia = async () => {
      if (state.pushing || !state.sessionId) return;
      const pushUrl = root.getAttribute('data-push-url');
      if (!pushUrl) return;
      state.pushing = true;
      try {
        const frame = await captureFrame();
        const audio = state.muted ? null : drainAudio();
        if (!frame && !audio) return;
        const fd = new FormData();
        fd.append('session_id', String(state.sessionId));
        fd.append('peer_id', state.peerId);
        fd.append('token', state.token);
        if (csrf) fd.append('_csrf', csrf);
        if (frame) fd.append('frame', frame, 'frame.jpg');
        if (audio) fd.append('audio', audio, 'audio.bin');
        const headers = { Accept: 'application/json' };
        if (csrf) headers['X-CSRF-TOKEN'] = csrf;
        await fetch(pushUrl, { method: 'POST', body: fd, credentials: 'same-origin', headers });
      } catch (e) {
      } finally {
        state.pushing = false;
      }
    };

    const startPush = () => {
      if (state.pushTimer) clearInterval(state.pushTimer);
      state.pushTimer = setInterval(pushMedia, FRAME_MS);
      pushMedia();
    };

    const stopPush = () => {
      if (state.pushTimer) clearInterval(state.pushTimer);
      state.pushTimer = null;
    };

    const swapStream = async (next, source) => {
      const old = state.stream;
      state.stream = next;
      state.source = source;
      applyMicState();
      attachPreview();
      hookAudio(next);
      await replaceTracks(false);
      if (old && old !== next) {
        old.getTracks().forEach((track) => {
          if (!next.getTracks().includes(track)) {
            try { track.stop(); } catch (e) {}
          }
        });
      }
    };

    const openCamera = async (facing, switching, excludeId) => {
      const size = landscapeConstraints();
      const deviceId = await pickCameraId(facing, excludeId || '');
      const videos = [];
      if (deviceId) {
        videos.push(Object.assign({ deviceId: { exact: deviceId } }, size));
        videos.push({ deviceId: { exact: deviceId }, facingMode: { ideal: facing } });
        videos.push({ deviceId: { exact: deviceId } });
      }
      videos.push(Object.assign({ facingMode: { exact: facing } }, size));
      videos.push({ facingMode: { exact: facing } });
      videos.push(Object.assign({ facingMode: { ideal: facing } }, size));
      if (!switching) {
        videos.push(size);
        videos.push(true);
      }
      let next = null;
      let lastErr = null;
      for (const video of videos) {
        try {
          next = await navigator.mediaDevices.getUserMedia({ audio: AUDIO, video });
          lastErr = null;
          break;
        } catch (err) {
          lastErr = err;
        }
      }
      if (!next) {
        for (const video of videos) {
          try {
            next = await navigator.mediaDevices.getUserMedia({ audio: false, video });
            lastErr = null;
            break;
          } catch (err) {
            lastErr = err;
          }
        }
      }
      if (!next) throw lastErr || new Error('Allow the camera and microphone and try again.');
      next = await mergeMic(next);
      const vt = next.getVideoTracks()[0];
      hintTrack(vt, 'motion');
      try { vt.enabled = true; } catch (e) {}
      const settings = vt && vt.getSettings ? vt.getSettings() : {};
      if (settings.deviceId) state.camId = settings.deviceId;
      if (settings.facingMode) state.facing = settings.facingMode;
      else state.facing = facing;
      return next;
    };

    const grabCamera = async (switching) => {
      const excludeId = switching ? (state.camId || '') : '';
      const prev = state.stream;
      if (switching && prev) {
        stopTracks(prev);
        state.stream = null;
        await wait(isiOS ? 180 : 80);
      }
      const raw = await openCamera(state.facing, switching, excludeId);
      await swapStream(raw, 'camera');
    };

    const grabScreen = async () => {
      if (!canShareScreen) throw new Error(shareHint());
      const display = await navigator.mediaDevices.getDisplayMedia({
        video: { frameRate: 24, width: { max: 1920 }, height: { max: 1080 } },
        audio: true,
      });
      await mergeMic(display);
      hintTrack(display.getVideoTracks()[0], 'detail');
      display.getVideoTracks()[0]?.addEventListener('ended', () => {
        if (state.source !== 'screen') return;
        grabCamera(false).then(() => {
          if (modeBtn) modeBtn.textContent = 'Share screen';
          setNote('Screen share ended. Camera is on. Live is still running.');
          post(root.getAttribute('data-media-url'), {
            session_id: state.sessionId,
            peer_id: state.peerId,
            token: state.token,
            source: 'camera',
          }, csrf);
        }).catch(() => setNote('Screen share ended. Switch back to camera — live is still on.'));
      });
      await swapStream(display, 'screen');
    };

    const ensurePc = (viewerId) => {
      if (state.pcs[viewerId]) return state.pcs[viewerId];
      const pc = new RTCPeerConnection(iceServers);
      state.pcs[viewerId] = pc;
      attachTracks(pc);
      applyBitrate(pc, Object.keys(state.pcs).length, state.source === 'screen');
      pc.onicecandidate = (ev) => {
        if (ev.candidate) sendSignal(viewerId, 'ice', packIce(ev.candidate));
      };
      pc.onconnectionstatechange = () => {
        if (pc.connectionState === 'failed' || pc.connectionState === 'disconnected') {
          setTimeout(() => {
            if (state.pcs[viewerId] !== pc) return;
            if (pc.connectionState === 'connected') return;
            try { pc.close(); } catch (e) {}
            delete state.pcs[viewerId];
          }, 1600);
        }
      };
      return pc;
    };

    const offerTo = async (viewerId) => {
      if (state.pending[viewerId]) return;
      const existing = state.pcs[viewerId];
      if (existing && existing.signalingState !== 'stable' && existing.signalingState !== 'have-local-offer') {
        return;
      }
      state.pending[viewerId] = true;
      try {
        const pc = ensurePc(viewerId);
        if (pc.signalingState === 'have-local-offer') return;
        attachTracks(pc);
        const offer = await pc.createOffer();
        await pc.setLocalDescription(offer);
        await sendSignal(viewerId, 'offer', packSdp(pc.localDescription));
      } catch (e) {
        setNote(e.message || 'Could not reach a viewer.');
        try { state.pcs[viewerId].close(); } catch (err) {}
        delete state.pcs[viewerId];
      } finally {
        state.pending[viewerId] = false;
      }
    };

    const handleSignals = async (signals) => {
      for (const msg of signals || []) {
        if (msg.kind === 'need') {
          const existing = state.pcs[msg.from];
          const st = existing ? existing.connectionState : '';
          if (existing && st !== 'failed' && st !== 'disconnected' && st !== 'closed') {
            continue;
          }
          if (existing) {
            try { existing.close(); } catch (e) {}
            delete state.pcs[msg.from];
          }
          offerTo(msg.from);
          continue;
        }
        const pc = state.pcs[msg.from] || ensurePc(msg.from);
        if (msg.kind === 'answer' && msg.payload) {
          try {
            if (pc.signalingState === 'have-local-offer') {
              await pc.setRemoteDescription(msg.payload);
            }
          } catch (e) {}
        }
        if (msg.kind === 'ice' && msg.payload) {
          try { await pc.addIceCandidate(msg.payload); } catch (e) {}
        }
        if (msg.kind === 'bye') {
          try { pc.close(); } catch (e) {}
          delete state.pcs[msg.from];
        }
      }
    };

    const syncViewers = (ids) => {
      const want = {};
      (ids || []).forEach((id) => { want[id] = true; });
      Object.keys(state.pcs).forEach((id) => {
        if (!want[id]) {
          try { state.pcs[id].close(); } catch (e) {}
          delete state.pcs[id];
        }
      });
      (ids || []).forEach((id) => {
        if (!state.pcs[id]) offerTo(id);
      });
      const n = (ids || []).length;
      if (viewersEl) viewersEl.textContent = n + ' watching';
      Object.keys(state.pcs).forEach((id) => applyBitrate(state.pcs[id], n, state.source === 'screen'));
    };

    const poll = async () => {
      if (state.polling || !state.sessionId) return;
      state.polling = true;
      try {
        const res = await post(root.getAttribute('data-state-url'), {
          session_id: state.sessionId,
          peer_id: state.peerId,
          token: state.token,
          after: chat.lastId(),
        }, csrf);
        if (res.data && res.data.ended) {
          teardown(false);
          return;
        }
        if (!res.okHttp) return;
        await handleSignals(res.data.signals || []);
        syncViewers(res.data.viewers || []);
        chat.add(res.data.comments || []);
      } catch (e) {
      } finally {
        state.polling = false;
      }
    };

    const teardown = (notify) => {
      if (state.timer) clearInterval(state.timer);
      state.timer = null;
      stopPush();
      Object.keys(state.pcs).forEach((id) => {
        try { state.pcs[id].close(); } catch (e) {}
      });
      state.pcs = {};
      try {
        if (state.audioSrc) state.audioSrc.disconnect();
        if (state.audioProc) state.audioProc.disconnect();
        if (state.audioGain) state.audioGain.disconnect();
        if (state.audioCtx) state.audioCtx.close();
      } catch (e) {}
      state.audioSrc = null;
      state.audioProc = null;
      state.audioGain = null;
      state.audioCtx = null;
      state.audioBuf = [];
      stopStream();
      releaseAwake();
      chat.show(false);
      document.body.classList.remove('plive-hosting');
      if (preview) preview.srcObject = null;
      if (notify && state.sessionId) {
        post(root.getAttribute('data-end-url'), {
          session_id: state.sessionId,
          peer_id: state.peerId,
          token: state.token,
        }, csrf);
      }
      state.sessionId = 0;
      state.muted = false;
      state.camId = '';
      state.facing = 'user';
      if (studio) studio.hidden = true;
      if (idle) idle.hidden = false;
      if (busy) busy.hidden = true;
    };

    const goLive = async () => {
      if (state.starting) return;
      if (!isSecure) {
        window.alert('Open Admin on HTTPS (or localhost) so the browser allows camera and screen share.');
        return;
      }
      state.starting = true;
      ensureAudioCtx();
      root.querySelectorAll('[data-plive-start]').forEach((btn) => { btn.disabled = true; });
      setNote('Allow the camera and microphone when the browser asks.');
      try {
        await grabCamera(false);
      } catch (err) {
        setNote((err && err.message) || 'Permission denied. Allow the camera and microphone and try again.');
        state.starting = false;
        root.querySelectorAll('[data-plive-start]').forEach((btn) => { btn.disabled = false; });
        return;
      }
      const res = await post(root.getAttribute('data-start-url'), {
        title: titleInput ? titleInput.value : '',
        source: 'camera',
      }, csrf);
      if (!res.data || !res.data.ok) {
        setNote((res.data && res.data.error) || 'Could not start live.');
        stopStream();
        state.starting = false;
        root.querySelectorAll('[data-plive-start]').forEach((btn) => { btn.disabled = false; });
        return;
      }
      state.sessionId = res.data.session_id;
      state.peerId = res.data.peer_id;
      state.token = res.data.token;
      showStudio();
      keepAwake();
      applyMicState();
      hookAudio(state.stream);
      startPush();
      const micOn = !!(state.stream && state.stream.getAudioTracks().length);
      setNote(micOn
        ? 'You are live. Keep this tab open — picture and mic go to the public Live page.'
        : 'You are live but the mic did not start. Allow the microphone and press Go live again.');
      if (modeBtn) {
        modeBtn.textContent = 'Share screen';
        modeBtn.hidden = !canShareScreen;
      }
      syncViewers(res.data.viewers || []);
      chat.add(res.data.comments || []);
      if (state.timer) clearInterval(state.timer);
      state.timer = setInterval(poll, POLL_MS);
      poll();
      state.starting = false;
    };

    root.querySelectorAll('[data-plive-start]').forEach((btn) => {
      btn.addEventListener('click', () => goLive());
    });
    root.querySelectorAll('[data-plive-end]').forEach((btn) => {
      btn.addEventListener('click', () => {
        if (window.confirm('End the public live for everyone?')) teardown(true);
      });
    });
    if (muteBtn) {
      muteBtn.addEventListener('click', () => {
        state.muted = !state.muted;
        applyMicState();
        setNote(state.muted ? 'Mic is off. Picture is still live.' : 'Mic is on.');
      });
    }
    if (camBtn) {
      camBtn.addEventListener('click', async () => {
        if (state.source !== 'camera') {
          setNote('Switch back to camera first, then change front/back.');
          return;
        }
        camBtn.disabled = true;
        const prevFacing = state.facing;
        const nextFacing = prevFacing === 'user' ? 'environment' : 'user';
        state.facing = nextFacing;
        try {
          await grabCamera(true);
          setNote(state.facing === 'environment' ? 'Back camera is on.' : 'Front camera is on.');
        } catch (e) {
          state.facing = prevFacing;
          try {
            await grabCamera(false);
          } catch (err) {}
          setNote('Could not switch camera. Allow camera permission and try again.');
        }
        camBtn.disabled = false;
      });
    }
    if (modeBtn) {
      if (!canShareScreen) modeBtn.hidden = true;
      modeBtn.addEventListener('click', async () => {
        modeBtn.disabled = true;
        try {
          if (state.source === 'screen') await grabCamera(false);
          else await grabScreen();
          modeBtn.textContent = state.source === 'screen' ? 'Use camera' : 'Share screen';
          setNote(state.source === 'screen' ? 'Screen is live. The broadcast did not end.' : 'Camera is live.');
          post(root.getAttribute('data-media-url'), {
            session_id: state.sessionId,
            peer_id: state.peerId,
            token: state.token,
            source: state.source,
          }, csrf);
        } catch (err) {
          setNote((err && err.message) || shareHint());
        }
        modeBtn.disabled = false;
      });
    }
    if (fsBtn) {
      fsBtn.addEventListener('click', () => {
        if (fsElement()) {
          exitLiveFullscreen(stage, preview);
          return;
        }
        enterLiveFullscreen(stage, preview);
      });
    }
    document.addEventListener('fullscreenchange', () => {
      if (!fsElement()) unlockOrientation(stage);
    });
    document.addEventListener('visibilitychange', () => {
      if (document.visibilityState === 'visible' && state.sessionId) {
        keepAwake();
        if (state.audioCtx) state.audioCtx.resume().catch(() => {});
        poll();
        pushMedia();
      }
    });
  };

  const bootWatch = (root) => {
    const video = root.querySelector('[data-plive-video]');
    const fallback = root.querySelector('[data-plive-fallback]');
    const player = root.querySelector('[data-plive-player]');
    const offline = root.querySelector('[data-plive-offline]');
    const connecting = root.querySelector('[data-plive-connecting]');
    const chrome = root.querySelector('[data-plive-chrome]');
    const caption = root.querySelector('[data-plive-caption]');
    const status = root.querySelector('[data-plive-status]');
    const viewersEl = root.querySelector('[data-plive-viewers]');
    const playBtn = root.querySelector('[data-plive-play]');
    const clock = root.querySelector('[data-plive-clock]');
    const fsBtn = root.querySelector('[data-plive-fs]');
    const volEl = root.querySelector('[data-plive-vol]');
    const pipBtn = root.querySelector('[data-plive-pip]');
    const unmuteBtn = root.querySelector('[data-plive-unmute]');
    const chat = bindChat(root);

    const state = {
      sessionId: 0,
      peerId: '',
      token: '',
      hostId: '',
      pc: null,
      pendingIce: [],
      live: false,
      started: 0,
      timer: null,
      statusTimer: null,
      joining: false,
      expectLive: false,
      mediaTimer: null,
      pulling: false,
      lastAudioSeq: 0,
      blobUrl: '',
      audioCtx: null,
      audioAt: 0,
      wantSound: false,
      hasFrame: false,
    };

    if (video) {
      video.setAttribute('playsinline', '');
      video.setAttribute('webkit-playsinline', '');
      video.muted = true;
      video.volume = 1;
    }
    if (pipBtn) {
      pipBtn.hidden = !(document.pictureInPictureEnabled || (video && video.webkitSetPresentationMode));
    }

    const rtcHasVideo = () => !!(video && video.videoWidth > 16 && video.readyState >= 2);

    const hasPicture = () => {
      if (state.hasFrame) return true;
      if (rtcHasVideo()) return true;
      if (!video) return false;
      const src = video.srcObject;
      if (!src || typeof src.getVideoTracks !== 'function') return false;
      return src.getVideoTracks().some((t) => t.readyState === 'live');
    };

    const showUnmute = (on) => {
      if (unmuteBtn) unmuteBtn.hidden = !on;
    };

    const volume = () => (volEl ? Number(volEl.value || 1) : 1);

    const ensureWatchAudio = () => {
      if (state.audioCtx) {
        if (state.audioCtx.state === 'suspended') state.audioCtx.resume().catch(() => {});
        return state.audioCtx;
      }
      const AC = window.AudioContext || window.webkitAudioContext;
      if (!AC) return null;
      try {
        state.audioCtx = new AC({ sampleRate: AUDIO_RATE });
      } catch (e) {
        try { state.audioCtx = new AC(); } catch (err) { return null; }
      }
      state.audioAt = 0;
      return state.audioCtx;
    };

    const playPcm = (int16, sampleRate) => {
      if (!state.wantSound || !int16 || !int16.length) return;
      const ctx = ensureWatchAudio();
      if (!ctx || ctx.state === 'suspended') {
        showUnmute(true);
        return;
      }
      const f32 = new Float32Array(int16.length);
      const gain = volume();
      for (let i = 0; i < int16.length; i += 1) f32[i] = (int16[i] / 32768) * gain;
      const rate = sampleRate || AUDIO_RATE;
      const buf = ctx.createBuffer(1, f32.length, rate);
            try {
              buf.copyToChannel(f32, 0);
            } catch (e) {
              buf.getChannelData(0).set(f32);
            }
      const src = ctx.createBufferSource();
      src.buffer = buf;
      src.connect(ctx.destination);
      const now = ctx.currentTime;
      if (state.audioAt < now + 0.04) state.audioAt = now + 0.04;
      if (state.audioAt - now > 0.7) state.audioAt = now + 0.04;
      src.start(state.audioAt);
      state.audioAt += buf.duration;
    };

    const playWithSound = async () => {
      state.wantSound = true;
      ensureWatchAudio();
      if (!video) {
        showUnmute(false);
        return;
      }
      video.playsInline = true;
      video.setAttribute('playsinline', '');
      video.setAttribute('webkit-playsinline', '');
      video.volume = volume();
      try {
        video.muted = false;
        await video.play();
        showUnmute(false);
      } catch (e) {
        try {
          video.muted = true;
          await video.play();
        } catch (err) {}
        showUnmute(true);
      }
      if (hasPicture()) setLiveUi();
    };

    const setOffline = () => {
      state.live = false;
      state.expectLive = false;
      state.hasFrame = false;
      chat.show(false);
      showUnmute(false);
      if (offline) offline.hidden = false;
      if (connecting) connecting.hidden = true;
      if (chrome) chrome.hidden = true;
      if (fallback) {
        fallback.hidden = true;
        fallback.removeAttribute('src');
      }
      if (state.blobUrl) {
        try { URL.revokeObjectURL(state.blobUrl); } catch (e) {}
        state.blobUrl = '';
      }
      if (video) {
        video.srcObject = null;
        video.removeAttribute('src');
      }
      if (status) status.textContent = '';
      if (state.pc) {
        try { state.pc.close(); } catch (e) {}
        state.pc = null;
      }
      if (state.mediaTimer) {
        clearInterval(state.mediaTimer);
        state.mediaTimer = null;
      }
      if (player) player.classList.remove('is-fs-rotate');
    };

    const setConnecting = () => {
      if (hasPicture()) return;
      state.expectLive = true;
      if (offline) offline.hidden = true;
      if (connecting) connecting.hidden = false;
      if (chrome) chrome.hidden = true;
    };

    const setLiveUi = () => {
      if (offline) offline.hidden = true;
      if (connecting) connecting.hidden = true;
      if (chrome) chrome.hidden = false;
      chat.show(true);
      if (rtcHasVideo()) {
        if (fallback) fallback.hidden = true;
      } else if (state.hasFrame && fallback) {
        fallback.hidden = false;
      }
      if (!state.wantSound) showUnmute(true);
    };

    const pullMedia = async () => {
      if (state.pulling || !state.expectLive) return;
      const frameUrl = root.getAttribute('data-frame-url');
      const audioUrl = root.getAttribute('data-audio-url');
      if (!frameUrl) return;
      state.pulling = true;
      try {
        const res = await fetch(frameUrl + '?t=' + Date.now(), {
          cache: 'no-store',
          credentials: 'same-origin',
        });
        if (res.status === 404) {
          setOffline();
          state.sessionId = 0;
          return;
        }
        if (res.ok && res.status !== 204) {
          const blob = await res.blob();
          if (blob && blob.size > 32) {
            const url = URL.createObjectURL(blob);
            if (fallback) {
              fallback.src = url;
              fallback.hidden = rtcHasVideo();
            }
            if (state.blobUrl) URL.revokeObjectURL(state.blobUrl);
            state.blobUrl = url;
            state.hasFrame = true;
            setLiveUi();
          }
        }
        const rtcAudio = !!(video && !video.muted && video.srcObject
          && typeof video.srcObject.getAudioTracks === 'function'
          && video.srcObject.getAudioTracks().some((t) => t.readyState === 'live'));
        if (audioUrl && state.wantSound && !rtcAudio) {
          const ares = await fetch(audioUrl + '?t=' + Date.now(), {
            cache: 'no-store',
            credentials: 'same-origin',
          });
          const seq = Number(ares.headers.get('X-Live-Seq') || 0);
          if (ares.ok && ares.status !== 204 && seq && seq !== state.lastAudioSeq) {
            state.lastAudioSeq = seq;
            const buf = await ares.arrayBuffer();
            if (buf.byteLength >= 16) {
              playPcm(new Int16Array(buf), Number(ares.headers.get('X-Live-Rate') || AUDIO_RATE));
            }
          }
        }
      } catch (e) {
      } finally {
        state.pulling = false;
      }
    };

    const startMediaPull = () => {
      if (state.mediaTimer) return;
      pullMedia();
      state.mediaTimer = setInterval(pullMedia, FRAME_MS);
    };

    if (video) {
      video.addEventListener('loadedmetadata', () => {
        if (state.wantSound) playWithSound();
        else video.play().catch(() => {});
        if (video.videoWidth) setLiveUi();
      });
      video.addEventListener('playing', () => {
        if (video.videoWidth || video.readyState >= 2) setLiveUi();
      });
    }
    if (unmuteBtn) {
      unmuteBtn.addEventListener('click', (ev) => {
        ev.preventDefault();
        ev.stopPropagation();
        playWithSound();
      });
    }

    const fmt = (sec) => {
      const s = Math.max(0, Math.floor(sec));
      const m = Math.floor(s / 60);
      const r = s % 60;
      return String(m).padStart(2, '0') + ':' + String(r).padStart(2, '0');
    };

    const sendSignal = (kind, payload) =>
      post(root.getAttribute('data-signal-url'), {
        session_id: state.sessionId,
        peer_id: state.peerId,
        token: state.token,
        to: state.hostId,
        kind,
        payload,
      }, '');

    const askOffer = () => {
      if (!state.sessionId || !state.hostId) return;
      sendSignal('need', { t: Date.now() });
    };

    const ensurePc = () => {
      if (state.pc) return state.pc;
      const pc = new RTCPeerConnection(iceServers);
      try {
        pc.addTransceiver('audio', { direction: 'recvonly' });
        pc.addTransceiver('video', { direction: 'recvonly' });
      } catch (e) {}
      state.pc = pc;
      pc.ontrack = (ev) => {
        if (!video) return;
        const stream = ev.streams && ev.streams[0] ? ev.streams[0] : new MediaStream([ev.track]);
        if (video.srcObject !== stream) video.srcObject = stream;
        else if (ev.track && ev.track.kind === 'audio' && video.srcObject && typeof video.srcObject.addTrack === 'function') {
          const has = video.srcObject.getAudioTracks().some((t) => t.id === ev.track.id);
          if (!has) video.srcObject.addTrack(ev.track);
        }
        if (ev.track) {
          ev.track.onunmute = () => {
            if (state.wantSound) playWithSound();
            else video.play().catch(() => {});
          };
        }
        if (state.wantSound) playWithSound();
        else video.play().catch(() => {});
        if (video.videoWidth || (ev.track && ev.track.kind === 'video')) setLiveUi();
      };
      pc.onicecandidate = (ev) => {
        if (ev.candidate) sendSignal('ice', packIce(ev.candidate));
      };
      pc.onconnectionstatechange = () => {
        if (pc.connectionState === 'connected' && hasPicture()) setLiveUi();
        if (pc.connectionState !== 'failed') return;
        setTimeout(() => {
          if (state.pc !== pc) return;
          try { pc.close(); } catch (e) {}
          state.pc = null;
          if (state.expectLive) {
            if (!hasPicture()) setConnecting();
            askOffer();
            join();
          }
        }, 1200);
      };
      return pc;
    };

    const handleSignals = async (signals) => {
      let pc = ensurePc();
      for (const msg of signals || []) {
        if (msg.kind === 'offer' && msg.payload) {
          try {
            if (pc.signalingState === 'have-local-offer') {
              await pc.setLocalDescription({ type: 'rollback' }).catch(() => {});
            }
            await pc.setRemoteDescription(msg.payload);
          } catch (e) {
            try { pc.close(); } catch (err) {}
            state.pc = null;
            pc = ensurePc();
            await pc.setRemoteDescription(msg.payload);
          }
          for (const ice of state.pendingIce) {
            try { await pc.addIceCandidate(ice); } catch (e) {}
          }
          state.pendingIce = [];
          const answer = await pc.createAnswer();
          await pc.setLocalDescription(answer);
          await sendSignal('answer', packSdp(pc.localDescription));
        }
        if (msg.kind === 'ice' && msg.payload) {
          if (!pc.remoteDescription) state.pendingIce.push(msg.payload);
          else {
            try { await pc.addIceCandidate(msg.payload); } catch (e) {}
          }
        }
        if (msg.kind === 'bye') setOffline();
      }
    };

    const poll = async () => {
      if (!state.sessionId) return;
      try {
        const res = await post(root.getAttribute('data-state-url'), {
          session_id: state.sessionId,
          peer_id: state.peerId,
          token: state.token,
          after: chat.lastId(),
        }, '');
        if (res.data && (res.data.ended || res.data.live === false)) {
          setOffline();
          state.sessionId = 0;
          return;
        }
        if (!res.okHttp) return;
        if (res.data.host_id) state.hostId = res.data.host_id;
        if (viewersEl) viewersEl.textContent = (root.getAttribute('data-watching-label') || 'Watching') + ': ' + (res.data.viewers || 0);
        chat.add(res.data.comments || []);
        await handleSignals(res.data.signals || []);
      } catch (e) {}
    };

    const join = async () => {
      if (state.joining) return;
      state.joining = true;
      try {
        const res = await post(root.getAttribute('data-join-url'), {}, '');
        if (!res.data || !res.data.ok) {
          if (state.expectLive) setConnecting();
          else setOffline();
          startMediaPull();
          return;
        }
        state.sessionId = res.data.session_id;
        state.peerId = res.data.peer_id;
        state.token = res.data.token;
        state.hostId = res.data.host_id;
        state.live = true;
        state.expectLive = true;
        state.started = Date.now();
        if (caption) caption.textContent = res.data.title || '';
        if (viewersEl) viewersEl.textContent = 'Watching: ' + (res.data.viewers || 0);
        chat.show(true);
        chat.add(res.data.comments || []);
        setConnecting();
        startMediaPull();
        ensurePc();
        askOffer();
        if (state.timer) clearInterval(state.timer);
        state.timer = setInterval(poll, POLL_MS);
        await poll();
        setTimeout(askOffer, 800);
      } catch (e) {
        if (state.expectLive) setConnecting();
        else setOffline();
        startMediaPull();
      } finally {
        state.joining = false;
      }
    };

    const checkStatus = async () => {
      try {
        const res = await fetch(root.getAttribute('data-status-url'), {
          headers: { Accept: 'application/json' },
          credentials: 'same-origin',
        });
        const data = await res.json();
        if (data && data.live) {
          state.expectLive = true;
          startMediaPull();
          if (!hasPicture()) setConnecting();
          if (!state.sessionId) join();
        } else if (state.sessionId || state.expectLive) {
          setOffline();
          state.sessionId = 0;
        }
      } catch (e) {}
    };

    if (chat.form) {
      chat.form.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        if (!state.sessionId) return;
        const name = chat.nameEl ? chat.nameEl.value.trim() : '';
        const body = chat.bodyEl ? chat.bodyEl.value.trim() : '';
        if (!body) return;
        try {
          sessionStorage.setItem('plive-chat-name', name);
        } catch (e) {}
        const btn = chat.form.querySelector('button[type="submit"]');
        if (btn) btn.disabled = true;
        const res = await post(root.getAttribute('data-comment-url'), {
          session_id: state.sessionId,
          peer_id: state.peerId,
          token: state.token,
          name,
          body,
        }, '');
        if (btn) btn.disabled = false;
        if (res.data && res.data.ok && res.data.comment) {
          chat.add([res.data.comment]);
          if (chat.bodyEl) chat.bodyEl.value = '';
        } else if (status) {
          status.textContent = (res.data && res.data.error) || 'Could not send the comment.';
        }
      });
    }

    if (playBtn && video) {
      playBtn.addEventListener('click', () => {
        if (video.paused) playWithSound();
        else video.pause();
      });
      video.addEventListener('play', () => { playBtn.textContent = 'Pause'; });
      video.addEventListener('pause', () => { playBtn.textContent = 'Play'; });
    }

    if (volEl && video) {
      volEl.addEventListener('input', () => {
        video.volume = Number(volEl.value || 1);
        if (video.volume > 0) {
          video.muted = false;
          state.wantSound = true;
          showUnmute(false);
        }
      });
    }

    if (pipBtn && video) {
      pipBtn.addEventListener('click', async () => {
        try {
          if (document.pictureInPictureElement) {
            await document.exitPictureInPicture();
            return;
          }
          if (video.webkitSetPresentationMode) {
            const next = video.webkitPresentationMode === 'picture-in-picture' ? 'inline' : 'picture-in-picture';
            video.webkitSetPresentationMode(next);
            return;
          }
          if (document.pictureInPictureEnabled) await video.requestPictureInPicture();
        } catch (e) {}
      });
    }

    if (fsBtn) {
      fsBtn.addEventListener('click', () => {
        if (fsElement() || (video && video.webkitDisplayingFullscreen)) {
          exitLiveFullscreen(player, video);
          return;
        }
        enterLiveFullscreen(player, video);
      });
    }
    document.addEventListener('fullscreenchange', () => {
      if (!fsElement()) unlockOrientation(player);
    });
    document.addEventListener('webkitfullscreenchange', () => {
      if (!fsElement()) unlockOrientation(player);
    });
    if (video) {
      video.addEventListener('webkitendfullscreen', () => unlockOrientation(player));
      video.addEventListener('webkitbeginfullscreen', () => lockLandscape(player));
    }

    if (player) {
      player.addEventListener('click', (ev) => {
        if (ev.target.closest('.plive-controls')) return;
        if (ev.target.closest('[data-plive-unmute]')) return;
        playWithSound();
      });
    }
    setInterval(() => {
      if (clock && state.live && state.started) clock.textContent = fmt((Date.now() - state.started) / 1000);
    }, 1000);

    window.addEventListener('pagehide', (ev) => {
      if (ev.persisted || isPhone()) return;
      if (!state.sessionId) return;
      try {
        navigator.sendBeacon(
          root.getAttribute('data-leave-url'),
          new Blob([JSON.stringify({
            session_id: state.sessionId,
            peer_id: state.peerId,
            token: state.token,
          })], { type: 'application/json' })
        );
      } catch (e) {}
    });
    document.addEventListener('visibilitychange', () => {
      if (document.visibilityState !== 'visible') return;
      if (state.expectLive && !state.sessionId) join();
      else if (state.sessionId) poll();
      if (state.expectLive) pullMedia();
    });

    if (connecting && !connecting.hidden) {
      state.expectLive = true;
      chat.show(true);
      startMediaPull();
    }
    checkStatus();
    state.statusTimer = setInterval(checkStatus, 2500);
  };

  const hostRoot = document.querySelector('[data-plive-host]');
  if (hostRoot) bootHost(hostRoot);
  const watchRoot = document.querySelector('[data-plive-watch]');
  if (watchRoot) bootWatch(watchRoot);
})();
