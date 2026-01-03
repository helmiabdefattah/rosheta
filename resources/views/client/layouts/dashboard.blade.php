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
    </style>

    @stack('styles')
</head>
<body class="bg-gray-100 text-slate-800 font-sans antialiased overflow-hidden">

    <div class="flex h-screen w-full">

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

                <a href="{{ route('client.addresses.index') }}" class="nav-item {{ request()->routeIs('client.addresses.*') ? 'active' : '' }}">
                    <svg class="w-4 h-4 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'عناويني' : 'My Addresses' }}</span>
                </a>

                <div class="menu-header">{{ app()->getLocale() === 'ar' ? 'الإعدادات' : 'SETTINGS' }}</div>
                        @if(Auth::guard('client')->user()->nurse_id == null)

                <a href="{{ route('client.profile.edit') }}" class="nav-item {{ request()->routeIs('client.profile.*') ? 'active' : '' }}">
                    <svg class="w-4 h-4 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'تعديل الملف الشخصي' : 'Edit my Profile' }}</span>
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

            <div class="p-4 border-t border-slate-800/50">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center">
                        <i class="bi bi-person text-primary"></i>
                    </div>
                    <div class="flex-1 min-w-0">

                    <p class="text-sm font-semibold text-white truncate">{{ Auth::guard('client')->user()->name }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ Auth::guard('client')->user()->email ?? Auth::guard('client')->user()->phone_number }}</p>
                    </div>
                </div>

                <!-- Language Toggle -->
                <div class="mb-3">
                    <a href="{{ route('locale', app()->getLocale() === 'ar' ? 'en' : 'ar') }}"
                       class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-800/50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                        </svg>
                        <span>{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</span>
                    </a>
                </div>

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

        <div class="flex-1 flex flex-col h-full bg-gray-100 relative">

            <header class="h-16 bg-white flex items-center justify-between px-6 border-b border-gray-200 shadow-sm">
                <div class="flex items-center gap-4">
                    <button id="open-sidebar" class="lg:hidden text-slate-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div>
                        <h1 class="text-xl font-bold text-slate-800">@yield('page-title', app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard')</h1>
                        @hasSection('page-description')
                            <p class="text-sm text-slate-500">@yield('page-description')</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Language Toggle -->
                    <a href="{{ route('locale', app()->getLocale() === 'ar' ? 'en' : 'ar') }}"
                       class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-700 hover:text-primary hover:bg-slate-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                        </svg>
                        <span>{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</span>
                    </a>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 lg:p-8">
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

    @stack('scripts')
</body>
</html>

