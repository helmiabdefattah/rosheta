<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ app()->getLocale() === 'ar' ? 'تسجيل مقدم خدمة' : 'Register as service provider' }} - Mostashfa-on</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes blob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }
        .blob { animation: blob 7s infinite; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 via-sky-50 to-cyan-50 py-12 px-4 font-sans">
    <div class="fixed top-6 {{ app()->getLocale() === 'ar' ? 'left-6' : 'right-6' }} z-50 flex gap-2">
        <a href="{{ route('locale', app()->getLocale() === 'ar' ? 'en' : 'ar') }}"
           class="px-4 py-2 bg-white/80 backdrop-blur-md border border-sky-100 rounded-full shadow-sm text-sm font-semibold text-slate-700 hover:bg-white">
            {{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}
        </a>
    </div>

    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="blob bg-sky-300/25 w-[30rem] h-[30rem] rounded-full absolute -top-28 -left-28 blur-3xl"></div>
        <div class="blob bg-cyan-300/25 w-[30rem] h-[30rem] rounded-full absolute -bottom-28 -right-28 blur-3xl" style="animation-delay: 2s;"></div>
    </div>

    <div class="max-w-4xl mx-auto relative z-10">
        <div class="text-center mb-10">
            <a href="{{ route('login') }}" class="text-sm font-medium text-sky-600 hover:text-sky-500">
                {{ app()->getLocale() === 'ar' ? '← العودة لتسجيل الدخول' : '← Back to login' }}
            </a>
            <h1 class="mt-6 text-3xl font-black text-slate-900 tracking-tight">
                {{ app()->getLocale() === 'ar' ? 'تسجيل مقدم خدمة' : 'Register as a service provider' }}
            </h1>
            <p class="mt-2 text-slate-600 max-w-xl mx-auto">
                {{ app()->getLocale() === 'ar'
                    ? 'اختر نوع الخدمة التي تقدمها. سيتم مراجعة طلبك وتفعيل الحساب من قبل الإدارة.'
                    : 'Choose the type of service you provide. Your request will be reviewed and your account activated by an administrator.' }}
            </p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @php
                $cards = [
                    'pharmacy' => ['en' => 'Pharmacy', 'ar' => 'صيدلية', 'icon' => '💊'],
                    'laboratory' => ['en' => 'Laboratory', 'ar' => 'معمل تحاليل', 'icon' => '🧪'],
                    'radiology' => ['en' => 'Radiology lab', 'ar' => 'مركز أشعة', 'icon' => '📷'],
                    'nurse' => ['en' => 'Nurse', 'ar' => 'تمريض منزلي', 'icon' => '🩺'],
                    'doctor' => ['en' => 'Doctor', 'ar' => 'طبيب', 'icon' => '👨‍⚕️'],
                    'charitable_organization' => ['en' => 'Charitable organization', 'ar' => 'منظمة خيرية', 'icon' => '❤️'],
                ];
                $ar = app()->getLocale() === 'ar';
            @endphp
            @foreach ($cards as $key => $label)
                <a href="{{ route('service-provider.register.create', $key) }}"
                   class="group flex items-center gap-4 p-6 bg-white/90 backdrop-blur-xl border border-sky-100 rounded-2xl shadow-lg hover:shadow-xl hover:border-sky-300 transition-all duration-300 hover:-translate-y-0.5">
                    <span class="text-4xl" aria-hidden="true">{{ $label['icon'] }}</span>
                    <div class="flex-1 {{ $ar ? 'text-right' : 'text-left' }}">
                        <div class="text-lg font-bold text-slate-900 group-hover:text-sky-700">{{ $ar ? $label['ar'] : $label['en'] }}</div>
                        <div class="text-sm text-slate-500 mt-1">{{ $ar ? 'متابعة التسجيل' : 'Continue registration' }} →</div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</body>
</html>
