<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ app()->getLocale() === 'ar' ? 'تسجيل الدخول' : 'Login' }} - Mostashfa-on</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ["{{ app()->getLocale() === 'ar' ? 'Cairo' : 'Plus Jakarta Sans' }}", 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes blob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }
        .blob {
            animation: blob 7s infinite;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 via-sky-50 to-cyan-50 relative flex items-center justify-center py-12 px-4 font-sans">
    
    <!-- Language Toggle -->
    <div class="fixed top-6 {{ app()->getLocale() === 'ar' ? 'left-6' : 'right-6' }} z-50">
        <a href="{{ route('locale', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" 
           class="flex items-center gap-2 px-4 py-2 bg-white/80 backdrop-blur-md border border-sky-100 rounded-full shadow-sm text-sm font-semibold text-slate-700 hover:bg-white hover:shadow-md transition-all duration-300">
            <svg class="w-5 h-5 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
            </svg>
            <span>{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</span>
        </a>
    </div>

    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="blob bg-sky-300/25 w-[30rem] h-[30rem] rounded-full absolute -top-28 -left-28 blur-3xl"></div>
        <div class="blob bg-cyan-300/25 w-[30rem] h-[30rem] rounded-full absolute -bottom-28 -right-28 blur-3xl" style="animation-delay: 2s;"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <div class="bg-white/90 backdrop-blur-xl border border-sky-100 rounded-2xl shadow-xl p-8">
            <div class="flex flex-col items-center justify-center gap-4 mb-8">
                <img src="{{ url('/images/mo-logo.png') }}" alt="Mostashfa-on" class="w-24 h-24 rounded-2xl ring-2 ring-sky-200 shadow-md object-contain transition-transform hover:scale-105 duration-300">
                <div class="text-center leading-tight">
                    <div class="text-3xl font-black text-slate-900 tracking-tight">Mostashfa-on</div>
                    <div class="text-base font-medium text-slate-500 mt-1">
                        {{ app()->getLocale() === 'ar' ? 'مرحباً بعودتك' : 'Welcome back' }}
                    </div>
                </div>
            </div>

            @if (session('info'))
                <div class="mb-6 p-4 bg-sky-50 border-s-4 border-sky-500 rounded-lg">
                    <p class="text-sm font-medium text-sky-900">{{ session('info') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-s-4 border-red-500 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ms-3">
                            <h3 class="text-sm font-bold text-red-800">
                                {{ app()->getLocale() === 'ar' ? 'فشل تسجيل الدخول' : 'Login Failed' }}
                            </h3>
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

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني أو رقم الهاتف' : 'Email or Phone Number' }}
                    </label>
                    <input
                        id="email"
                        type="text"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition duration-200 outline-none placeholder-gray-400"
                        placeholder="{{ app()->getLocale() === 'ar' ? 'أدخل بريدك الإلكتروني أو رقم هاتفك' : 'Enter your email or phone number' }}"
                    >
                    <p class="mt-2 text-xs text-gray-400 leading-relaxed italic">
                        {{ app()->getLocale() === 'ar' ? '* يمكن للعملاء تسجيل الدخول باستخدام البريد الإلكتروني أو رقم الهاتف' : '* Clients can login using email or phone number' }}
                    </p>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'كلمة المرور' : 'Password' }}
                    </label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition duration-200 outline-none placeholder-gray-400"
                        placeholder="{{ app()->getLocale() === 'ar' ? 'أدخل كلمة المرور الخاصة بك' : 'Enter your password' }}"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input
                            id="remember"
                            name="remember"
                            type="checkbox"
                            class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-gray-300 rounded cursor-pointer"
                        >
                        <label for="remember" class="ms-2 block text-sm text-gray-600 cursor-pointer select-none">
                            {{ app()->getLocale() === 'ar' ? 'تذكرني' : 'Remember me' }}
                        </label>
                    </div>
                </div>

                <div>
                    <button
                        type="submit"
                        class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-r from-sky-500 via-sky-600 to-cyan-500 hover:from-sky-600 hover:to-cyan-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 transition-all duration-300 transform hover:-translate-y-0.5"
                    >
                        {{ app()->getLocale() === 'ar' ? 'تسجيل الدخول' : 'Sign in' }}
                    </button>
                </div>

                <div class="text-center space-y-3">
                    <p class="text-sm text-gray-600">
                        {{ app()->getLocale() === 'ar' ? 'ليس لديك حساب؟' : "Don't have an account?" }}
                        <a href="{{ route('register') }}" class="font-bold text-sky-600 hover:text-sky-500 underline decoration-sky-100 underline-offset-4 decoration-2 hover:decoration-sky-500 transition-all">
                            {{ app()->getLocale() === 'ar' ? 'سجل هنا' : 'Register here' }}
                        </a>
                    </p>
                    <p class="text-sm text-gray-600">
                        <a href="{{ route('service-provider.register') }}" class="font-bold text-emerald-600 hover:text-emerald-500 underline decoration-emerald-100 underline-offset-4 decoration-2 hover:decoration-emerald-500 transition-all">
                            {{ app()->getLocale() === 'ar' ? 'تسجيل كمقدم خدمة (صيدلية، معمل، أشعة، تمريض، طبيب)' : 'Register as a service provider (pharmacy, lab, radiology, nurse, doctor)' }}
                        </a>
                    </p>
                </div>
            </form>
        </div>

        {{-- Demo sandbox: a populated clinic, no signup, wiped when it ends. --}}
        @if (config('demo.enabled'))
            <div class="mt-6 bg-white/90 backdrop-blur-xl border border-emerald-100 rounded-2xl shadow-lg p-6">
                <div class="flex items-center gap-3 mb-1">
                    <span class="text-2xl">🧪</span>
                    <div>
                        <div class="text-base font-bold text-slate-900">
                            {{ app()->getLocale() === 'ar' ? 'جرّب النظام بدون تسجيل' : 'Try the system — no signup' }}
                        </div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            {{ app()->getLocale() === 'ar'
                                ? 'عيادة كاملة بمرضى ومواعيد وروشتات — تُمسح بالكامل عند الإنهاء.'
                                : 'A full clinic with patients, appointments and prescriptions — erased when you finish.' }}
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('demo.start') }}" class="mt-4">
                    @csrf

                    {{-- Pick a specialization: the seeded clinic matches it. --}}
                    @if (!empty($demoSpecializations['tailored']) && $demoSpecializations['tailored']->isNotEmpty())
                        <label for="demo-specialty" class="block text-xs font-semibold text-slate-600 mb-1.5">
                            {{ app()->getLocale() === 'ar' ? 'اختر تخصصك لتظهر لك عيادة مناسبة' : 'Pick your specialty to get a matching clinic' }}
                        </label>
                        <select
                            name="specialty" id="demo-specialty"
                            class="w-full mb-3 px-3 py-2.5 rounded-xl border border-emerald-200 bg-white text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        >
                            <optgroup label="{{ app()->getLocale() === 'ar' ? 'عيادات مجهزة بمحتوى التخصص' : 'Clinics with specialty content' }}">
                                @foreach ($demoSpecializations['tailored'] as $specialization)
                                    <option value="{{ $specialization->slug }}">{{ $specialization->name }}</option>
                                @endforeach
                            </optgroup>

                            @if ($demoSpecializations['general']->isNotEmpty())
                                <optgroup label="{{ app()->getLocale() === 'ar' ? 'تخصصات أخرى (محتوى باطنة عامة)' : 'Other specialties (general content)' }}">
                                    @foreach ($demoSpecializations['general'] as $specialization)
                                        <option value="{{ $specialization->slug }}">{{ $specialization->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <button
                        type="submit" name="role" value="doctor"
                        class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl shadow-md text-sm font-bold text-white bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-300 transform hover:-translate-y-0.5"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        {{ app()->getLocale() === 'ar' ? 'جرّب كطبيب' : 'Try as a doctor' }}
                    </button>

                    <button
                        type="submit" name="role" value="assistant"
                        class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl border-2 border-emerald-200 text-sm font-bold text-emerald-700 bg-white hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-300"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ app()->getLocale() === 'ar' ? 'جرّب كمساعد' : 'Try as an assistant' }}
                    </button>
                    </div>
                </form>

                <p class="text-[11px] text-slate-400 mt-3 text-center leading-relaxed">
                    {{ app()->getLocale() === 'ar'
                        ? 'بيانات التجربة وهمية ومعزولة تماماً عن بيانات المرضى الحقيقية.'
                        : 'Demo data is fictional and fully isolated from real patient records.' }}
                </p>
            </div>
        @endif

        <div class="text-center text-xs text-slate-400 mt-8 font-medium">
            &copy; {{ date('Y') }} Mostashfa-on. {{ app()->getLocale() === 'ar' ? 'جميع الحقوق محفوظة.' : 'All rights reserved.' }}
        </div>
    </div>
</body>
</html>
