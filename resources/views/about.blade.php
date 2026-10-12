@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'عن التطبيق - مستشفي اون' : 'About - Mostashfa-on')

@php
    $isAr = app()->getLocale() === 'ar';

    $values = $isAr ? [
        ['t' => 'الأمان والخصوصية أولاً', 'd' => 'بياناتك الصحية حساسة، لذلك بنينا مستشفي اون بمعايير أمان عالية تحمي بيانات عيادتك ومرضاك.'],
        ['t' => 'بساطة في الاستخدام', 'd' => 'واجهة عربية واضحة تعمل على هاتفك وحاسوبك، لا تحتاج تدريبًا طويلًا لتبدأ بها.'],
        ['t' => 'دعم بشري حقيقي', 'd' => 'فريق يساعدك في الإعداد والتشغيل ويجيب عن أسئلتك بالعربية وقت ما تحتاج.'],
        ['t' => 'تحديثات مستمرة', 'd' => 'نطوّر النظام باستمرار، وكل الميزات الجديدة تصلك دون رسوم إضافية.'],
    ] : [
        ['t' => 'Security & privacy first', 'd' => 'Your health data is sensitive, so Mostashfa-on is built with high security standards protecting your clinic and patients.'],
        ['t' => 'Simple to use', 'd' => 'A clear Arabic interface that runs on your phone and computer, with no long training needed to start.'],
        ['t' => 'Real human support', 'd' => 'A team that helps you set up and run the system and answers your questions whenever you need.'],
        ['t' => 'Always improving', 'd' => 'We keep developing the system, and every new feature reaches you at no extra cost.'],
    ];

    $stats = $isAr ? [
        ['v' => '5', 'l' => 'خطوات من الاستقبال حتى الطباعة'],
        ['v' => '100%', 'l' => 'واجهة عربية بالكامل'],
        ['v' => '24/7', 'l' => 'بياناتك متاحة وآمنة'],
    ] : [
        ['v' => '5', 'l' => 'steps from reception to printing'],
        ['v' => '100%', 'l' => 'fully Arabic interface'],
        ['v' => '24/7', 'l' => 'your data available & secure'],
    ];
@endphp

@section('content')
    <header class="bg-slate-900 text-white pt-40 pb-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-tr from-primary/20 to-teal-500/20 opacity-40"></div>
        <div class="absolute inset-0 opacity-10" style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 40px 40px;"></div>
        <div class="absolute -bottom-1 left-0 w-full h-10 bg-slate-50" style="clip-path: polygon(0 100%, 100% 100%, 100% 0);"></div>

        <div class="container mx-auto px-4 relative z-10 text-center max-w-3xl">
            <span class="inline-block px-4 py-1.5 mb-5 rounded-full bg-white/10 border border-white/20 text-sm font-semibold text-teal-300">
                {{ $isAr ? 'عن التطبيق' : 'About' }}
            </span>
            <h1 class="text-4xl md:text-6xl font-black mb-5">
                {{ $isAr ? 'نغيّر مفهوم الرعاية الصحية في مصر' : 'Redefining healthcare in Egypt' }}
            </h1>
            <p class="text-lg text-slate-300 leading-relaxed">
                {{ $isAr
                    ? 'مستشفي اون نظام متكامل لإدارة العيادات — من الاستقبال والحجز حتى الكشف والطباعة، مع تطبيق جوال يربط المريض بعيادتك.'
                    : 'Mostashfa-on is a complete clinic management system — from reception and booking to examination and printing, with a mobile app that connects patients to your clinic.' }}
            </p>
        </div>
    </header>

    <main class="bg-slate-50 pb-24">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-6xl">

            {{-- Stats --}}
            <div class="grid sm:grid-cols-3 gap-6 -mt-10 relative z-20">
                @foreach($stats as $s)
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 text-center">
                        <div class="text-4xl font-black text-primary mb-1">{{ $s['v'] }}</div>
                        <p class="text-sm text-slate-500 leading-relaxed">{{ $s['l'] }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Mission --}}
            <div class="mt-20 grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <span class="text-primary font-bold tracking-wider uppercase text-sm mb-2 block">{{ $isAr ? 'مهمتنا' : 'Our mission' }}</span>
                    <h2 class="text-3xl sm:text-4xl font-black text-slate-900 mb-5">
                        {{ $isAr ? 'عيادة منظّمة، ومريض مطمئن' : 'An organized clinic, a reassured patient' }}
                    </h2>
                    <p class="text-slate-600 leading-relaxed mb-4">
                        {{ $isAr
                            ? 'نؤمن أن الطبيب يجب أن يركّز على مريضه لا على الأوراق. لذلك جمعنا الحجز والاستقبال والكشف والطباعة والتأمين في نظام واحد بسيط، وأضفنا تطبيقًا للمريض يتابع به دوره وسجله الطبي.'
                            : 'We believe a doctor should focus on their patient, not paperwork. So we brought booking, reception, examination, printing and insurance into one simple system, and added a patient app to follow their turn and medical history.' }}
                    </p>
                    <p class="text-slate-600 leading-relaxed">
                        {{ $isAr
                            ? 'النتيجة: طابور أقل فوضى، سجل طبي منظّم، ووقت أطول لما يهم فعلاً.'
                            : 'The result: a calmer queue, an organized medical record, and more time for what truly matters.' }}
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    @foreach($values as $v)
                        <div class="bg-white rounded-2xl border border-slate-100 p-5">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <h3 class="font-bold text-slate-900 mb-1 text-sm leading-snug">{{ $v['t'] }}</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">{{ $v['d'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- CTA --}}
            <div class="max-w-3xl mx-auto mt-20 text-center">
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mb-3">
                    {{ $isAr ? 'جاهز تبدأ مع مستشفي اون؟' : 'Ready to start with Mostashfa-on?' }}
                </h2>
                <p class="text-slate-500 mb-6">
                    {{ $isAr ? 'اطّلع على الباقات أو تواصل معنا لأي سؤال.' : 'See the plans or contact us with any question.' }}
                </p>
                <div class="flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('pricing') }}" class="inline-flex items-center gap-2 px-7 py-3.5 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 transition-all shadow-lg">
                        {{ $isAr ? 'الأسعار والباقات' : 'Pricing & plans' }}
                    </a>
                    <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 px-7 py-3.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold hover:bg-slate-50 transition-all">
                        {{ $isAr ? 'اتصل بنا' : 'Contact us' }}
                    </a>
                </div>
            </div>
        </div>
    </main>
@endsection
