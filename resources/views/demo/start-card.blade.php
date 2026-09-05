{{--
    The demo sandbox invitation: a full clinic, no signup, wiped when it ends.

    Shared by the login page and the landing page rather than copied into both.
    The specialization picker is supplied by a view composer bound to THIS view
    (see DemoServiceProvider), so a page can include the card without knowing
    anything about where that list comes from.

    $wrapperClass — spacing chosen by the page including it. The card styles
    itself; only its margin belongs to its surroundings.
--}}
@if (config('demo.enabled'))
    @php($wrapperClass = $wrapperClass ?? 'mt-6')

<div class="{{ $wrapperClass }} bg-white/90 backdrop-blur-xl border border-emerald-100 rounded-2xl shadow-lg p-6">
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

        {{-- Optional: the visitor's own name. It is written across
             the whole sandbox — the clinic sign, the prescriptions,
             the reports — so what they are looking at reads as
             their clinic rather than someone else's. Left empty, a
             believable name is generated instead. --}}
        <label for="demo-doctor-name" class="block text-xs font-semibold text-slate-600 mb-1.5">
            {{ app()->getLocale() === 'ar' ? 'اسمك (اختياري)' : 'Your name (optional)' }}
        </label>
        <input
            type="text" name="doctor_name" id="demo-doctor-name" maxlength="60"
            autocomplete="name"
            placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: أحمد سامي — ليظهر اسمك داخل التجربة' : 'e.g. Ahmed Sami — shown across your demo clinic' }}"
            class="w-full mb-3 px-3 py-2.5 rounded-xl border border-emerald-200 bg-white text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
        >

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
