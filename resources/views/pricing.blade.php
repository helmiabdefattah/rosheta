@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'الأسعار والباقات - مستشفي اون' : 'Pricing & Plans - Mostashfa-on')

@php
    $isAr = app()->getLocale() === 'ar';
    $ctaUrl = url('/') . '#contact';

    // 20% off when billed annually.
    $annualRate = 0.8;

    $packages = [
        [
            'key'      => 'starter',
            'name'     => $isAr ? 'الأساسية' : 'Starter',
            'tagline'  => $isAr ? 'عيادة واحدة على أجهزتك الخاصة' : 'One clinic, on your own devices',
            'monthly'  => 1000,
            'popular'  => false,
            'accent'   => 'blue',
            'included' => $isAr ? [
                'عيادة واحدة',
                'حساب طبيب واحد',
                'حسابان للمساعدين',
                'تطبيق الجوال للمريض',
                'إشعارات الجوال للمرضى',
                'شاشة انتظار تفاعلية (على شاشتك)',
                'طباعة الحجوزات والروشتات والفواتير (PDF أو طابعتك)',
                'يعمل على هاتفك وحاسوبك',
            ] : [
                '1 clinic',
                '1 doctor account',
                '2 assistant accounts',
                'Patient mobile application',
                'Patient mobile notifications',
                'Interactive waiting screen (your own screen)',
                'Print bookings, prescriptions & invoices (PDF or your printer)',
                'Runs on your own phones & computers',
            ],
            'excluded' => $isAr ? [
                'جهاز الاستقبال الذكي (تابلت كشك)',
                'طابعة حرارية',
            ] : [
                'Kiosk tablet (smart check-in device)',
                'Thermal receipt printer',
            ],
        ],
        [
            'key'      => 'equipped',
            'name'     => $isAr ? 'المجهّزة' : 'Equipped',
            'tagline'  => $isAr ? 'كل مميزات الأساسية + التابلت والطابعة' : 'Everything in Starter + tablet & printer',
            'monthly'  => 1500,
            'popular'  => true,
            'accent'   => 'primary',
            'included' => $isAr ? [
                'كل مميزات باقة «الأساسية»',
                'جهاز تابلت (كشك) للاستقبال الذاتي',
                'طابعة حرارية للتذاكر والروشتات والفواتير',
                'إعداد وتجهيز الأجهزة',
            ] : [
                'Everything in Starter',
                'Kiosk tablet for self check-in',
                'Thermal printer for tickets, prescriptions & invoices',
                'Device setup & onboarding',
            ],
            'excluded' => [],
        ],
        [
            'key'      => 'multi',
            'name'     => $isAr ? 'متعددة العيادات' : 'Multi-Clinic',
            'tagline'  => $isAr ? 'حتى 3 عيادات وفريق أكبر' : 'Up to 3 clinics and a bigger team',
            'monthly'  => 1800,
            'popular'  => false,
            'accent'   => 'indigo',
            'included' => $isAr ? [
                'حتى 3 عيادات',
                'حتى 3 حسابات أطباء',
                '8 حسابات للمساعدين',
                'تطبيق المريض والإشعارات',
                'شاشات انتظار تفاعلية',
                'طباعة الحجوزات والروشتات والفواتير',
                'تابلت (كشك) وطابعة حرارية',
            ] : [
                'Up to 3 clinics',
                'Up to 3 doctor accounts',
                '8 assistant accounts',
                'Patient app & notifications',
                'Interactive waiting screens',
                'Bookings, prescriptions & invoices printing',
                'Kiosk tablet & thermal printer included',
            ],
            'excluded' => [],
        ],
    ];

    $accentMap = [
        'blue'    => ['ring' => 'ring-blue-200',    'text' => 'text-blue-600',    'btn' => 'bg-blue-600 hover:bg-blue-700',    'soft' => 'bg-blue-50'],
        'primary' => ['ring' => 'ring-primary',     'text' => 'text-primary',     'btn' => 'bg-primary hover:bg-blue-700',     'soft' => 'bg-blue-50'],
        'indigo'  => ['ring' => 'ring-indigo-200',  'text' => 'text-indigo-600',  'btn' => 'bg-indigo-600 hover:bg-indigo-700', 'soft' => 'bg-indigo-50'],
    ];
@endphp

@section('content')
    <header class="bg-slate-900 text-white pt-40 pb-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-tr from-primary/20 to-teal-500/20 opacity-40"></div>
        <div class="absolute inset-0 opacity-10" style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 40px 40px;"></div>
        <div class="absolute -bottom-1 left-0 w-full h-10 bg-slate-50" style="clip-path: polygon(0 100%, 100% 100%, 100% 0);"></div>

        <div class="container mx-auto px-4 relative z-10 text-center">
            <span class="inline-block px-4 py-1.5 mb-5 rounded-full bg-white/10 border border-white/20 text-sm font-semibold text-teal-300">
                {{ $isAr ? 'باقات مرنة لكل عيادة' : 'Flexible plans for every clinic' }}
            </span>
            <h1 class="text-4xl md:text-6xl font-black mb-5">
                {{ $isAr ? 'أسعار بسيطة وواضحة' : 'Simple, transparent pricing' }}
            </h1>
            <p class="text-lg text-slate-300 max-w-2xl mx-auto">
                {{ $isAr
                    ? 'ابدأ من 1000 جنيه شهريًا. وفّر 20% مع الاشتراك السنوي.'
                    : 'Starting from 1,000 EGP per month. Save 20% when you pay annually.' }}
            </p>
        </div>
    </header>

    <main class="bg-slate-50 pb-24">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">

            {{-- Billing toggle --}}
            <div class="flex justify-center -mt-8 relative z-20 mb-14">
                <div class="inline-flex items-center gap-1 p-1.5 bg-white rounded-full shadow-xl border border-slate-100">
                    <button type="button" id="billing-monthly" class="billing-btn px-6 py-2.5 rounded-full text-sm font-bold transition-all bg-slate-900 text-white">
                        {{ $isAr ? 'شهري' : 'Monthly' }}
                    </button>
                    <button type="button" id="billing-annual" class="billing-btn px-6 py-2.5 rounded-full text-sm font-bold transition-all text-slate-600 hover:text-slate-900 flex items-center gap-2">
                        {{ $isAr ? 'سنوي' : 'Annual' }}
                        <span class="text-[11px] font-black px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">-20%</span>
                    </button>
                </div>
            </div>

            {{-- Plans --}}
            <div class="grid lg:grid-cols-3 gap-8 items-stretch max-w-6xl mx-auto">
                @foreach($packages as $p)
                    @php $a = $accentMap[$p['accent']]; @endphp
                    <div class="relative flex flex-col rounded-[2rem] bg-white border {{ $p['popular'] ? 'ring-2 ' . $a['ring'] . ' shadow-2xl shadow-primary/10 lg:-translate-y-4' : 'border-slate-100 shadow-sm' }} p-8 transition-all">
                        @if($p['popular'])
                            <span class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1.5 rounded-full bg-primary text-white text-xs font-black uppercase tracking-wider shadow-lg shadow-primary/30">
                                {{ $isAr ? 'الأكثر رواجًا' : 'Most Popular' }}
                            </span>
                        @endif

                        <h3 class="text-2xl font-black text-slate-900">{{ $p['name'] }}</h3>
                        <p class="text-sm text-slate-500 mt-1 mb-6 min-h-[2.5rem]">{{ $p['tagline'] }}</p>

                        {{-- Monthly price --}}
                        <div class="price-monthly mb-6">
                            <div class="flex items-end gap-2">
                                <span class="text-5xl font-black {{ $a['text'] }}">{{ number_format($p['monthly']) }}</span>
                                <span class="text-lg font-bold text-slate-500 mb-1.5">{{ $isAr ? 'ج.م' : 'EGP' }}</span>
                            </div>
                            <p class="text-sm text-slate-500 mt-1">{{ $isAr ? 'شهريًا' : 'per month' }}</p>
                        </div>

                        {{-- Annual price (20% off) --}}
                        <div class="price-annual mb-6 hidden">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-lg text-slate-400 line-through">{{ number_format($p['monthly']) }}</span>
                                <span class="text-[11px] font-black px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">{{ $isAr ? 'وفّر 20%' : 'Save 20%' }}</span>
                            </div>
                            <div class="flex items-end gap-2">
                                <span class="text-5xl font-black {{ $a['text'] }}">{{ number_format($p['monthly'] * $annualRate) }}</span>
                                <span class="text-lg font-bold text-slate-500 mb-1.5">{{ $isAr ? 'ج.م' : 'EGP' }}</span>
                            </div>
                            <p class="text-sm text-slate-500 mt-1">
                                {{ $isAr ? 'شهريًا · تُدفع سنويًا' : 'per month · billed yearly' }}
                                <span class="block text-slate-400">{{ $isAr ? number_format($p['monthly'] * 12 * $annualRate) . ' ج.م / سنة' : number_format($p['monthly'] * 12 * $annualRate) . ' EGP / year' }}</span>
                            </p>
                        </div>

                        <a href="{{ $ctaUrl }}" class="block w-full text-center py-3.5 rounded-xl font-bold text-white {{ $a['btn'] }} transition-all shadow-lg hover:-translate-y-0.5 mb-8">
                            {{ $isAr ? 'ابدأ الآن' : 'Get started' }}
                        </a>

                        <ul class="space-y-3.5 text-sm">
                            @foreach($p['included'] as $item)
                                <li class="flex items-start gap-3">
                                    <span class="mt-0.5 shrink-0 w-5 h-5 rounded-full {{ $a['soft'] }} {{ $a['text'] }} flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                    <span class="text-slate-700 leading-snug">{{ $item }}</span>
                                </li>
                            @endforeach
                            @foreach($p['excluded'] as $item)
                                <li class="flex items-start gap-3 text-slate-400">
                                    <span class="mt-0.5 shrink-0 w-5 h-5 rounded-full bg-slate-100 flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </span>
                                    <span class="leading-snug line-through decoration-slate-300">{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            {{-- Add-on: personal / institution profile site --}}
            <div class="max-w-6xl mx-auto mt-10">
                <div class="relative overflow-hidden rounded-[2rem] bg-slate-900 text-white p-8 sm:p-12 grid lg:grid-cols-2 gap-10 items-center">
                    <div class="absolute inset-0 opacity-10" style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 40px 40px;"></div>
                    <div class="absolute -top-16 -right-16 w-64 h-64 bg-teal-500/20 rounded-full blur-3xl"></div>

                    <div class="relative z-10">
                        <span class="inline-block px-3 py-1 mb-4 rounded-full bg-teal-500/20 border border-teal-400/30 text-teal-300 text-xs font-black uppercase tracking-wider">
                            {{ $isAr ? 'إضافة' : 'Add-on' }}
                        </span>
                        <h3 class="text-3xl font-black mb-3">
                            {{ $isAr ? 'موقع تعريفي خاص بك' : 'Your own profile website' }}
                        </h3>
                        <p class="text-slate-300 leading-relaxed mb-6">
                            {{ $isAr
                                ? 'موقع تعريفي للطبيب أو المؤسسة على نطاق فرعي خاص بك — اعرض خدماتك وأطباءك وأتِح للمرضى الحجز أونلاين.'
                                : 'A profile site for a doctor or an institution on your own subdomain — showcase your services and doctors, and let patients book online.' }}
                        </p>
                        <div class="flex items-end gap-2 mb-6">
                            <span class="text-sm text-slate-400 mb-1.5">{{ $isAr ? 'يبدأ من' : 'from' }}</span>
                            <span class="text-4xl font-black text-teal-300">200</span>
                            <span class="text-lg font-bold text-slate-400 mb-1">{{ $isAr ? 'ج.م / شهر' : 'EGP / mo' }}</span>
                        </div>
                        <a href="{{ $ctaUrl }}" class="inline-flex items-center gap-2 px-7 py-3.5 bg-teal-500 hover:bg-teal-400 text-slate-900 rounded-xl font-bold transition-all shadow-lg hover:-translate-y-0.5">
                            {{ $isAr ? 'أضِفه إلى أي باقة' : 'Add to any plan' }}
                        </a>
                    </div>

                    {{-- Browser mockup with the subdomain --}}
                    <div class="relative z-10">
                        <div class="bg-slate-800 rounded-2xl border border-slate-700 shadow-2xl overflow-hidden">
                            <div class="flex items-center gap-2 px-4 py-3 border-b border-slate-700">
                                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                                <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                                <span class="w-3 h-3 rounded-full bg-green-500"></span>
                                <div class="flex-1 mx-2 bg-slate-900 rounded-md px-3 py-1.5 text-xs font-mono text-slate-400 truncate">
                                    https://<span class="text-teal-300 font-bold">{name}</span>.mostashfaon.com
                                </div>
                            </div>
                            <div class="p-6" dir="ltr">
                                <div class="flex items-center gap-3 mb-5">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-teal-400 to-blue-500"></div>
                                    <div class="space-y-1.5">
                                        <div class="h-3 w-32 bg-slate-600 rounded-full"></div>
                                        <div class="h-2.5 w-20 bg-slate-700 rounded-full"></div>
                                    </div>
                                    <div class="ms-auto h-8 w-24 bg-teal-500 rounded-lg"></div>
                                </div>
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="h-16 bg-slate-700/60 rounded-xl"></div>
                                    <div class="h-16 bg-slate-700/60 rounded-xl"></div>
                                    <div class="h-16 bg-slate-700/60 rounded-xl"></div>
                                </div>
                                <div class="mt-3 space-y-2">
                                    <div class="h-2.5 w-full bg-slate-700 rounded-full"></div>
                                    <div class="h-2.5 w-4/5 bg-slate-700 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Reassurance strip --}}
            <div class="max-w-4xl mx-auto mt-16 grid sm:grid-cols-3 gap-6 text-center">
                @php
                    $notes = $isAr ? [
                        ['t' => 'وفّر 20% سنويًا', 'd' => 'خصم فوري عند الدفع السنوي على كل الباقات.'],
                        ['t' => 'كل التحديثات مشمولة', 'd' => 'ميزات وتحديثات النظام الجديدة دون رسوم إضافية.'],
                        ['t' => 'دعم فني', 'd' => 'فريقنا يساعدك في الإعداد والتشغيل.'],
                    ] : [
                        ['t' => 'Save 20% yearly', 'd' => 'Instant discount on every plan when billed annually.'],
                        ['t' => 'All updates included', 'd' => 'New features and system updates at no extra cost.'],
                        ['t' => 'Human support', 'd' => 'Our team helps you set up and get running.'],
                    ];
                @endphp
                @foreach($notes as $n)
                    <div class="bg-white rounded-2xl border border-slate-100 p-6">
                        <h4 class="font-bold text-slate-900 mb-1">{{ $n['t'] }}</h4>
                        <p class="text-sm text-slate-500 leading-relaxed">{{ $n['d'] }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Contact / talk to us --}}
            @if (config('demo.contact.facebook') || config('demo.contact.whatsapp') || config('demo.contact.phone'))
                <div class="max-w-2xl mx-auto mt-20 text-center">
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mb-3">
                        {{ $isAr ? 'تحتاج مساعدة في الاختيار؟' : 'Need help choosing?' }}
                    </h2>
                    <p class="text-slate-500 mb-2">
                        {{ $isAr ? 'تواصل مع فريقنا وسنساعدك على اختيار الباقة المناسبة.' : 'Talk to our team and we\'ll help you pick the right plan.' }}
                    </p>
                    @include('demo.contact', ['heading' => $isAr ? 'تحدث مع فريقنا' : 'Talk to our team'])
                </div>
            @else
                <div class="max-w-2xl mx-auto mt-20 text-center">
                    <a href="{{ $ctaUrl }}" class="inline-flex items-center gap-2 px-8 py-4 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 transition-all shadow-xl">
                        {{ $isAr ? 'تواصل معنا لاختيار الباقة' : 'Contact us to choose a plan' }}
                    </a>
                </div>
            @endif
        </div>
    </main>

    <script>
        (function () {
            var monthlyBtn = document.getElementById('billing-monthly');
            var annualBtn = document.getElementById('billing-annual');
            var monthlyEls = document.querySelectorAll('.price-monthly');
            var annualEls = document.querySelectorAll('.price-annual');
            var activeCls = ['bg-slate-900', 'text-white'];
            var idleCls = ['text-slate-600', 'hover:text-slate-900'];

            function setActive(btn, on) {
                if (on) { btn.classList.add.apply(btn.classList, activeCls); btn.classList.remove.apply(btn.classList, idleCls); }
                else { btn.classList.remove.apply(btn.classList, activeCls); btn.classList.add.apply(btn.classList, idleCls); }
            }

            function show(annual) {
                monthlyEls.forEach(function (el) { el.classList.toggle('hidden', annual); });
                annualEls.forEach(function (el) { el.classList.toggle('hidden', !annual); });
                setActive(annualBtn, annual);
                setActive(monthlyBtn, !annual);
            }

            monthlyBtn.addEventListener('click', function () { show(false); });
            annualBtn.addEventListener('click', function () { show(true); });
        })();
    </script>
@endsection
