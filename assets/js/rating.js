/* ============================================================
 * rating.js — تقييم الموقع بـ3 أوجه (راضٍ/محايد/غير راضٍ).
 * يجلب الحالة الحالية، ويسجّل صوتاً واحداً عبر api/rate.php (CSRF بالترويسة).
 * يعمل مع كاش الصفحات (JS يُنفَّذ في كل تحميل). كل النصوص من data-attributes.
 * ============================================================ */
'use strict';

(function () {
  var box = document.getElementById('footerRate');
  if (!box) return;

  var api = box.getAttribute('data-api');
  if (!api) return;

  var faces  = box.querySelectorAll('[data-face]');
  var result = document.getElementById('rateResult');
  var THANKS = box.getAttribute('data-thanks') || 'Thanks!';
  var SATIS  = box.getAttribute('data-satisfied') || 'satisfied';
  var csrf   = '';

  function showResult(pct) {
    if (!result) return;
    result.hidden = false;
    result.textContent = THANKS + ' — ' + pct + '% ' + SATIS;
  }

  function lock() {
    box.classList.add('is-voted');
    faces.forEach(function (b) { b.disabled = true; });
  }

  function load() {
    fetch(api + '?action=current', { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (!d || !d.ok) return;
        csrf = d.csrf || '';
        if (d.voted) { lock(); showResult(d.pct); }
      })
      .catch(function () {});
  }

  function vote(face) {
    fetch(api, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF': csrf },
      body: JSON.stringify({ action: 'vote', face: face })
    })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (d && d.ok) { lock(); showResult(d.pct); }
      })
      .catch(function () {});
  }

  faces.forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (box.classList.contains('is-voted')) return;
      lock();                          // قفل تفاؤلي فوري
      vote(btn.getAttribute('data-face'));
    });
  });

  load();
})();
