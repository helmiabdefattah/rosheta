@php
	$isAr = app()->getLocale() === 'ar';
	$client = Auth::guard('client')->user();
	$clientName = $client?->name ?? ($isAr ? 'عميلنا العزيز' : 'Guest');
@endphp

{{-- Landing: greeting, ads, promos, search, results, services, facts, contact --}}
<div class="mb-10 space-y-8">
	{{-- Header --}}
	<div class="px-1">
		<div class="flex items-center gap-2 text-slate-500 text-base">
			<span>{{ $isAr ? 'أهلًا بك' : 'Welcome' }}</span>
			<span aria-hidden="true">👋</span>
		</div>
		<h2 class="mt-1 text-xl md:text-2xl font-bold text-slate-900 truncate" title="{{ $clientName }}">{{ $clientName }}</h2>
	</div>

	{{-- Ads: mobile = one full-width slide; scroll-snap; auto-advance --}}
	<div class="space-y-2">
		<p class="text-sm font-semibold text-slate-800">{{ $isAr ? 'إعلانات' : 'Ads' }}</p>
		<div
			id="client-dashboard-ads"
			dir="ltr"
			class="flex gap-3 overflow-x-auto snap-x snap-mandatory scroll-smooth touch-pan-x pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
			aria-roledescription="carousel"
		>
			<a href="{{ route('client.test-requests.create', 'test') }}" data-ad-slide class="snap-start shrink-0 min-w-full md:min-w-[calc((100%-0.75rem)/2)] lg:min-w-[calc((100%-1.5rem)/3)] rounded-2xl overflow-hidden shadow-md border border-white/20 bg-gradient-to-br from-[#1976D2] to-[#1565C0] text-white p-5 flex flex-col justify-between min-h-[132px] hover:opacity-95 transition-opacity">
				<div>
					<p class="text-xs font-semibold uppercase tracking-wide text-white/80">{{ $isAr ? 'إعلان' : 'Ad' }}</p>
					<p class="font-bold text-lg leading-tight mt-1">{{ $isAr ? 'خصم على التحاليل الطبية' : 'Save on lab tests' }}</p>
					<p class="text-sm text-white/90 mt-1">{{ $isAr ? 'احجز تحليلك الآن' : 'Book your tests today' }}</p>
				</div>
				<span class="inline-flex self-start px-3 py-1.5 rounded-lg bg-white text-[#1976D2] text-sm font-semibold">{{ $isAr ? 'تصفح' : 'Browse' }}</span>
			</a>
			<a href="{{ route('client.medicine-requests.create') }}" data-ad-slide class="snap-start shrink-0 min-w-full md:min-w-[calc((100%-0.75rem)/2)] lg:min-w-[calc((100%-1.5rem)/3)] rounded-2xl overflow-hidden shadow-md border border-white/20 bg-gradient-to-br from-[#9C27B0] to-[#7B1FA2] text-white p-5 flex flex-col justify-between min-h-[132px] hover:opacity-95 transition-opacity">
				<div>
					<p class="text-xs font-semibold uppercase tracking-wide text-white/80">{{ $isAr ? 'إعلان' : 'Ad' }}</p>
					<p class="font-bold text-lg leading-tight mt-1">{{ $isAr ? 'توصيل أدوية سريع' : 'Fast medicine delivery' }}</p>
					<p class="text-sm text-white/90 mt-1">{{ $isAr ? 'اطلب من أقرب صيدلية' : 'Order from nearby pharmacies' }}</p>
				</div>
				<span class="inline-flex self-start px-3 py-1.5 rounded-lg bg-white text-[#9C27B0] text-sm font-semibold">{{ $isAr ? 'اطلب' : 'Order' }}</span>
			</a>
			<a href="{{ route('client.doctor-reservation.index') }}" data-ad-slide class="snap-start shrink-0 min-w-full md:min-w-[calc((100%-0.75rem)/2)] lg:min-w-[calc((100%-1.5rem)/3)] rounded-2xl overflow-hidden shadow-md border border-white/20 bg-gradient-to-br from-[#00897B] to-[#00695C] text-white p-5 flex flex-col justify-between min-h-[132px] hover:opacity-95 transition-opacity">
				<div>
					<p class="text-xs font-semibold uppercase tracking-wide text-white/80">{{ $isAr ? 'إعلان' : 'Ad' }}</p>
					<p class="font-bold text-lg leading-tight mt-1">{{ $isAr ? 'حجز موعد طبيب' : 'Book a doctor' }}</p>
					<p class="text-sm text-white/90 mt-1">{{ $isAr ? 'عيادات موثوقة' : 'Trusted clinics' }}</p>
				</div>
				<span class="inline-flex self-start px-3 py-1.5 rounded-lg bg-white text-[#00897B] text-sm font-semibold">{{ $isAr ? 'احجز' : 'Book' }}</span>
			</a>
			<a href="{{ route('client.offers.index') }}" data-ad-slide class="snap-start shrink-0 min-w-full md:min-w-[calc((100%-0.75rem)/2)] lg:min-w-[calc((100%-1.5rem)/3)] rounded-2xl overflow-hidden shadow-md border border-white/20 bg-gradient-to-br from-[#F57C00] to-[#E65100] text-white p-5 flex flex-col justify-between min-h-[132px] hover:opacity-95 transition-opacity">
				<div>
					<p class="text-xs font-semibold uppercase tracking-wide text-white/80">{{ $isAr ? 'إعلان' : 'Ad' }}</p>
					<p class="font-bold text-lg leading-tight mt-1">{{ $isAr ? 'عروض حصرية' : 'Exclusive offers' }}</p>
					<p class="text-sm text-white/90 mt-1">{{ $isAr ? 'وفر على طلباتك' : 'Save on your orders' }}</p>
				</div>
				<span class="inline-flex self-start px-3 py-1.5 rounded-lg bg-white text-[#E65100] text-sm font-semibold">{{ $isAr ? 'اكتشف' : 'Discover' }}</span>
			</a>
		</div>
	</div>

	{{-- Promo carousel --}}
	<div>
		<p class="text-base font-bold text-slate-900 mb-3">{{ $isAr ? 'عروض مميزة' : 'Featured offers' }}</p>
		<div class="flex gap-4 overflow-x-auto pb-2 snap-x snap-mandatory scroll-smooth [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
			<a href="{{ route('client.test-requests.create', 'test') }}" class="snap-start shrink-0 w-[min(100%,320px)] rounded-2xl overflow-hidden shadow-md border border-white/20 bg-gradient-to-br from-[#1976D2] to-[#1565C0] text-white p-5 flex flex-col justify-between min-h-[140px] hover:opacity-95 transition-opacity">
				<div>
					<p class="font-bold text-lg leading-tight">{{ $isAr ? 'خصم 25% على التحاليل' : '25% off lab tests' }}</p>
					<p class="text-sm text-white/90 mt-1">{{ $isAr ? 'لفترة محدودة' : 'Limited time' }}</p>
				</div>
				<span class="inline-flex self-start px-3 py-1.5 rounded-lg bg-white text-[#1976D2] text-sm font-semibold">{{ $isAr ? 'اطلب الآن' : 'Order now' }}</span>
			</a>
			<div class="snap-start shrink-0 w-[min(100%,320px)] rounded-2xl overflow-hidden shadow-md border border-white/20 bg-gradient-to-br from-[#4CAF50] to-[#388E3C] text-white p-5 flex flex-col justify-between min-h-[140px]">
				<div>
					<p class="font-bold text-lg leading-tight">{{ $isAr ? 'توصيل مجاني للأدوية' : 'Free medicine delivery' }}</p>
					<p class="text-sm text-white/90 mt-1">{{ $isAr ? 'للطلبات فوق 500 جنيه' : 'On orders over 500 EGP' }}</p>
				</div>
			</div>
			<a href="{{ route('client.medicine-requests.create') }}" class="snap-start shrink-0 w-[min(100%,320px)] rounded-2xl overflow-hidden shadow-md border border-white/20 bg-gradient-to-br from-[#9C27B0] to-[#7B1FA2] text-white p-5 flex flex-col justify-between min-h-[140px] hover:opacity-95 transition-opacity">
				<div>
					<p class="font-bold text-lg leading-tight">{{ $isAr ? 'خصم 20% على الأدوية' : '20% off medicines' }}</p>
					<p class="text-sm text-white/90 mt-1">{{ $isAr ? 'عروض الأسبوع' : 'Weekly deals' }}</p>
				</div>
				<span class="inline-flex self-start px-3 py-1.5 rounded-lg bg-white text-[#9C27B0] text-sm font-semibold">{{ $isAr ? 'تسوق الآن' : 'Shop now' }}</span>
			</a>
		</div>
	</div>

	{{-- Search: service + location — collapsed by default; opens when filters were submitted --}}
	@php
		$searchPanelOpen = request()->filled('provider_type');
	@endphp
	<div class="bg-white rounded-lg shadow border border-gray-200">
		<div class="p-4 border-b border-gray-200 filter-toggle cursor-pointer select-none" id="filterToggle" role="button" tabindex="0" aria-expanded="{{ $searchPanelOpen ? 'true' : 'false' }}" aria-controls="filterContent">
			<div class="flex justify-between items-center gap-3">
				<div class="min-w-0">
					<h3 class="text-lg font-semibold text-gray-800">
						{{ $isAr ? 'البحث عن مقدمي الخدمات' : 'Search service providers' }}
					</h3>
					<p class="text-sm text-gray-500 mt-0.5">{{ $isAr ? 'اختر نوع الخدمة والمحافظة والمدينة ثم اضغط بحث.' : 'Choose service type, governorate, and city, then search.' }}</p>
				</div>
				<svg id="filterIcon" class="w-5 h-5 text-gray-600 shrink-0 transition-transform duration-200 {{ $searchPanelOpen ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
				</svg>
			</div>
		</div>
		<div class="filter-content {{ $searchPanelOpen ? 'expanded' : 'collapsed' }}" id="filterContent">
			<div class="p-6">
				<form method="GET" action="{{ route('client.dashboard') }}" class="space-y-4" id="filterForm">
					<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
						<div>
							<label class="block text-sm font-medium text-gray-700 mb-1" for="provider_type">
								{{ $isAr ? 'نوع مقدم الخدمة' : 'Service provider type' }}
								<span class="text-red-500">*</span>
							</label>
							<select name="provider_type" id="provider_type" class="w-full border border-gray-300 rounded-md p-2 text-sm" required>
								<option value="">{{ $isAr ? 'اختر النوع' : 'Select type' }}</option>
								<option value="radiology_lab" @selected(request('provider_type') === 'radiology_lab')>{{ $isAr ? 'معمل أشعة' : 'Radiology lab' }}</option>
								<option value="test_lab" @selected(request('provider_type') === 'test_lab')>{{ $isAr ? 'معمل تحاليل' : 'Test lab' }}</option>
								<option value="pharmacy" @selected(request('provider_type') === 'pharmacy')>{{ $isAr ? 'صيدلية' : 'Pharmacy' }}</option>
								<option value="nursing" @selected(request('provider_type') === 'nursing')>{{ $isAr ? 'تمريض منزلي' : 'Home nursing' }}</option>
								<option value="charity" @selected(request('provider_type') === 'charity')>{{ $isAr ? 'منظمة خيرية' : 'Charitable organization' }}</option>
								<option value="doctor" @selected(request('provider_type') === 'doctor')>{{ $isAr ? 'أطباء وعيادات' : 'Doctors & clinics' }}</option>
							</select>
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-700 mb-1" for="governorate_id">
								{{ $isAr ? 'المحافظة' : 'Governorate' }}
							</label>
							<select name="governorate_id" id="governorate_id" class="w-full border border-gray-300 rounded-md p-2 text-sm">
								<option value="">{{ $isAr ? 'اختر المحافظة' : 'Select governorate' }}</option>
								@foreach($governorates ?? [] as $governorate)
									<option value="{{ $governorate->id }}" @selected(request('governorate_id') == $governorate->id)>
										{{ $isAr ? ($governorate->name_ar ?? $governorate->name) : ($governorate->name ?? $governorate->name_ar) }}
									</option>
								@endforeach
							</select>
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-700 mb-1" for="city_id">
								{{ $isAr ? 'المدينة' : 'City' }}
								<span class="text-gray-400 text-xs">({{ $isAr ? 'اختياري' : 'Optional' }})</span>
							</label>
							<select name="city_id" id="city_id" class="w-full border border-gray-300 rounded-md p-2 text-sm">
								<option value="">{{ $isAr ? 'جميع المدن' : 'All cities' }}</option>
								@foreach($cities ?? [] as $city)
									<option value="{{ $city->id }}" @selected(request('city_id') == $city->id)>
										{{ $isAr ? ($city->name_ar ?? $city->name) : ($city->name ?? $city->name_ar) }}
									</option>
								@endforeach
							</select>
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-700 mb-1" for="area_id">
								{{ $isAr ? 'المنطقة' : 'Area' }}
								<span class="text-gray-400 text-xs">({{ $isAr ? 'اختياري' : 'Optional' }})</span>
							</label>
							<select name="area_id" id="area_id" class="w-full border border-gray-300 rounded-md p-2 text-sm">
								<option value="">{{ $isAr ? 'جميع المناطق' : 'All areas' }}</option>
								@foreach($areas ?? [] as $area)
									<option value="{{ $area->id }}" @selected(request('area_id') == $area->id)>
										{{ $isAr ? ($area->name_ar ?? $area->name) : ($area->name ?? $area->name_ar) }}
									</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="flex flex-wrap gap-2">
						<button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium">
							{{ $isAr ? 'بحث' : 'Search' }}
						</button>
						<a href="{{ route('client.dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm font-medium">
							{{ $isAr ? 'إعادة تعيين' : 'Reset' }}
						</a>
					</div>
				</form>
			</div>
		</div>
	</div>

	@include('client.partials.dashboard-service-provider-results')

	{{-- Services grid --}}
	<div>
		<p class="text-base font-bold text-slate-900 mb-4">{{ $isAr ? 'خدماتنا' : 'Our services' }}</p>
		<div class="grid grid-cols-2 gap-4">
			<a href="{{ route('client.doctor-reservation.index') }}" class="landing-service-card group relative rounded-[1.25rem] overflow-hidden min-h-[160px] border border-slate-200/80 shadow-md bg-white">
				<div class="absolute inset-0 bg-gradient-to-br from-teal-500/25 to-cyan-700/15"></div>
				<div class="relative p-4 h-full flex flex-col justify-between">
					<span class="inline-flex w-12 h-12 rounded-full bg-[#00897B] text-white items-center justify-center shadow-lg"><i class="bi bi-hospital text-xl"></i></span>
					<div class="rounded-xl bg-white/95 border border-white/80 shadow-sm px-3 py-2 mt-4">
						<p class="font-extrabold text-slate-900 text-[15px] leading-tight">{{ $isAr ? 'العيادات والأطباء' : 'Clinics & doctors' }}</p>
						<p class="text-[11px] font-semibold text-slate-600 mt-0.5">{{ $isAr ? 'ابحث واحجز موعدك' : 'Search & book a visit' }}</p>
					</div>
				</div>
			</a>
			<a href="{{ route('client.medicine-requests.create') }}" class="landing-service-card group relative rounded-[1.25rem] overflow-hidden min-h-[160px] border border-slate-200/80 shadow-md bg-white">
				<div class="absolute inset-0 bg-gradient-to-br from-teal-500/25 to-emerald-600/15"></div>
				<div class="relative p-4 h-full flex flex-col justify-between">
					<span class="inline-flex w-12 h-12 rounded-full bg-[#009688] text-white items-center justify-center shadow-lg"><i class="bi bi-receipt text-xl"></i></span>
					<div class="rounded-xl bg-white/95 border border-white/80 shadow-sm px-3 py-2 mt-4">
						<p class="font-extrabold text-slate-900 text-[15px] leading-tight">{{ $isAr ? 'طلبات الأدوية' : 'Medicine orders' }}</p>
						<p class="text-[11px] font-semibold text-slate-600 mt-0.5">{{ $isAr ? 'صرف الأدوية بسهولة' : 'Easy dispensing' }}</p>
					</div>
				</div>
			</a>
			<a href="{{ route('client.test-requests.create', 'test') }}" class="landing-service-card group relative rounded-[1.25rem] overflow-hidden min-h-[160px] border border-slate-200/80 shadow-md bg-white">
				<div class="absolute inset-0 bg-gradient-to-br from-blue-500/25 to-blue-700/15"></div>
				<div class="relative p-4 h-full flex flex-col justify-between">
					<span class="inline-flex w-12 h-12 rounded-full bg-[#1976D2] text-white items-center justify-center shadow-lg"><i class="bi bi-droplet text-xl"></i></span>
					<div class="rounded-xl bg-white/95 border border-white/80 shadow-sm px-3 py-2 mt-4">
						<p class="font-extrabold text-slate-900 text-[15px] leading-tight">{{ $isAr ? 'حجز التحاليل' : 'Book lab tests' }}</p>
						<p class="text-[11px] font-semibold text-slate-600 mt-0.5">{{ $isAr ? 'من المنزل أو المعمل' : 'Home or lab' }}</p>
					</div>
				</div>
			</a>
			<a href="{{ route('client.test-requests.create', 'radiology') }}" class="landing-service-card group relative rounded-[1.25rem] overflow-hidden min-h-[160px] border border-slate-200/80 shadow-md bg-white">
				<div class="absolute inset-0 bg-gradient-to-br from-violet-500/25 to-purple-700/15"></div>
				<div class="relative p-4 h-full flex flex-col justify-between">
					<span class="inline-flex w-12 h-12 rounded-full bg-[#673AB7] text-white items-center justify-center shadow-lg"><i class="bi bi-bullseye text-xl"></i></span>
					<div class="rounded-xl bg-white/95 border border-white/80 shadow-sm px-3 py-2 mt-4">
						<p class="font-extrabold text-slate-900 text-[15px] leading-tight">{{ $isAr ? 'أشعة تشخيصية' : 'Diagnostic imaging' }}</p>
						<p class="text-[11px] font-semibold text-slate-600 mt-0.5">{{ $isAr ? 'حجز مراكز الأشعة' : 'Radiology centers' }}</p>
					</div>
				</div>
			</a>
			<a href="{{ route('client.offers.index') }}" class="landing-service-card group relative rounded-[1.25rem] overflow-hidden min-h-[160px] border border-slate-200/80 shadow-md bg-white">
				<div class="absolute inset-0 bg-gradient-to-br from-orange-500/25 to-amber-600/15"></div>
				<div class="relative p-4 h-full flex flex-col justify-between">
					<span class="inline-flex w-12 h-12 rounded-full bg-[#F57C00] text-white items-center justify-center shadow-lg"><i class="bi bi-tag text-xl"></i></span>
					<div class="rounded-xl bg-white/95 border border-white/80 shadow-sm px-3 py-2 mt-4">
						<p class="font-extrabold text-slate-900 text-[15px] leading-tight">{{ $isAr ? 'العروض' : 'Offers' }}</p>
						<p class="text-[11px] font-semibold text-slate-600 mt-0.5">{{ $isAr ? 'عروض وتوفير' : 'Deals & savings' }}</p>
					</div>
				</div>
			</a>
			<a href="{{ route('client.requests.index') }}" class="landing-service-card group relative rounded-[1.25rem] overflow-hidden min-h-[160px] border border-slate-200/80 shadow-md bg-white">
				<div class="absolute inset-0 bg-gradient-to-br from-purple-500/20 to-pink-600/15"></div>
				<div class="relative p-4 h-full flex flex-col justify-between">
					<span class="inline-flex w-12 h-12 rounded-full bg-[#9C27B0] text-white items-center justify-center shadow-lg"><i class="bi bi-bag-check text-xl"></i></span>
					<div class="rounded-xl bg-white/95 border border-white/80 shadow-sm px-3 py-2 mt-4">
						<p class="font-extrabold text-slate-900 text-[15px] leading-tight">{{ $isAr ? 'الطلبات الحالية' : 'All requests' }}</p>
						<p class="text-[11px] font-semibold text-slate-600 mt-0.5">{{ $isAr ? 'عرض ومتابعة' : 'View & track' }}</p>
					</div>
				</div>
			</a>
			<button type="button" id="landing-free-services-btn" class="landing-service-card text-start group relative rounded-[1.25rem] overflow-hidden min-h-[160px] border border-slate-200/80 shadow-md bg-white w-full cursor-pointer">
				<div class="absolute inset-0 bg-gradient-to-br from-green-500/25 to-green-700/15"></div>
				<div class="relative p-4 h-full flex flex-col justify-between">
					<span class="inline-flex w-12 h-12 rounded-full bg-[#43A047] text-white items-center justify-center shadow-lg"><i class="bi bi-heart-pulse text-xl"></i></span>
					<div class="rounded-xl bg-white/95 border border-white/80 shadow-sm px-3 py-2 mt-4">
						<p class="font-extrabold text-slate-900 text-[15px] leading-tight">{{ $isAr ? 'خدمات مجانية' : 'Free services' }}</p>
						<p class="text-[11px] font-semibold text-slate-600 mt-0.5">{{ $isAr ? 'قريبًا' : 'Coming soon' }}</p>
					</div>
				</div>
			</button>
		</div>
		<p id="landing-free-toast" class="hidden mt-3 text-sm text-center text-green-800 bg-green-50 border border-green-200 rounded-lg py-2 px-3">{{ $isAr ? 'قريبًا: خدمات مجانية لعملائنا المميزين' : 'Coming soon: free services for loyal clients' }}</p>
		<div class="mt-4 flex flex-wrap gap-2 justify-center">
			<a href="{{ route('client.doctor-reservation.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200 text-sm font-semibold text-slate-800 shadow-sm hover:border-primary/40 hover:text-primary transition-colors">
				<i class="bi bi-calendar-heart text-primary"></i>
				{{ $isAr ? 'حجز موعد طبيب' : 'Book a doctor visit' }}
			</a>
			<a href="{{ route('client.service-providers.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200 text-sm font-semibold text-slate-800 shadow-sm hover:border-primary/40 hover:text-primary transition-colors">
				<i class="bi bi-geo-alt text-primary"></i>
				{{ $isAr ? 'مقدمو الخدمة' : 'Service providers' }}
			</a>
		</div>
	</div>

	{{-- Facts --}}
	<div>
		<p class="text-base font-bold text-slate-900 mb-4">{{ $isAr ? 'نحن نفخر ب' : 'We are proud of' }}</p>
		<div class="flex flex-wrap justify-center gap-6 sm:gap-10">
			<div class="w-[100px] h-[100px] rounded-full bg-white border border-slate-200 shadow-md flex flex-col items-center justify-center p-2 text-center">
				<span class="text-xl font-bold text-[#1B5E20]">100+</span>
				<span class="text-[11px] font-semibold text-slate-600 leading-tight mt-1">{{ $isAr ? 'صيدلية' : 'Pharmacies' }}</span>
			</div>
			<div class="w-[100px] h-[100px] rounded-full bg-white border border-slate-200 shadow-md flex flex-col items-center justify-center p-2 text-center">
				<span class="text-xl font-bold text-[#0D47A1]">150+</span>
				<span class="text-[11px] font-semibold text-slate-600 leading-tight mt-1">{{ $isAr ? 'معمل تحاليل' : 'Labs' }}</span>
			</div>
			<div class="w-[100px] h-[100px] rounded-full bg-white border border-slate-200 shadow-md flex flex-col items-center justify-center p-2 text-center">
				<span class="text-xl font-bold text-[#4A148C]">1000+</span>
				<span class="text-[11px] font-semibold text-slate-600 leading-tight mt-1">{{ $isAr ? 'عميل راضٍ' : 'Happy clients' }}</span>
			</div>
		</div>
	</div>

	{{-- Contact --}}
	<div>
		<p class="text-base font-bold text-slate-900 mb-4">{{ $isAr ? 'تواصل معنا' : 'Contact us' }}</p>
		<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
			<p class="text-center text-sm font-bold text-slate-800 mb-3">{{ $isAr ? 'تواصل معنا عبر' : 'Reach us via' }}</p>
			<div class="flex flex-wrap justify-center gap-6">
				<a href="https://wa.me/201060446447" target="_blank" rel="noopener noreferrer" class="flex flex-col items-center gap-2 w-20 text-center group">
					<span class="w-[50px] h-[50px] rounded-full bg-[#25D366] text-white flex items-center justify-center shadow-md group-hover:scale-105 transition-transform"><i class="bi bi-whatsapp text-xl"></i></span>
					<span class="text-xs font-semibold text-slate-600">{{ $isAr ? 'واتساب' : 'WhatsApp' }}</span>
				</a>
				<a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="flex flex-col items-center gap-2 w-20 text-center group">
					<span class="w-[50px] h-[50px] rounded-full bg-[#1877F2] text-white flex items-center justify-center shadow-md group-hover:scale-105 transition-transform"><i class="bi bi-facebook text-xl"></i></span>
					<span class="text-xs font-semibold text-slate-600">{{ $isAr ? 'فيسبوك' : 'Facebook' }}</span>
				</a>
				<a href="tel:01551095853" class="flex flex-col items-center gap-2 w-20 text-center group">
					<span class="w-[50px] h-[50px] rounded-full bg-[#34B7F1] text-white flex items-center justify-center shadow-md group-hover:scale-105 transition-transform"><i class="bi bi-telephone text-xl"></i></span>
					<span class="text-xs font-semibold text-slate-600">{{ $isAr ? 'اتصل' : 'Call' }}</span>
				</a>
			</div>
		</div>
	</div>
</div>

<script>
(function () {
	var freeBtn = document.getElementById('landing-free-services-btn');
	var freeToast = document.getElementById('landing-free-toast');
	if (freeBtn && freeToast) {
		freeBtn.addEventListener('click', function () {
			freeToast.classList.remove('hidden');
			setTimeout(function () { freeToast.classList.add('hidden'); }, 4000);
		});
	}

	/* Ads carousel: auto-advance every 4.5s; mobile = one full-width slide (min-w-full) */
	var adsScroller = document.getElementById('client-dashboard-ads');
	if (adsScroller) {
		var slides = adsScroller.querySelectorAll('[data-ad-slide]');
		if (slides.length > 1) {
			var adIdx = 0;
			setInterval(function () {
				adIdx = (adIdx + 1) % slides.length;
				var slide = slides[adIdx];
				/* Scroll only the horizontal strip — never scrollIntoView (it scrolls the whole page). */
				var maxLeft = Math.max(0, adsScroller.scrollWidth - adsScroller.clientWidth);
				var left = Math.min(slide.offsetLeft, maxLeft);
				adsScroller.scrollTo({ left: left, behavior: 'smooth' });
			}, 4500);
		}
	}
})();
</script>
