<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? __('app.dental.title') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Same shared controls as the clinic workspace, so an embedded page looks
           identical to the full one. */
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: .4rem; border-radius: .5rem;
               font-size: .875rem; font-weight: 600; line-height: 1.25rem; padding: .5rem 1rem;
               border: 1px solid transparent; white-space: nowrap; cursor: pointer; transition: background-color .12s, color .12s, border-color .12s; }
        .btn:disabled { opacity: .5; cursor: not-allowed; }
        .btn-sm { padding: .3rem .7rem; font-size: .8125rem; }
        .btn-block { width: 100%; }
        .btn-primary { background: #4f46e5; color: #fff; }           .btn-primary:hover { background: #4338ca; }
        .btn-secondary { background: #fff; color: #334155; border-color: #cbd5e1; } .btn-secondary:hover { background: #f8fafc; }
        .btn-danger { background: transparent; color: #dc2626; }    .btn-danger:hover { background: #fef2f2; }
        .btn-complete { background: #059669; color: #fff; }         .btn-complete:hover { background: #047857; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: .75rem; box-shadow: 0 1px 2px rgba(15, 23, 42, .05); }
        main input:not([type="file"]):not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
        main select, main textarea { border-color: #cbd5e1; background: #fff; color: #0f172a; }
        main input:not([type="file"]):not([type="checkbox"]):not([type="radio"]):focus,
        main select:focus, main textarea:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, .18); }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800">
    <main class="max-w-5xl mx-auto px-4 py-5">
        @if (session('status'))
            <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
