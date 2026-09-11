<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
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
        $endedMessage = $messages[$reason] ?? $messages['purged'];
        $surveyToken = $surveyToken ?? '';
        $answered = $answered ?? false;
    @endphp

    <div class="w-full max-w-lg">
        <div class="bg-white/90 backdrop-blur-xl border border-slate-200 rounded-2xl shadow-xl p-8 sm:p-10 text-center">
            <div class="text-5xl mb-4">🧪</div>

            <h1 class="text-2xl font-black text-slate-900 mb-3">انتهت التجربة</h1>

            <p class="text-slate-600 leading-relaxed mb-2">{{ $endedMessage }}</p>

            <p class="text-xs text-slate-400">
                لم تُحفظ أي بيانات من التجربة، ولم تُمسّ بيانات المرضى الحقيقية في أي وقت.
            </p>
        </div>

        {{--
            The exit survey. Asked here and nowhere else: the visitor has just
            finished, nothing they were doing is half-done, and three questions
            are about as much as anyone answers for free. Only the first one is
            required — the two boxes are where the useful answers actually come
            from, and demanding them would cost us the yes/no as well.
        --}}
        @if ($answered)
            <div class="mt-5 bg-emerald-50 border border-emerald-200 rounded-2xl p-6 text-center">
                <div class="text-3xl mb-2">🙏</div>
                <div class="text-base font-bold text-emerald-900">شكراً لرأيك</div>
                <p class="text-sm text-emerald-700 mt-1 leading-relaxed">
                    وصلتنا إجابتك، وسنأخذها في الاعتبار ونحن نطوّر النظام.
                </p>
            </div>
        @else
            <form method="POST" action="{{ route('demo.survey') }}"
                  class="mt-5 bg-white/90 backdrop-blur-xl border border-slate-200 rounded-2xl shadow-lg p-6 sm:p-8">
                @csrf
                <input type="hidden" name="token" value="{{ $surveyToken }}">
                <input type="hidden" name="reason" value="{{ $reason }}">

                <div class="text-center mb-6">
                    <h2 class="text-lg font-bold text-slate-900">٣ أسئلة سريعة قبل أن تذهب</h2>
                    <p class="text-xs text-slate-500 mt-1">رأيك هو ما يحدد الخطوة التالية في النظام.</p>
                </div>

                {{-- Q1 — the only required answer. --}}
                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-800 mb-3">
                        ١. هل كان النظام مفيداً بالنسبة لك؟
                        <span class="text-rose-500">*</span>
                    </label>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="was_useful" value="1" class="sr-only peer"
                                   {{ old('was_useful') === '1' ? 'checked' : '' }} required>
                            <span class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl border-2 border-slate-200 bg-white text-sm font-bold text-slate-600
                                         peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700
                                         peer-focus-visible:ring-2 peer-focus-visible:ring-emerald-500 peer-focus-visible:ring-offset-2
                                         hover:bg-slate-50 transition-all">
                                <span class="text-lg leading-none">👍</span> نعم، مفيد
                            </span>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="was_useful" value="0" class="sr-only peer"
                                   {{ old('was_useful') === '0' ? 'checked' : '' }}>
                            <span class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl border-2 border-slate-200 bg-white text-sm font-bold text-slate-600
                                         peer-checked:border-rose-400 peer-checked:bg-rose-50 peer-checked:text-rose-700
                                         peer-focus-visible:ring-2 peer-focus-visible:ring-rose-400 peer-focus-visible:ring-offset-2
                                         hover:bg-slate-50 transition-all">
                                <span class="text-lg leading-none">👎</span> لا
                            </span>
                        </label>
                    </div>

                    @error('was_useful')
                        <p class="text-xs text-rose-600 mt-2">برجاء اختيار إجابة.</p>
                    @enderror
                </div>

                {{-- Q2 — what is missing. --}}
                <div class="mb-5">
                    <label for="wants_added" class="block text-sm font-bold text-slate-800 mb-2">
                        ٢. هل هناك شيء تحب إضافته للنظام؟
                        <span class="text-xs font-normal text-slate-400">(اختياري)</span>
                    </label>
                    <textarea name="wants_added" id="wants_added" rows="3" maxlength="2000"
                        placeholder="اكتب هنا أي ميزة أو تفصيلة كنت تتمنى أن تجدها…"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 resize-y">{{ old('wants_added') }}</textarea>
                    @error('wants_added')
                        <p class="text-xs text-rose-600 mt-1">النص أطول من المسموح.</p>
                    @enderror
                </div>

                {{-- Q3 — what got in the way. --}}
                <div class="mb-6">
                    <label for="wants_removed" class="block text-sm font-bold text-slate-800 mb-2">
                        ٣. هل هناك شيء تحب حذفه أو لم يعجبك؟
                        <span class="text-xs font-normal text-slate-400">(اختياري)</span>
                    </label>
                    <textarea name="wants_removed" id="wants_removed" rows="3" maxlength="2000"
                        placeholder="اكتب هنا أي شيء أزعجك أو رأيته زائداً عن الحاجة…"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 resize-y">{{ old('wants_removed') }}</textarea>
                    @error('wants_removed')
                        <p class="text-xs text-rose-600 mt-1">النص أطول من المسموح.</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full py-3 px-4 rounded-xl shadow-md text-sm font-bold text-white bg-gradient-to-r from-sky-500 to-indigo-500 hover:from-sky-600 hover:to-indigo-600 transition-all duration-300">
                    أرسل رأيك
                </button>
            </form>
        @endif

        {{-- What comes next: another run, a real account, or a human to ask. --}}
        <div class="mt-5 bg-white/90 backdrop-blur-xl border border-slate-200 rounded-2xl shadow-lg p-6 sm:p-8">
            <div class="text-center mb-4">
                <div class="text-sm font-bold text-slate-900">تحتاج تجربة أخرى أو حساباً حقيقياً؟</div>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    ابدأ تجربة جديدة بعيادة نظيفة، أو افتح حسابك الحقيقي وابدأ العمل ببياناتك.
                </p>
            </div>

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

            @include('demo.contact', ['heading' => 'أو تواصل معنا مباشرة لأي استفسار'])

            <div class="text-center">
                <a href="{{ route('login') }}" class="inline-block mt-6 text-sm text-slate-500 hover:text-slate-700 underline underline-offset-4">
                    العودة لتسجيل الدخول
                </a>
            </div>
        </div>

        <div class="text-center text-xs text-slate-400 mt-8 font-medium">
            &copy; {{ date('Y') }} Mostashfa-on. جميع الحقوق محفوظة.
        </div>
    </div>
</body>
</html>
