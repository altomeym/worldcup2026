/*!
 * analytics.js — أحداث dataLayer لـ Google Tag Manager (foot-boll.com).
 * يُحمَّل مبكراً؛ WCAnalytics متاح قبل predict.js و share.js.
 */
(function (global) {
  'use strict';

  function push(obj) {
    global.dataLayer = global.dataLayer || [];
    global.dataLayer.push(obj);
  }

  function event(name, params) {
    var o = { event: name };
    if (params) {
      for (var k in params) {
        if (Object.prototype.hasOwnProperty.call(params, k)) o[k] = params[k];
      }
    }
    push(o);
  }

  var WCAnalytics = {
    push: push,
    event: event,
    share: function (channel, url) {
      event('share', {
        share_channel: channel || 'unknown',
        share_url: url || (global.location && global.location.href) || ''
      });
    },
    contact: function (kind) {
      event('generate_lead', { lead_type: kind || 'contact' });
    },
    predict: function (action, matchId) {
      event('predict_' + action, { match_id: matchId != null ? matchId : null });
    },
    pwa: function (action) {
      event('pwa_' + action, {});
    },
    langSwitch: function (lang) {
      event('language_switch', { target_lang: lang || '' });
    },
    navBottom: function (href) {
      event('nav_bottom', { nav_target: href || '' });
    }
  };

  global.WCAnalytics = WCAnalytics;

  function bindDelegation() {
    global.document.addEventListener('click', function (ev) {
      var gtmEl = ev.target.closest && ev.target.closest('[data-gtm-event]');
      if (gtmEl) {
        var evtName = gtmEl.getAttribute('data-gtm-event');
        if (evtName) {
          var params = {};
          gtmEl.getAttributeNames().forEach(function (attr) {
            if (attr.indexOf('data-gtm-') !== 0 || attr === 'data-gtm-event') return;
            var key = attr.slice(9).replace(/-/g, '_');
            var val = gtmEl.getAttribute(attr);
            if (val !== null && val !== '') params[key] = val;
          });
          event(evtName, params);
        }
      }

      var langA = ev.target.closest && ev.target.closest('.lang-switch');
      if (langA) {
        WCAnalytics.langSwitch(langA.getAttribute('hreflang') || langA.getAttribute('lang') || '');
        return;
      }
      var bn = ev.target.closest && ev.target.closest('.fb-dock .fb-dock-link');
      if (bn && !bn.classList.contains('active')) {
        WCAnalytics.navBottom(bn.getAttribute('href') || '');
      }
    });
  }

  if (global.document) {
    if (global.document.readyState === 'loading') {
      global.document.addEventListener('DOMContentLoaded', bindDelegation);
    } else {
      bindDelegation();
    }
  }
})(typeof window !== 'undefined' ? window : this);
