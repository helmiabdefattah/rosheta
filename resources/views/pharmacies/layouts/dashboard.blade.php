<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>@yield('title', app()->getLocale() === 'ar' ? 'لوحة تحكم الصيدلية' : 'Pharmacy Dashboard')</title>

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
						sidebar: '#0f172a',
						sidebarHover: '#1e293b',
						rprimary: '#0d9488',
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

	<style>
		.sidebar-scroll::-webkit-scrollbar { width: 4px; }
		.sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
		.sidebar-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 2px; }
		.nav-item { display:flex;align-items:center;padding:12px 16px;margin-bottom:4px;color:#94a3b8;border-radius:8px;transition:all .2s;font-weight:500;font-size:.9rem;border-left:3px solid transparent;}
		[dir="rtl"] .nav-item{border-left:none;border-right:3px solid transparent;}
		.nav-item:hover{background-color:rgba(255,255,255,.05);color:#f1f5f9;}
		.nav-item.active{background:linear-gradient(90deg, rgba(13,148,136,.1) 0%, transparent 100%);color:#0d9488;border-color:#0d9488;}
		.menu-header{font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:#475569;margin:24px 16px 8px;font-weight:700;}
		.sidebar-transition{transition:transform .3s cubic-bezier(.4,0,.2,1);}
		.sidebar-closed-ltr{transform:translateX(-100%);}
		.sidebar-closed-rtl{transform:translateX(100%);}
	</style>

	@stack('styles')
</head>
<body class="bg-gray-100 text-slate-800 font-sans antialiased overflow-hidden">
<div class="flex h-screen w-full">
	<div id="mobile-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden backdrop-blur-sm"></div>
	<aside id="sidebar" class="fixed lg:static inset-y-0 z-50 w-64 bg-sidebar text-white flex flex-col shadow-2xl sidebar-transition sidebar-closed-ltr lg:translate-x-0 h-full">
		<div class="h-16 flex items-center px-6 border-b border-slate-800/50">
			<a href="{{ route('pharmacies.dashboard') }}" class="flex items-center gap-3">
				<img src="/images/full-logo.png" alt="Mostashfa-on Logo" class="h-12 w-auto object-contain">
				<span class="text-lg font-bold tracking-tight text-white">
					{{ app()->getLocale() === 'ar' ? 'مستشفى-أون' : 'Mostashfa-on' }}
				</span>
			</a>
			<button id="close-sidebar" class="lg:hidden ms-auto text-slate-400 hover:text-white">
				<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></path></svg>
			</button>
		</div>
		<nav class="flex-1 overflow-y-auto p-4 sidebar-scroll">
			<div class="menu-header">{{ app()->getLocale() === 'ar' ? 'الرئيسية' : 'MAIN' }}</div>
			<a href="{{ route('pharmacies.dashboard') }}" class="nav-item {{ request()->routeIs('pharmacies.dashboard') ? 'active' : ' ' }}">
				<svg class="w-4 h-4 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h6m4 0h6M4 12h16M4 18h16"/>
				</svg>
				<span>{{ app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard' }}</span>
			</a>
			<div class="menu-header">{{ app()->getLocale() === 'ar' ? 'الطلبات والعروض' : 'REQUESTS & OFFERS' }}</div>
			<a href="{{ route('pharmacies.requests.index') }}" class="nav-item {{ request()->routeIs('pharmacies.requests.*') ? 'active' : '' }}">
				<svg class="w-4 h-4 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586l5.414 5.414V19a2 2 0 01-2 2z"/>
				</svg>
				<span>{{ app()->getLocale() === 'ar' ? 'طلبات الأدوية' : 'Medicine Requests' }}</span>
			</a>
			<a href="{{ route('pharmacies.offers.index') }}" class="nav-item {{ request()->routeIs('pharmacies.offers.index') ? 'active' : '' }}">
				<svg class="w-4 h-4 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586l5.414 5.414V19a2 2 0 01-2 2z"/>
				</svg>
				<span>{{ app()->getLocale() === 'ar' ? 'العروض المرسلة' : 'Sent Offers' }}</span>
			</a>
			<a href="{{ route('pharmacies.offers.accepted') }}" class="nav-item {{ request()->routeIs('pharmacies.offers.accepted') ? 'active' : '' }}">
				<svg class="w-4 h-4 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
				</svg>
				<span>{{ app()->getLocale() === 'ar' ? 'العروض المقبولة' : 'Accepted Offers' }}</span>
			</a>
			<div class="menu-header">{{ app()->getLocale() === 'ar' ? 'الطلبات' : 'ORDERS' }}</div>
			<a href="{{ route('pharmacies.orders.index') }}" class="nav-item {{ request()->routeIs('pharmacies.orders.*') ? 'active' : '' }}">
				<svg class="w-4 h-4 me-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
				</svg>
				<span>{{ app()->getLocale() === 'ar' ? 'إدارة الطلبات' : 'Manage Orders' }}</span>
			</a>
			<div class="menu-header">{{ app()->getLocale() === 'ar' ? 'الإعدادات' : 'SETTINGS' }}</div>
			{{-- Placeholder for pharmacy profile routes if exist --}}
		</nav>
		<div class="border-t border-slate-800 p-4 bg-black/10">
			<div class="flex items-center gap-3 mb-3">
				<img src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}&background=0d9488&color=fff" class="w-10 h-10 rounded-full border-2 border-slate-700">
				<div class="flex-1 overflow-hidden">
					<p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
					<p class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</p>
				</div>
			</div>
			<a href="{{ route('locale', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="flex items-center justify-center gap-2 px-3 py-2 mb-3 text-sm text-slate-300 hover:text-white hover:bg-slate-800/50 rounded-lg transition-colors">
				<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
				</svg>
				<span>{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</span>
			</a>
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
		<header class="h-16 bg-white flex items-center justify-between px-6 border-b border-gray-200 shadow-sm">
			<div class="flex items-center gap-4">
				<button id="open-sidebar" class="lg:hidden text-slate-600">
					<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==" alt="" class="w-6 h-6">
				</button>
				<div>
					<h1 class="text-xl font-bold text-slate-800">@yield('page-title', app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard')</h1>
					@hasSection('page-description')
						<p class="text-sm text-slate-500">@yield('page-description')</p>
					@endif
				</div>
			</div>
			<div class="hidden md:block text-right">
				@if(isset($pharmacy) && $pharmacy)
					<p class="text-sm font-semibold text-slate-800">{{ $pharmacy->name }}</p>
					<p class="text-xs text-slate-500">{{ app()->getLocale() === 'ar' ? 'الصيدلية' : 'Pharmacy' }}</p>
				@endif
			</div>
		</header>
		<main class="flex-1 overflow-y-auto p-4 lg:p-8">
			@if(session('success'))
				<div class="mb-6 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded shadow-sm" role="alert">
					<p class="font-bold">{{ app()->getLocale() === 'ar' ? 'نجح' : 'Success' }}</p>
					<p>{{ session('success') }}</p>
				</div>
			@endif
			@if(session('error'))
				<div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm" role="alert">
					<p class="font-bold">{{ app()->getLocale() === 'ar' ? 'خطأ' : 'Error' }}</p>
					<p>{{ session('error') }}</p>
				</div>
			@endif
			@yield('content')
		</main>
	</div>
</div>
<script>
	$(function(){
		const isRTL = $('html').attr('dir') === 'rtl';
		const $sidebar = $('#sidebar');
		const $overlay = $('#mobile-overlay');
		const closedClass = isRTL ? 'sidebar-closed-rtl' : 'sidebar-closed-ltr';
		if ($(window).width() < 1024) { $sidebar.addClass(closedClass); }
		$('#open-sidebar').on('click', function(){ $sidebar.removeClass(closedClass); $overlay.removeClass('hidden'); });
		$('#close-sidebar, #mobile-overlay').on('click', function(){ $sidebar.addClass(closedClass); $overlay.addClass('hidden'); });
		$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
	});
</script>
@stack('scripts')
</body>
</html>



