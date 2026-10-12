@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'أنشئ حسابك الحقيقي - مستشفي اون' : 'Create your real account - Mostashfa-on')

@php
    $isAr = app()->getLocale() === 'ar';

    // What the clinic gets, recapped from the public features section so the
    // visitor who just left the sandbox sees exactly what a paid account keeps.
    $features = $isAr ? [
        ['t' => 'إدارة عيادة متكاملة', 'd' => 'السجل الطبي، الاستقبال، شركات التأمين، والحجز الإلكتروني في مكان واحد.'],
        ['t' => 'شاشة كشف قابلة للتخصيص', 'd' => 'صمّم شاشة الكشف بما يناسب تخصصك — حقول وأقسام مرنة تُفعّلها كما تريد.'],
        ['t' => 'شاشات انتظار تفاعلية', 'd' => 'تعرض الحجوزات ودور المريض الحالي ومن يليه في الطابور.'],
        ['t' => 'طباعة الحجوزات والروشتات والفواتير', 'd' => 'اطبع التذاكر والوصفات والفواتير مباشرة على طابعة العيادة الحرارية.'],
        ['t' => 'تطبيق جوال للمريض', 'd' => 'يتابع المريض دوره وسجله الطبي، ويستقبل إشعارات مخصصة من مساعد الطبيب.'],
        ['t' => 'بياناتك لك وحدك', 'd' => 'لا تُمسح بياناتك بانتهاء التجربة — حساب حقيقي ببيانات دائمة وآمنة.'],
    ] : [
        ['t' => 'Full clinic management', 'd' => 'Medical history, reception, insurance companies and online booking in one place.'],
        ['t' => 'Customizable examination screen', 'd' => 'Shape the examination screen around your specialty — flexible fields you switch on as you need.'],
        ['t' => 'Interactive waiting screens', 'd' => 'Show reservations, the patient in service now, and who is next in the queue.'],
        ['t' => 'Bookings, prescriptions & invoices printing', 'd' => 'Print tickets, prescriptions and invoices straight to the clinic\'s thermal printer.'],
        ['t' => 'Patient mobile app', 'd' => 'Patients follow their turn and medical history and get custom notifications from the assistant.'],
        ['t' => 'Your data stays yours', 'd' => 'Nothing is wiped when a trial ends — a real account with permanent, secure data.'],
    ];

    $steps = $isAr ? [
        ['n' => '1', 't' => 'اختر باقتك', 'd' => 'باقات تبدأ من 1000 ج.م شهريًا، مع خصم 20% سنويًا.'],
        ['n' => '2', 't' => 'نجهّز عيادتك', 'd' => 'نساعدك في الإعداد والأجهزة وحسابات الأطباء والمساعدين.'],
        ['n' => '3', 't' => 'ابدأ العمل ببياناتك', 'd' => 'استقبل مرضاك وأصدر الروشتات والفواتير من أول يوم.'],
    ] : [
        ['n' => '1', 't' => 'Pick your plan', 'd' => 'Plans from 1,000 EGP / month, with 20% off annually.'],
        ['n' => '2', 't' => 'We set you up', 'd' => 'We help with onboarding, devices and doctor/assistant accounts.'],
        ['n' => '3', 't' => 'Start with your data', 'd' => 'See patients and issue prescriptions and invoices from day one.'],
    ];
@endphp

@section('content')
    {{-- Hero — same slate-900 treatment as the pricing page so the header sits
         on a dark band and the marketing nav reads cleanly. --}}
    <header class="bg-slate-900 text-white pt-40 pb-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-tr from-emerald-500/20 to-teal-500/20 opacity-40"></div>
        <div class="absolute inset-0 opacity-10" style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 40px 40px;"></div>
        <div class="absolute -bottom-1 left-0 w-full h-10 bg-slate-50" style="clip-path: polygon(0 100%, 100% 100%, 100% 0);"></div>

        <div class="container mx-auto px-4 relative z-10 text-center max-w-3xl">
            <span class="inline-block px-4 py-1.5 mb-5 rounded-full bg-emerald-500/15 border border-emerald-400/30 text-sm font-semibold text-emerald-300">
                {{ $isAr ? 'أعجبتك التجربة؟' : 'Liked the trial?' }}
            </span>
            <h1 class="text-4xl md:text-6xl font-black mb-5">
                {{ $isAr ? 'أنشئ حسابك الحقيقي' : 'Create your real account' }}
            </h1>
            <p class="text-lg text-slate-300 leading-relaxed">
                {{ $isAr
                    ? 'جرّبت العيادة في وضع التجربة — الآن احصل على عيادتك الحقيقية ببيانات دائمة لا تُمسح، وفريق يساعدك في الإعداد.'
                    : 'You tried the clinic in sandbox mode — now get your real clinic with permanent data that is never wiped, and a team to help you set up.' }}
            </p>

            <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('pricing') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-emerald-500 hover:bg-emerald-400 text-slate-900 rounded-xl font-bold transition-all shadow-xl shadow-emerald-500/20 hover:-translate-y-0.5">
                    {{ $isAr ? 'اختر باقتك وابدأ' : 'Pick a plan & start' }}
                    <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
                <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-white/10 border border-white/20 hover:bg-white/20 text-white rounded-xl font-bold transition-all">
                    {{ $isAr ? 'تحدّث مع فريقنا' : 'Talk to our team' }}
                </a>
            </div>
        </div>
    </header>

    <main class="bg-slate-50 pb-24">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-6xl">

            {{-- How to start --}}
            <div class="grid sm:grid-cols-3 gap-6 -mt-10 relative z-20">
                @foreach($steps as $s)
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 font-black mb-4">{{ $s['n'] }}</span>
                        <h3 class="font-bold text-slate-900 mb-1">{{ $s['t'] }}</h3>
                        <p class="text-sm text-slate-500 leading-relaxed">{{ $s['d'] }}</p>
                    </div>
                @endforeach
            </div>

            {{-- What your account includes --}}
            <div class="mt-20 text-center mb-12">
                <span class="text-emerald-600 font-bold tracking-wider uppercase text-sm mb-2 block">{{ $isAr ? 'ما يشمله حسابك' : 'What your account includes' }}</span>
                <h2 class="text-3xl sm:text-4xl font-black text-slate-900">
                    {{ $isAr ? 'كل ما جرّبته — بشكل دائم' : 'Everything you tried — permanently' }}
                </h2>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($features as $f)
                    <div class="bg-white rounded-2xl border border-slate-100 p-6 flex items-start gap-4">
                        <span class="mt-0.5 shrink-0 w-6 h-6 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <div>
                            <h3 class="font-bold text-slate-900 mb-1 leading-snug">{{ $f['t'] }}</h3>
                            <p class="text-sm text-slate-500 leading-relaxed">{{ $f['d'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Final CTA + talk to a human --}}
            <div class="max-w-2xl mx-auto mt-20 text-center">
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mb-3">
                    {{ $isAr ? 'جاهز تبدأ؟' : 'Ready to start?' }}
                </h2>
                <p class="text-slate-500 mb-6">
                    {{ $isAr ? 'اختر باقتك الآن، أو تواصل معنا وسنساعدك في كل خطوة.' : 'Pick your plan now, or contact us and we\'ll help you every step of the way.' }}
                </p>
                <a href="{{ route('pricing') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 transition-all shadow-xl">
                    {{ $isAr ? 'اطّلع على الأسعار والباقات' : 'View pricing & plans' }}
                </a>
                @include('demo.contact', ['heading' => $isAr ? 'أو تواصل معنا مباشرة' : 'Or contact us directly'])
            </div>
        </div>
    </main>
@endsection
