(() => {
  const root = document.querySelector('[data-live-class]');
  if (!root) return;

  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const sameOrigin = (raw) => {
    if (!raw) return raw;
    try {
      const u = new URL(raw, location.href);
      if (location.protocol === 'https:' && u.protocol === 'http:' && u.hostname === location.hostname) {
        u.protocol = 'https:';
      }
      if (u.origin === location.origin) {
        return u.pathname + u.search + u.hash;
      }
      return u.href;
    } catch (_) {
      return raw;
    }
  };
  const cfg = {
    role: root.dataset.role,
    name: root.dataset.name,
    avatar: root.dataset.avatar || '',
    leave: sameOrigin(root.dataset.leave),
    joinUrl: sameOrigin(root.dataset.joinUrl),
    stateUrl: sameOrigin(root.dataset.stateUrl),
    signalUrl: sameOrigin(root.dataset.signalUrl),
    chatUrl: sameOrigin(root.dataset.chatUrl),
    mediaUrl: sameOrigin(root.dataset.mediaUrl),
    handUrl: sameOrigin(root.dataset.handUrl),
    leaveUrl: sameOrigin(root.dataset.leaveUrl),
    kickUrl: sameOrigin(root.dataset.kickUrl),
    presenterUrl: sameOrigin(root.dataset.presenterUrl),
    pushUrl: sameOrigin(root.dataset.pushUrl),
    frameUrl: sameOrigin(root.dataset.frameUrl),
    audioUrl: sameOrigin(root.dataset.audioUrl),
    muteAllUrl: sameOrigin(root.dataset.muteAllUrl),
  };

  const isSecurePage = window.isSecureContext
    || location.hostname === 'localhost'
    || location.hostname === '127.0.0.1';

  const FRAME_MS = 70;
  const AUDIO_RATE = 16000;

  const iceServers = {
    iceCandidatePoolSize: 8,
    bundlePolicy: 'max-bundle',
    iceServers: [
      { urls: 'stun:stun.l.google.com:19302' },
      { urls: 'stun:stun1.l.google.com:19302' },
      { urls: 'stun:stun.cloudflare.com:3478' },
      {
        urls: [
          'turn:openrelay.metered.ca:80',
          'turn:openrelay.metered.ca:443',
          'turn:openrelay.metered.ca:80?transport=tcp',
          'turns:openrelay.metered.ca:443?transport=tcp',
        ],
        username: 'openrelayproject',
        credential: 'openrelayproject',
      },
    ],
  };

  const state = {
    peerId: null,
    localStream: null,
    screenStream: null,
    screenTrack: null,
    pcs: {},
    tx: {},
    streams: {},
    offering: {},
    makingOffer: {},
    ignoreOffer: {},
    audio: true,
    video: true,
    screen: false,
    canShare: cfg.role === 'host',
    hand: false,
    lastChat: 0,
    pollTimer: null,
    lastPeers: [],
    panel: '',
    unread: 0,
    mediaBusy: false,
    peopleSig: '',
    soundBlocked: false,
    pendingIce: {},
    leaving: false,
    leaveSent: false,
    polling: false,
    pollInFlight: false,
    frames: {},
    blobUrls: {},
    lastAudioSeq: {},
    audioBuf: [],
    audioCtx: null,
    audioSrc: null,
    audioProc: null,
    audioGain: null,
    canvas: null,
    canvasCtx: null,
    pushing: false,
    pushTimer: null,
    pullTimer: null,
    pulling: false,
    wantSound: false,
    watchAudioAt: 0,
  };

  const lobby = document.getElementById('live-lobby');
  const stage = document.getElementById('live-stage');
  const dock = document.getElementById('live-dock');
  const grid = document.getElementById('live-grid');
  const people = document.getElementById('live-people');
  const chatLog = document.getElementById('live-chat-log');
  const lobbyError = document.getElementById('live-lobby-error');
  const countEl = document.getElementById('live-count');
  const shareBtn = document.getElementById('live-share-btn');
  const peopleCountEl = document.getElementById('live-people-count');
  const chatBadge = document.getElementById('live-chat-badge');
  const panelPeople = document.getElementById('live-panel-people');
  const panelChat = document.getElementById('live-panel-chat');
  const soundBar = document.getElementById('live-sound-bar');

  const api = async (url, body) => {
    const res = await fetch(url, {
      method: body ? 'POST' : 'GET',
      credentials: 'same-origin',
      cache: 'no-store',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
        Accept: 'application/json',
      },
      body: body ? JSON.stringify(body) : undefined,
    });
    const data = await res.json().catch(() => ({ ok: false, error: 'Bad response' }));
    if (!res.ok || data.ok === false) {
      throw new Error(data.error || 'Request failed');
    }
    return data;
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

  const tileId = (peerId) => 'tile-' + peerId;

  const initialsFrom = (name) => {
    const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
    const letters = parts.slice(0, 2).map((p) => p.charAt(0).toUpperCase()).join('');
    return letters || 'IC';
  };

  const liveTracks = (stream, kind) => {
    if (!stream) return [];
    return stream.getTracks().filter((t) => t.kind === kind && t.readyState === 'live');
  };

  const cameraTrack = () => liveTracks(state.localStream, 'video')[0] || null;
  const micTrack = () => liveTracks(state.localStream, 'audio')[0] || null;

  const outboundVideo = () => {
    if (state.screen && state.screenTrack && state.screenTrack.readyState === 'live') {
      return state.screenTrack;
    }
    return cameraTrack();
  };

  const setShareVisible = (on) => {
    state.canShare = !!on;
    if (!shareBtn) return;
    shareBtn.hidden = !state.canShare;
    if (!state.canShare && state.screen) {
      stopScreenShare(false);
    }
  };

  const showSoundBar = (on) => {
    state.soundBlocked = !!on;
    if (soundBar) soundBar.hidden = !on;
  };

  const playEl = (el) => {
    if (!el) return;
    const p = el.play();
    if (p && typeof p.catch === 'function') {
      p.catch(() => {
        if (!el.muted) showSoundBar(true);
      });
    }
  };

  const rtcStream = (peerId) => state.streams[peerId] || null;

  const rtcVideoReady = (peerId) => {
    const tile = document.getElementById(tileId(peerId));
    const video = tile ? tile.querySelector('video') : null;
    if (video && video.srcObject && video.videoWidth > 8 && video.readyState >= 2) return true;
    const ms = rtcStream(peerId);
    return !!(ms && liveTracks(ms, 'video').length);
  };

  const rtcAudioReady = (peerId) => {
    const ms = rtcStream(peerId);
    return !!(ms && liveTracks(ms, 'audio').length);
  };

  const pcLive = (peerId) => {
    const pc = state.pcs[peerId];
    if (!pc) return false;
    const ice = pc.iceConnectionState;
    const conn = pc.connectionState;
    return ice === 'connected' || ice === 'completed' || conn === 'connected';
  };

  const streamHasLiveVideo = (peerId) => {
    if (peerId === state.peerId) return rtcVideoReady(peerId);
    return rtcVideoReady(peerId) || !!state.frames[peerId];
  };

  const applyTilePicture = (peerId) => {
    const tile = document.getElementById(tileId(peerId));
    if (!tile) return;
    const video = tile.querySelector('video');
    const img = tile.querySelector('.live-tile-frame');
    const isLocal = peerId === state.peerId;
    const rtcOn = !isLocal && rtcVideoReady(peerId);
    const httpOn = !isLocal && !!state.frames[peerId] && !rtcOn;
    if (img) {
      if (httpOn) {
        img.hidden = false;
        if (img.src !== state.frames[peerId]) img.src = state.frames[peerId];
      } else {
        img.hidden = true;
      }
    }
    if (video && !isLocal) {
      video.muted = !state.wantSound;
      video.classList.toggle('is-blank', !rtcOn);
    }
    const sharing = tile.classList.contains('is-screen');
    const camOn = sharing || (isLocal ? rtcVideoReady(peerId) : (rtcOn || httpOn));
    if (!sharing) tile.classList.toggle('is-cam-off', !camOn);
    tile.classList.toggle('has-frame', httpOn);
    const flagsEl = tile.querySelector('.live-tile-flags');
    if (flagsEl && !isLocal) {
      const parts = String(flagsEl.textContent || '').split(' · ').map((s) => s.trim()).filter(Boolean)
        .filter((f) => f !== 'Camera off');
      if (!camOn && !sharing) parts.push('Camera off');
      flagsEl.textContent = parts.join(' · ');
    }
  };

  const bindRemote = (peerId) => {
    const tile = document.getElementById(tileId(peerId));
    const video = tile ? tile.querySelector('video') : null;
    const ms = rtcStream(peerId);
    if (video) {
      video.playsInline = true;
      video.setAttribute('playsinline', '');
      video.setAttribute('webkit-playsinline', '');
      if (ms) {
        if (video.srcObject !== ms) video.srcObject = ms;
        const hasVideo = liveTracks(ms, 'video').length > 0;
        video.classList.toggle('is-blank', !hasVideo);
        video.muted = !state.wantSound;
        playEl(video);
      } else {
        video.classList.add('is-blank');
      }
    }
    applyTilePicture(peerId);
  };

  const bindLocal = () => {
    const tile = document.getElementById(tileId(state.peerId));
    const video = tile ? tile.querySelector('video') : null;
    if (!video) return;
    video.muted = true;
    video.playsInline = true;
    let preview = null;
    if (state.screen && state.screenStream) {
      preview = state.screenStream;
    } else if (state.video && cameraTrack()) {
      preview = state.localStream;
    }
    if (video.srcObject !== preview) {
      video.srcObject = preview;
    }
    video.classList.toggle('is-blank', !preview);
    if (tile && !state.screen) {
      tile.classList.toggle('is-cam-off', !preview);
    }
    if (preview) playEl(video);
  };

  const layoutGrid = () => {
    if (!grid) return;
    const tiles = Array.from(grid.children);
    const n = tiles.length;
    const presenting = tiles.some((t) => t.classList.contains('is-screen'));
    grid.classList.toggle('is-presenting', presenting);
    root.classList.toggle('is-presenting', presenting);
    if (presenting) {
      grid.style.removeProperty('--live-cols');
      grid.style.removeProperty('--live-rows');
      grid.dataset.count = String(n);
      return;
    }
    const narrow = window.matchMedia('(max-width: 700px)').matches;
    let cols = 1;
    let rows = 1;
    if (n <= 1) {
      cols = 1;
      rows = 1;
    } else if (n === 2) {
      cols = narrow ? 1 : 2;
      rows = narrow ? 2 : 1;
    } else if (n <= 4) {
      cols = 2;
      rows = 2;
    } else if (n <= 6) {
      cols = narrow ? 2 : 3;
      rows = Math.ceil(n / cols);
    } else {
      cols = narrow ? 2 : 4;
      rows = Math.ceil(n / cols);
    }
    grid.style.setProperty('--live-cols', String(cols));
    grid.style.setProperty('--live-rows', String(rows));
    grid.dataset.count = String(n);
  };

  const paintFace = (tile, meta, camOn) => {
    const sharing = tile.classList.contains('is-screen');
    tile.classList.toggle('is-cam-off', !camOn && !sharing);
    const photo = tile.querySelector('.live-tile-photo');
    const initials = tile.querySelector('.live-tile-initials');
    const avatar = meta.avatar || (tile.id === tileId(state.peerId) ? cfg.avatar : '');
    initials.textContent = meta.initials || initialsFrom(meta.display_name);
    if (avatar) {
      photo.src = avatar;
      photo.hidden = false;
    } else {
      photo.removeAttribute('src');
      photo.hidden = true;
    }
  };

  const roleLabel = (meta) => {
    if ((meta.role || '') === 'host') return ' · Teacher';
    if (Number(meta.is_presenter) === 1) return ' · Host';
    return '';
  };

  const ensureTile = (peerId, meta) => {
    let tile = document.getElementById(tileId(peerId));
    if (!tile) {
      tile = document.createElement('article');
      tile.id = tileId(peerId);
      tile.innerHTML = '<video playsinline webkit-playsinline autoplay muted></video><img class="live-tile-frame" alt="" hidden><div class="live-tile-face"><img class="live-tile-photo" alt="" hidden><span class="live-tile-initials"></span></div><span class="live-tile-name"></span><span class="live-tile-flags"></span>';
      grid.appendChild(tile);
    }
    const sharing = Number(meta.screen_on) === 1 || (peerId === state.peerId && state.screen);
    const localCam = peerId === state.peerId && !!cameraTrack() && state.video;
    const remoteCam = peerId !== state.peerId && (
      streamHasLiveVideo(peerId) || !!state.frames[peerId] || Number(meta.video_on) === 1
    );
    const camOn = sharing || localCam || remoteCam;
    tile.className = 'live-tile'
      + (peerId === state.peerId ? ' is-local' : '')
      + ((meta.role || '') === 'host' ? ' is-host' : '')
      + (Number(meta.is_presenter) === 1 ? ' is-presenter' : '')
      + (sharing ? ' is-screen' : '');
    tile.querySelector('.live-tile-name').textContent =
      (meta.display_name || 'Student')
      + (peerId === state.peerId ? ' (you)' : '')
      + roleLabel(meta)
      + (sharing ? ' · Screen' : '');
    const flags = [];
    if (sharing) flags.push('Screen');
    if (Number(meta.hand_raised)) flags.push('Hand');
    if (!Number(meta.audio_on)) flags.push('Muted');
    if (!camOn && !sharing) flags.push('Camera off');
    tile.querySelector('.live-tile-flags').textContent = flags.join(' · ');
    paintFace(tile, meta, camOn);
    const video = tile.querySelector('video');
    video.setAttribute('playsinline', '');
    video.setAttribute('webkit-playsinline', '');
    video.setAttribute('autoplay', '');
    if (peerId === state.peerId) {
      bindLocal();
    } else {
      applyTilePicture(peerId);
      if (rtcStream(peerId)) bindRemote(peerId);
    }
    layoutGrid();
    return tile;
  };

  const removeTile = (peerId) => {
    const tile = document.getElementById(tileId(peerId));
    if (tile) tile.remove();
    layoutGrid();
  };

  const personSub = (p) => {
    const bits = [];
    if (p.peer_id === state.peerId) bits.push('You');
    if ((p.role || '') === 'host') bits.push('Teacher');
    else if (Number(p.is_presenter) === 1) bits.push('Host');
    if (Number(p.hand_raised)) bits.push('Hand raised');
    if (Number(p.screen_on)) bits.push('Sharing screen');
    if (!Number(p.audio_on)) bits.push('Muted');
    if (!Number(p.video_on) && !Number(p.screen_on)) bits.push('Camera off');
    return bits.join(' · ') || 'In class';
  };

  const actionBtn = (label, className, onClick) => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'live-action' + (className ? ' ' + className : '');
    btn.textContent = label;
    btn.addEventListener('click', (ev) => {
      ev.preventDefault();
      ev.stopPropagation();
      onClick();
    });
    return btn;
  };

  const renderPeople = (peers) => {
    const ordered = peers.slice().sort((a, b) => {
      const ah = Number(a.hand_raised) ? 0 : 1;
      const bh = Number(b.hand_raised) ? 0 : 1;
      if (ah !== bh) return ah - bh;
      const ar = (a.role || '') === 'host' ? 0 : 1;
      const br = (b.role || '') === 'host' ? 0 : 1;
      if (ar !== br) return ar - br;
      return String(a.display_name || '').localeCompare(String(b.display_name || ''));
    });
    const sig = ordered.map((p) => [
      p.peer_id, p.display_name, p.role, p.is_presenter, p.hand_raised,
      p.audio_on, p.video_on, p.screen_on, p.avatar,
    ].join(':')).join('|');
    const n = peers.length;
    if (countEl) countEl.textContent = n + (n === 1 ? ' in class' : ' in class');
    if (peopleCountEl) peopleCountEl.textContent = String(n);
    const peopleCtl = document.getElementById('live-people-ctl');
    if (peopleCtl) peopleCtl.textContent = String(n);
    if (sig === state.peopleSig && people.childElementCount) return;
    state.peopleSig = sig;
    people.innerHTML = '';
    ordered.forEach((p) => {
      const row = document.createElement('div');
      row.className = 'live-person';
      if (p.avatar) {
        const img = document.createElement('img');
        img.className = 'live-person-avatar';
        img.src = p.avatar;
        img.alt = '';
        row.appendChild(img);
      } else {
        const fallback = document.createElement('span');
        fallback.className = 'live-person-fallback';
        fallback.textContent = p.initials || initialsFrom(p.display_name);
        row.appendChild(fallback);
      }
      const meta = document.createElement('div');
      meta.className = 'live-person-meta';
      const name = document.createElement('span');
      name.className = 'live-person-name';
      name.textContent = p.display_name || 'Student';
      const sub = document.createElement('span');
      sub.className = 'live-person-sub';
      sub.textContent = personSub(p);
      meta.appendChild(name);
      meta.appendChild(sub);
      row.appendChild(meta);
      if (cfg.role === 'host' && (p.role || '') !== 'host' && p.peer_id !== state.peerId) {
        const actions = document.createElement('div');
        actions.className = 'live-person-actions';
        const hosting = Number(p.is_presenter) === 1;
        actions.appendChild(actionBtn(hosting ? 'Remove host' : 'Make host', hosting ? 'is-hosting' : '', async () => {
          try {
            await api(cfg.presenterUrl, { peer_id: p.peer_id, on: !hosting });
            state.peopleSig = '';
            poll();
          } catch (err) {
            window.alert(err.message);
          }
        }));
        if (Number(p.audio_on) === 1) {
          actions.appendChild(actionBtn('Mute', 'is-mute', () => {
            api(cfg.signalUrl, { to: p.peer_id, kind: 'control', payload: { action: 'mute' } }).catch((err) => {
              window.alert(err.message);
            });
          }));
        }
        actions.appendChild(actionBtn('Remove', 'is-remove', async () => {
          if (!window.confirm('Remove ' + (p.display_name || 'this student') + ' from the class?')) return;
          try {
            await api(cfg.kickUrl, { peer_id: p.peer_id });
            state.peopleSig = '';
          } catch (err) {
            window.alert(err.message);
          }
        }));
        row.appendChild(actions);
      }
      people.appendChild(row);
    });
  };

  const paintChatLine = (msg) => {
    if (!chatLog) return null;
    const p = document.createElement('p');
    p.className = 'live-chat-item';
    p.innerHTML = '<strong></strong><span></span>';
    p.querySelector('strong').textContent = msg.display_name || cfg.name || 'You';
    p.querySelector('span').textContent = msg.body || '';
    chatLog.appendChild(p);
    chatLog.scrollTop = chatLog.scrollHeight;
    return p;
  };

  const appendChat = (msg) => {
    const id = Number(msg.id);
    if (!id || id <= state.lastChat) return;
    state.lastChat = id;
    paintChatLine(msg);
    if (state.panel !== 'chat' && msg.peer_id !== state.peerId) {
      state.unread += 1;
      if (chatBadge) {
        chatBadge.hidden = false;
        chatBadge.textContent = state.unread > 9 ? '9+' : String(state.unread);
      }
    }
  };

  const applyBitrate = (pc, screen) => {
    if (!pc) return;
    const max = screen ? 2200000 : 1600000;
    pc.getSenders().forEach((sender) => {
      if (!sender.track || sender.track.kind !== 'video') return;
      const params = sender.getParameters();
      if (!params.encodings || !params.encodings.length) params.encodings = [{}];
      params.encodings[0].maxBitrate = max;
      params.encodings[0].maxFramerate = screen ? 24 : 30;
      try { params.degradationPreference = screen ? 'maintain-resolution' : 'balanced'; } catch (_) {}
      sender.setParameters(params).catch(() => {});
    });
  };

  const applyLocalTracks = async (remoteId) => {
    const tx = state.tx[remoteId];
    if (!tx) return;
    const audio = micTrack();
    const video = outboundVideo();
    try {
      if (tx.audio) await tx.audio.sender.replaceTrack(audio);
    } catch (_) {}
    try {
      if (tx.video) {
        try { tx.video.direction = video ? 'sendrecv' : 'recvonly'; } catch (__) {}
        if (video) {
          try { video.contentHint = state.screen ? 'detail' : 'motion'; } catch (__) {}
        }
        await tx.video.sender.replaceTrack(video || null);
        applyBitrate(state.pcs[remoteId], state.screen);
      }
    } catch (_) {}
  };

  const applyLocalTracksAll = async (renegotiate) => {
    const ids = Object.keys(state.pcs);
    for (const id of ids) {
      await applyLocalTracks(id);
      if (renegotiate) {
        try { await makeOffer(id); } catch (_) {}
      }
    }
  };

  const pcFor = (remoteId) => {
    if (state.pcs[remoteId]) return state.pcs[remoteId];
    const pc = new RTCPeerConnection(iceServers);
    state.pcs[remoteId] = pc;
    state.pendingIce[remoteId] = state.pendingIce[remoteId] || [];
    const hasOutVideo = !!outboundVideo();
    state.tx[remoteId] = {
      audio: pc.addTransceiver('audio', { direction: 'sendrecv' }),
      video: pc.addTransceiver('video', { direction: hasOutVideo ? 'sendrecv' : 'recvonly' }),
    };
    applyLocalTracks(remoteId);
    applyBitrate(pc, state.screen);
    pc.onicecandidate = (ev) => {
      if (ev.candidate) {
        const payload = typeof ev.candidate.toJSON === 'function'
          ? ev.candidate.toJSON()
          : {
            candidate: ev.candidate.candidate,
            sdpMid: ev.candidate.sdpMid,
            sdpMLineIndex: ev.candidate.sdpMLineIndex,
            usernameFragment: ev.candidate.usernameFragment,
          };
        api(cfg.signalUrl, { to: remoteId, kind: 'ice', payload }).catch(() => {});
      }
    };
    pc.ontrack = (ev) => {
      if (!state.streams[remoteId]) {
        state.streams[remoteId] = new MediaStream();
      }
      const ms = state.streams[remoteId];
      ms.getTracks().filter((t) => t.kind === ev.track.kind && t !== ev.track).forEach((old) => {
        try { ms.removeTrack(old); } catch (_) {}
      });
      if (ev.track && !ms.getTracks().includes(ev.track)) {
        ms.addTrack(ev.track);
      }
      const refresh = () => bindRemote(remoteId);
      ev.track.addEventListener('mute', refresh);
      ev.track.addEventListener('unmute', refresh);
      ev.track.addEventListener('ended', () => {
        try { ms.removeTrack(ev.track); } catch (_) {}
        refresh();
      });
      refresh();
    };
    pc.onconnectionstatechange = () => {
      if (pc.connectionState === 'connected') {
        applyBitrate(pc, state.screen);
        bindRemote(remoteId);
      }
      if (pc.connectionState === 'failed') {
        makeOffer(remoteId, true).catch(() => {});
      }
      if (pc.connectionState === 'closed') {
        closePeer(remoteId);
      }
    };
    pc.oniceconnectionstatechange = () => {
      if (pc.iceConnectionState === 'connected' || pc.iceConnectionState === 'completed') {
        bindRemote(remoteId);
      }
      if (pc.iceConnectionState === 'failed') {
        makeOffer(remoteId, true).catch(() => {});
      }
    };
    return pc;
  };

  const closePeer = (remoteId) => {
    const pc = state.pcs[remoteId];
    if (pc) {
      try { pc.close(); } catch (_) {}
      delete state.pcs[remoteId];
    }
    delete state.tx[remoteId];
    delete state.streams[remoteId];
    delete state.offering[remoteId];
    delete state.makingOffer[remoteId];
    delete state.pendingIce[remoteId];
    if (state.blobUrls[remoteId]) {
      try { URL.revokeObjectURL(state.blobUrls[remoteId]); } catch (_) {}
      delete state.blobUrls[remoteId];
    }
    delete state.frames[remoteId];
    delete state.lastAudioSeq[remoteId];
    if (remoteId !== state.peerId) removeTile(remoteId);
  };

  const makeOffer = async (remoteId, iceRestart = false) => {
    const pc = pcFor(remoteId);
    if (state.offering[remoteId]) return;
    if (pc.signalingState !== 'stable' && !iceRestart) return;
    if (!iceRestart && pc.remoteDescription && pcLive(remoteId)) return;
    state.offering[remoteId] = true;
    state.makingOffer[remoteId] = true;
    try {
      await applyLocalTracks(remoteId);
      const offer = await pc.createOffer(iceRestart ? { iceRestart: true } : undefined);
      if (pc.signalingState !== 'stable' && !iceRestart) return;
      await pc.setLocalDescription(offer);
      await api(cfg.signalUrl, { to: remoteId, kind: 'offer', payload: pc.localDescription });
    } finally {
      state.offering[remoteId] = false;
      state.makingOffer[remoteId] = false;
    }
  };

  const flushIce = async (remoteId) => {
    const pc = state.pcs[remoteId];
    const queued = state.pendingIce[remoteId] || [];
    state.pendingIce[remoteId] = [];
    if (!pc || !pc.remoteDescription) return;
    for (const candidate of queued) {
      try {
        await pc.addIceCandidate(new RTCIceCandidate(candidate));
      } catch (_) {}
    }
  };

  const handleSignal = async (sig) => {
    const from = sig.from;
    if (!from || from === state.peerId) return;
    if (sig.kind === 'bye' || sig.kind === 'ended') {
      if (sig.kind === 'ended') {
        exitRoom();
        return;
      }
      closePeer(from);
      return;
    }
    if (sig.kind === 'control') {
      const action = sig.payload && sig.payload.action;
      if (action === 'mute') {
        await setMic(false);
        state.peopleSig = '';
      }
      if (action === 'kick') {
        window.alert('The teacher removed you from this class.');
        window.location.href = cfg.leave;
      }
      if (action === 'presenter') {
        const mine = (sig.payload.peer_id || from) === state.peerId || sig.payload.peer_id === state.peerId;
        if (mine && cfg.role !== 'host') {
          setShareVisible(!!sig.payload.on);
        }
      }
      if (action === 'stop-screen' && state.screen) {
        await stopScreenShare(true);
      }
      return;
    }
    const pc = pcFor(from);
    const polite = state.peerId > from;
    if (sig.kind === 'offer') {
      const colliding = state.makingOffer[from] || pc.signalingState !== 'stable';
      state.ignoreOffer[from] = !polite && colliding;
      if (state.ignoreOffer[from]) return;
      try {
        if (colliding) {
          await pc.setLocalDescription({ type: 'rollback' });
        }
      } catch (_) {}
      await pc.setRemoteDescription(new RTCSessionDescription(sig.payload));
      await flushIce(from);
      await applyLocalTracks(from);
      const answer = await pc.createAnswer();
      await pc.setLocalDescription(answer);
      await api(cfg.signalUrl, { to: from, kind: 'answer', payload: pc.localDescription });
      bindRemote(from);
    } else if (sig.kind === 'answer') {
      if (state.ignoreOffer[from]) return;
      if (pc.signalingState === 'have-local-offer') {
        await pc.setRemoteDescription(new RTCSessionDescription(sig.payload));
        await flushIce(from);
        bindRemote(from);
      }
    } else if (sig.kind === 'ice' && sig.payload) {
      if (pc.remoteDescription) {
        try {
          await pc.addIceCandidate(new RTCIceCandidate(sig.payload));
        } catch (_) {}
      } else {
        (state.pendingIce[from] = state.pendingIce[from] || []).push(sig.payload);
      }
    }
  };

  const syncPeers = async (peers) => {
    state.lastPeers = peers;
    const ids = new Set(peers.map((p) => p.peer_id));
    const me = peers.find((p) => p.peer_id === state.peerId);
    if (me) {
      setShareVisible(cfg.role === 'host' || Number(me.can_share) === 1 || Number(me.is_presenter) === 1);
    }
    peers.forEach((p) => {
      ensureTile(p.peer_id, p);
      if (p.peer_id === state.peerId) return;
      pcFor(p.peer_id);
      if (state.peerId > p.peer_id) {
        makeOffer(p.peer_id).catch(() => {});
      }
    });
    Object.keys(state.pcs).forEach((id) => {
      if (!ids.has(id) && id !== state.peerId) closePeer(id);
    });
    Array.from(grid.children).forEach((tile) => {
      const id = tile.id.replace('tile-', '');
      if (id !== state.peerId && !ids.has(id)) tile.remove();
    });
    renderPeople(peers);
    layoutGrid();
    if (peers.some((p) => p.peer_id !== state.peerId) && !state.wantSound) {
      showSoundBar(true);
    }
  };

  const onLocalAudio = (ev) => {
    if (!state.audio || !state.peerId) return;
    const input = ev.inputBuffer.getChannelData(0);
    const fromRate = ev.inputBuffer.sampleRate || (state.audioCtx && state.audioCtx.sampleRate) || 48000;
    const down = downsample(input, fromRate, AUDIO_RATE);
    if (!down.length) return;
    state.audioBuf.push(floatToInt16(down));
    let total = 0;
    for (let i = 0; i < state.audioBuf.length; i += 1) total += state.audioBuf[i].length;
    const cap = AUDIO_RATE * 2;
    while (total > cap && state.audioBuf.length > 1) {
      total -= state.audioBuf[0].length;
      state.audioBuf.shift();
    }
  };

  const ensureAudioContext = () => {
    const AC = window.AudioContext || window.webkitAudioContext;
    if (!AC) return null;
    if (!state.audioCtx) {
      try {
        state.audioCtx = new AC();
      } catch (_) {
        return null;
      }
    }
    if (state.audioCtx.state === 'suspended') {
      state.audioCtx.resume().catch(() => {});
    }
    return state.audioCtx;
  };

  const hookLocalAudio = (stream) => {
    if (!stream) return;
    try {
      if (state.audioSrc) state.audioSrc.disconnect();
    } catch (_) {}
    state.audioSrc = null;
    const tracks = stream.getAudioTracks();
    if (!tracks.length) return;
    const ctx = ensureAudioContext();
    if (!ctx) return;
    try {
      const micStream = new MediaStream(tracks);
      state.audioSrc = ctx.createMediaStreamSource(micStream);
      if (!state.audioProc) {
        state.audioProc = ctx.createScriptProcessor(4096, 1, 1);
        state.audioGain = ctx.createGain();
        state.audioGain.gain.value = 0;
        state.audioProc.onaudioprocess = onLocalAudio;
        state.audioProc.connect(state.audioGain);
        state.audioGain.connect(ctx.destination);
      }
      state.audioSrc.connect(state.audioProc);
    } catch (_) {}
  };

  const drainAudio = () => {
    if (!state.audioBuf.length) return null;
    let total = 0;
    for (let i = 0; i < state.audioBuf.length; i += 1) total += state.audioBuf[i].length;
    if (total < 160) return null;
    const maxSamples = Math.floor(AUDIO_RATE * 0.4);
    const take = Math.min(total, maxSamples);
    const merged = new Int16Array(take);
    let off = 0;
    const remain = [];
    for (let i = 0; i < state.audioBuf.length; i += 1) {
      const chunk = state.audioBuf[i];
      if (off >= take) {
        remain.push(chunk);
        continue;
      }
      const need = take - off;
      if (chunk.length <= need) {
        merged.set(chunk, off);
        off += chunk.length;
      } else {
        merged.set(chunk.subarray(0, need), off);
        remain.push(chunk.subarray(need));
        off = take;
      }
    }
    state.audioBuf = remain;
    return new Blob([merged.buffer], { type: 'application/octet-stream' });
  };

  const captureVideoEl = () => {
    const tile = document.getElementById(tileId(state.peerId));
    const fromTile = tile ? tile.querySelector('video') : null;
    if (fromTile && fromTile.readyState >= 2 && fromTile.videoWidth) return fromTile;
    const lobbyPreview = document.getElementById('live-lobby-preview');
    if (lobbyPreview && lobbyPreview.readyState >= 2 && lobbyPreview.videoWidth) return lobbyPreview;
    return null;
  };

  const captureFrame = () => new Promise((resolve) => {
    const video = captureVideoEl();
    if (!video || !video.videoWidth) {
      resolve(null);
      return;
    }
    const screen = !!state.screen;
    const tw = screen ? 1280 : 960;
    const th = screen ? 720 : 540;
    if (!state.canvas) {
      state.canvas = document.createElement('canvas');
      state.canvasCtx = state.canvas.getContext('2d', { alpha: false });
    }
    state.canvas.width = tw;
    state.canvas.height = th;
    const ctx = state.canvasCtx;
    const vw = video.videoWidth;
    const vh = video.videoHeight;
    ctx.fillStyle = '#071510';
    ctx.fillRect(0, 0, tw, th);
    const scale = Math.min(tw / vw, th / vh);
    const w = vw * scale;
    const h = vh * scale;
    try {
      ctx.drawImage(video, (tw - w) / 2, (th - h) / 2, w, h);
    } catch (_) {
      resolve(null);
      return;
    }
    state.canvas.toBlob((blob) => resolve(blob || null), 'image/jpeg', screen ? 0.72 : 0.7);
  });

  const remotesNeedHttp = () => {
    const peers = (state.lastPeers || []).filter((p) => p && p.peer_id !== state.peerId);
    if (!peers.length) return false;
    return peers.some((p) => !pcLive(p.peer_id) || !rtcVideoReady(p.peer_id));
  };

  const remotesNeedHttpAudio = () => {
    const peers = (state.lastPeers || []).filter((p) => p && p.peer_id !== state.peerId);
    if (!peers.length) return false;
    return peers.some((p) => !pcLive(p.peer_id) || !rtcAudioReady(p.peer_id));
  };

  const pushMedia = async () => {
    if (state.pushing || !state.peerId || !cfg.pushUrl || state.leaving) return;
    const needVideo = remotesNeedHttp() && (state.video || state.screen);
    const needAudio = remotesNeedHttpAudio() && state.audio;
    if (!needVideo && !needAudio) return;
    state.pushing = true;
    try {
      const frame = needVideo ? await captureFrame() : null;
      const audio = needAudio ? drainAudio() : null;
      if (!frame && !audio) return;
      const fd = new FormData();
      if (csrf) fd.append('_csrf', csrf);
      if (frame) fd.append('frame', frame, 'frame.jpg');
      if (audio) fd.append('audio', audio, 'audio.bin');
      const headers = { Accept: 'application/json' };
      if (csrf) headers['X-CSRF-TOKEN'] = csrf;
      await fetch(cfg.pushUrl, { method: 'POST', body: fd, credentials: 'same-origin', headers });
    } catch (_) {
    } finally {
      state.pushing = false;
    }
  };

  const ensureWatchAudio = () => ensureAudioContext();

  const playPcm = (int16, sampleRate) => {
    if (!state.wantSound || !int16 || !int16.length) return;
    const ctx = ensureAudioContext();
    if (!ctx || ctx.state === 'suspended') {
      showSoundBar(true);
      return;
    }
    const f32 = new Float32Array(int16.length);
    for (let i = 0; i < int16.length; i += 1) f32[i] = int16[i] / 32768;
    const rate = sampleRate || AUDIO_RATE;
    const buf = ctx.createBuffer(1, f32.length, rate);
    try {
      buf.copyToChannel(f32, 0);
    } catch (_) {
      buf.getChannelData(0).set(f32);
    }
    const src = ctx.createBufferSource();
    src.buffer = buf;
    src.connect(ctx.destination);
    const now = ctx.currentTime;
    if (state.watchAudioAt < now + 0.02) state.watchAudioAt = now + 0.02;
    if (state.watchAudioAt - now > 1.15) state.watchAudioAt = now + 0.02;
    src.start(state.watchAudioAt);
    state.watchAudioAt += buf.duration;
  };

  const pullPeerMedia = async (peerId) => {
    if (!peerId || peerId === state.peerId || !cfg.frameUrl) return;
    const peer = (state.lastPeers || []).find((p) => p && p.peer_id === peerId);
    const wantVideo = !rtcVideoReady(peerId) && (!peer || Number(peer.video_on) === 1 || Number(peer.screen_on) === 1 || Number(peer.has_frame) === 1);
    const wantAudio = !rtcAudioReady(peerId) && !!peer && Number(peer.audio_on) === 1 && !!cfg.audioUrl && state.wantSound;
    try {
      const jobs = [];
      if (wantVideo) {
        jobs.push((async () => {
          const res = await fetch(cfg.frameUrl + '?peer=' + encodeURIComponent(peerId) + '&t=' + Date.now(), {
            cache: 'no-store',
            credentials: 'same-origin',
          });
          if (res.ok && res.status !== 204) {
            const blob = await res.blob();
            if (blob && blob.size > 32) {
              const url = URL.createObjectURL(blob);
              const prev = state.blobUrls[peerId];
              state.blobUrls[peerId] = url;
              state.frames[peerId] = url;
              const tile = document.getElementById(tileId(peerId));
              const img = tile ? tile.querySelector('.live-tile-frame') : null;
              if (img) {
                img.onload = () => {
                  if (prev && prev !== url) {
                    try { URL.revokeObjectURL(prev); } catch (_) {}
                  }
                  applyTilePicture(peerId);
                };
                img.src = url;
                img.hidden = false;
              } else if (prev && prev !== url) {
                try { URL.revokeObjectURL(prev); } catch (_) {}
              }
              applyTilePicture(peerId);
            }
          } else if (res.status === 204 || res.status === 404) {
            if (state.frames[peerId]) {
              delete state.frames[peerId];
              applyTilePicture(peerId);
            }
          }
        })());
      }
      if (wantAudio) {
        jobs.push((async () => {
          const ares = await fetch(cfg.audioUrl + '?peer=' + encodeURIComponent(peerId) + '&t=' + Date.now(), {
            cache: 'no-store',
            credentials: 'same-origin',
          });
          const seq = Number(ares.headers.get('X-Live-Seq') || 0);
          if (ares.ok && ares.status !== 204 && seq && seq !== state.lastAudioSeq[peerId]) {
            state.lastAudioSeq[peerId] = seq;
            const buf = await ares.arrayBuffer();
            if (buf.byteLength >= 16) {
              playPcm(new Int16Array(buf), Number(ares.headers.get('X-Live-Rate') || AUDIO_RATE));
            }
          }
        })());
      }
      if (jobs.length) await Promise.all(jobs);
    } catch (_) {}
  };

  const pullMedia = async () => {
    if (state.pulling || state.leaving || !state.peerId) return;
    state.pulling = true;
    try {
      const peers = (state.lastPeers || []).filter((p) => p && p.peer_id !== state.peerId);
      await Promise.all(peers.map((p) => pullPeerMedia(p.peer_id)));
    } finally {
      state.pulling = false;
    }
  };

  const httpDelay = () => (remotesNeedHttp() || remotesNeedHttpAudio() ? FRAME_MS : 700);

  const armPush = () => {
    if (state.leaving) return;
    if (state.pushTimer) clearTimeout(state.pushTimer);
    state.pushTimer = setTimeout(async () => {
      await pushMedia();
      armPush();
    }, httpDelay());
  };

  const armPull = () => {
    if (state.leaving) return;
    if (state.pullTimer) clearTimeout(state.pullTimer);
    state.pullTimer = setTimeout(async () => {
      await pullMedia();
      armPull();
    }, httpDelay());
  };

  const startRelay = () => {
    hookLocalAudio(state.localStream);
    armPush();
    armPull();
    pushMedia();
    pullMedia();
  };

  const stopRelay = () => {
    if (state.pushTimer) {
      clearTimeout(state.pushTimer);
      state.pushTimer = null;
    }
    if (state.pullTimer) {
      clearTimeout(state.pullTimer);
      state.pullTimer = null;
    }
    Object.keys(state.blobUrls).forEach((id) => {
      try { URL.revokeObjectURL(state.blobUrls[id]); } catch (_) {}
    });
    state.blobUrls = {};
    state.frames = {};
  };

  const iceBusy = () => Object.values(state.pcs).some((pc) => {
    const ice = pc.iceConnectionState;
    const conn = pc.connectionState;
    return ice === 'new' || ice === 'checking' || ice === 'disconnected'
      || conn === 'new' || conn === 'connecting';
  });

  const stopLocalMedia = () => {
    stopRelay();
    try {
      if (state.localStream) {
        state.localStream.getTracks().forEach((t) => { try { t.stop(); } catch (_) {} });
      }
    } catch (_) {}
    try {
      if (state.screenStream) {
        state.screenStream.getTracks().forEach((t) => { try { t.stop(); } catch (_) {} });
      }
    } catch (_) {}
    Object.keys(state.pcs).forEach((id) => {
      try { state.pcs[id].close(); } catch (_) {}
    });
  };

  const stopPoll = () => {
    state.polling = false;
    if (state.pollTimer) {
      clearTimeout(state.pollTimer);
      state.pollTimer = null;
    }
  };

  const pollDelay = () => {
    if (state.panel === 'chat') return 280;
    const peers = (state.lastPeers || []).filter((p) => p && p.peer_id !== state.peerId);
    if (peers.length && peers.every((p) => pcLive(p.peer_id))) return 700;
    return 280;
  };

  const armPoll = () => {
    if (state.leaving || !state.polling) return;
    if (state.pollTimer) clearTimeout(state.pollTimer);
    state.pollTimer = setTimeout(() => { poll(); }, pollDelay());
  };

  const exitRoom = () => {
    if (state.leaving && state.leaveSent) {
      window.location.href = cfg.leave;
      return;
    }
    state.leaving = true;
    state.leaveSent = true;
    stopPoll();
    stopLocalMedia();
    window.location.href = cfg.leave;
  };

  const hangUp = (end) => {
    if (state.leaving) return;
    state.leaving = true;
    stopPoll();
    stopLocalMedia();
    const btn = document.querySelector('[data-ctl="leave"]');
    if (btn) {
      btn.disabled = true;
      const label = btn.querySelector('.live-ctl-label');
      const sub = btn.querySelector('.live-ctl-state');
      if (label) label.textContent = end ? 'Ending' : 'Leaving';
      if (sub) sub.textContent = '…';
    }
    const payload = JSON.stringify({ end: !!end, _csrf: csrf });
    try {
      fetch(cfg.leaveUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          Accept: 'application/json',
        },
        body: payload,
        keepalive: true,
      }).catch(() => {});
    } catch (_) {}
    setTimeout(() => {
      state.leaveSent = true;
      window.location.href = cfg.leave;
    }, 250);
  };

  const poll = async () => {
    if (state.leaving || !state.polling) return;
    if (state.pollInFlight) {
      armPoll();
      return;
    }
    state.pollInFlight = true;
    try {
      const data = await api(cfg.stateUrl + '?after_chat=' + state.lastChat);
      if (state.leaving) return;
      if (data.class && data.class.status === 'ended') {
        exitRoom();
        return;
      }
      (data.messages || []).forEach(appendChat);
      for (const sig of data.signals || []) {
        try {
          await handleSignal(sig);
        } catch (_) {}
      }
      await syncPeers(data.peers || []);
    } catch (err) {
      if (state.leaving) return;
      if (String(err.message).includes('not live') || String(err.message).includes('ended')) {
        exitRoom();
      }
    } finally {
      state.pollInFlight = false;
      if (!state.leaving) armPoll();
    }
  };

  const isPhone = () => {
    const ua = navigator.userAgent || '';
    return window.matchMedia('(max-width: 900px)').matches
      || /Android|iPhone|iPod|iPad|Mobile/i.test(ua);
  };

  const audioConstraints = { echoCancellation: true, noiseSuppression: true, autoGainControl: true };
  const videoConstraints = () => (isPhone()
    ? { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 }, frameRate: { ideal: 30, max: 30 } }
    : { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 }, frameRate: { ideal: 30, max: 30 } });

  const showLobbyPreview = () => {
    const preview = document.getElementById('live-lobby-preview');
    const note = document.getElementById('live-preview-note');
    if (!preview) return;
    if (cameraTrack()) {
      preview.srcObject = state.localStream;
      preview.hidden = false;
      preview.muted = true;
      playEl(preview);
      if (note) note.textContent = 'This is your camera. Join when you are ready — you can turn it off inside the class.';
    } else {
      preview.hidden = true;
      if (note) note.textContent = state.audio
        ? 'Microphone is ready. You can join with mic only, then turn the camera on after you enter.'
        : 'Allow the camera and microphone when the browser asks.';
    }
  };

  const ensureMic = async () => {
    if (micTrack()) {
      micTrack().enabled = true;
      return true;
    }
    const extra = await navigator.mediaDevices.getUserMedia({ audio: audioConstraints, video: false });
    extra.getVideoTracks().forEach((t) => t.stop());
    const track = extra.getAudioTracks()[0];
    if (!track) return false;
    if (!state.localStream) state.localStream = extra;
    else state.localStream.addTrack(track);
    return true;
  };

  const ensureCam = async () => {
    if (cameraTrack()) {
      cameraTrack().enabled = true;
      return true;
    }
    const extra = await navigator.mediaDevices.getUserMedia({ audio: false, video: videoConstraints() });
    extra.getAudioTracks().forEach((t) => t.stop());
    const track = extra.getVideoTracks()[0];
    if (!track) return false;
    if (!state.localStream) state.localStream = extra;
    else state.localStream.addTrack(track);
    return true;
  };

  const warmMedia = async () => {
    const note = document.getElementById('live-preview-note');
    if (!isSecurePage) {
      if (note) note.textContent = 'Camera and microphone need HTTPS. Open this class on the https:// website address, then join.';
      if (lobbyError) {
        lobbyError.hidden = false;
        lobbyError.textContent = 'This page is not a secure HTTPS address, so the browser will block the camera and mic.';
      }
      return;
    }
    if (state.localStream) {
      showLobbyPreview();
      return;
    }
    try {
      state.localStream = await navigator.mediaDevices.getUserMedia({
        audio: audioConstraints,
        video: videoConstraints(),
      });
    } catch (_) {
      try {
        state.localStream = await navigator.mediaDevices.getUserMedia({
          audio: audioConstraints,
          video: false,
        });
      } catch (err) {
        const note = document.getElementById('live-preview-note');
        if (note) note.textContent = 'Allow the microphone (and camera) in the browser, then refresh. (' + err.message + ')';
        return;
      }
    }
    state.audio = !!micTrack();
    state.video = !!cameraTrack();
    showLobbyPreview();
  };

  const start = async (mode) => {
    if (lobbyError) lobbyError.hidden = true;
    if (!isSecurePage) {
      if (lobbyError) {
        lobbyError.hidden = false;
        lobbyError.textContent = 'Open this class on https:// so the camera and microphone can start.';
      }
      return;
    }
    const wantVideo = mode === 'av';
    try {
      await ensureMic();
      if (wantVideo) {
        try { await ensureCam(); } catch (err) {
          if (lobbyError) {
            lobbyError.hidden = false;
            lobbyError.textContent = 'Camera could not start. You can still join with mic only. (' + err.message + ')';
          }
        }
      } else {
        liveTracks(state.localStream, 'video').forEach((t) => {
          try { t.stop(); } catch (_) {}
          try { state.localStream.removeTrack(t); } catch (_) {}
        });
      }
    } catch (err) {
      if (lobbyError) {
        lobbyError.hidden = false;
        lobbyError.textContent = 'Allow the microphone in the browser, then try again. (' + err.message + ')';
      }
      return;
    }
    const audio = micTrack();
    if (audio) audio.enabled = true;
    state.audio = !!audio;
    state.video = wantVideo && !!cameraTrack();
    if (!state.audio) {
      if (lobbyError) {
        lobbyError.hidden = false;
        lobbyError.textContent = 'No microphone was found. Check the browser permission and try again.';
      }
      return;
    }
    setCtl('mic', state.audio);
    setCtl('cam', state.video);
    const joined = await api(cfg.joinUrl, { audio: state.audio, video: state.video });
    state.peerId = joined.peer_id;
    const you = joined.you || {};
    setShareVisible(cfg.role === 'host' || Number(you.is_presenter) === 1);
    lobby.hidden = true;
    stage.hidden = false;
    dock.hidden = false;
    document.body.classList.add('is-in-room');
    root.classList.add('is-in-room');
    ensureTile(state.peerId, {
      display_name: cfg.name,
      role: cfg.role,
      avatar: cfg.avatar,
      audio_on: state.audio ? 1 : 0,
      video_on: state.video ? 1 : 0,
      screen_on: 0,
      is_presenter: cfg.role === 'host' ? 1 : 0,
      hand_raised: 0,
    });
    await syncPeers(joined.peers || []);
    state.polling = true;
    poll();
    startClock();
    startRelay();
  };

  const unlockAudio = () => {
    state.wantSound = true;
    const ctx = ensureAudioContext();
    hookLocalAudio(state.localStream);
    Object.keys(state.streams).forEach((id) => bindRemote(id));
    if (ctx && ctx.state !== 'suspended') showSoundBar(false);
  };

  document.querySelectorAll('[data-join]').forEach((btn) => {
    btn.addEventListener('click', () => {
      unlockAudio();
      start(btn.getAttribute('data-join')).catch((err) => {
        lobbyError.hidden = false;
        lobbyError.textContent = err.message;
      });
    });
  });

  document.getElementById('live-chat-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const input = document.getElementById('live-chat-input');
    const body = input.value.trim();
    if (!body) return;
    input.value = '';
    const pending = paintChatLine({ display_name: cfg.name, body, peer_id: state.peerId });
    try {
      const data = await api(cfg.chatUrl, { body });
      if (pending) pending.remove();
      if (data.message) appendChat(data.message);
      if (state.polling && !state.pollInFlight) poll();
    } catch (err) {
      if (pending) pending.remove();
      input.value = body;
      window.alert(err.message);
    }
  });

  soundBar?.addEventListener('click', () => {
    unlockAudio();
    Object.keys(state.streams).forEach((id) => bindRemote(id));
    showSoundBar(false);
  });
  document.getElementById('live-enable-sound')?.addEventListener('click', (e) => {
    e.stopPropagation();
    unlockAudio();
    Object.keys(state.streams).forEach((id) => bindRemote(id));
    showSoundBar(false);
  });

  const setCamera = async (on) => {
    if (!state.localStream) return false;
    if (on) {
      let track = cameraTrack();
      if (!track) {
        try {
          const extra = await navigator.mediaDevices.getUserMedia({
            video: videoConstraints(),
          });
          extra.getAudioTracks().forEach((t) => t.stop());
          track = extra.getVideoTracks()[0];
          if (!track) throw new Error('No camera track');
          state.localStream.addTrack(track);
        } catch (err) {
          window.alert('Allow the camera in the browser, then try again. (' + (err.message || err) + ')');
          return false;
        }
      }
      track.enabled = true;
      state.video = true;
    } else {
      liveTracks(state.localStream, 'video').forEach((t) => {
        try { t.stop(); } catch (_) {}
        try { state.localStream.removeTrack(t); } catch (_) {}
      });
      state.video = false;
    }
    setCtl('cam', state.video);
    bindLocal();
    await applyLocalTracksAll(true);
    api(cfg.mediaUrl, { audio: state.audio, video: state.video, screen: state.screen }).catch(() => {});
    return true;
  };

  const setMic = async (on) => {
    if (!state.localStream) return false;
    if (on) {
      let track = micTrack();
      if (!track) {
        try {
          const extra = await navigator.mediaDevices.getUserMedia({
            audio: { echoCancellation: true, noiseSuppression: true, autoGainControl: true },
          });
          extra.getVideoTracks().forEach((t) => t.stop());
          track = extra.getAudioTracks()[0];
          if (!track) throw new Error('No microphone track');
          state.localStream.addTrack(track);
        } catch (err) {
          window.alert('Allow the microphone in the browser, then try again. (' + (err.message || err) + ')');
          return false;
        }
      }
      track.enabled = true;
      state.audio = true;
      hookLocalAudio(state.localStream);
    } else {
      const track = micTrack();
      if (track) track.enabled = false;
      state.audio = false;
      state.audioBuf = [];
    }
    setCtl('mic', state.audio);
    await applyLocalTracksAll(false);
    api(cfg.mediaUrl, { audio: state.audio, video: state.video, screen: state.screen }).catch(() => {});
    return true;
  };

  const setPanel = (which) => {
    if (which && state.panel === which) which = '';
    state.panel = which || '';
    root.classList.toggle('is-side-open', !!state.panel);
    root.classList.toggle('is-people-open', state.panel === 'people');
    root.classList.toggle('is-chat-open', state.panel === 'chat');
    if (panelPeople) panelPeople.hidden = state.panel !== 'people';
    if (panelChat) panelChat.hidden = state.panel !== 'chat';
    const peopleBtn = document.querySelector('[data-ctl="people"].live-ctl');
    const chatBtn = document.querySelector('[data-ctl="chat"].live-ctl');
    if (peopleBtn) {
      peopleBtn.classList.toggle('is-active', state.panel === 'people');
      peopleBtn.setAttribute('aria-pressed', state.panel === 'people' ? 'true' : 'false');
    }
    if (chatBtn) {
      chatBtn.classList.toggle('is-active', state.panel === 'chat');
      chatBtn.setAttribute('aria-pressed', state.panel === 'chat' ? 'true' : 'false');
      const stateEl = chatBtn.querySelector('.live-ctl-state');
      if (stateEl) stateEl.textContent = state.panel === 'chat' ? 'Hide' : 'Open';
    }
    if (state.panel === 'chat') {
      state.unread = 0;
      if (chatBadge) {
        chatBadge.hidden = true;
        chatBadge.textContent = '0';
      }
      const input = document.getElementById('live-chat-input');
      if (input) input.focus();
      if (state.polling) poll();
    }
    layoutGrid();
  };

  const fsElement = () => document.fullscreenElement || document.webkitFullscreenElement || null;

  const unlockOrientation = () => {
    root.classList.remove('is-fs-rotate');
    try {
      if (screen.orientation && screen.orientation.unlock) screen.orientation.unlock();
    } catch (_) {}
  };

  const lockLandscape = async () => {
    try {
      if (screen.orientation && screen.orientation.lock) {
        await screen.orientation.lock('landscape');
        root.classList.remove('is-fs-rotate');
        return;
      }
    } catch (_) {}
    try {
      if (screen.orientation && screen.orientation.lock) {
        await screen.orientation.lock('landscape-primary');
        root.classList.remove('is-fs-rotate');
        return;
      }
    } catch (_) {}
    if (isPhone() && window.matchMedia('(orientation: portrait)').matches) {
      root.classList.add('is-fs-rotate');
    }
  };

  const enterClassFullscreen = async () => {
    const el = root;
    try {
      if (el.requestFullscreen) {
        try {
          await el.requestFullscreen({ navigationUI: 'hide' });
        } catch (_) {
          await el.requestFullscreen();
        }
      } else if (el.webkitRequestFullscreen) {
        el.webkitRequestFullscreen();
      }
      await lockLandscape();
    } catch (_) {
      if (isPhone()) root.classList.add('is-fs-rotate');
    }
  };

  const exitClassFullscreen = async () => {
    try {
      if (fsElement() && document.exitFullscreen) await document.exitFullscreen();
      else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
    } catch (_) {}
    unlockOrientation();
  };

  const toggleClassFullscreen = () => {
    if (fsElement()) exitClassFullscreen();
    else enterClassFullscreen();
  };

  document.addEventListener('fullscreenchange', () => {
    if (!fsElement()) unlockOrientation();
    else lockLandscape();
    layoutGrid();
  });
  document.addEventListener('webkitfullscreenchange', () => {
    if (!fsElement()) unlockOrientation();
    layoutGrid();
  });

  const setCtl = (ctl, on) => {
    const btn = document.querySelector('[data-ctl="' + ctl + '"]');
    if (!btn) return;
    btn.classList.toggle('is-on', !!on);
    btn.classList.toggle('is-off', !on);
    btn.setAttribute('aria-pressed', on ? 'true' : 'false');
    const stateEl = btn.querySelector('.live-ctl-state');
    if (stateEl && (ctl === 'mic' || ctl === 'cam' || ctl === 'hand' || ctl === 'screen')) {
      const labels = {
        mic: ['Off', 'On'],
        cam: ['Off', 'On'],
        hand: ['Down', 'Raised'],
        screen: ['Share', 'Stop'],
      };
      const pair = labels[ctl];
      stateEl.textContent = on ? pair[1] : pair[0];
    }
  };

  const startClock = () => {
    const el = document.getElementById('live-elapsed');
    if (!el || state.clockStarted) return;
    state.clockStarted = Date.now();
    const tick = () => {
      const s = Math.floor((Date.now() - state.clockStarted) / 1000);
      const h = Math.floor(s / 3600);
      const m = String(Math.floor((s % 3600) / 60)).padStart(2, '0');
      const sec = String(s % 60).padStart(2, '0');
      el.textContent = h ? h + ':' + m + ':' + sec : m + ':' + sec;
    };
    tick();
    setInterval(tick, 1000);
  };

  const shareNotSupportedMessage = () => {
    const ua = navigator.userAgent || '';
    if (/iPad|iPhone|iPod/.test(ua)) {
      return 'On iPhone/iPad, screen share works in Safari 17 or newer: pick a Tab or Window. Chrome on iPhone cannot share a screen — use Safari, or share from a laptop.';
    }
    if (/Android/i.test(ua)) {
      return 'On Android, open this class in Chrome and allow “Share screen” or “A tab” when asked. Some in-app browsers (WhatsApp, Instagram) cannot share a screen.';
    }
    return 'This browser cannot share a screen. Use Chrome, Edge, or Safari on a phone or computer.';
  };

  const stopScreenShare = async (report) => {
    if (state.screenTrack) {
      try { state.screenTrack.stop(); } catch (_) {}
    }
    if (state.screenStream) {
      state.screenStream.getTracks().forEach((t) => {
        try { t.stop(); } catch (_) {}
      });
    }
    state.screenTrack = null;
    state.screenStream = null;
    state.screen = false;
    setCtl('screen', false);
    bindLocal();
    await applyLocalTracksAll(true);
    layoutGrid();
    if (report !== false) {
      api(cfg.mediaUrl, { audio: state.audio, video: state.video, screen: false }).catch(() => {});
    }
  };

  const startScreenShare = async () => {
    if (!state.canShare) {
      window.alert('Only the teacher, or a student the teacher makes host, can share the screen.');
      return;
    }
    if (!navigator.mediaDevices || typeof navigator.mediaDevices.getDisplayMedia !== 'function') {
      window.alert(shareNotSupportedMessage());
      return;
    }
    let stream;
    const mobileShare = isPhone();
    const shareOpts = mobileShare
      ? { video: true, audio: false }
      : {
          video: {
            cursor: 'always',
            frameRate: { ideal: 15, max: 30 },
            width: { ideal: 1920, max: 1920 },
            height: { ideal: 1080, max: 1080 },
          },
          audio: false,
          selfBrowserSurface: 'include',
          surfaceSwitching: 'include',
          systemAudio: 'exclude',
        };
    try {
      stream = await navigator.mediaDevices.getDisplayMedia(shareOpts);
    } catch (err) {
      if (err && (err.name === 'NotAllowedError' || err.name === 'AbortError')) {
        return;
      }
      if (!mobileShare) {
        try {
          stream = await navigator.mediaDevices.getDisplayMedia({ video: true, audio: false });
        } catch (err2) {
          if (err2 && (err2.name === 'NotAllowedError' || err2.name === 'AbortError')) return;
          window.alert(shareNotSupportedMessage() + (err2 && err2.message ? ' (' + err2.message + ')' : ''));
          return;
        }
      } else {
        window.alert(shareNotSupportedMessage() + (err && err.message ? ' (' + err.message + ')' : ''));
        return;
      }
    }
    const track = stream.getVideoTracks()[0];
    if (!track) {
      window.alert('No screen was returned. Try Chrome or Edge on a computer.');
      return;
    }
    try { track.contentHint = 'detail'; } catch (_) {}
    if (state.screenTrack) {
      await stopScreenShare(false);
    }
    state.screenStream = stream;
    state.screenTrack = track;
    state.screen = true;
    setCtl('screen', true);
    bindLocal();
    await applyLocalTracksAll(true);
    layoutGrid();
    try {
      await api(cfg.mediaUrl, { audio: state.audio, video: state.video, screen: true });
    } catch (err) {
      await stopScreenShare(false);
      window.alert(err.message);
      return;
    }
    track.onended = () => {
      stopScreenShare(true);
    };
  };

  root.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-ctl]');
    if (!btn) return;
    const ctl = btn.getAttribute('data-ctl');
    if (ctl === 'chat') {
      setPanel('chat');
      return;
    }
    if (ctl === 'people') {
      setPanel('people');
      return;
    }
    if (ctl === 'close-panel') {
      setPanel('');
      return;
    }
    if (ctl === 'mic') {
      if (state.mediaBusy) return;
      state.mediaBusy = true;
      setMic(!state.audio).finally(() => {
        hookLocalAudio(state.localStream);
        state.mediaBusy = false;
      });
      return;
    }
    if (ctl === 'cam') {
      if (state.mediaBusy) return;
      state.mediaBusy = true;
      setCamera(!state.video).finally(() => { state.mediaBusy = false; });
      return;
    }
    if (ctl === 'hand') {
      state.hand = !state.hand;
      setCtl('hand', state.hand);
      api(cfg.handUrl, { raised: state.hand }).catch(() => {});
    }
    if (ctl === 'mute-all') {
      if (!cfg.muteAllUrl || cfg.role !== 'host') return;
      if (state.mediaBusy) return;
      state.mediaBusy = true;
      api(cfg.muteAllUrl, {}).then(() => {
        state.peopleSig = '';
        if (state.polling) poll();
      }).catch((err) => {
        window.alert(err.message || 'Could not mute everyone.');
      }).finally(() => {
        state.mediaBusy = false;
      });
      return;
    }
    if (ctl === 'fs') {
      toggleClassFullscreen();
      return;
    }
    if (ctl === 'screen') {
      if (state.screen) {
        await stopScreenShare(true);
      } else {
        await startScreenShare();
      }
    }
    if (ctl === 'leave') {
      const end = cfg.role === 'host';
      if (end && !window.confirm('End this live class for everyone? The room will stay closed until you start a new class.')) {
        return;
      }
      hangUp(end);
    }
  });

  window.addEventListener('resize', layoutGrid);
  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && state.panel) {
      setPanel('');
    }
  });
  warmMedia();
})();
