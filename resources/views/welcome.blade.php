@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'مستشفي اون - قريباً' : 'Mostashfa-on - Coming Soon')

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

    <section id="features" class="py-24 bg-white relative">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20 reveal">
                <span class="text-primary font-bold tracking-wider uppercase text-sm mb-2 block">{{ app()->getLocale() === 'ar' ? 'لماذا نحن' : 'Why Choose Us' }}</span>
                <h2 class="text-4xl sm:text-5xl font-black text-slate-900 mb-6">
                    {{ app()->getLocale() === 'ar' ? 'كل ما تحتاجه لصحة أفضل' : 'Everything you need for better health' }}
                </h2>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8 max-w-7xl mx-auto">
                @php
                    $features = app()->getLocale() === 'ar' ? [
                        ['icon' => 'calendar', 'color' => 'bg-blue-500', 'title' => 'حجز فوري', 'desc' => 'لا مزيد من الانتظار في العيادات. احجز موعدك بنقرة واحدة.'],
                        ['icon' => 'video', 'color' => 'bg-teal-500', 'title' => 'استشارات فيديو', 'desc' => 'تحدث مع نخبة الأطباء من منزلك عبر مكالمات فيديو آمنة ومشفرة.'],
                        ['icon' => 'folder', 'color' => 'bg-indigo-500', 'title' => 'ملف طبي إلكتروني', 'desc' => 'تاريخك الطبي بالكامل، الوصفات، والتحاليل في جيبك.'],
                        ['icon' => 'truck', 'color' => 'bg-rose-500', 'title' => 'صيدلية أونلاين', 'desc' => 'اطلب الدواء ليصلك إلى باب منزلك في أقل من 60 دقيقة.'],
                        ['icon' => 'bell', 'color' => 'bg-amber-500', 'title' => 'تذكيرات ذكية', 'desc' => 'لن تنسى موعد الدواء مرة أخرى مع نظام التنبيهات الذكي.'],
                        ['icon' => 'shield', 'color' => 'bg-emerald-500', 'title' => 'شبكة معتمدة', 'desc' => 'جميع الأطباء والمراكز الطبية تم التحقق من هوياتهم وتراخيصهم.']
                    ] : [
                        ['icon' => 'calendar', 'color' => 'bg-blue-500', 'title' => 'Instant Booking', 'desc' => 'No more waiting rooms. Book appointments in one tap.'],
                        ['icon' => 'video', 'color' => 'bg-teal-500', 'title' => 'Video Consultations', 'desc' => 'Talk to top doctors from home via secure encrypted calls.'],
                        ['icon' => 'folder', 'color' => 'bg-indigo-500', 'title' => 'Digital Records', 'desc' => 'Your entire history, prescriptions, and labs in your pocket.'],
                        ['icon' => 'truck', 'color' => 'bg-rose-500', 'title' => 'Online Pharmacy', 'desc' => 'Order medicine delivered to your doorstep in under 60 mins.'],
                        ['icon' => 'bell', 'color' => 'bg-amber-500', 'title' => 'Smart Reminders', 'desc' => 'Never miss a pill again with our intelligent notification system.'],
                        ['icon' => 'shield', 'color' => 'bg-emerald-500', 'title' => 'Verified Network', 'desc' => 'All doctors and centers are thoroughly vetted and licensed.']
                    ];
                @endphp

                @foreach($features as $index => $feature)
                <div class="group p-8 rounded-[2rem] bg-slate-50 border border-slate-100 hover:border-blue-100 hover:shadow-xl hover:shadow-blue-900/5 transition-all duration-300 hover:-translate-y-2 reveal" style="transition-delay: {{ $index * 100 }}ms">
                    <div class="w-14 h-14 {{ $feature['color'] }} rounded-2xl flex items-center justify-center mb-6 text-white shadow-lg {{ str_replace('bg-', 'shadow-', $feature['color']) }}/30 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">{{ $feature['title'] }}</h3>
                    <p class="text-slate-600 leading-relaxed">{{ $feature['desc'] }}</p>
                </div>
                @endforeach
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
@endsection
