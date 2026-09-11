{{--
    "Talk to a human" — Facebook, WhatsApp, phone.

    Shown wherever the visitor is deciding something: on the invitation card
    before the demo, and again on the ended page next to "run it again" and
    "open a real account". Someone who wants a second trial, an account, or an
    answer the sandbox cannot give needs a way to ask.

    The handles are placeholders until the real ones exist — see
    config/demo.php -> demo.contact. An empty value hides its button, so the
    block degrades to whatever is actually set.

    $tone — 'light' on white cards, 'muted' where the surroundings are quieter.
    $heading — overrides the default line above the buttons.
--}}
@php
    $facebook = config('demo.contact.facebook');
    $whatsapp = preg_replace('/\D/', '', (string) config('demo.contact.whatsapp'));
    $phone = config('demo.contact.phone');
    $isArabic = app()->getLocale() === 'ar';
    $heading = $heading ?? ($isArabic ? 'تحتاج مساعدة أو لديك سؤال؟' : 'Need help, or have a question?');
@endphp

@if ($facebook || $whatsapp || $phone)
<div class="mt-6 pt-5 border-t border-slate-200">
    <div class="text-xs font-semibold text-slate-500 mb-3 text-center">
        {{ $heading }}
    </div>

    <div class="flex flex-wrap items-center justify-center gap-2">
        @if ($facebook)
            {{-- rel=noreferrer: the ended page carries a signed session token
                 in its URL, and that is nobody else's business. --}}
            <a href="{{ $facebook }}" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-sky-50 hover:border-sky-200 text-xs font-bold text-slate-700 transition-colors">
                <svg class="w-4 h-4 text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.96h-1.51c-1.49 0-1.96.93-1.96 1.89v2.26h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07z"/>
                </svg>
                {{ $isArabic ? 'فيسبوك' : 'Facebook' }}
            </a>
        @endif

        @if ($whatsapp)
            <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-emerald-50 hover:border-emerald-200 text-xs font-bold text-slate-700 transition-colors">
                <svg class="w-4 h-4 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.25-.46-2.39-1.47-.88-.79-1.48-1.76-1.65-2.06-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.22 3.08c.15.2 2.1 3.2 5.08 4.49.71.3 1.26.49 1.69.63.71.22 1.36.19 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35zM12.05 21.8h-.02a9.8 9.8 0 01-4.99-1.37l-.36-.21-3.71.97.99-3.62-.23-.37a9.77 9.77 0 01-1.5-5.22c0-5.4 4.41-9.8 9.83-9.8 2.62 0 5.09 1.02 6.94 2.88a9.73 9.73 0 012.87 6.93c0 5.4-4.41 9.8-9.82 9.8zM20.52 3.45A11.72 11.72 0 0012.05 0C5.53 0 .22 5.3.22 11.8c0 2.08.55 4.11 1.59 5.9L.12 24l6.45-1.69a11.83 11.83 0 005.48 1.39h.01c6.52 0 11.83-5.3 11.83-11.81 0-3.15-1.23-6.12-3.37-8.35z"/>
                </svg>
                {{ $isArabic ? 'واتساب' : 'WhatsApp' }}
            </a>
        @endif

        @if ($phone)
            <a href="tel:{{ preg_replace('/[^\d+]/', '', (string) $phone) }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.95.68l1.5 4.5a1 1 0 01-.5 1.21l-2.26 1.13a11 11 0 005.5 5.5l1.13-2.26a1 1 0 011.21-.5l4.5 1.5a1 1 0 01.68.95V19a2 2 0 01-2 2h-1C9.72 21 3 14.28 3 6V5z"/>
                </svg>
                <span dir="ltr" class="tabular-nums">{{ $phone }}</span>
            </a>
        @endif
    </div>
</div>
@endif
