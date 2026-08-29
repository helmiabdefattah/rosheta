{{--
    Holds a screen wake lock so a tablet left on a clinic screen never dims or
    sleeps.

    The lock is dropped by the browser whenever the tab is hidden (switching
    apps, the screen being turned off by hand), so it is re-taken on every
    return to visibility — without that it silently stops working after the
    first interruption.

    Requires a secure context: it works over HTTPS and on localhost, and is a
    no-op on plain HTTP. Android WebView has supported it since 84; for older
    shells the app itself has to hold the lock (Flutter: wakelock_plus).
--}}
<script>
    (function () {
        if (!('wakeLock' in navigator)) return;

        var lock = null;

        function acquire() {
            if (lock || document.visibilityState !== 'visible') return;

            navigator.wakeLock.request('screen').then(function (sentinel) {
                lock = sentinel;
                // Released by the browser, not by us — allow it to be re-taken.
                sentinel.addEventListener('release', function () { lock = null; });
            }).catch(function () {
                // Denied (insecure context, battery saver, no user activation):
                // the screen behaves as it did before, nothing else breaks.
            });
        }

        document.addEventListener('visibilitychange', acquire);
        // A gesture satisfies browsers that require activation before granting.
        // Not once-only: an early denial must not stop the next tap retrying,
        // and acquire() is a no-op once a lock is held.
        document.addEventListener('click', acquire);
        acquire();
    })();
</script>
