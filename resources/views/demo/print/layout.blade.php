{{--
    The paper the clinic's thermal printer would have produced, drawn on screen
    for the demo. Rendered standalone inside an iframe so the clinic app's
    Tailwind never touches it — this page is meant to look like paper, not like
    the rest of the workspace.

    80mm roll, 203dpi head: the printable band is ~72mm, which is the 272px
    inner width below. Everything is monochrome monospace because that is all a
    thermal head can do.
--}}
<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ $lang === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 18px 12px 28px;
            background: #e2e8f0;
            font-family: "Courier New", ui-monospace, monospace;
            color: #111;
            -webkit-font-smoothing: none;
        }

        /* The roll. The zig-zag pseudo-elements are the tear edges. */
        .paper {
            position: relative;
            width: 302px;
            margin: 0 auto;
            padding: 20px 15px 24px;
            background: #fff;
            filter: drop-shadow(0 6px 14px rgba(15, 23, 42, .28));
        }

        .paper::before,
        .paper::after {
            content: "";
            position: absolute;
            inset-inline: 0;
            height: 7px;
            background:
                repeating-linear-gradient(-45deg, #fff 0 6px, transparent 6px 12px),
                repeating-linear-gradient(45deg, #fff 0 6px, transparent 6px 12px);
        }
        .paper::before { top: -6px; transform: scaleY(-1); }
        .paper::after  { bottom: -6px; }

        /* Thermal ink is never quite black and never quite even. */
        .ink { opacity: .88; }

        .c { text-align: center; }
        /* Phone numbers, dates and codes are logically LTR: without isolation
           the RTL paragraph reorders their groups and 0100 000 0000 prints
           back to front. */
        .ltr { direction: ltr; unicode-bidi: isolate; }
        .start { text-align: start; }

        .sm   { font-size: 11px; line-height: 1.45; }
        .base { font-size: 13px; line-height: 1.5; }
        .lg   { font-size: 20px; font-weight: 700; line-height: 1.25; }
        .xl   { font-size: 58px; font-weight: 700; line-height: 1; letter-spacing: 2px; }
        .b    { font-weight: 700; }

        hr {
            border: 0;
            border-top: 1px dashed #111;
            opacity: .55;
            margin: 9px 0;
        }

        /* The ESC/POS logo raster is 1-bit: no grey, no colour. */
        .logo {
            width: 72px;
            margin: 0 auto 4px;
            display: block;
            filter: grayscale(1) contrast(3);
        }

        .qr { width: 118px; height: 118px; display: block; margin: 4px auto 2px; image-rendering: pixelated; }

        .row { display: flex; justify-content: space-between; gap: 10px; font-size: 12px; padding: 1px 0; }
        .row .v { font-weight: 700; text-align: end; }

        .item { margin: 7px 0; }
        .item + .item { border-top: 1px dotted #999; padding-top: 7px; }
        .sub { margin-inline-start: 14px; font-size: 11px; opacity: .85; }

        .feed { height: 26px; }
    </style>
</head>
<body>
    <div class="paper ink">
        @yield('paper')
        {{-- ESC d 4: the blank feed that clears the tear bar. --}}
        <div class="feed"></div>
    </div>
</body>
</html>
