{{--
    Demo-only print viewer.

    Outside the demo, "print" hands the ticket to the staff mobile app, which
    pushes it to the clinic's Bluetooth printer — nothing appears on this
    screen. A demo visitor has neither, so the print endpoints answer with a
    preview URL instead and this overlay shows the paper that would have come
    out of the printer.

    Self-contained CSS on purpose: it renders over whichever clinic screen the
    visitor pressed print on, and the paper itself lives in the iframe.
--}}
@if (($demoContext ?? null) && $demoContext->isDemo())
    <div id="demo-print" dir="rtl" hidden>
        <div class="demo-print__backdrop" data-close></div>

        <div class="demo-print__panel" role="dialog" aria-modal="true" aria-labelledby="demo-print-title">
            <header class="demo-print__head">
                <div>
                    <h2 id="demo-print-title">🧾 هذه هي الورقة الخارجة من الطابعة</h2>
                    <p>في وضع التجربة لا توجد طابعة بلوتوث متصلة، فنعرض لك شكل المطبوع كما سيخرج فعلياً في عيادتك.</p>
                </div>
                <button type="button" class="demo-print__x" data-close aria-label="إغلاق">&times;</button>
            </header>

            <div class="demo-print__body">
                <iframe id="demo-print-frame" title="نموذج المطبوع"></iframe>
            </div>

            <footer class="demo-print__foot">
                <button type="button" class="demo-print__btn" id="demo-print-do">🖨️ اطبعها على طابعتك</button>
                <button type="button" class="demo-print__btn demo-print__btn--ghost" data-close>إغلاق</button>
            </footer>
        </div>
    </div>

    <style>
        #demo-print { position: fixed; inset: 0; z-index: 80; display: flex; align-items: center; justify-content: center; padding: 16px; }
        #demo-print[hidden] { display: none; }
        .demo-print__backdrop { position: absolute; inset: 0; background: rgba(15, 23, 42, .72); }
        .demo-print__panel {
            position: relative; display: flex; flex-direction: column;
            width: 100%; max-width: 430px; max-height: 96vh; min-height: min(560px, 88vh);
            transition: max-width .15s ease;
            background: #0f172a; color: #e2e8f0; border-radius: 16px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, .45); overflow: hidden;
            font-family: system-ui, "Segoe UI", sans-serif;
        }
        /* The A5 prescription sheet needs the room; a receipt roll does not. */
        #demo-print.demo-print--wide .demo-print__panel { max-width: 660px; }
        .demo-print__head { display: flex; align-items: flex-start; gap: 10px; padding: 14px 16px; border-bottom: 1px solid #1e293b; }
        .demo-print__head h2 { margin: 0; font-size: 15px; font-weight: 700; }
        .demo-print__head p { margin: 4px 0 0; font-size: 12px; line-height: 1.6; color: #94a3b8; }
        .demo-print__x { margin-inline-start: auto; background: none; border: 0; color: #94a3b8; font-size: 26px; line-height: 1; cursor: pointer; }
        .demo-print__x:hover { color: #fff; }
        /* The scroll container: the iframe is grown to its full document
           height below, so the roll scrolls here as one continuous strip
           rather than inside a second, nested scrollbar. */
        .demo-print__body { flex: 1 1 auto; min-height: 0; overflow-y: auto; overflow-x: hidden; background: #e2e8f0; -webkit-overflow-scrolling: touch; }
        .demo-print__body::-webkit-scrollbar { width: 10px; }
        .demo-print__body::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 5px; }
        #demo-print-frame { display: block; width: 100%; height: 420px; border: 0; }
        .demo-print__foot { display: flex; gap: 8px; padding: 12px 16px; border-top: 1px solid #1e293b; }
        .demo-print__btn { flex: 1; padding: 9px 12px; border: 0; border-radius: 10px; background: #10b981; color: #052e2b; font-size: 13px; font-weight: 700; cursor: pointer; }
        .demo-print__btn:hover { background: #34d399; }
        .demo-print__btn--ghost { background: #1e293b; color: #e2e8f0; }
        .demo-print__btn--ghost:hover { background: #334155; }
    </style>

    <script>
        (function () {
            var root = document.getElementById('demo-print');
            var frame = document.getElementById('demo-print-frame');
            var body = root.querySelector('.demo-print__body');

            /**
             * Grow the iframe to the full height of the paper inside it, so the
             * modal body is what scrolls. Without this a long prescription
             * scrolls inside the frame instead, which reads as a cut-off page:
             * the roll should behave like one continuous strip.
             *
             * The preview is same-origin, so its height can simply be read.
             */
            function fitToContent() {
                try {
                    var doc = frame.contentDocument;

                    if (!doc || !doc.body) {
                        return;
                    }

                    // Collapse first, or the frame can never shrink back down
                    // when a shorter document replaces a taller one.
                    frame.style.height = '0px';
                    frame.style.height = Math.max(
                        doc.body.scrollHeight,
                        doc.documentElement.scrollHeight
                    ) + 'px';
                } catch (e) {
                    // Should not happen (same origin), but a preview that
                    // cannot be measured must still be readable.
                    frame.style.height = '70vh';
                }
            }

            frame.addEventListener('load', function () {
                fitToContent();
                // The logo and the QR are images: measure again once they have
                // decoded, or the first reading is short by their height.
                setTimeout(fitToContent, 200);
                body.scrollTop = 0;
            });

            window.addEventListener('resize', function () {
                if (!root.hidden) {
                    fitToContent();
                }
            });

            function close() {
                root.hidden = true;
                frame.src = 'about:blank';
                frame.style.height = '420px';
            }

            /**
             * Called by every print button in the clinic workspace once the
             * server answers with a preview URL instead of a push.
             */
            window.demoPrintPreview = function (url) {
                // The A5 sheet needs a wider panel than a thermal roll does.
                root.classList.toggle('demo-print--wide', url.indexOf('/print/sheet/') !== -1);
                frame.src = url;
                root.hidden = false;
            };

            /**
             * The A5 prescription opens as a normal link in a new tab. Inside a
             * demo there is no printer behind it either, so it goes to the same
             * viewer as the thermal prints rather than to a bare browser tab.
             */
            document.addEventListener('click', function (e) {
                var link = e.target.closest('a[href]');

                if (!link) {
                    return;
                }

                // /practice/prescriptions/{id}/print — not /pdf, not print-thermal.
                var match = link.getAttribute('href')
                    .match(/\/practice\/prescriptions\/(\d+)\/print(?:\?[^#]*)?$/);

                if (!match) {
                    return;
                }

                e.preventDefault();
                window.demoPrintPreview(@json(url('demo/print/sheet')) + '/' + match[1]);
            });

            root.addEventListener('click', function (e) {
                if (e.target.closest('[data-close]')) close();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !root.hidden) close();
            });

            // Let the visitor put it on a real (paper) printer if they have one.
            document.getElementById('demo-print-do').addEventListener('click', function () {
                frame.contentWindow.focus();
                frame.contentWindow.print();
            });
        })();
    </script>
@endif
