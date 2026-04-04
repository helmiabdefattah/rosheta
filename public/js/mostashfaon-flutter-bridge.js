/**
 * Mobile app WebView (Flutter): intercept web logout so the native shell can clear the session
 * and return to the login screen. Requires window.MostashfaOnFlutterWebView === true (injected by Flutter).
 */
(function () {
    'use strict';

    function isFlutterWebView() {
        return typeof window.MostashfaOnFlutterWebView !== 'undefined' && window.MostashfaOnFlutterWebView === true;
    }

    function flutterCallHandler() {
        return window.flutter_inappwebview && typeof window.flutter_inappwebview.callHandler === 'function';
    }

    function postToFlutter(payload) {
        if (!isFlutterWebView() || !flutterCallHandler()) {
            return;
        }
        try {
            window.flutter_inappwebview.callHandler('mostashfaon', JSON.stringify(payload));
        } catch (e) {
            console.warn('MostashfaOn Flutter bridge:', e);
        }
    }

    function wireLogoutForms() {
        document.querySelectorAll('form[action*="logout"]').forEach(function (form) {
            if (form.dataset.mostashfaonFlutterLogoutWired === '1') {
                return;
            }
            form.dataset.mostashfaonFlutterLogoutWired = '1';
            form.addEventListener(
                'submit',
                function (e) {
                    if (!isFlutterWebView() || !flutterCallHandler()) {
                        return;
                    }
                    e.preventDefault();
                    e.stopPropagation();
                    postToFlutter({ action: 'logout' });
                    return false;
                },
                true
            );
        });
    }

    document.addEventListener('DOMContentLoaded', wireLogoutForms);

    if (typeof MutationObserver !== 'undefined') {
        var mo = new MutationObserver(function () {
            wireLogoutForms();
        });
        mo.observe(document.documentElement, { childList: true, subtree: true });
    }
})();
