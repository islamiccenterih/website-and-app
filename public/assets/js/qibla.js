(function () {
  'use strict';

  var root = document.querySelector('[data-qibla-root]');
  if (!root) return;

  var statusEl = root.querySelector('[data-qibla-status]');
  var dial = root.querySelector('[data-qibla-dial]');
  var mark = root.querySelector('[data-qibla-mark]');
  var needle = root.querySelector('[data-qibla-needle]');
  var faceEl = root.querySelector('[data-qibla-face]');
  var faceHint = root.querySelector('[data-qibla-face-hint]');
  var cardEl = root.querySelector('[data-qibla-card]');
  var bearEl = root.querySelector('[data-qibla-bearing]');
  var kmEl = root.querySelector('[data-qibla-km]');
  var placeEl = root.querySelector('[data-qibla-place]');
  var invertBtn = root.querySelector('[data-qibla-invert]');
  var gate = root.querySelector('[data-qibla-gate]');
  var banner = root.querySelector('[data-qibla-banner]');
  var startWrap = root.querySelector('[data-qibla-start]');
  var startBtn = root.querySelector('[data-qibla-compass]');
  var startHint = root.querySelector('[data-qibla-start-hint]');
  var apiUrl = root.getAttribute('data-api') || '/api/qibla';
  var invertKey = 'ic_qibla_invert';

  var qibla = parseFloat(root.getAttribute('data-qibla') || '') || 0;
  var qiblaReady = false;
  var rawHeading = 0;
  var heading = 0;
  var headingReady = false;
  var compassOn = false;
  var listenersOn = false;
  var watchId = null;
  var lastAbsAt = 0;
  var lastEventAt = 0;
  var samples = 0;
  var waitTimer = 0;
  var locOk = false;
  var invert = 0;
  var lastTs = 0;
  var shownHeading = 0;
  var raf = 0;

  try {
    invert = Number(localStorage.getItem(invertKey) || 0) === 180 ? 180 : 0;
  } catch (err) {
    invert = 0;
  }

  var names = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
  var ua = navigator.userAgent || '';
  var isAndroid = /Android/i.test(ua);
  var isiOS = /iPad|iPhone|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

  function norm(deg) {
    deg = Number(deg);
    if (isNaN(deg)) return 0;
    return ((deg % 360) + 360) % 360;
  }

  function shortest(from, to) {
    return from + ((((to - from) + 540) % 360) - 180);
  }

  function cardinal(deg) {
    return names[Math.round(norm(deg) / 45) % 8];
  }

  function setStatus(text) {
    if (statusEl) statusEl.textContent = text;
  }

  function setBanner(text, kind) {
    if (!banner) return;
    if (!text) {
      banner.hidden = true;
      banner.textContent = '';
      banner.className = 'qibla-banner';
      return;
    }
    banner.hidden = false;
    banner.textContent = text;
    banner.className = 'qibla-banner' + (kind ? ' is-' + kind : '');
  }

  function inAppBrowser() {
    return /FBAN|FBAV|Instagram|Line\/|WhatsApp|Twitter|Snapchat|TikTok|MicroMessenger|FB_IAB|FBIOS/i.test(ua)
      || (/Android/i.test(ua) && /;\s*wv\)/.test(ua));
  }

  function deltaToQibla(head) {
    return ((qibla - head + 540) % 360) - 180;
  }

  function placeOnRose(el, deg) {
    if (el) el.style.transform = 'rotate(' + norm(deg) + 'deg)';
  }

  var KAABA_LAT = 21.422487;
  var KAABA_LNG = 39.826206;

  function qiblaBearing(lat, lng) {
    var p1 = lat * Math.PI / 180;
    var p2 = KAABA_LAT * Math.PI / 180;
    var dL = (KAABA_LNG - lng) * Math.PI / 180;
    var y = Math.sin(dL) * Math.cos(p2);
    var x = Math.cos(p1) * Math.sin(p2) - Math.sin(p1) * Math.cos(p2) * Math.cos(dL);
    return norm((Math.atan2(y, x) * 180) / Math.PI);
  }

  function kaabaKm(lat, lng) {
    var earth = 6371;
    var dP = (KAABA_LAT - lat) * Math.PI / 180;
    var dL = (KAABA_LNG - lng) * Math.PI / 180;
    var a = Math.sin(dP / 2) * Math.sin(dP / 2)
      + Math.cos(lat * Math.PI / 180) * Math.cos(KAABA_LAT * Math.PI / 180)
      * Math.sin(dL / 2) * Math.sin(dL / 2);
    return earth * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  }

  function advice(head) {
    if (!qiblaReady) {
      return 'Location milne ka intezaar — Kaaba tab aapki GPS se set hoga, Firozabad se nahi.';
    }
    var d = deltaToQibla(head);
    if (Math.abs(d) <= 5) return 'Aap Kaaba ki simt mein hain — You are facing the Kaaba.';
    if (d > 0) return 'Right / dayein ghumo ' + Math.round(d) + '° jab tak gold mark upar notch ke neeche na aa jaye.';
    return 'Left / baen ghumo ' + Math.round(-d) + '° jab tak gold mark upar notch ke neeche na aa jaye.';
  }

  function paintQibla(data) {
    if (data.qibla != null && !isNaN(Number(data.qibla))) {
      qibla = Number(data.qibla);
    }
    qiblaReady = true;
    locOk = true;
    root.setAttribute('data-qibla', String(qibla));
    root.className = (root.className.replace(/\bis-located\b/g, '') + ' is-located').replace(/\s+/g, ' ');
    if (mark) {
      mark.hidden = false;
      placeOnRose(mark, qibla);
    }
    if (needle) {
      needle.hidden = false;
      placeOnRose(needle, qibla);
    }
    if (bearEl) bearEl.textContent = qibla.toFixed(2) + '°';
    if (kmEl && data.distance_km != null) kmEl.textContent = Number(data.distance_km).toFixed(1) + ' km';
    if (cardEl && !headingReady) cardEl.textContent = data.compass || cardinal(qibla);
    if (headingReady) {
      setStatus(advice(heading));
    }
  }

  function paintDial(face) {
    face = norm(face);
    if (dial) dial.style.transform = 'rotate(' + (-face) + 'deg)';
    if (faceEl) faceEl.textContent = Math.round(face) + '°';
    if (faceHint) faceHint.textContent = 'You face / aap ka rukh';
    if (cardEl) cardEl.textContent = cardinal(face);
    var aligned = qiblaReady && Math.abs(deltaToQibla(face)) <= 5;
    if (root.classList.toggle) root.classList.toggle('is-aligned', aligned);
    else if (aligned) root.className += ' is-aligned';
  }

  function tick() {
    raf = 0;
    if (!headingReady) return;
    shownHeading += (shortest(shownHeading, heading) - shownHeading) * 0.82;
    paintDial(shownHeading);
    if (compassOn && samples > 1) setStatus(advice(shownHeading));
  }

  function applyHeading(value) {
    if (value == null || isNaN(Number(value))) return;
    rawHeading = norm(value);
    heading = norm(rawHeading + invert);
    headingReady = true;
    samples += 1;
    root.className = (root.className.replace(/\bis-live\b/g, '') + ' is-live').replace(/\s+/g, ' ');
    if (gate) gate.hidden = true;

    if (samples < 8) {
      shownHeading = heading;
      paintDial(heading);
      return;
    }
    if (typeof requestAnimationFrame === 'function') {
      if (!raf) raf = requestAnimationFrame(tick);
    } else {
      shownHeading = heading;
      paintDial(heading);
    }
  }

  function screenAngle() {
    if (window.screen && screen.orientation && typeof screen.orientation.angle === 'number') {
      return screen.orientation.angle;
    }
    if (typeof window.orientation === 'number') return window.orientation;
    return 0;
  }

  /**
   * iOS: webkitCompassHeading is already the heading of the top of the phone.
   * Android Chrome: alpha increases counter-clockwise, compass heading is 360 - alpha.
   * Do not mix AbsoluteOrientationSensor — it was swallowing real orientation events.
   */
  function headingFromEvent(event) {
    if (!event) return null;

    if (typeof event.webkitCompassHeading === 'number' && !isNaN(event.webkitCompassHeading)) {
      return norm(event.webkitCompassHeading);
    }

    var alpha = typeof event.alpha === 'number' ? event.alpha : null;
    if (alpha == null || isNaN(alpha)) return null;

    return norm(360 - alpha + screenAngle());
  }

  function onOrient(event) {
    if (!event) return;
    if (event.timeStamp && event.timeStamp === lastTs) return;
    lastTs = event.timeStamp || 0;

    var isAbs = event.type === 'deviceorientationabsolute' || event.absolute === true;
    var value = headingFromEvent(event);
    if (value == null) return;

    if (isAbs) lastAbsAt = Date.now();
    lastEventAt = Date.now();
    applyHeading(value);
  }

  function attachOrientation() {
    if (listenersOn) return;
    listenersOn = true;
    window.addEventListener('deviceorientationabsolute', onOrient, true);
    window.addEventListener('deviceorientation', onOrient, true);
    window.addEventListener('deviceorientationabsolute', onOrient, false);
    window.addEventListener('deviceorientation', onOrient, false);
    window.addEventListener('compassneedscalibration', function (event) {
      if (event && typeof event.preventDefault === 'function') event.preventDefault();
      setStatus('Compass calibrate karein — phone ko figure-8 mein ghumayein, metal se door.');
    }, true);
  }

  function load(lat, lng, label) {
    var params = 'lat=' + encodeURIComponent(String(lat)) + '&lng=' + encodeURIComponent(String(lng));
    var join = apiUrl.indexOf('?') >= 0 ? '&' : '?';
    return fetch(apiUrl + join + params, { headers: { Accept: 'application/json' } })
      .then(function (res) {
        if (!res.ok) throw new Error('qibla');
        return res.json();
      })
      .then(function (data) {
        paintQibla(data);
        if (placeEl && !placeEl.getAttribute('data-gps-label')) {
          placeEl.textContent = Number(data.lat).toFixed(4) + ', ' + Number(data.lng).toFixed(4);
        }
        return data;
      });
  }

  function applyGps(lat, lng, acc) {
    var bearing = qiblaBearing(lat, lng);
    var km = kaabaKm(lat, lng);
    var label = lat.toFixed(5) + ', ' + lng.toFixed(5) + (acc ? ' (±' + acc + ' m)' : '');
    if (placeEl) {
      placeEl.setAttribute('data-gps-label', '1');
      placeEl.textContent = 'Your GPS: ' + label;
    }
    paintQibla({
      qibla: bearing,
      distance_km: km,
      lat: lat,
      lng: lng,
      compass: cardinal(bearing)
    });
    setBanner('', '');
    if (acc && acc > 8000) {
      setStatus('Location rough hai (±' + acc + ' m). Khuli jagah mein khade hon — Qibla phir bhi aapki GPS se hai, Firozabad se nahi.');
    } else if (headingReady) {
      setStatus(advice(heading));
    } else {
      setStatus('Kaaba ab aapki GPS se set hai (' + bearing.toFixed(1) + '°). Phone flat rakhein, gold mark ko notch ke neeche laayein.');
    }
    load(lat, lng, label).catch(function () {});
  }

  function onPosition(pos) {
    if (!pos || !pos.coords) return;
    var lat = pos.coords.latitude;
    var lng = pos.coords.longitude;
    if (!isFinite(lat) || !isFinite(lng)) return;
    var acc = pos.coords.accuracy ? Math.round(pos.coords.accuracy) : null;
    applyGps(lat, lng, acc);
  }

  function geoError(err) {
    var code = err && err.code;
    if (code === 1) {
      setBanner('Location Allow nahi hua. Chrome → site settings → Location Allow karein. Bina GPS ke Kaaba nahi dikhega — Firozabad ka rukh aapka Qibla nahi hai.', 'warn');
      setStatus('Location allow karein, phir Start compass ya Use my location dabayein. Jab tak GPS na mile, Kaaba mark nahi dikhega.');
      return;
    }
    if (code === 3) {
      setStatus('Location timeout. Bahar / khidki ke paas khade hon, GPS on rakhein, phir Use my location.');
      return;
    }
    setStatus('Location nahi mili. Phone ka GPS on karein, phir Use my location. Bina aapki jagah ke Kaaba nahi dikhaya jaayega.');
  }

  function locate(watch) {
    if (!navigator.geolocation) {
      setBanner('Is browser mein GPS nahi hai. Chrome mein kholen. Firozabad ka Qibla aap par apply nahi hota.', 'warn');
      setStatus('Location API nahi hai — Kaaba aapki jagah se nahi dikh sakta.');
      return;
    }
    if (!window.isSecureContext) {
      setStatus('Location ke liye HTTPS chahiye. Phone par https link kholen, phir Start compass.');
      return;
    }
    setStatus('Aapki GPS dhundh rahe hain — Kaaba aapki jagah se set hoga…');
    var fine = { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 };
    var coarse = { enableHighAccuracy: false, timeout: 18000, maximumAge: 60000 };
    navigator.geolocation.getCurrentPosition(onPosition, function (err) {
      if (err && err.code === 1) {
        geoError(err);
        return;
      }
      navigator.geolocation.getCurrentPosition(onPosition, geoError, coarse);
    }, fine);
    if (watch && watchId == null) {
      watchId = navigator.geolocation.watchPosition(onPosition, function () {}, fine);
    }
  }

  function startCompass() {
    var Doe = window.DeviceOrientationEvent;
    if (!Doe) {
      setStatus('Is browser mein compass nahi hai. Chrome ya Samsung Internet mein kholen (WhatsApp/Facebook nahi).');
      setBanner('Phone par Chrome mein kholen — desktop ya in-app browser mein compass ka magnetometer nahi hota.', 'warn');
      return;
    }
    if (!window.isSecureContext) {
      setStatus('Compass ke liye HTTPS chahiye. Share kiye gaye https link se kholen, phir yahan tap karein.');
      return;
    }
    if (inAppBrowser()) {
      setBanner('WhatsApp / Instagram / Facebook ke andar compass band rehta hai. Menu → Open in Chrome / Browser se kholen.', 'warn');
    }

    function afterPermission(ok) {
      if (!ok && isiOS) {
        setStatus('iPhone par Safari mein Motion & Orientation allow karein, phir dubara tap karein.');
        return;
      }
      attachOrientation();
      compassOn = true;
      samples = 0;
      headingReady = false;
      lastAbsAt = 0;
      if (startWrap) {
        startWrap.className = (startWrap.className.replace(/\bis-on\b/g, '') + ' is-on').replace(/\s+/g, ' ');
      }
      if (startBtn) startBtn.textContent = 'Compass on — tap to restart';
      if (startHint) startHint.textContent = 'Phone flat rakhein aur ghumayein. Beech ka number change hona chahiye.';
      if (faceHint) faceHint.textContent = 'Waiting / intezaar';
      setStatus(isAndroid
        ? 'Phone flat rakhein aur ghumayein. Upar wala number change hona chahiye. Agar ulta ghumey to Reverse compass dabayein.'
        : 'Phone flat rakhein. Upar notch aapki direction hai — Kaaba mark ko uske neeche laayein.');
      clearTimeout(waitTimer);
      waitTimer = window.setTimeout(function () {
        if (samples < 2) {
          setBanner('Phone ne compass data nahi bheja. Chrome / Samsung Internet kholen, Settings → Site settings → Sensors + Location allow karein, figure-8 ghumayein.', 'warn');
          setStatus(isAndroid
            ? 'Abhi koi compass reading nahi aayi. Chrome mein kholen, Sensors allow karein, phone ko figure-8 mein ghumayein, phir Start compass.'
            : 'Abhi reading nahi aayi. iPhone par Safari use karein aur Motion allow karein.');
        }
      }, 3500);
    }

    if (Doe && typeof Doe.requestPermission === 'function') {
      try {
        var p = Doe.requestPermission();
        if (p && typeof p.then === 'function') {
          p.then(function (state) {
            afterPermission(state !== 'denied');
          }).catch(function () {
            afterPermission(false);
          });
          return;
        }
      } catch (err) {
        afterPermission(true);
        return;
      }
    }
    afterPermission(true);
  }

  function unlock() {
    locate(true);
    startCompass();
  }

  function toggleInvert() {
    invert = invert === 180 ? 0 : 180;
    try { localStorage.setItem(invertKey, String(invert)); } catch (err) { /* private mode */ }
    if (headingReady) applyHeading(rawHeading);
    setStatus(invert === 180
      ? 'Compass reverse ho gaya. Agar ab bhi ulta lage to figure-8 calibrate karein.'
      : 'Compass default direction par wapas.');
  }

  function on(el, fn) {
    if (!el) return;
    el.addEventListener('click', fn);
  }

  if (qibla) {
    paintQibla({
      qibla: qibla,
      distance_km: parseFloat(root.getAttribute('data-qibla-km') || '') || null,
      compass: root.getAttribute('data-qibla-compass-name') || '',
      label: root.getAttribute('data-qibla-label') || '',
    });
    locOk = false;
    if (placeEl && root.getAttribute('data-qibla-label')) {
      placeEl.textContent = root.getAttribute('data-qibla-label') + ' (center). Phone GPS se apna Qibla set karein.';
    }
  }

  on(root.querySelector('[data-qibla-locate]'), function () { locate(true); });
  on(startBtn, unlock);
  on(invertBtn, toggleInvert);
  on(gate, unlock);
  on(root.querySelector('.qibla-bezel'), function () {
    if (!compassOn) unlock();
  });

  if (inAppBrowser()) {
    setBanner('Yeh WhatsApp/Facebook ke andar khula hai — compass yahan kaam nahi karta. Chrome mein Open in browser karein.', 'warn');
  } else if (!window.isSecureContext) {
    setBanner('HTTPS link se kholen, warna phone compass nahi dega.', 'warn');
  } else if (!isiOS && !(window.matchMedia && window.matchMedia('(pointer: coarse)').matches) && !isAndroid) {
    setBanner('Qibla compass phone par chalega. Is computer mein magnetometer nahi hai — Chrome se phone par kholen.', 'info');
  }

})();
