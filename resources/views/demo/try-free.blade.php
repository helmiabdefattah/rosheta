{{--
    "Try it free" — the link from the PRODUCTION site to the demo deployment.

    The sandbox is its own installation with its own database, so from here it
    is an outbound link rather than a form. It is on by default and points at
    the sandbox we run; whether it shows at all and where it points are admin
    settings (Admin → Free Trial Invitation), not env vars, so the demo can
    move or go quiet without a deploy.

    Hidden inside the demo installation itself: there the invitation is the
    start card, and this would be a link to the page you are already on.

    $demoInviteUrl — supplied by a view composer bound to this view (see
    DemoServiceProvider), so a page includes it and needs to know nothing else.

    $class — the whole button class list, chosen by the page including it: the
    landing hero and the login card look nothing alike, and only the label and
    the icon are shared between them.
--}}
@if (!empty($demoInviteUrl))
    <a
        href="{{ $demoInviteUrl }}"
        target="_blank"
        rel="noopener noreferrer"
        class="{{ $class ?? 'px-8 py-4 bg-emerald-500 text-white rounded-xl font-bold hover:bg-emerald-600 transition-all shadow-xl shadow-emerald-500/20 hover:-translate-y-1 flex items-center justify-center gap-2' }}"
    >
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        <span>{{ app()->getLocale() === 'ar' ? 'جرّب مجاناً' : 'Try it free' }}</span>
    </a>
@endif
