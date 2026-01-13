<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', app()->getLocale() === 'ar' ? 'لوحة تحكم العميل' : 'Client Dashboard')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ["{{ app()->getLocale() === 'ar' ? 'Cairo' : 'Plus Jakarta Sans' }}", 'sans-serif'],
                    },
                    colors: {
                        sidebar: '#0f172a', // Slate 900
                        sidebarHover: '#1e293b', // Slate 800
                        primary: '#0d9488', // Teal 600
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        /* Custom Scrollbar */
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 2px; }

        /* Main Content Scrollbar - Both Directions */
        .main-content-scroll::-webkit-scrollbar { 
            width: 8px; 
            height: 8px; 
        }
        .main-content-scroll::-webkit-scrollbar-track { 
            background: #f1f5f9; 
        }
        .main-content-scroll::-webkit-scrollbar-thumb { 
            background: #cbd5e1; 
            border-radius: 4px; 
        }
        .main-content-scroll::-webkit-scrollbar-thumb:hover { 
            background: #94a3b8; 
        }
        .main-content-scroll::-webkit-scrollbar-corner {
            background: #f1f5f9;
        }

        /* Navigation Item Styles */
        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            margin-bottom: 4px;
            color: #94a3b8; /* Slate 400 */
            border-radius: 8px;
            transition: all 0.2s ease;
            font-weight: 500;
            font-size: 0.9rem;
            border-left: 3px solid transparent;
        }

        [dir="rtl"] .nav-item { border-left: none; border-right: 3px solid transparent; }

        .nav-item:hover {
            background-color: rgba(255,255,255,0.05);
            color: #f1f5f9;
        }

        /* Active State */
        .nav-item.active {
            background: linear-gradient(90deg, rgba(13, 148, 136, 0.1) 0%, transparent 100%);
            color: #0d9488; /* Primary Color */
            border-color: #0d9488;
        }

        /* Submenu Header */
        .menu-header {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            margin: 24px 16px 8px;
            font-weight: 700;
        }

        /* Mobile Sidebar Transition */
        .sidebar-transition { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .sidebar-closed-ltr { transform: translateX(-100%); }
        .sidebar-closed-rtl { transform: translateX(100%); }
        [x-cloak] { display: none !important; }
    </style>

    <script>
        window.UserConfig = {
            notificationSound: {{ (Auth::guard('client')->user()?->notification_sound ?? false) ? 'true' : 'false' }}
        };
    </script>

    @stack('styles')
</head>
<body class="bg-gray-100 text-slate-800 font-sans antialiased overflow-hidden">

    <div class="flex h-screen min-w-full overflow-hidden">

        <div id="mobile-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden backdrop-blur-sm"></div>

        <aside id="sidebar" class="fixed lg:static inset-y-0 z-50 w-64 bg-sidebar text-white flex flex-col shadow-2xl sidebar-transition sidebar-closed-ltr lg:translate-x-0 h-full">

            <div class="h-16 flex items-center px-6 border-b border-slate-800/50">
                <a href="{{ route('client.dashboard') }}" class="flex items-center gap-3">
                    <img src="/images/mo-logo.png" alt="Mostashfa-on Logo" class="h-10 w-auto object-contain">
                    <span class="text-lg font-bold tracking-tight text-white">
                        {{ app()->getLocale() === 'ar' ? 'مستشفى-أون' : 'Mostashfa-on' }}
                    </span>
                </a>
                <button id="close-sidebar" class="lg:hidden ms-auto text-slate-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto p-4 sidebar-scroll">
                <div class="menu-header">{{ app()->getLocale() === 'ar' ? 'الرئيسية' : 'MAIN' }}</div>
                @if(Auth::guard('client')->user()->nurse_id != null)
                <a href="{{ route('client.nurse.dashboard') }}" class="nav-item {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
               @else
                <a href="{{ route('client.dashboard') }}" class="nav-item {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
                    @endif
                    <svg class="w-4 h-4 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard' }}</span>
                </a>

                <div class="menu-header">{{ app()->getLocale() === 'ar' ? 'الطلبات' : 'REQUESTS' }}</div>

                <a href="{{ route('client.test-requests.create', 'test') }}"
                   class="nav-item {{ request()->is('client/test-requests/create/test') ? 'active' : '' }}">
                    <span>{{ app()->getLocale() === 'ar' ? 'طلب تحاليل طبية' : 'Request Medical Tests' }}</span>
                </a>
                <a href="{{ route('client.test-requests.create', 'radiology') }}"
                   class="nav-item {{ request()->is('client/test-requests/create/radiology') ? 'active' : '' }}">
                    <span>{{ app()->getLocale() === 'ar' ? 'طلب أشعة' : 'Request Radiology Tests' }}</span>
                </a>
                <a href="{{ route('client.medicine-requests.create') }}"
                   class="nav-item {{ request()->routeIs('client.medicine-requests.*') ? 'active' : '' }}">
                    <span>{{ app()->getLocale() === 'ar' ? 'طلب أدوية' : 'Request Medicines' }}</span>
                </a>
                        @if(Auth::guard('client')->user()->nurse_id == null)

                        <a href="{{ route('client.nurse-requests.index') }}"
                   class="nav-item {{ request()->routeIs('client.nurse-requests.*') ? 'active' : '' }}">
                    <span>{{ app()->getLocale() === 'ar' ? 'طلبات التمريض' : 'Nursing Requests' }}</span>
                </a>
                        @endif

                <a href="{{ route('client.offers.index') }}" class="nav-item {{ request()->routeIs('client.offers.*') ? 'active' : '' }}">
                    <svg class="w-4 h-4 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'العروض' : 'My Offers' }}</span>
                </a>

                <a href="{{ route('client.orders.index') }}" class="nav-item {{ request()->routeIs('client.orders.*') ? 'active' : '' }}">
                    <svg class="w-4 h-4 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'تتبع الطلبات' : 'Track Orders' }}</span>
                </a>

                <a href="{{ route('client.test-results.index') }}" class="nav-item {{ request()->routeIs('client.test-results.*') ? 'active' : '' }}">
                    <svg class="w-4 h-4 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'نتائج التحاليل' : 'Test Results' }}</span>
                </a>

                <a href="{{ route('client.addresses.index') }}" class="nav-item {{ request()->routeIs('client.addresses.*') ? 'active' : '' }}">
                    <svg class="w-4 h-4 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'عناويني' : 'My Addresses' }}</span>
                </a>

                <a href="{{ route('client.laboratories.index') }}" class="nav-item {{ request()->routeIs('client.laboratories.*') ? 'active' : '' }}">
                    <svg class="w-4 h-4 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'المعامل' : 'Laboratories' }}</span>
                </a>

                <div class="menu-header">{{ app()->getLocale() === 'ar' ? 'الإعدادات' : 'SETTINGS' }}</div>
                        @if(Auth::guard('client')->user()->nurse_id == null)

                <a href="{{ route('client.profile.edit') }}" class="nav-item {{ request()->routeIs('client.profile.*') ? 'active' : '' }}">
                    <svg class="w-4 h-4 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'تعديل الملف الشخصي' : 'Edit my Profile' }}</span>
                </a>

                <a href="{{ route('client.feedback.create') }}" class="nav-item {{ request()->routeIs('client.feedback.*') ? 'active' : '' }}">
                    <svg class="w-4 h-4 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'إرسال ملاحظة' : 'Submit Feedback' }}</span>
                </a>
                        @else
                            <a href="{{ route('client.nurses.edit', auth('client')->user()->nurse_id) }}"
                               class="nav-item {{ request()->routeIs('nurses.edit') ? 'active' : '' }}">

                                <svg class="w-4 h-4 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>

                                <span>{{ app()->getLocale() === 'ar' ? 'تعديل الملف الشخصي' : 'Edit my Profile' }}</span>
                            </a>
                @endif
            </nav>

            @php
                $clientUser = Auth::guard('client')->user();
                $profileUrl = $clientUser->nurse_id ? route('client.nurses.edit', $clientUser->nurse_id) : route('client.profile.edit');
                $avatarUrl = $clientUser->avatar ? asset('storage/' . $clientUser->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($clientUser->name) . '&background=0d9488&color=fff';
            @endphp

            <a href="{{ $profileUrl }}" class="p-4 border-t border-slate-800/50 block hover:bg-black/20 transition-colors">
                <div class="flex items-center gap-3">
                    <img src="{{ $avatarUrl }}" class="w-10 h-10 rounded-full border-2 border-slate-700 object-cover" alt="{{ $clientUser->name }}">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ $clientUser->name }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ $clientUser->email ?? $clientUser->phone_number }}</p>
                    </div>
                </div>
            </a>

            <div class="p-4 bg-black/5">
                <!-- Language Toggle -->
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-800/50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>{{ app()->getLocale() === 'ar' ? 'تسجيل الخروج' : 'Logout' }}</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col bg-gray-100 relative min-w-0 h-screen overflow-hidden">

            <header class="h-16 bg-white flex items-center justify-between px-6 border-b border-gray-200 shadow-sm transition-all duration-300">
                <div class="flex items-center gap-4">
                    <button id="open-sidebar" class="lg:hidden text-slate-600 hover:text-primary transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div>
                        <h1 class="text-xl font-bold text-slate-800">@yield('page-title', app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard')</h1>
                        @hasSection('page-description')
                            <p class="text-sm text-slate-500 hidden sm:block">@yield('page-description')</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-4">
                @php
                    $client = Auth::guard('client')->user();
                    $availableBonusPoints = \App\Models\BonusPoint::where('client_id', $client->id)
                                                                   ->sum('points') ?? 0;
                @endphp
                <div class="flex items-center gap-4">
                    <!-- Compact Points Display -->
                    <div class="relative hidden md:block">
                        <div class="flex items-center bg-gradient-to-r from-amber-50 to-yellow-50 px-4 py-1 rounded-xl border border-amber-200 shadow-sm">
                            <div class="text-right me-3">
                                <p class="text-xs font-semibold text-amber-800 mb-0.5">
                                    {{ app()->getLocale() === 'ar' ? 'النقاط' : 'Points' }}
                                </p>
                                <span class="text-2xl font-bold text-amber-600">
                        {{ number_format($availableBonusPoints ?? 0) }}
                    </span>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-r from-amber-400 to-yellow-500 flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Points Display -->
                    <div class="md:hidden">
                        <div class="flex items-center bg-gradient-to-r from-amber-50 to-yellow-50 px-3 py-1.5 rounded-lg border border-amber-200">
                            <svg class="w-4 h-4 text-amber-600 me-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span class="text-lg font-bold text-amber-600">{{ number_format($availableBonusPoints ?? 0) }}</span>
                        </div>
                    </div>

                    <!-- Language Toggle -->
                    <a href="{{ route('locale', app()->getLocale() === 'ar' ? 'en' : 'ar') }}"
                       class="hidden sm:flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-slate-600 hover:text-primary hover:bg-primary/5 rounded-lg transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                        </svg>
                        <span>{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</span>
                    </a>

                    <x-notification-dropdown />

                    <!-- Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-3 focus:outline-none group">
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-bold text-slate-800 leading-none">{{ $clientUser->name }}</p>
                                <p class="text-[11px] text-primary mt-1 leading-none uppercase tracking-wider font-semibold">
                                    {{ $clientUser->nurse_id ? (app()->getLocale() === 'ar' ? 'ممرض' : 'Nurse') : (app()->getLocale() === 'ar' ? 'عميل' : 'Client') }}
                                </p>
                            </div>
                            <img src="{{ $avatarUrl }}"
                                 class="w-10 h-10 rounded-full border-2 border-gray-100 shadow-sm object-cover group-hover:border-primary transition-colors"
                                 alt="{{ $clientUser->name }}">
                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Dropdown menu -->
                        <div x-show="open"
                             @click.away="open = false"
                             x-cloak
                             class="absolute ltr:right-0 rtl:left-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50 ltr:origin-top-right rtl:origin-top-left transition-all"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 translate-y-[-10px]"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 translate-y-[-10px]">

                            <div class="px-4 py-2 border-b border-gray-50 mb-1">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Account</p>
                            </div>

                            <a href="{{ $profileUrl }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-primary/5 hover:text-primary transition-colors">
                                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                {{ app()->getLocale() === 'ar' ? 'الملف الشخصي' : 'Profile Settings' }}
                            </a>

                            <div class="my-1 border-t border-gray-50"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center text-red-600">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                    </div>
                                    {{ app()->getLocale() === 'ar' ? 'تسجيل الخروج' : 'Logout' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>
            <main class="flex-1 overflow-y-auto overflow-x-auto p-4 lg:p-8 main-content-scroll">
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-800">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Errors</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc list-inside space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Alpine.js for interactive components -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Toastr Notifications -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        // Mobile sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-overlay');
        const openBtn = document.getElementById('open-sidebar');
        const closeBtn = document.getElementById('close-sidebar');

        function openSidebar() {
            sidebar.classList.remove('sidebar-closed-ltr', 'sidebar-closed-rtl');
            overlay.classList.remove('hidden');
        }

        function closeSidebar() {
            const isRtl = document.documentElement.dir === 'rtl';
            sidebar.classList.add(isRtl ? 'sidebar-closed-rtl' : 'sidebar-closed-ltr');
            overlay.classList.add('hidden');
        }

        openBtn?.addEventListener('click', openSidebar);
        closeBtn?.addEventListener('click', closeSidebar);
        overlay?.addEventListener('click', closeSidebar);

        // Handle RTL
        if (document.documentElement.dir === 'rtl') {
            sidebar.classList.remove('sidebar-closed-ltr');
            sidebar.classList.add('sidebar-closed-rtl');
        }
    </script>

    <!-- Firebase SDK -->
    @if(config('services.fcm.api_key'))
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js"></script>
    <script>
        const firebaseConfig = {
            apiKey: "{{ config('services.fcm.api_key', '') }}",
            authDomain: "{{ config('services.fcm.auth_domain', '') }}",
            projectId: "{{ config('services.fcm.project_id', '') }}",
            storageBucket: "{{ config('services.fcm.storage_bucket', '') }}",
            messagingSenderId: "{{ config('services.fcm.messaging_sender_id', '') }}",
            appId: "{{ config('services.fcm.app_id', '') }}"
        };

        try {
            firebase.initializeApp(firebaseConfig);
            window.firebase = firebase;
            window.firebaseConfig = firebaseConfig;
            window.FCM_VAPID_KEY = "{{ config('services.fcm.vapid_key', '') }}";
            console.log('FCM: Firebase SDK loaded successfully');

            // Send config to service worker if it's registered
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.ready.then(registration => {
                    registration.active?.postMessage({
                        type: 'FIREBASE_CONFIG',
                        config: firebaseConfig
                    });
                });
            }

            // Set refresh flag if coming from login
            @if(session('fcm_token_refresh'))
            sessionStorage.setItem('fcm_token_refresh', 'true');
            @endif
        } catch (error) {
            console.error('FCM: Error initializing Firebase SDK:', error);
        }
    </script>
    <script src="{{ asset('js/fcm-token-manager.js') }}?v=1.0.2"></script>
    @endif

    <!-- Notification Manager -->
    <script src="{{ asset('js/notification-manager.js') }}?v=1.0.2"></script>
    <script>
        // Configure Toastr
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        @if(app()->getLocale() === 'ar')
        toastr.options.rtl = true;
        @endif
    </script>

    @stack('scripts')
</body>
</html>

