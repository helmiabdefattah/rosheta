@php
    $isAr = app()->getLocale() === 'ar';
    $storeUrl = 'https://play.google.com/store/apps/details?id=com.helmi.mostashfaon';
    $appName = $isAr ? 'مستشفى أون' : 'Mostashfa On';
    $title = $isAr ? 'حمّل تطبيق مستشفى أون' : 'Get the Mostashfa On app';
    $description = $isAr
        ? 'احجز الأطباء والعيادات، اطلب الأدوية والتحاليل، وتابع طلباتك — كل ذلك من تطبيق مستشفى أون.'
        : 'Book doctors & clinics, order medicines and lab tests, and track your requests — all in the Mostashfa On app.';
    // Absolute URL so social crawlers (WhatsApp/Facebook/Twitter) can fetch it.
    $ogImage = url('/images/full-logo.png');
    $pageUrl = url()->current();
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">

    {{-- Open Graph (WhatsApp, Facebook, LinkedIn, …) --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $appName }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:secure_url" content="{{ $ogImage }}">
    <meta property="og:image:alt" content="{{ $appName }}">
    <meta property="og:url" content="{{ $pageUrl }}">
    <meta property="og:locale" content="{{ $isAr ? 'ar_AR' : 'en_US' }}">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <link rel="icon" href="/images/favicon-32x32.png" sizes="32x32">
    <link rel="apple-touch-icon" href="/images/apple-touch-icon.png">

    {{-- Send real visitors to the store. Crawlers read the tags above and don't
         follow this, so the link preview still renders. --}}
    <meta http-equiv="refresh" content="0; url={{ $storeUrl }}">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            background: linear-gradient(135deg, #00897B 0%, #00695C 100%);
            color: #fff; padding: 24px; text-align: center;
        }
        .card {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 20px; padding: 32px 24px; max-width: 380px; width: 100%;
            backdrop-filter: blur(6px);
        }
        .logo { width: 160px; max-width: 70%; height: auto; margin: 0 auto 20px; display: block; }
        h1 { font-size: 20px; margin: 0 0 8px; }
        p { font-size: 14px; line-height: 1.6; color: rgba(255,255,255,.9); margin: 0 0 22px; }
        .btn {
            display: inline-block; background: #fff; color: #00695C;
            font-weight: 700; font-size: 15px; text-decoration: none;
            padding: 12px 24px; border-radius: 12px;
        }
        .muted { margin-top: 16px; font-size: 12px; color: rgba(255,255,255,.7); }
        .muted a { color: #fff; }
    </style>
</head>
<body>
    <div class="card">
        <img class="logo" src="/images/full-logo.png" alt="{{ $appName }}">
        <h1>{{ $title }}</h1>
        <p>{{ $description }}</p>
        <a class="btn" href="{{ $storeUrl }}" id="store-link">
            {{ $isAr ? 'تحميل من Google Play' : 'Get it on Google Play' }}
        </a>
        <p class="muted">
            {{ $isAr ? 'إذا لم يتم تحويلك تلقائيًا،' : "If you're not redirected automatically," }}
            <a href="{{ $storeUrl }}">{{ $isAr ? 'اضغط هنا' : 'tap here' }}</a>.
        </p>
    </div>

    <script>
        // Belt-and-braces redirect for browsers (crawlers don't run JS, so the
        // preview above is unaffected).
        window.location.replace({!! json_encode($storeUrl) !!});
    </script>
</body>
</html>
