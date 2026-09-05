<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نجهّز رحلتك - Mostashfa-on</title>

    {{--
        No Tailwind CDN and no render-blocking font here, unlike the rest of
        the demo's standalone pages. This is the one screen whose entire job is
        to appear IMMEDIATELY: measured against the CDN it sat blank for about
        a second and a half before the first paint, which is a third of the
        wait it exists to cover — and on a network that cannot reach the CDN at
        all, it would stay blank for the whole build. Everything it needs is
        below, in the document.

        Cairo is still requested, but asynchronously: it swaps in if it arrives
        and is never waited on.
    --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" media="print" onload="this.media='all'"
          href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;800&display=swap">

    <style>
        :root {
            --ink: #0f172a;
            --muted: #64748b;
            --faint: #94a3b8;
            --line: #d1fae5;
            --brand: #10b981;
            --brand-2: #14b8a6;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 16px;
            background: linear-gradient(135deg, #f8fafc 0%, #f0f9ff 50%, #ecfdf5 100%);
            font-family: Cairo, 'Segoe UI', Tahoma, system-ui, sans-serif;
            color: var(--ink);
            text-align: center;
        }

        .card {
            width: 100%;
            max-width: 32rem;
            background: rgba(255, 255, 255, .9);
            border: 1px solid var(--line);
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, .08), 0 8px 10px -6px rgba(0, 0, 0, .05);
            padding: 40px 32px;
        }

        .spinner {
            position: relative;
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
        }

        .spinner .ring,
        .spinner .arc {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 4px solid var(--line);
        }

        .spinner .arc {
            border-color: transparent;
            border-top-color: var(--brand);
            border-left-color: var(--brand-2);
            animation: spin 1s linear infinite;
        }

        .spinner .mark {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        h1 {
            margin: 0 0 8px;
            font-size: 1.5rem;
            font-weight: 800;
        }

        .lead {
            margin: 0 0 32px;
            font-size: .875rem;
            color: var(--muted);
        }

        .track {
            height: 8px;
            border-radius: 999px;
            background: #f1f5f9;
            overflow: hidden;
            margin-bottom: 16px;
        }

        .bar {
            height: 100%;
            width: 0;
            border-radius: 999px;
            background: linear-gradient(to left, var(--brand), #2dd4bf);
            transition: width .7s ease-out;
        }

        .step {
            margin: 0;
            min-height: 1.5rem;
            font-size: .875rem;
            font-weight: 700;
            color: #047857;
        }

        .note {
            margin: 32px 0 0;
            font-size: 11px;
            line-height: 1.7;
            color: var(--faint);
        }

        .error {
            margin: 24px 0 0;
            font-size: .875rem;
            font-weight: 700;
            color: #b91c1c;
        }

        [hidden] { display: none !important; }

        .fallback {
            display: block;
            width: 100%;
            margin-top: 24px;
            padding: 12px 16px;
            border: 0;
            border-radius: .75rem;
            font: inherit;
            font-size: .875rem;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(to right, var(--brand), var(--brand-2));
            cursor: pointer;
        }

        /* The spinner is decoration; the step text carries the meaning. */
        @media (prefers-reduced-motion: reduce) {
            .spinner .arc { animation-duration: 3s; }
            .bar { transition: none; }
        }
    </style>
</head>
<body>

    {{--
        The build is asked for over fetch, and this page navigates itself when
        the answer comes back. Submitting the form instead — the obvious
        version — costs the visitor the page: a pending cross-document
        navigation stops the browser painting the document it came from, so the
        spinner would vanish for the whole build and leave exactly the blank
        screen this page exists to replace.

        The form is still here and still works: it is what the no-JS path and
        the retry button post, and the controller answers it with an ordinary
        redirect.
    --}}
    <form id="demo-build" method="POST" action="{{ route('demo.build') }}">
        @csrf
        <input type="hidden" name="role" value="{{ $role }}">
    </form>

    <div class="card">
        <div class="spinner">
            <div class="ring"></div>
            <div class="arc"></div>
            <div class="mark">🧪</div>
        </div>

        <h1>
            @if ($doctorName)
                نجهّز رحلتك يا {{ $doctorName }}
            @else
                نجهّز رحلتك داخل النظام
            @endif
        </h1>

        <p class="lead">ننشئ لك عيادة كاملة بمرضى ومواعيد وملفات طبية — تستغرق بضع ثوانٍ.</p>

        {{-- Eases toward the end and waits there: how long the build will take
             is not knowable from the browser, so the bar never claims to. --}}
        <div class="track"><div class="bar" id="demo-progress"></div></div>

        <p class="step" id="demo-step">نبدأ التجهيز…</p>

        <p class="note">
            من فضلك لا تغلق هذه الصفحة. بيانات التجربة وهمية بالكامل ومعزولة عن بيانات المرضى الحقيقية،
            وتُمسح نهائياً عند إنهاء التجربة.
        </p>

        {{-- Shown only if the build never answers: never leave the visitor
             watching a spinner that has stopped meaning anything. --}}
        <p class="error" id="demo-error" hidden>
            تعذّر تجهيز بيئة التجربة. تحقّق من الاتصال ثم حاول مرة أخرى.
        </p>

        <noscript>
            <button type="submit" form="demo-build" class="fallback">
                اضغط هنا لتجهيز بيئة التجربة
            </button>
        </noscript>

        <button type="submit" form="demo-build" class="fallback" id="demo-retry" hidden>
            حاول مرة أخرى
        </button>
    </div>

    <script>
        (function () {
            // Each line names something the seeder is genuinely doing, in the
            // order it does it — the visitor should be able to tell where they
            // are, not just that something is happening.
            var steps = [
                'ننشئ عيادتك وحساب الطبيب…',
                'نجهّز حساب المساعد وصلاحياته…',
                'نضيف الخدمات والأسعار وخطط العلاج…',
                'نسجّل ملفات المرضى…',
                'نكتب تشخيصات وروشتات الزيارات السابقة…',
                'نرفع نتائج التحاليل والأشعة…',
                'نرتّب مواعيد الأيام الماضية…',
                'نجهّز طابور اليوم والحالة تحت الكشف…',
                'نحجز مواعيد الأيام القادمة والمتابعات…',
                'اللمسات الأخيرة…'
            ];

            var stepEl = document.getElementById('demo-step');
            var barEl = document.getElementById('demo-progress');
            var i = 0;

            function tick() {
                stepEl.textContent = steps[i];
                // Approach 95% and stop; the redirect is what finishes it.
                barEl.style.width = Math.round(((i + 1) / steps.length) * 95) + '%';

                if (i < steps.length - 1) {
                    i++;
                    setTimeout(tick, 2200);
                }
            }

            tick();

            var form = document.getElementById('demo-build');

            function failed() {
                // A spinner still turning over an error message says the wrong
                // thing about what is happening.
                document.querySelector('.spinner .arc').style.animation = 'none';
                stepEl.hidden = true;
                document.getElementById('demo-error').hidden = false;
                document.getElementById('demo-retry').hidden = false;
            }

            function build() {
                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new FormData(form),
                    credentials: 'same-origin'
                })
                    .then(function (res) {
                        if (!res.ok) { throw new Error(res.status); }
                        return res.json();
                    })
                    .then(function (data) {
                        if (!data || !data.redirect) { throw new Error('no redirect'); }
                        // replace(), so Back does not return to this page.
                        window.location.replace(data.redirect);
                    })
                    .catch(failed);
            }

            // Only once this page has actually been painted: two frames, then
            // a short breath.
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    setTimeout(build, 250);
                });
            });
        })();
    </script>
</body>
</html>
