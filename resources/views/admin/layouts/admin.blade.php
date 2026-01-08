<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard')</title>

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
                        primary: '#2dd4bf', // Teal 400 (Brighter for dark bg)
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    
    <!-- Alpine.js for interactive components -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Toastr Notifications -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    @stack('styles')

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
            background: linear-gradient(90deg, rgba(45, 212, 191, 0.1) 0%, transparent 100%);
            color: #2dd4bf; /* Primary Color */
            border-color: #2dd4bf;
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
</head>
<body class="bg-gray-100 text-slate-800 font-sans antialiased overflow-hidden">

    <div class="flex h-screen w-full">

        <div id="mobile-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden backdrop-blur-sm"></div>

        <aside id="sidebar" class="fixed lg:static inset-y-0 z-50 w-64 bg-sidebar text-white flex flex-col shadow-2xl sidebar-transition sidebar-closed-ltr lg:translate-x-0 h-full">

            <div class="h-16 flex items-center px-6 border-b border-slate-800/50">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <img src="/images/full-logo.png" alt="Mostashfa-on Logo" class="h-12 w-auto object-contain">

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

                <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg class="w-5 h-5 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard' }}</span>
                </a>

                <div class="menu-header">{{ app()->getLocale() === 'ar' ? 'طبي' : 'MEDICAL' }}</div>

                <a href="{{ route('admin.medicines.index') }}" class="nav-item {{ request()->routeIs('admin.medicines.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'الأدوية' : 'Medicines' }}</span>
                </a>

                <a href="{{ route('admin.pharmacies.index') }}" class="nav-item {{ request()->routeIs('admin.pharmacies.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'الصيدليات' : 'Pharmacies' }}</span>
                </a>

                <a href="{{ route('admin.laboratories.index') }}" class="nav-item {{ request()->routeIs('admin.laboratories.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'المعامل' : 'Laboratories' }}</span>
                </a>

                <a href="{{ route('admin.medical-tests.index') }}" class="nav-item {{ request()->routeIs('admin.medical-tests.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'الفحوصات' : 'Medical Tests' }}</span>
                </a>

                <a href="{{ route('admin.medical-test-offers.index') }}" class="nav-item {{ request()->routeIs('admin.medical-test-offers.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2-1.343-2-3-2zM4 12a8 8 0 1016 0 8 8 0 10-16 0zm8 4a4 4 0 110-8 4 4 0 010 8z"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'عروض الفحوصات' : 'Medical Test Offers' }}</span>
                </a>

                <a href="{{ route('admin.nurses.index') }}" class="nav-item {{ request()->routeIs('admin.nurses.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m0-6l-9-5m9 5l9-5"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'التمريض' : 'Nurses' }}</span>
                </a>

                <div class="menu-header">{{ app()->getLocale() === 'ar' ? 'إدارة' : 'BUSINESS' }}</div>

                <a href="{{ route('admin.orders.index') }}" class="nav-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'الطلبات' : 'Orders' }}</span>
                </a>

                <a href="{{ route('admin.client-requests.index') }}" class="nav-item {{ request()->routeIs('admin.client-requests.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'الطلبات الخاصة' : 'Requests' }}</span>
                </a>

                <a href="{{ route('admin.offers.index') }}" class="nav-item {{ request()->routeIs('admin.offers.*') ? 'active' : '' }}">
                     <svg class="w-5 h-5 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 011 12V7a4 4 0 014-4z"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'العروض' : 'Offers' }}</span>
                </a>

                <div class="menu-header">{{ app()->getLocale() === 'ar' ? 'النظام' : 'SYSTEM' }}</div>

                <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'المشرفين' : 'Admins' }}</span>
                </a>

                <a href="{{ route('admin.clients.index') }}" class="nav-item {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'العملاء' : 'Clients' }}</span>
                </a>

                <a href="{{ route('admin.feedback.index') }}" class="nav-item {{ request()->routeIs('admin.feedback.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'ملاحظات العملاء' : 'Client Feedback' }}</span>
                </a>

                <a href="{{ route('admin.support-tickets.index') }}" class="nav-item {{ request()->routeIs('admin.support-tickets.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4v-3a2 2 0 00-2-2H5z"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'تذاكر الدعم' : 'Support Tickets' }}</span>
                </a>

                <a href="{{ route('admin.governorates.index') }}" class="nav-item {{ request()->routeIs('admin.governorates.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'المناطق الجغرافية' : 'Locations' }}</span>
                </a>
            </nav>

            <a href="{{ route('admin.users.edit', auth()->id()) }}" class="border-t border-slate-800 p-4 bg-black/10 block hover:bg-black/20 transition-colors">
                <div class="flex items-center gap-3">
                    <img src="{{ auth()->user()->getFirstMediaUrl('profile_image') ?: 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=2dd4bf&color=fff' }}" 
                         class="w-10 h-10 rounded-full border-2 border-slate-700 object-cover">
                    <div class="flex-1 overflow-hidden">
                        <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>
            </a>

            <div class="p-4 bg-black/5">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="w-full flex items-center justify-center gap-2 px-3 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-800/50 rounded-lg transition-colors" title="Logout">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>{{ app()->getLocale() === 'ar' ? 'تسجيل الخروج' : 'Logout' }}</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col h-full bg-gray-100 relative">

            <header class="h-16 bg-white flex items-center justify-between px-6 border-b border-gray-200">
                <div class="flex items-center gap-4">
                    <button id="open-sidebar" class="lg:hidden text-slate-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <span class="font-bold text-slate-800 hidden lg:block">Mostashfa-on</span>
                </div>
                
                <div class="flex items-center gap-4">
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
                                <p class="text-sm font-bold text-slate-800 leading-none">{{ auth()->user()->name }}</p>
                                <p class="text-[11px] text-slate-500 mt-1 leading-none uppercase tracking-wider font-semibold">Admin</p>
                            </div>
                            <img src="{{ auth()->user()->getFirstMediaUrl('profile_image') ?: 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=2dd4bf&color=fff' }}" 
                                 class="w-10 h-10 rounded-full border-2 border-gray-100 shadow-sm object-cover group-hover:border-primary transition-colors" 
                                 alt="{{ auth()->user()->name }}">
                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <div x-show="open" 
                             @click.away="open = false" 
                             x-cloak
                             class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50 origin-top-right transition-all"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 translate-y-[-10px]"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 translate-y-[-10px]">
                            
                            <div class="px-4 py-2 border-b border-gray-50 mb-1">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Account</p>
                            </div>

                            <a href="{{ route('admin.users.edit', auth()->id()) }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-primary/5 hover:text-primary transition-colors">
                                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
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

            <main class="flex-1 overflow-y-auto p-4 lg:p-8">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800">@yield('page-title', 'Dashboard')</h1>
                        <p class="text-slate-500 text-sm mt-1">@yield('page-description')</p>
                    </div>
                    <div>
                        @yield('header-actions')
                    </div>
                </div>

                @if(session('success'))
                    <div class="mb-6 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded shadow-sm" role="alert">
                        <p class="font-bold">Success</p>
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const isRTL = $('html').attr('dir') === 'rtl';
            const $sidebar = $('#sidebar');
            const $overlay = $('#mobile-overlay');
            const closedClass = isRTL ? 'sidebar-closed-rtl' : 'sidebar-closed-ltr';

            // Initial State for Mobile
            if ($(window).width() < 1024) {
                $sidebar.addClass(closedClass);
            }

            // Open Sidebar
            $('#open-sidebar').on('click', function() {
                $sidebar.removeClass(closedClass);
                $overlay.removeClass('hidden');
            });

            // Close Sidebar
            $('#close-sidebar, #mobile-overlay').on('click', function() {
                $sidebar.addClass(closedClass);
                $overlay.addClass('hidden');
            });

            // Setup CSRF for Ajax
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });
        });
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
    <script src="{{ asset('js/fcm-token-manager.js') }}"></script>
    @endif
    
    <!-- Notification Manager -->
    <script src="{{ asset('js/notification-manager.js') }}"></script>
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
