{{--
    Submits every form on the examination screen in the background instead of
    reloading the page.

    How it works, and why it needs no controller changes: the actions here all
    end in `back()`, so the POST's redirect lands on this same screen. fetch
    follows that redirect on its own, which hands us the freshly rendered page —
    including the flash banner, any validation errors and the repopulated
    old input. Swapping <main> for the new one is therefore exactly what a full
    reload would have produced, minus the reload: the scroll position, the open
    panels above the form, and anything typed elsewhere all survive.

    Scripts that bind to elements inside <main> re-register themselves through
    onExamineReady(); see the note there.
--}}
<script>
    (function () {
        var main = document.querySelector('main');
        if (!main) return;

        /**
         * Re-run the page's initialisers after a swap.
         *
         * Every registered function binds to elements inside <main>, which the
         * swap replaces — so re-running rebinds to the new nodes and the old
         * listeners die with the old nodes. Nothing here may bind to document
         * or window, or it would stack up on every submit.
         */
        function runReady() {
            (window.examineReady || []).forEach(function (fn) {
                try { fn(); } catch (e) { console.error(e); }
            });
        }

        function busy(form, on) {
            form.querySelectorAll('button[type="submit"], button:not([type])').forEach(function (b) {
                b.disabled = on;
            });
        }

        /** Swap in the freshly rendered screen and rebind everything. */
        function apply(html) {
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var fresh = doc.querySelector('main');
            if (!fresh) return false;

            main.innerHTML = fresh.innerHTML;
            runReady();

            // A flash or an error is the answer to what was just submitted, so
            // bring it into view; otherwise leave the page exactly where it was.
            var banner = main.querySelector('main > div.mb-4, .mb-4.rounded-lg');
            if (banner) banner.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            return true;
        }

        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (form.hasAttribute('data-no-ajax')) return;
            if (!main.contains(form)) return;

            e.preventDefault();
            busy(form, true);

            fetch(form.action, {
                method: (form.method || 'POST').toUpperCase(),
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                credentials: 'same-origin',
            })
                .then(function (r) {
                    return r.text().then(function (html) { return { html: html, url: r.url }; });
                })
                .then(function (res) {
                    // An action that sends the doctor somewhere else entirely is
                    // still a navigation — follow it rather than swapping.
                    if (!apply(res.html)) window.location.href = res.url;
                })
                .catch(function () {
                    // Offline or blocked: fall back to a real submit so the
                    // doctor is never left with a button that does nothing.
                    form.setAttribute('data-no-ajax', '1');
                    form.submit();
                })
                .finally(function () { busy(form, false); });
        });
    })();
</script>
