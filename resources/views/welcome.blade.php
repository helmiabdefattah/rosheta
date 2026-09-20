@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'مستشفي اون - الجيل القادم من الرعاية الصحية' : 'Mostashfa-on - Next Gen Healthcare')

@push('background-blobs')
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="blob bg-blue-400 w-96 h-96 rounded-full top-0 left-0 -translate-x-1/2 -translate-y-1/2"></div>
        <div class="blob bg-teal-300 w-96 h-96 rounded-full bottom-0 right-0 translate-x-1/2 translate-y-1/2" style="animation-delay: -2s"></div>
    </div>
@endpush

@section('content')

    @php
        $playStoreUrl = 'https://play.google.com/store/apps/details?id=com.helmi.mostashfaon';
    @endphp

    <section class="relative min-h-screen flex items-center pt-28 pb-20 overflow-hidden">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-8 items-center">
                
                <div class="text-center lg:text-start lg:rtl:text-right space-y-8 reveal active">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-blue-50 border border-blue-100 rounded-full text-blue-600 text-sm font-semibold">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                        </span>
                        {{ app()->getLocale() === 'ar' ? 'الجيل القادم من الرعاية الصحية' : 'Next Gen Healthcare' }}
                    </div>

                    <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black text-slate-900 leading-[1.1] tracking-tight">
                        {{ app()->getLocale() === 'ar' ? 'صحتك في' : 'Your Health in' }}
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-teal-400 block mt-2">
                            {{ app()->getLocale() === 'ar' ? 'أيدٍ أمينة' : 'Safe Hands' }}
                        </span>
                    </h1>
                    
                    <p class="text-lg sm:text-xl text-slate-600 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                        {{ app()->getLocale() === 'ar' 
                            ? 'تجربة طبية متكاملة تبدأ من هاتفك. استشارات فورية، حجز أطباء، وصيدلية متكاملة، كل ذلك في تطبيق واحد ذكي.' 
                            : 'A complete medical experience starting from your phone. Instant consultations, doctor bookings, and a full pharmacy, all in one smart app.' }}
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="{{ route('register') }}" class="px-8 py-4 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 transition-all shadow-xl shadow-slate-900/20 hover:-translate-y-1 flex items-center justify-center gap-2">
                            <span>{{ app()->getLocale() === 'ar' ? 'إنشاء حساب' : 'Register' }}</span>
                            <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                        </a>
                        <a href="{{ route('login') }}" class="px-8 py-4 bg-white text-slate-700 border border-slate-200 rounded-xl font-bold hover:bg-slate-50 transition-all hover:-translate-y-1">
                            {{ app()->getLocale() === 'ar' ? 'تسجيل الدخول' : 'Login' }}
                        </a>

                        {{-- The sandbox runs on its own deployment, so from
                             here it is a link out to it. Shown unless an
                             administrator has switched it off. --}}
                        @include('demo.try-free', [
                            'class' => 'px-8 py-4 bg-emerald-500 text-white rounded-xl font-bold hover:bg-emerald-600 transition-all shadow-xl shadow-emerald-500/20 hover:-translate-y-1 flex items-center justify-center gap-2',
                        ])
                        <a href="{{ $playStoreUrl }}" target="_blank" rel="noopener noreferrer" class="lg:hidden px-8 py-4 bg-primary text-white rounded-xl font-bold hover:bg-blue-600 transition-all shadow-xl shadow-primary/20 hover:-translate-y-1 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M3.609 1.814L13.792 12 3.61 22.186a1.003 1.003 0 0 1-.61-.92V2.734a1.003 1.003 0 0 1 .609-.92zm10.89 10.893l2.302 2.302-10.937 6.333 8.635-8.635zm3.199-3.198l2.807 1.626a1 1 0 0 1 0 1.73l-2.808 1.626L15.206 12l2.492-2.491zM5.864 2.658L16.8 9.99l-2.302 2.302-8.634-8.634z"/>
                            </svg>
                            <span>{{ app()->getLocale() === 'ar' ? 'حمّل التطبيق' : 'Download App' }}</span>
                        </a>
                    </div>

                    <div class="pt-8 border-t border-slate-200/60 flex items-center justify-center lg:justify-start gap-8">
                        <div>
                            <p class="text-3xl font-bold text-slate-900">50K+</p>
                            <p class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'مريض' : 'Patients' }}</p>
                        </div>
                        <div class="w-px h-10 bg-slate-200"></div>
                        <div>
                            <p class="text-3xl font-bold text-slate-900">1K+</p>
                            <p class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'طبيب' : 'Doctors' }}</p>
                        </div>
                        <div class="w-px h-10 bg-slate-200"></div>
                        <div>
                            <p class="text-3xl font-bold text-slate-900">4.9</p>
                            <p class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'تقييم' : 'Rating' }}</p>
                        </div>
                    </div>
                </div>

                <div class="relative lg:h-[800px] flex items-center justify-center reveal" style="transition-delay: 200ms;">
                    <div class="absolute inset-0 bg-gradient-to-tr from-primary/20 to-teal-200/20 rounded-full blur-3xl transform rotate-12 scale-75"></div>
                    
                    <div class="relative w-[300px] sm:w-[350px] h-[600px] sm:h-[700px] bg-slate-900 rounded-[3rem] border-[8px] border-slate-900 shadow-2xl overflow-hidden ring-1 ring-white/20">
                        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-32 h-7 bg-black rounded-b-2xl z-20"></div>
                        
                        <div class="w-full h-full bg-slate-50 overflow-hidden relative">
                            <div class="bg-primary text-white p-6 pt-12 pb-8 rounded-b-[2rem] shadow-lg">
                                <div class="flex justify-between items-center mb-6">
                                    <div>
                                        <p class="text-blue-100 text-xs">{{ app()->getLocale() === 'ar' ? 'مرحباً،' : 'Welcome,' }}</p>
                                        <p class="font-bold text-lg">{{ app()->getLocale() === 'ar' ? 'أحمد محمد' : 'Ahmed Mohamed' }}</p>
                                    </div>
                                    <img src="/images/mo-logo.png" alt="Mostashfa-on Icon" class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm p-1 object-contain">
                                </div>
                                <div class="w-full h-12 bg-white/20 rounded-xl backdrop-blur-md flex items-center px-4 text-white/70 text-sm">
                                    <svg class="w-5 h-5 me-2 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                    {{ app()->getLocale() === 'ar' ? 'ابحث عن طبيب، تخصص...' : 'Find doctor, specialty...' }}
                                </div>
                            </div>

                            <div class="p-6 pb-20 space-y-6">
                                <div class="flex gap-4 overflow-x-hidden">
                                    <div class="w-20 h-24 bg-white rounded-xl shadow-sm flex flex-col items-center justify-center gap-2 p-2">
                                        <div class="w-10 h-10 bg-blue-50 rounded-full text-blue-500 flex items-center justify-center">🩺</div>
                                        <span class="text-[10px] font-bold text-slate-600">Doctor</span>
                                    </div>
                                    <div class="w-20 h-24 bg-white rounded-xl shadow-sm flex flex-col items-center justify-center gap-2 p-2">
                                        <div class="w-10 h-10 bg-teal-50 rounded-full text-teal-500 flex items-center justify-center">💊</div>
                                        <span class="text-[10px] font-bold text-slate-600">Pharmacy</span>
                                    </div>
                                    <div class="w-20 h-24 bg-white rounded-xl shadow-sm flex flex-col items-center justify-center gap-2 p-2">
                                        <div class="w-10 h-10 bg-purple-50 rounded-full text-purple-500 flex items-center justify-center">🔬</div>
                                        <span class="text-[10px] font-bold text-slate-600">Labs</span>
                                    </div>
                                </div>

                                <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex flex-col items-center text-center gap-3">
                                    <img src="/images/mo-logo.png" alt="Mostashfa-on" class="w-14 h-14 rounded-xl object-contain">
                                    <p class="text-sm font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? 'حمّل تطبيق مستشفى-أون' : 'Get the Mostashfa-on app' }}</p>
                                    <a href="{{ $playStoreUrl }}" target="_blank" rel="noopener noreferrer"
                                       class="flex w-full items-center justify-center gap-2 h-10 bg-primary text-white rounded-lg text-sm font-bold hover:bg-blue-600 transition-colors">
                                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M3.609 1.814L13.792 12 3.61 22.186a1.003 1.003 0 0 1-.61-.92V2.734a1.003 1.003 0 0 1 .609-.92zm10.89 10.893l2.302 2.302-10.937 6.333 8.635-8.635zm3.199-3.198l2.807 1.626a1 1 0 0 1 0 1.73l-2.808 1.626L15.206 12l2.492-2.491zM5.864 2.658L16.8 9.99l-2.302 2.302-8.634-8.634z"/>
                                        </svg>
                                        <span>{{ app()->getLocale() === 'ar' ? 'تحميل' : 'Download' }}</span>
                                    </a>
                                </div>

                                <a href="{{ $playStoreUrl }}" target="_blank" rel="noopener noreferrer"
                                   class="flex w-full items-center justify-center gap-2 py-4 px-4 bg-slate-900 text-white rounded-2xl font-black text-base sm:text-lg text-center shadow-lg shadow-slate-900/25 hover:bg-slate-800 transition-all hover:-translate-y-0.5">
                                    <svg class="w-6 h-6 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path d="M3.609 1.814L13.792 12 3.61 22.186a1.003 1.003 0 0 1-.61-.92V2.734a1.003 1.003 0 0 1 .609-.92zm10.89 10.893l2.302 2.302-10.937 6.333 8.635-8.635zm3.199-3.198l2.807 1.626a1 1 0 0 1 0 1.73l-2.808 1.626L15.206 12l2.492-2.491zM5.864 2.658L16.8 9.99l-2.302 2.302-8.634-8.634z"/>
                                    </svg>
                                    <span>{{ app()->getLocale() === 'ar' ? 'احصل على التطبيق' : 'Get the App' }}</span>
                                </a>
                            </div>
                            
                            <div class="absolute bottom-0 w-full h-16 bg-white border-t border-slate-100 flex justify-around items-center px-6">
                                <div class="w-6 h-6 rounded-full bg-primary"></div>
                                <div class="w-6 h-6 rounded-full bg-slate-200"></div>
                                <div class="w-6 h-6 rounded-full bg-slate-200"></div>
                                <div class="w-6 h-6 rounded-full bg-slate-200"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Try it for real, before any of the claims below. Doctors and their
         assistants are the ones who have to be convinced by more than a
         screenshot, and this is a whole working clinic they can open in a few
         seconds without handing over anything. --}}
    <section id="demo" class="py-16 sm:py-20 relative">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto reveal">
                @include('demo.start-card', ['wrapperClass' => ''])
            </div>
        </div>
    </section>

    <section id="features" class="py-24 bg-white relative">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20 reveal">
                <span class="text-primary font-bold tracking-wider uppercase text-sm mb-2 block">{{ app()->getLocale() === 'ar' ? 'مميزات النظام' : 'System Features' }}</span>
                <h2 class="text-4xl sm:text-5xl font-black text-slate-900 mb-6">
                    {{ app()->getLocale() === 'ar' ? 'نظام متكامل لإدارة العيادات' : 'A complete clinic management system' }}
                </h2>
                <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                    {{ app()->getLocale() === 'ar'
                        ? 'من الاستقبال والحجز حتى الكشف والطباعة، مع تطبيق جوال يربط المريض بعيادتك.'
                        : 'From reception and booking to examination and printing, with a mobile app that connects patients to your clinic.' }}
                </p>
            </div>
            
            @php $isAr = app()->getLocale() === 'ar'; @endphp

            {{-- ── Workflow infographic: the patient journey through the system ── --}}
            @php
                $steps = [
                    ['label' => $isAr ? 'الاستقبال' : 'Reception',        'text' => 'text-blue-600',    'border' => 'border-blue-200',    'icon' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'],
                    ['label' => $isAr ? 'حجز إلكتروني' : 'Online Booking', 'text' => 'text-teal-600',    'border' => 'border-teal-200',    'icon' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'],
                    ['label' => $isAr ? 'الكشف' : 'Examination',           'text' => 'text-indigo-600',  'border' => 'border-indigo-200',  'icon' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>'],
                    ['label' => $isAr ? 'الطباعة' : 'Print',               'text' => 'text-rose-600',    'border' => 'border-rose-200',    'icon' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>'],
                    ['label' => $isAr ? 'تطبيق المريض' : 'Patient App',    'text' => 'text-emerald-600', 'border' => 'border-emerald-200', 'icon' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>'],
                ];
            @endphp
            <div class="max-w-6xl mx-auto mb-24 reveal">
                <div class="relative grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-y-10 gap-x-4">
                    <div class="hidden md:block absolute top-8 left-[10%] right-[10%] h-0.5 bg-gradient-to-r from-blue-400 via-indigo-400 to-emerald-400 rtl:bg-gradient-to-l"></div>
                    @foreach($steps as $i => $step)
                        <div class="relative flex flex-col items-center text-center gap-3">
                            <div class="relative z-10 w-16 h-16 rounded-2xl bg-white border-2 {{ $step['border'] }} shadow-lg shadow-slate-900/5 flex items-center justify-center {{ $step['text'] }}">
                                {!! $step['icon'] !!}
                            </div>
                            <span class="text-sm font-bold text-slate-700">{{ $step['label'] }}</span>
                            <span class="absolute -top-2 {{ $isAr ? 'left-1/2 translate-x-8' : 'right-1/2 -translate-x-8' }} text-xs font-black text-slate-300">0{{ $i + 1 }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ── Illustrated feature cards: each with a hand-built SVG drawing ── --}}
            @php
                $features = $isAr ? [
                    ['title' => 'إدارة عيادة متكاملة',        'desc' => 'إدارة كاملة للعيادة تشمل السجل الطبي، الاستقبال، شركات التأمين، والحجز الإلكتروني في مكان واحد.'],
                    ['title' => 'شاشة كشف قابلة للتخصيص',     'desc' => 'صمّم شاشة الكشف بالكامل بما يناسب تخصصك وطريقة عملك — حقول وأقسام مرنة تُفعّلها كما تريد.'],
                    ['title' => 'شاشات تفاعلية للانتظار',     'desc' => 'شاشات عرض تفاعلية توضّح الحجوزات ودور المريض الحالي ومن يليه في قائمة الانتظار.'],
                    ['title' => 'طباعة الحجوزات والروشتات والفواتير', 'desc' => 'اطبع تذاكر الحجز، الوصفات الطبية، والفواتير مباشرة على طابعة العيادة الحرارية.'],
                    ['title' => 'تطبيق جوال للمريض',          'desc' => 'يتابع المريض دوره في الطابور وسجله الطبي، يتواصل مباشرة، ويستقبل إشعارات مخصصة من مساعد الطبيب.'],
                ] : [
                    ['title' => 'Full Clinic Management',      'desc' => 'Complete clinic operations — medical history, reception, insurance companies, and online reservation in one place.'],
                    ['title' => 'Customizable Examination Screen', 'desc' => 'Shape the examination screen entirely around your specialty and workflow, with flexible fields you switch on as you need.'],
                    ['title' => 'Interactive Waiting Screens', 'desc' => 'Interactive display screens showing reservations, the current patient in service, and who is next in the queue.'],
                    ['title' => 'Bookings, Prescriptions & Invoices', 'desc' => 'Print booking tickets, prescriptions, and invoices straight to the clinic\'s thermal printer.'],
                    ['title' => 'Patient Mobile App',          'desc' => 'Patients track their queue turn and medical history, communicate directly, and receive custom notifications from the assistant.'],
                ];
            @endphp

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
                @foreach($features as $index => $feature)
                <div class="group flex flex-col rounded-[2rem] bg-slate-50 border border-slate-100 hover:border-blue-100 hover:shadow-xl hover:shadow-blue-900/5 transition-all duration-300 hover:-translate-y-2 overflow-hidden reveal {{ $index === 4 ? 'lg:col-span-1 md:col-span-2' : '' }}" style="transition-delay: {{ $index * 100 }}ms">
                    <div class="p-5 bg-gradient-to-br from-white to-slate-100 border-b border-slate-100">
                        @switch($index)
                            @case(0)
                                {{-- Clinic management dashboard --}}
                                <svg viewBox="0 0 260 150" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto">
                                    <rect x="8" y="10" width="244" height="130" rx="16" fill="#ffffff" stroke="#e2e8f0" stroke-width="2"/>
                                    <circle cx="26" cy="28" r="3" fill="#cbd5e1"/><circle cx="38" cy="28" r="3" fill="#cbd5e1"/><circle cx="50" cy="28" r="3" fill="#cbd5e1"/>
                                    <line x1="8" y1="42" x2="252" y2="42" stroke="#e2e8f0" stroke-width="2"/>
                                    <rect x="20" y="54" width="56" height="76" rx="8" fill="#eff6ff"/>
                                    <rect x="30" y="66" width="36" height="6" rx="3" fill="#60a5fa"/>
                                    <rect x="30" y="80" width="36" height="6" rx="3" fill="#bfdbfe"/>
                                    <rect x="30" y="94" width="36" height="6" rx="3" fill="#bfdbfe"/>
                                    <rect x="30" y="108" width="36" height="6" rx="3" fill="#bfdbfe"/>
                                    <rect x="90" y="56" width="150" height="20" rx="6" fill="#f1f5f9"/>
                                    <rect x="98" y="63" width="70" height="6" rx="3" fill="#94a3b8"/>
                                    <rect x="90" y="84" width="150" height="20" rx="6" fill="#f1f5f9"/>
                                    <rect x="98" y="91" width="92" height="6" rx="3" fill="#cbd5e1"/>
                                    <rect x="90" y="112" width="150" height="20" rx="6" fill="#eff6ff"/>
                                    <path d="M104 116l8-3 8 3v5c0 4-4 6-8 8-4-2-8-4-8-8v-5z" fill="#3b82f6"/>
                                    <rect x="128" y="119" width="60" height="6" rx="3" fill="#93c5fd"/>
                                </svg>
                                @break
                            @case(1)
                                {{-- Customizable examination screen: sliders & toggles --}}
                                <svg viewBox="0 0 260 150" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto">
                                    <rect x="8" y="10" width="244" height="130" rx="16" fill="#ffffff" stroke="#e2e8f0" stroke-width="2"/>
                                    <line x1="8" y1="42" x2="252" y2="42" stroke="#e2e8f0" stroke-width="2"/>
                                    <rect x="26" y="24" width="70" height="8" rx="4" fill="#99f6e4"/>
                                    <rect x="30" y="60" width="150" height="6" rx="3" fill="#e2e8f0"/>
                                    <circle cx="120" cy="63" r="9" fill="#14b8a6"/>
                                    <rect x="196" y="54" width="40" height="18" rx="9" fill="#99f6e4"/><circle cx="227" cy="63" r="7" fill="#14b8a6"/>
                                    <rect x="30" y="88" width="150" height="6" rx="3" fill="#e2e8f0"/>
                                    <circle cx="70" cy="91" r="9" fill="#14b8a6"/>
                                    <rect x="196" y="82" width="40" height="18" rx="9" fill="#e2e8f0"/><circle cx="205" cy="91" r="7" fill="#94a3b8"/>
                                    <rect x="30" y="116" width="150" height="6" rx="3" fill="#e2e8f0"/>
                                    <circle cx="150" cy="119" r="9" fill="#14b8a6"/>
                                    <rect x="196" y="110" width="40" height="18" rx="9" fill="#99f6e4"/><circle cx="227" cy="119" r="7" fill="#14b8a6"/>
                                </svg>
                                @break
                            @case(2)
                                {{-- Interactive queue display: NOW SERVING --}}
                                <svg viewBox="0 0 260 150" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto">
                                    <rect x="8" y="10" width="244" height="130" rx="16" fill="#0f172a"/>
                                    <rect x="24" y="30" width="128" height="90" rx="12" fill="#1e1b4b"/>
                                    <text x="88" y="58" text-anchor="middle" font-size="10" fill="#a5b4fc" font-family="sans-serif" font-weight="700">NOW SERVING</text>
                                    <text x="88" y="102" text-anchor="middle" font-size="42" fill="#c7d2fe" font-family="sans-serif" font-weight="800">12</text>
                                    <rect x="166" y="30" width="70" height="20" rx="6" fill="#6366f1"/>
                                    <text x="201" y="44" text-anchor="middle" font-size="10" fill="#ffffff" font-family="sans-serif" font-weight="700">NEXT</text>
                                    <rect x="166" y="58" width="70" height="18" rx="6" fill="#312e81"/><text x="176" y="71" font-size="10" fill="#c7d2fe" font-family="sans-serif" font-weight="700">13</text>
                                    <rect x="166" y="82" width="70" height="18" rx="6" fill="#312e81"/><text x="176" y="95" font-size="10" fill="#a5b4fc" font-family="sans-serif" font-weight="700">14</text>
                                    <rect x="166" y="106" width="70" height="18" rx="6" fill="#312e81"/><text x="176" y="119" font-size="10" fill="#a5b4fc" font-family="sans-serif" font-weight="700">15</text>
                                </svg>
                                @break
                            @case(3)
                                {{-- Thermal printer with a receipt --}}
                                <svg viewBox="0 0 260 150" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto">
                                    <rect x="8" y="10" width="244" height="130" rx="16" fill="#ffffff" stroke="#e2e8f0" stroke-width="2"/>
                                    <rect x="86" y="20" width="88" height="52" rx="4" fill="#fff1f2" stroke="#fecdd3" stroke-width="2"/>
                                    <rect x="98" y="30" width="46" height="5" rx="2.5" fill="#fb7185"/>
                                    <rect x="98" y="42" width="64" height="3" rx="1.5" fill="#fda4af"/>
                                    <rect x="98" y="50" width="64" height="3" rx="1.5" fill="#fda4af"/>
                                    <rect x="98" y="58" width="40" height="3" rx="1.5" fill="#fda4af"/>
                                    <rect x="64" y="70" width="132" height="44" rx="10" fill="#0f172a"/>
                                    <rect x="80" y="80" width="100" height="7" rx="3.5" fill="#334155"/>
                                    <circle cx="180" cy="98" r="4" fill="#f43f5e"/>
                                    <rect x="86" y="110" width="88" height="26" rx="4" fill="#fff1f2" stroke="#fecdd3" stroke-width="2"/>
                                    <rect x="98" y="119" width="64" height="3" rx="1.5" fill="#fda4af"/>
                                    <rect x="98" y="127" width="44" height="3" rx="1.5" fill="#fda4af"/>
                                </svg>
                                @break
                            @case(4)
                                {{-- Patient phone: your turn + notification bell --}}
                                <svg viewBox="0 0 260 150" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto">
                                    <rect x="8" y="10" width="244" height="130" rx="16" fill="#ffffff" stroke="#e2e8f0" stroke-width="2"/>
                                    <rect x="96" y="16" width="68" height="120" rx="14" fill="#0f172a"/>
                                    <rect x="102" y="26" width="56" height="100" rx="7" fill="#ecfdf5"/>
                                    <rect x="120" y="20" width="20" height="4" rx="2" fill="#334155"/>
                                    <text x="130" y="48" text-anchor="middle" font-size="7" fill="#059669" font-family="sans-serif" font-weight="700">YOUR TURN</text>
                                    <text x="130" y="76" text-anchor="middle" font-size="26" fill="#047857" font-family="sans-serif" font-weight="800">3</text>
                                    <text x="130" y="90" text-anchor="middle" font-size="6" fill="#10b981" font-family="sans-serif" font-weight="600">2 ahead of you</text>
                                    <rect x="112" y="100" width="36" height="4" rx="2" fill="#a7f3d0"/>
                                    <rect x="112" y="110" width="36" height="4" rx="2" fill="#d1fae5"/>
                                    <rect x="112" y="120" width="24" height="4" rx="2" fill="#d1fae5"/>
                                    <circle cx="164" cy="30" r="15" fill="#10b981"/>
                                    <path d="M158 33c1.2-1 1.8-2.2 1.8-4.2a4.2 4.2 0 018.4 0c0 2 .6 3.2 1.8 4.2h-12z" fill="#ffffff"/>
                                    <circle cx="164" cy="35.5" r="1.6" fill="#ffffff"/>
                                </svg>
                                @break
                        @endswitch
                    </div>
                    <div class="p-8 flex-1">
                        <h3 class="text-xl font-bold text-slate-900 mb-3">{{ $feature['title'] }}</h3>
                        <p class="text-slate-600 leading-relaxed">{{ $feature['desc'] }}</p>
                    </div>
                </div>
                @endforeach

                {{-- Charts panel: a clinic-at-a-glance infographic (illustrative data) --}}
                <div class="md:col-span-2 lg:col-span-2 rounded-[2rem] bg-slate-900 text-white p-8 sm:p-10 flex flex-col reveal overflow-hidden" style="transition-delay: 500ms">
                    <div class="flex items-baseline justify-between mb-8 flex-wrap gap-2">
                        <h3 class="text-2xl font-black">{{ $isAr ? 'عيادتك في لمحة' : 'Your clinic at a glance' }}</h3>
                        <span class="text-xs text-slate-500">{{ $isAr ? 'بيانات توضيحية' : 'illustrative data' }}</span>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-10 items-center">
                        {{-- Bar chart: patients per hour --}}
                        <div>
                            <p class="text-sm font-semibold text-slate-400 mb-4">{{ $isAr ? 'المرضى في كل ساعة' : 'Patients per hour' }}</p>
                            <svg viewBox="0 0 240 130" class="w-full h-auto" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <linearGradient id="barGrad" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#38bdf8"/><stop offset="100%" stop-color="#3b82f6"/>
                                    </linearGradient>
                                </defs>
                                <line x1="26" y1="104" x2="234" y2="104" stroke="#334155" stroke-width="1.5"/>
                                @php $bars = [34, 52, 44, 70, 40, 58]; $labels = ['9','10','11','12','1','2']; @endphp
                                @foreach($bars as $bi => $bh)
                                    <rect x="{{ 32 + $bi * 34 }}" y="{{ 104 - $bh }}" width="20" height="{{ $bh }}" rx="5" fill="url(#barGrad)"/>
                                    <text x="{{ 42 + $bi * 34 }}" y="118" text-anchor="middle" font-size="9" fill="#64748b" font-family="sans-serif">{{ $labels[$bi] }}</text>
                                @endforeach
                            </svg>
                        </div>
                        {{-- Donut: appointment types --}}
                        <div class="flex items-center gap-6">
                            <svg viewBox="0 0 100 100" class="w-32 h-32 shrink-0" xmlns="http://www.w3.org/2000/svg">
                                <g transform="rotate(-90 50 50)" fill="none" stroke-width="14">
                                    <circle cx="50" cy="50" r="36" stroke="#334155"/>
                                    <circle cx="50" cy="50" r="36" stroke="#3b82f6" stroke-dasharray="101.8 226.2" stroke-dashoffset="0"/>
                                    <circle cx="50" cy="50" r="36" stroke="#14b8a6" stroke-dasharray="67.9 226.2" stroke-dashoffset="-101.8"/>
                                    <circle cx="50" cy="50" r="36" stroke="#f59e0b" stroke-dasharray="56.5 226.2" stroke-dashoffset="-169.7"/>
                                </g>
                            </svg>
                            <ul class="space-y-3 text-sm">
                                <li class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-blue-500"></span><span class="text-slate-300">{{ $isAr ? 'كشف' : 'New visits' }} <b class="text-white">45%</b></span></li>
                                <li class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-teal-500"></span><span class="text-slate-300">{{ $isAr ? 'متابعة' : 'Follow-ups' }} <b class="text-white">30%</b></span></li>
                                <li class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-amber-500"></span><span class="text-slate-300">{{ $isAr ? 'تأمين' : 'Insurance' }} <b class="text-white">25%</b></span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="py-24 bg-slate-900 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10" style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 40px 40px;"></div>
        
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div class="reveal">
                    <h2 class="text-4xl sm:text-5xl font-black mb-6">
                        {{ app()->getLocale() === 'ar' ? 'الأمان والخصوصية أولاً' : 'Security & Privacy First' }}
                    </h2>
                    <p class="text-xl text-slate-400 mb-8 leading-relaxed">
                        {{ app()->getLocale() === 'ar' 
                            ? 'نحن ندرك أن بياناتك الصحية حساسة للغاية. لهذا بنينا مستشفي اون بمعايير أمان عالمية تشبه تلك المستخدمة في البنوك.' 
                            : 'We understand your health data is sensitive. That\'s why we built Mostashfa-on with bank-grade security standards.' }}
                    </p>
                    
                    <ul class="space-y-6">
                        @php
                            $points = app()->getLocale() === 'ar' 
                            ? ['تشفير طرف-إلى-طرف لجميع المحادثات', 'متوافق مع معايير HIPAA العالمية', 'بياناتك ملك لك وحدك']
                            : ['End-to-End Encryption for all chats', 'HIPAA Compliant Standards', 'You own your data completely'];
                        @endphp
                        @foreach($points as $point)
                        <li class="flex items-center gap-4">
                            <div class="w-8 h-8 rounded-full bg-teal-500/20 text-teal-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <span class="text-lg font-medium">{{ $point }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>

                <div class="relative reveal">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-teal-500 rounded-3xl transform rotate-3 blur-sm opacity-50"></div>
                    <div class="relative bg-slate-800 border border-slate-700 rounded-3xl p-8 sm:p-12">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex space-x-2 space-x-reverse">
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            </div>
                            <div class="text-xs text-slate-500 font-mono">ENCRYPTED CONNECTION</div>
                        </div>
                        <div class="space-y-4 font-mono text-sm sm:text-base">
                            <div class="flex gap-4 text-green-400">
                                <span>></span>
                                <span>Initializing secure protocol...</span>
                            </div>
                            <div class="flex gap-4 text-green-400">
                                <span>></span>
                                <span>Verifying doctor credentials... <span class="text-white">Done</span></span>
                            </div>
                            <div class="flex gap-4 text-green-400">
                                <span>></span>
                                <span>Encrypting patient records... <span class="text-white">100%</span></span>
                            </div>
                            <div class="p-4 bg-slate-900/50 rounded-lg border border-slate-700 mt-6 text-center">
                                <span class="text-3xl">🔒</span>
                                <p class="text-slate-400 mt-2">SSL Secured</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Get in touch: the same contact channels used across the app (config/demo.contact) --}}
    @if (config('demo.contact.facebook') || config('demo.contact.whatsapp') || config('demo.contact.phone'))
        @php $isAr = app()->getLocale() === 'ar'; @endphp
        <section class="py-20 bg-white">
            <div class="max-w-2xl mx-auto px-4 text-center">
                <h2 class="text-3xl sm:text-4xl font-black text-slate-900 mb-3">
                    {{ $isAr ? 'تواصل معنا' : 'Get in touch' }}
                </h2>
                <p class="text-lg text-slate-500">
                    {{ $isAr ? 'فريقنا جاهز للإجابة على أسئلتك ومساعدتك على البدء.' : 'Our team is ready to answer your questions and help you get started.' }}
                </p>
                @include('demo.contact', ['heading' => $isAr ? 'تحدث مع فريقنا' : 'Talk to our team'])
            </div>
        </section>
    @endif
@endsection
