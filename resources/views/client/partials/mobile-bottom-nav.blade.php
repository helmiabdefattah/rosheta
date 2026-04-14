{{-- Mobile bottom tab bar (client area only) --}}
@php
    $isAr = app()->getLocale() === 'ar';
    $homeUrl = route('client.dashboard');
    $homeActive = request()->routeIs('client.dashboard');
    $requestsActive = request()->routeIs('client.requests.*');
    $ordersActive = request()->routeIs('client.orders.*');
    $medicineActive = request()->routeIs('client.medicine-requests.*');
    $testsActive = request()->routeIs('client.test-requests.create') && (request()->route('type') ?? '') === 'test';
@endphp
<nav
    class="lg:hidden fixed bottom-0 inset-x-0 z-[42] border-t border-slate-200/90 bg-white/95 backdrop-blur-md shadow-[0_-4px_20px_rgba(15,23,42,0.06)]"
    style="padding-bottom: env(safe-area-inset-bottom, 0px);"
    aria-label="{{ $isAr ? 'التنقل السريع' : 'Quick navigation' }}"
>
    <div class="grid grid-cols-5 gap-0.5 max-w-lg mx-auto min-h-[3.5rem] px-1 pt-1">
        <a href="{{ $homeUrl }}"
           class="flex flex-col items-center justify-center gap-0.5 rounded-lg py-1.5 px-0.5 min-w-0 transition-colors {{ $homeActive ? 'text-primary' : 'text-slate-500 hover:text-slate-800' }}"
           aria-current="{{ $homeActive ? 'page' : 'false' }}">
            <svg class="w-6 h-6 shrink-0 {{ $homeActive ? 'text-primary' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-[10px] font-semibold leading-tight text-center truncate w-full">{{ $isAr ? 'الرئيسية' : 'Home' }}</span>
        </a>
        <a href="{{ route('client.requests.index') }}"
           class="flex flex-col items-center justify-center gap-0.5 rounded-lg py-1.5 px-0.5 min-w-0 transition-colors {{ $requestsActive ? 'text-primary' : 'text-slate-500 hover:text-slate-800' }}"
           aria-current="{{ $requestsActive ? 'page' : 'false' }}">
            <svg class="w-6 h-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <span class="text-[10px] font-semibold leading-tight text-center truncate w-full">{{ $isAr ? 'طلباتي' : 'Requests' }}</span>
        </a>
        <a href="{{ route('client.orders.index') }}"
           class="flex flex-col items-center justify-center gap-0.5 rounded-lg py-1.5 px-0.5 min-w-0 transition-colors {{ $ordersActive ? 'text-primary' : 'text-slate-500 hover:text-slate-800' }}"
           aria-current="{{ $ordersActive ? 'page' : 'false' }}">
            <svg class="w-6 h-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            <span class="text-[10px] font-semibold leading-tight text-center truncate w-full">{{ $isAr ? 'تتبّع' : 'Orders' }}</span>
        </a>
        <a href="{{ route('client.medicine-requests.create') }}"
           class="flex flex-col items-center justify-center gap-0.5 rounded-lg py-1.5 px-0.5 min-w-0 transition-colors {{ $medicineActive ? 'text-primary' : 'text-slate-500 hover:text-slate-800' }}"
           aria-current="{{ $medicineActive ? 'page' : 'false' }}">
            <svg class="w-6 h-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
            <span class="text-[10px] font-semibold leading-tight text-center truncate w-full">{{ $isAr ? 'أدوية' : 'Medicine' }}</span>
        </a>
        <a href="{{ route('client.test-requests.create', 'test') }}"
           class="flex flex-col items-center justify-center gap-0.5 rounded-lg py-1.5 px-0.5 min-w-0 transition-colors {{ $testsActive ? 'text-primary' : 'text-slate-500 hover:text-slate-800' }}"
           aria-current="{{ $testsActive ? 'page' : 'false' }}">
            <svg class="w-6 h-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="text-[10px] font-semibold leading-tight text-center truncate w-full">{{ $isAr ? 'تحاليل' : 'Tests' }}</span>
        </a>
    </div>
</nav>
