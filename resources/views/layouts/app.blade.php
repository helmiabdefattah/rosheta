<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', app()->getLocale() === 'ar' ? 'مستشفي اون - تطبيق طبي قوي في مصر يقدم حلول طبية متقدمة' : 'Mostashfa-on - A powerful medical app in Egypt presenting advanced medical solutions')">
    <title>@yield('title', app()->getLocale() === 'ar' ? 'مستشفي اون' : 'Mostashfa-on')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0ea5e9', // Sky 500
                        secondary: '#0f172a', // Slate 900
                        accent: '#2dd4bf', // Teal 400
                    },
                    fontFamily: {
                        sans: ["{{ app()->getLocale() === 'ar' ? 'Cairo' : 'Inter' }}", 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Glassmorphism Utilities */
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        /* Animations */
        .blob {
            position: absolute;
            filter: blur(80px);
            z-index: -1;
            opacity: 0.6;
            animation: float 10s infinite ease-in-out;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }

        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.5, 0, 0, 1);
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 antialiased overflow-x-hidden selection:bg-primary selection:text-white">

    @stack('background-blobs')

    <nav class="fixed w-full top-0 z-50 transition-all duration-300 @if(request()->is('terms') || request()->is('privacy')) bg-slate-900 @endif" id="navbar" data-page="{{ request()->is('/') ? 'welcome' : 'other' }}">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <img src="/images/full-logo.png" alt="{{ app()->getLocale() === 'ar' ? 'شعار مستشفي اون' : 'Mostashfa-on Logo' }}" class="h-12 w-auto object-contain">
                    <span id="logo-text" class="text-2xl font-black transition-colors duration-300 @if(request()->is('terms') || request()->is('privacy')) text-white @endif">
                        {{ app()->getLocale() === 'ar' ? 'مستشفى-أون' : 'Mostashfa-on' }}
                    </span>
                </a>

                <div class="hidden md:flex items-center gap-8 bg-white/50 backdrop-blur-md px-6 py-2.5 rounded-full border border-white/40 shadow-sm">
                    <a href="{{ url('/') }}#features" class="text-sm font-semibold text-slate-600 hover:text-primary transition-colors">{{ app()->getLocale() === 'ar' ? 'المميزات' : 'Features' }}</a>
                    <a href="{{ url('/') }}#about" class="text-sm font-semibold text-slate-600 hover:text-primary transition-colors">{{ app()->getLocale() === 'ar' ? 'عن التطبيق' : 'About' }}</a>
                    <a href="{{ url('/') }}#contact" class="text-sm font-semibold text-slate-600 hover:text-primary transition-colors">{{ app()->getLocale() === 'ar' ? 'اتصل بنا' : 'Contact' }}</a>
                </div>

                <div class="hidden md:flex items-center gap-4">
                    <a href="{{ route('locale', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="text-slate-500 hover:text-slate-900 font-bold text-sm uppercase">
                        {{ app()->getLocale() === 'ar' ? 'EN' : 'عربي' }}
                    </a>
                    @auth
                        <a href="{{ url('/admin') }}" class="px-5 py-2.5 bg-primary text-white rounded-lg hover:bg-blue-700 transition-all shadow-lg shadow-primary/20 font-medium text-sm">
                            {{ app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard' }}
                        </a>
                    @else
                        <a href="{{ url('/admin/login') }}" class="px-5 py-2.5 bg-primary text-white rounded-lg hover:bg-blue-700 transition-all shadow-lg shadow-primary/20 font-medium text-sm">
                            {{ app()->getLocale() === 'ar' ? 'تسجيل الدخول' : 'Login' }}
                        </a>
                    @endauth
                    <button class="px-5 py-2.5 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-all shadow-lg shadow-slate-900/20 font-medium text-sm">
                        {{ app()->getLocale() === 'ar' ? 'احصل على التطبيق' : 'Get the App' }}
                    </button>
                </div>

                <button id="mobile-menu-btn" class="md:hidden p-2 text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="hidden absolute top-20 left-0 w-full bg-white border-b border-gray-100 shadow-xl p-4 flex-col gap-4 md:hidden">
            <a href="{{ url('/') }}#features" class="block py-2 text-slate-600 font-medium">{{ app()->getLocale() === 'ar' ? 'المميزات' : 'Features' }}</a>
            <a href="{{ url('/') }}#about" class="block py-2 text-slate-600 font-medium">{{ app()->getLocale() === 'ar' ? 'عن التطبيق' : 'About' }}</a>
            @auth
                <a href="{{ url('/admin') }}" class="block py-2 text-primary font-bold">{{ app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard' }}</a>
            @else
                <a href="{{ url('/admin/login') }}" class="block py-2 text-primary font-bold">{{ app()->getLocale() === 'ar' ? 'تسجيل الدخول' : 'Login' }}</a>
            @endauth
            <a href="{{ route('locale', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="block py-2 text-primary font-bold">
                {{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}
            </a>
        </div>
    </nav>

    @yield('content')

    <footer id="contact" class="bg-white border-t border-slate-200 pt-20 pb-10">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-12 mb-16">
                <div class="col-span-1 md:col-span-1">
                    <div class="mb-6">
                        <img src="/images/full-logo.png" alt="{{ app()->getLocale() === 'ar' ? 'شعار مستشفي اون' : 'Mostashfa-on Logo' }}" class="h-14 w-auto object-contain">
                    </div>
                    <p class="text-slate-500 text-sm leading-relaxed">
                        {{ app()->getLocale() === 'ar' ? 'نغير مفهوم الرعاية الصحية في مصر عبر التكنولوجيا.' : 'Redefining healthcare in Egypt through technology.' }}
                    </p>
                </div>
                
                <div>
                    <h4 class="font-bold text-slate-900 mb-4">{{ app()->getLocale() === 'ar' ? 'الشركة' : 'Company' }}</h4>
                    <ul class="space-y-2 text-sm text-slate-500">
                        <li><a href="#" class="hover:text-primary">{{ app()->getLocale() === 'ar' ? 'من نحن' : 'About' }}</a></li>
                        <li><a href="#" class="hover:text-primary">{{ app()->getLocale() === 'ar' ? 'الوظائف' : 'Careers' }}</a></li>
                        <li><a href="#" class="hover:text-primary">{{ app()->getLocale() === 'ar' ? 'الأخبار' : 'News' }}</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-bold text-slate-900 mb-4">{{ app()->getLocale() === 'ar' ? 'قانوني' : 'Legal' }}</h4>
                    <ul class="space-y-2 text-sm text-slate-500">
                        <li><a href="{{ route('privacy') }}" class="hover:text-primary">{{ app()->getLocale() === 'ar' ? 'الخصوصية' : 'Privacy' }}</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-primary">{{ app()->getLocale() === 'ar' ? 'الشروط' : 'Terms' }}</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-slate-900 mb-4">{{ app()->getLocale() === 'ar' ? 'تواصل معنا' : 'Contact' }}</h4>
                    <p class="text-sm text-slate-500 mb-2">support@mostashfaon.com</p>
                    <div class="flex gap-4 mt-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-primary hover:text-white transition-colors">fb</a>
                        <a href="#" class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-primary hover:text-white transition-colors">tw</a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-slate-100 pt-8 text-center text-sm text-slate-400">
                &copy; {{ date('Y') }} {{ app()->getLocale() === 'ar' ? 'مستشفي اون. جميع الحقوق محفوظة.' : 'Mostashfa-on. All rights reserved.' }}
            </div>
        </div>
    </footer>

    <script>
        // Navbar Scrolled State
        const navbar = document.getElementById('navbar');
        const logoText = document.getElementById('logo-text');
        const isWelcomePage = navbar.dataset.page === 'welcome';
        
        function updateNavbar() {
            if (window.scrollY > 10) {
                navbar.classList.add('glass', 'shadow-sm');
                // On welcome page, change to dark text when scrolled (glass background is white)
                if (isWelcomePage && logoText) {
                    logoText.classList.remove('text-white');
                    logoText.classList.add('bg-clip-text', 'text-transparent', 'bg-gradient-to-r', 'from-slate-900', 'to-slate-700');
                }
            } else {
                navbar.classList.remove('glass', 'shadow-sm');
                // Always white text when at top (transparent navbar) or on dark background pages
                if (!isWelcomePage && logoText) {
                    logoText.classList.add('text-white');
                    logoText.classList.remove('bg-clip-text', 'text-transparent', 'bg-gradient-to-r', 'from-slate-900', 'to-slate-700');
                }
            }
        }
        
        // Initial check
        updateNavbar();
        
        window.addEventListener('scroll', updateNavbar);

        // Mobile Menu Toggle
        const btn = document.getElementById('mobile-menu-btn');
        const menu = document.getElementById('mobile-menu');
        if (btn && menu) {
            btn.addEventListener('click', () => {
                menu.classList.toggle('hidden');
                menu.classList.toggle('flex');
            });
        }

        // Scroll Reveal Animation using Intersection Observer
        const observerOptions = {
            threshold: 0.1,
            rootMargin: "0px 0px -50px 0px"
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    observer.unobserve(entry.target); // Only animate once
                }
            });
        }, observerOptions);

        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
    </script>

    @stack('scripts')
</body>
</html>

