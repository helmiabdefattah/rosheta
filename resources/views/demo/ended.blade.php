<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>انتهت التجربة - Mostashfa-on</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['Cairo', 'sans-serif'] } } } }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 via-sky-50 to-emerald-50 flex items-center justify-center py-12 px-4 font-sans">

    @php
        $messages = [
            'user_ended' => 'أنهيت التجربة. تم مسح كل البيانات التي أنشأتها نهائياً.',
            'expired'    => 'انتهت مدة التجربة. تم مسح كل البيانات التي أنشأتها نهائياً.',
            'idle'       => 'انتهت التجربة بسبب عدم النشاط. تم مسح كل البيانات التي أنشأتها نهائياً.',
            'converted'  => 'تم إنشاء حسابك الحقيقي — ومسح بيانات التجربة.',
            'purged'     => 'تم إنهاء التجربة ومسح بياناتها.',
        ];
        $message = $messages[$reason] ?? $messages['purged'];
    @endphp

    <div class="w-full max-w-lg text-center">
        <div class="bg-white/90 backdrop-blur-xl border border-slate-200 rounded-2xl shadow-xl p-10">
            <div class="text-5xl mb-4">🧪</div>

            <h1 class="text-2xl font-black text-slate-900 mb-3">انتهت التجربة</h1>

            <p class="text-slate-600 leading-relaxed mb-2">{{ $message }}</p>

            <p class="text-xs text-slate-400 mb-8">
                لم تُحفظ أي بيانات من التجربة، ولم تُمسّ بيانات المرضى الحقيقية في أي وقت.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <form method="POST" action="{{ route('demo.start') }}">
                    @csrf
                    <input type="hidden" name="role" value="doctor">
                    <button type="submit"
                        class="w-full py-3 px-4 rounded-xl shadow-md text-sm font-bold text-white bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 transition-all duration-300">
                        ابدأ تجربة جديدة
                    </button>
                </form>

                <a href="{{ route('register') }}"
                   class="w-full py-3 px-4 rounded-xl border-2 border-sky-200 text-sm font-bold text-sky-700 bg-white hover:bg-sky-50 transition-all duration-300 flex items-center justify-center">
                    أنشئ حسابك الحقيقي
                </a>
            </div>

            <a href="{{ route('login') }}" class="inline-block mt-6 text-sm text-slate-500 hover:text-slate-700 underline underline-offset-4">
                العودة لتسجيل الدخول
            </a>
        </div>

        <div class="text-center text-xs text-slate-400 mt-8 font-medium">
            &copy; {{ date('Y') }} Mostashfa-on. جميع الحقوق محفوظة.
        </div>
    </div>
</body>
</html>
