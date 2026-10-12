@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'اتصل بنا - مستشفي اون' : 'Contact us - Mostashfa-on')

@php
    $isAr = app()->getLocale() === 'ar';
    $whatsapp = preg_replace('/\D/', '', (string) config('demo.contact.whatsapp'));
    $facebook = config('demo.contact.facebook');
    $phone = config('demo.contact.phone');
    $email = 'support@mostashfaon.com';
@endphp

@section('content')
    <header class="bg-slate-900 text-white pt-40 pb-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-tr from-primary/20 to-teal-500/20 opacity-40"></div>
        <div class="absolute inset-0 opacity-10" style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 40px 40px;"></div>
        <div class="absolute -bottom-1 left-0 w-full h-10 bg-slate-50" style="clip-path: polygon(0 100%, 100% 100%, 100% 0);"></div>

        <div class="container mx-auto px-4 relative z-10 text-center max-w-3xl">
            <span class="inline-block px-4 py-1.5 mb-5 rounded-full bg-white/10 border border-white/20 text-sm font-semibold text-teal-300">
                {{ $isAr ? 'اتصل بنا' : 'Contact us' }}
            </span>
            <h1 class="text-4xl md:text-6xl font-black mb-5">
                {{ $isAr ? 'نحن هنا لمساعدتك' : 'We\'re here to help' }}
            </h1>
            <p class="text-lg text-slate-300 leading-relaxed">
                {{ $isAr
                    ? 'عندك سؤال عن النظام أو الباقات؟ راسلنا وسيرد عليك فريقنا في أقرب وقت.'
                    : 'Have a question about the system or the plans? Message us and our team will get back to you shortly.' }}
            </p>
        </div>
    </header>

    <main class="bg-slate-50 pb-24">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-5xl">
            <div class="grid lg:grid-cols-5 gap-8 -mt-10 relative z-20">

                {{-- Direct channels --}}
                <div class="lg:col-span-2 space-y-4">
                    @if ($whatsapp)
                        <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener noreferrer"
                           class="flex items-center gap-4 bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:border-emerald-200 hover:shadow-md transition-all">
                            <span class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center text-[#25D366]">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.25-.46-2.39-1.47-.88-.79-1.48-1.76-1.65-2.06-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.22 3.08c.15.2 2.1 3.2 5.08 4.49.71.3 1.26.49 1.69.63.71.22 1.36.19 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35z"/></svg>
                            </span>
                            <div>
                                <div class="font-bold text-slate-900">{{ $isAr ? 'واتساب' : 'WhatsApp' }}</div>
                                <div class="text-sm text-slate-500" dir="ltr">{{ $phone ?: '+20 10 7071 1504' }}</div>
                            </div>
                        </a>
                    @endif

                    @if ($phone)
                        <a href="tel:{{ preg_replace('/[^\d+]/', '', (string) $phone) }}"
                           class="flex items-center gap-4 bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:border-slate-200 hover:shadow-md transition-all">
                            <span class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.95.68l1.5 4.5a1 1 0 01-.5 1.21l-2.26 1.13a11 11 0 005.5 5.5l1.13-2.26a1 1 0 011.21-.5l4.5 1.5a1 1 0 01.68.95V19a2 2 0 01-2 2h-1C9.72 21 3 14.28 3 6V5z"/></svg>
                            </span>
                            <div>
                                <div class="font-bold text-slate-900">{{ $isAr ? 'اتصل هاتفيًا' : 'Call us' }}</div>
                                <div class="text-sm text-slate-500" dir="ltr">{{ $phone }}</div>
                            </div>
                        </a>
                    @endif

                    <a href="mailto:{{ $email }}"
                       class="flex items-center gap-4 bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:border-primary/30 hover:shadow-md transition-all">
                        <span class="w-11 h-11 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </span>
                        <div>
                            <div class="font-bold text-slate-900">{{ $isAr ? 'البريد الإلكتروني' : 'Email' }}</div>
                            <div class="text-sm text-slate-500" dir="ltr">{{ $email }}</div>
                        </div>
                    </a>

                    @if ($facebook)
                        <a href="{{ $facebook }}" target="_blank" rel="noopener noreferrer"
                           class="flex items-center gap-4 bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:border-sky-200 hover:shadow-md transition-all">
                            <span class="w-11 h-11 rounded-xl bg-sky-50 flex items-center justify-center text-[#1877F2]">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.96h-1.51c-1.49 0-1.96.93-1.96 1.89v2.26h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07z"/></svg>
                            </span>
                            <div>
                                <div class="font-bold text-slate-900">{{ $isAr ? 'فيسبوك' : 'Facebook' }}</div>
                                <div class="text-sm text-slate-500">{{ $isAr ? 'راسلنا على صفحتنا' : 'Message our page' }}</div>
                            </div>
                        </a>
                    @endif
                </div>

                {{-- Message form — composes a WhatsApp message (or an email as a
                     fallback) so it works without any backend or new tables. --}}
                <div class="lg:col-span-3 bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
                    <h2 class="text-xl font-black text-slate-900 mb-1">{{ $isAr ? 'أرسل لنا رسالة' : 'Send us a message' }}</h2>
                    <p class="text-sm text-slate-500 mb-6">{{ $isAr ? 'املأ البيانات وسيُفتح واتساب برسالتك جاهزة للإرسال.' : 'Fill in your details and WhatsApp will open with your message ready to send.' }}</p>

                    <form id="contact-form" class="space-y-4"
                          data-whatsapp="{{ $whatsapp }}" data-email="{{ $email }}">
                        <div>
                            <label for="cf-name" class="block text-sm font-semibold text-slate-700 mb-1.5">{{ $isAr ? 'الاسم' : 'Name' }}</label>
                            <input id="cf-name" name="name" type="text" required
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                        </div>
                        <div>
                            <label for="cf-phone" class="block text-sm font-semibold text-slate-700 mb-1.5">{{ $isAr ? 'رقم الهاتف' : 'Phone' }}</label>
                            <input id="cf-phone" name="phone" type="tel" dir="ltr"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                        </div>
                        <div>
                            <label for="cf-message" class="block text-sm font-semibold text-slate-700 mb-1.5">{{ $isAr ? 'رسالتك' : 'Your message' }}</label>
                            <textarea id="cf-message" name="message" rows="4" required
                                      class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition resize-y"></textarea>
                        </div>
                        <button type="submit"
                                class="w-full py-3.5 rounded-xl font-bold text-white bg-primary hover:bg-blue-700 transition-all shadow-lg shadow-primary/20">
                            {{ $isAr ? 'إرسال عبر واتساب' : 'Send via WhatsApp' }}
                        </button>
                        <p class="text-xs text-slate-400 text-center">
                            {{ $isAr ? 'لا يعمل واتساب؟ ستُفتح رسالة بريد بدلاً منه.' : 'WhatsApp unavailable? An email will open instead.' }}
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        (function () {
            var form = document.getElementById('contact-form');
            if (!form) return;
            var isAr = {{ $isAr ? 'true' : 'false' }};

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var name = (form.name.value || '').trim();
                var phone = (form.phone.value || '').trim();
                var message = (form.message.value || '').trim();
                if (!name || !message) return;

                var lines = [
                    (isAr ? 'الاسم: ' : 'Name: ') + name,
                    phone ? (isAr ? 'الهاتف: ' : 'Phone: ') + phone : null,
                    '',
                    message,
                ].filter(function (l) { return l !== null; });
                var body = lines.join('\n');

                var wa = form.dataset.whatsapp;
                if (wa) {
                    window.open('https://wa.me/' + wa + '?text=' + encodeURIComponent(body), '_blank', 'noopener');
                    return;
                }
                var subject = isAr ? 'استفسار من موقع مستشفي اون' : 'Enquiry from the Mostashfa-on site';
                window.location.href = 'mailto:' + form.dataset.email +
                    '?subject=' + encodeURIComponent(subject) +
                    '&body=' + encodeURIComponent(body);
            });
        })();
    </script>
@endsection
