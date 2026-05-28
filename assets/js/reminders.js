/* ============================================================
 * reminders.js — تذكيرات محلية ببدء المباريات (بلا خادم/دفع).
 * ------------------------------------------------------------
 * المستخدم يضغط «ذكّرني» على بطاقة مباراة قادمة، فنطلب إذن الإشعارات،
 * ونحفظ اختياره محلياً (localStorage)، ونُجدول إشعارين محليين:
 *   • قبل البدء بـ REMIND_BEFORE دقيقة.
 *   • عند البدء تماماً.
 * نُعيد الجدولة عند كل تحميل صفحة (للمباريات المستقبلية فقط) ونمنع التكرار
 * عبر علامات «أُطلِق» المخزّنة. الإشعار يُعرَض عبر تسجيل الـService Worker
 * إن توفّر (يسمح بفتح صفحة المباراة عند النقر)، وإلا عبر Notification مباشرةً.
 *
 * قيد معروف: التذكير المحلي يعمل بموثوقية عندما يكون الموقع/التطبيق قد فُتح
 * مؤخراً (المؤقّتات تعيش داخل الصفحة). ليس بديلاً عن دفع الخادم للموقع المغلق.
 * ============================================================ */
'use strict';

(function () {
  if (!('Notification' in window) || !window.localStorage) return;

  var STORE = 'wc_reminders';
  var CFG = window.WC_REMIND || {};
  var BEFORE_MIN = (typeof CFG.beforeMin === 'number') ? CFG.beforeMin : 10;
  var ICON = CFG.icon || '';
  var TXT = {
    soon:    CFG.soon    || 'تبدأ قريباً',
    start:   CFG.start   || 'بدأت الآن',
    on:      CFG.on      || 'سيتم تذكيرك',
    blocked: CFG.blocked || 'الإشعارات محظورة في المتصفح'
  };

  var timers = [];

  function load() {
    try { return JSON.parse(localStorage.getItem(STORE) || '{}') || {}; }
    catch (e) { return {}; }
  }
  function save(map) {
    try { localStorage.setItem(STORE, JSON.stringify(map)); } catch (e) {}
  }

  /** يعرض إشعاراً (عبر SW إن أمكن لدعم النقر، وإلا مباشرةً). */
  function notify(title, body, url, tag) {
    var opts = { body: body, icon: ICON, badge: ICON, tag: tag, data: { url: url } };
    if (navigator.serviceWorker && navigator.serviceWorker.ready) {
      navigator.serviceWorker.ready
        .then(function (reg) { reg.showNotification(title, opts); })
        .catch(function () { try { new Notification(title, opts); } catch (e) {} });
    } else {
      try { new Notification(title, opts); } catch (e) {}
    }
  }

  /** يطلق مرحلة تذكير ويعلّمها كمُطلَقة في التخزين. */
  function fire(id, stage, title, body, url) {
    var map = load();
    if (!map[id]) return;
    map[id].fired = map[id].fired || {};
    if (map[id].fired[stage]) return;     // أُطلِق سابقاً
    map[id].fired[stage] = true;
    save(map);
    notify(title, body, url, 'wc-match-' + id + '-' + stage);
  }

  /** يلغي كل المؤقّتات الحالية. */
  function clearTimers() {
    for (var i = 0; i < timers.length; i++) clearTimeout(timers[i]);
    timers = [];
  }

  /** يعيد جدولة كل التذكيرات المخزّنة (مستقبلية فقط)، ويُنظّف القديمة. */
  function reschedule() {
    clearTimers();
    var map = load();
    var now = Date.now();
    var changed = false;

    Object.keys(map).forEach(function (id) {
      var r = map[id];
      if (!r || !r.ts) { delete map[id]; changed = true; return; }
      var startMs = r.ts * 1000;

      // نظّف المباريات المنتهية منذ أكثر من ساعتين
      if (startMs < now - 2 * 3600 * 1000) { delete map[id]; changed = true; return; }

      r.fired = r.fired || {};
      var stages = [
        { key: 'soon',  at: startMs - BEFORE_MIN * 60 * 1000,
          title: TXT.soon + ' — ' + r.teams,
          body: (CFG.soonBody || 'تبدأ خلال {n} دقيقة').replace('{n}', BEFORE_MIN) },
        { key: 'start', at: startMs,
          title: TXT.start + ' — ' + r.teams,
          body: CFG.startBody || 'انطلقت المباراة الآن ⚽' }
      ];

      stages.forEach(function (s) {
        if (r.fired[s.key]) return;
        var delay = s.at - now;
        if (delay <= 0) {
          // فات وقتها: أطلقها فوراً فقط إن كانت ضمن آخر ساعة (تفادي إشعارات قديمة)
          if (s.at > now - 3600 * 1000) fire(id, s.key, s.title, s.body, r.url);
        } else {
          timers.push(setTimeout(function () { fire(id, s.key, s.title, s.body, r.url); }, delay));
        }
      });
    });

    if (changed) save(map);
    refreshButtons();
  }

  /** يحدّث مظهر كل أزرار «ذكّرني» حسب حالة التخزين. */
  function refreshButtons() {
    var map = load();
    document.querySelectorAll('[data-remind]').forEach(function (btn) {
      var id = btn.getAttribute('data-id');
      var on = !!map[id];
      btn.setAttribute('aria-pressed', on ? 'true' : 'false');
      btn.classList.toggle('is-on', on);
      var txt = btn.querySelector('.mc-remind-txt');
      if (txt) txt.textContent = on ? TXT.on : (btn.getAttribute('data-label') || txt.textContent);
    });
  }

  function toggle(btn) {
    var id = btn.getAttribute('data-id');
    if (!id) return;
    var map = load();

    if (map[id]) {                 // إلغاء التذكير
      delete map[id];
      save(map);
      reschedule();
      return;
    }

    // تفعيل: اطلب الإذن أولاً
    function enable() {
      var fresh = load();
      fresh[id] = {
        ts: parseInt(btn.getAttribute('data-ts'), 10) || 0,
        teams: btn.getAttribute('data-teams') || '',
        url: btn.getAttribute('data-url') || '',
        fired: {}
      };
      save(fresh);
      reschedule();
    }

    if (Notification.permission === 'granted') {
      enable();
    } else if (Notification.permission === 'denied') {
      alert(TXT.blocked);
    } else {
      Notification.requestPermission().then(function (p) {
        if (p === 'granted') enable();
        else if (p === 'denied') alert(TXT.blocked);
      });
    }
  }

  function init() {
    document.querySelectorAll('[data-remind]').forEach(function (btn) {
      var txt = btn.querySelector('.mc-remind-txt');
      if (txt && !btn.getAttribute('data-label')) btn.setAttribute('data-label', txt.textContent);
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        toggle(btn);
      });
    });
    reschedule();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
