<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', app()->getLocale() === 'ar' ? 'لوحة تحكم الطبيب' : 'Doctor Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    @php $doctor = request()->attributes->get('doctor') ?? auth()->user()->doctor; @endphp
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ["{{ app()->getLocale() === 'ar' ? 'Cairo' : 'Plus Jakarta Sans' }}", 'sans-serif'] },
                    colors: { sidebar: '#0f172a', primary: '#0d9488', doctorPrimary: '#0d9488' }
                }
            }
        };
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 2px; }
        .nav-item { display: flex; align-items: center; padding: 12px 16px; margin-bottom: 4px; color: #94a3b8; border-radius: 8px; transition: all 0.2s; font-weight: 500; font-size: 0.9rem; border-left: 3px solid transparent; }
        [dir="rtl"] .nav-item { border-left: none; border-right: 3px solid transparent; }
        .nav-item:hover { background-color: rgba(255,255,255,0.05); color: #f1f5f9; }
        .nav-item.active { background: linear-gradient(90deg, rgba(13, 148, 136, 0.15) 0%, transparent 100%); color: #0d9488; border-color: #0d9488; }
        .menu-header { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; margin: 24px 16px 8px; font-weight: 700; }
        .sidebar-transition { transition: transform 0.3s; }
        .sidebar-closed-ltr { transform: translateX(-100%); }
        .sidebar-closed-rtl { transform: translateX(100%); }
        .doctor-badge { background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100 text-slate-800 font-sans antialiased overflow-hidden">
<div class="flex h-screen w-full">
    <div id="mobile-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>
    <aside id="sidebar" class="fixed lg:static inset-y-0 z-50 w-64 bg-sidebar text-white flex flex-col shadow-2xl sidebar-transition sidebar-closed-ltr lg:translate-x-0 h-full">
        <div class="h-16 flex items-center px-6 border-b border-slate-800/50">
            <a href="{{ route('doctor.dashboard') }}" class="flex items-center gap-3">
                @if(file_exists(public_path('images/mo-logo.png')))
                    <img src="/images/mo-logo.png" alt="Logo" class="h-10 w-auto object-contain">
                @endif
                <span class="text-lg font-bold text-white">{{ app()->getLocale() === 'ar' ? 'طبيب' : 'Doctor' }}</span>
            </a>
            <button id="close-sidebar" class="lg:hidden ms-auto text-slate-400 hover:text-white"><i class="bi bi-x-lg"></i></button>
        </div>
        <nav class="flex-1 overflow-y-auto p-4 sidebar-scroll">
            <div class="menu-header">{{ app()->getLocale() === 'ar' ? 'الرئيسية' : 'MAIN' }}</div>
            <a href="{{ route('doctor.dashboard') }}" class="nav-item {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill me-3 opacity-70"></i><span>{{ app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard' }}</span>
            </a>
            <div class="menu-header">{{ app()->getLocale() === 'ar' ? 'الحساب والعيادة' : 'ACCOUNT & CLINIC' }}</div>
            <a href="{{ route('doctor.profile.edit') }}" class="nav-item {{ request()->routeIs('doctor.profile.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge me-3 opacity-70"></i><span>{{ app()->getLocale() === 'ar' ? 'الملف الشخصي' : 'My Profile' }}</span>
            </a>
            <a href="{{ route('doctor.clinics.index') }}" class="nav-item {{ request()->routeIs('doctor.clinics.*') ? 'active' : '' }}">
                <i class="bi bi-building me-3 opacity-70"></i><span>{{ app()->getLocale() === 'ar' ? 'عياداتي' : 'My Clinics' }}</span>
            </a>
            <a href="{{ route('doctor.staff.index') }}" class="nav-item {{ request()->routeIs('doctor.staff.*') ? 'active' : '' }}">
                <i class="bi bi-people me-3 opacity-70"></i><span>{{ app()->getLocale() === 'ar' ? 'المساعدون' : 'Assistants' }}</span>
            </a>
            <div class="menu-header">{{ app()->getLocale() === 'ar' ? 'المواعيد' : 'APPOINTMENTS' }}</div>
            <a href="{{ route('doctor.appointments.index') }}" class="nav-item {{ request()->routeIs('doctor.appointments.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-event me-3 opacity-70"></i><span>{{ app()->getLocale() === 'ar' ? 'المواعيد' : 'Appointments' }}</span>
            </a>
            <a href="{{ route('doctor.calendar.index') }}" class="nav-item {{ request()->routeIs('doctor.calendar.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-week me-3 opacity-70"></i><span>{{ app()->getLocale() === 'ar' ? 'أيام العمل والإجازة' : 'Working & Off Days' }}</span>
            </a>
            <div class="menu-header">{{ __('app.chat.menu_header') }}</div>
            <a href="{{ route('doctor.chat.index') }}" class="nav-item {{ request()->routeIs('doctor.chat.*') ? 'active' : '' }}">
                <i class="bi bi-chat-dots me-3 opacity-70"></i><span>{{ __('app.chat.title') }}</span>
            </a>
        </nav>
        @php $user = auth()->user(); $avatarUrl = $doctor->getFirstMediaUrl('profile_image') ?: 'https://ui-avatars.com/api/?name=' . urlencode($doctor->name ?? 'D') . '&background=0d9488&color=fff'; @endphp
        <div class="p-4 border-t border-slate-800/50">
            <div class="flex items-center gap-3 mb-3">
                <img src="{{ $avatarUrl }}" class="w-10 h-10 rounded-full border-2 border-slate-700 object-cover" alt="">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ $doctor->name }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ $user->email ?? $user->phone_number }}</p>
                </div>
            </div>
            <a href="{{ route('user.profile.edit') }}" class="block text-center text-sm text-slate-400 hover:text-white mb-2">{{ app()->getLocale() === 'ar' ? 'إعدادات الحساب' : 'Account' }}</a>
            {{-- Language switch (toggles to the other language) --}}
            <a href="{{ route('locale', app()->getLocale() === 'ar' ? 'en' : 'ar') }}"
               class="w-full flex items-center justify-center gap-2 px-4 py-2 mb-1 text-sm text-slate-300 hover:text-white hover:bg-slate-800/50 rounded-lg">
                <i class="bi bi-translate"></i><span>{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-800/50 rounded-lg">
                    <i class="bi bi-box-arrow-right"></i><span>{{ app()->getLocale() === 'ar' ? 'تسجيل الخروج' : 'Logout' }}</span>
                </button>
            </form>
        </div>
    </aside>
    <div class="flex-1 flex flex-col h-screen bg-gray-100 overflow-hidden">
        <header class="h-16 bg-white flex items-center justify-between px-6 border-b border-gray-200 shadow-sm transition-all duration-300">
            <div class="flex items-center gap-4">
                <button id="open-sidebar" class="lg:hidden text-slate-600 hover:text-primary transition-colors" aria-label="Menu">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <div>
                    <h1 class="text-base md:text-xl font-bold text-slate-800">@yield('page-title', app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard')</h1>
                    <p class="text-sm text-slate-500 hidden sm:block">@yield('page-description', app()->getLocale() === 'ar' ? 'نظرة عامة على عياداتك ومواعيدك' : 'Overview of your clinics and appointments')</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('locale', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="hidden sm:flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-slate-600 hover:text-primary hover:bg-primary/5 rounded-lg transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</span>
                </a>
                <x-chat-dropdown side="doctor" />
                <x-notification-dropdown />
                <span class="doctor-badge"><i class="bi bi-heart-pulse text-xs me-1"></i>{{ app()->getLocale() === 'ar' ? 'طبيب' : 'Doctor' }}</span>
            </div>
        </header>
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            @if(session('success'))<div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg"><ul class="list-disc list-inside text-red-700">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </main>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
document.getElementById('open-sidebar')?.addEventListener('click', function(){ document.getElementById('sidebar').classList.remove('sidebar-closed-ltr','sidebar-closed-rtl'); document.getElementById('mobile-overlay').classList.remove('hidden'); });
document.getElementById('close-sidebar')?.addEventListener('click', closeSidebar);
document.getElementById('mobile-overlay')?.addEventListener('click', closeSidebar);
function closeSidebar(){ var isRtl = document.documentElement.dir === 'rtl'; document.getElementById('sidebar').classList.add(isRtl ? 'sidebar-closed-rtl' : 'sidebar-closed-ltr'); document.getElementById('mobile-overlay').classList.add('hidden'); }
if (document.documentElement.dir === 'rtl') document.getElementById('sidebar').classList.remove('sidebar-closed-ltr').classList.add('sidebar-closed-rtl');

if (typeof toastr !== 'undefined') {
    toastr.options = { closeButton: true, newestOnTop: true, progressBar: true, positionClass: 'toast-top-right', preventDuplicates: false, timeOut: 5000 };
}
</script>
@if(config('services.fcm.api_key'))
<script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js"></script>
<script>
try {
    var firebaseConfig = { apiKey: "{{ config('services.fcm.api_key', '') }}", authDomain: "{{ config('services.fcm.auth_domain', '') }}", projectId: "{{ config('services.fcm.project_id', '') }}", storageBucket: "{{ config('services.fcm.storage_bucket', '') }}", messagingSenderId: "{{ config('services.fcm.messaging_sender_id', '') }}", appId: "{{ config('services.fcm.app_id', '') }}" };
    firebase.initializeApp(firebaseConfig);
    window.firebase = firebase;
    window.FCM_VAPID_KEY = "{{ config('services.fcm.vapid_key', '') }}";
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.ready.then(function(r) { r.active && r.active.postMessage({ type: 'FIREBASE_CONFIG', config: firebaseConfig }); });
    }
} catch (e) { console.error('FCM init error', e); }
</script>
<script src="{{ asset('js/fcm-token-manager.js') }}?v=1.0.3"></script>
@endif
<script src="{{ asset('js/notification-manager.js') }}?v=1.0.2"></script>
@stack('scripts')
</body>
</html>
