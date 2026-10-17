{{--
    Patient's dental lab orders on the examination screen (dental clinics): view
    the list, and open create / view / edit in an in-page modal (iframe to the
    embedded order pages) so the doctor never leaves the exam. On save the modal
    auto-closes and only this section refreshes — exam inputs are untouched.

    Expects: $appointment (with client), $doctor.
--}}
@php
    use App\Support\ClinicalChart;
@endphp

@if (ClinicalChart::forSpecialization($doctor->specialization) === ClinicalChart::TEETH)
    @php
        $dentalOrders = \App\Models\DentalLabOrder::where('clinic_id', $appointment->clinic_id)
            ->where('client_id', $appointment->client_id)
            ->with(['lab', 'items', 'payments', 'patientPayments'])
            ->latest()
            ->get();
        $createUrl = route('practice.dental-lab.orders.create', ['client_id' => $appointment->client_id, 'embed' => 1]);
    @endphp

    <section id="dental-lab" class="card p-4 sm:p-5">
        <div class="flex items-center justify-between gap-2 mb-3">
            <h2 class="text-base font-semibold text-slate-900">🦷 {{ __('app.dental.patient_account.file_title') }}</h2>
            <button type="button" data-dental-modal="{{ $createUrl }}" class="btn btn-primary btn-sm">+ {{ __('app.dental.order.add') }}</button>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse ($dentalOrders as $o)
                <details class="py-2.5 group/order">
                    <summary class="flex flex-wrap items-center justify-between gap-2 cursor-pointer list-none">
                        <div class="min-w-0">
                            <div class="text-sm font-medium text-slate-800 flex items-center gap-2">
                                <span class="text-slate-400 transition-transform group-open/order:rotate-90">▸</span>
                                {{ __('app.dental.order.number', ['id' => $o->id]) }}
                                {{-- Inline status change (AJAX; refreshes only this section). --}}
                                <form method="POST" action="{{ route('practice.dental-lab.orders.status', $o) }}"
                                      data-dental-status class="inline-flex" onclick="event.stopPropagation()">
                                    @csrf
                                    <select name="status" aria-label="{{ __('app.dental.order.change_status') }}"
                                            class="rounded-full border-0 text-xs font-medium px-2 py-0.5 cursor-pointer focus:ring-2 focus:ring-indigo-300 {{ $o->status->color() }}">
                                        @foreach(\App\Enums\DentalLabOrderStatus::cases() as $st)
                                            <option value="{{ $st->value }}" @selected($o->status === $st)>{{ $st->label() }}</option>
                                        @endforeach
                                    </select>
                                    <noscript><button class="ms-1 text-xs underline">{{ __('app.dental.actions.save') }}</button></noscript>
                                </form>
                            </div>
                            <div class="text-xs text-slate-500 ms-5">
                                {{ $o->lab?->name ?? '—' }}
                                · {{ __('app.dental.order.total') }}: {{ number_format($o->total(), 2) }}
                                @if ($o->isPatientBilled())
                                    · {{ __('app.dental.patient_account.clinic_due') }}: <b class="{{ $o->patientBalance() > 0 ? 'text-amber-700' : 'text-slate-500' }}">{{ number_format($o->patientBalance(), 2) }}</b>
                                @endif
                                · {{ __('app.dental.patient_account.lab_due') }}: <b class="{{ $o->balance() > 0 ? 'text-red-600' : 'text-slate-500' }}">{{ number_format($o->balance(), 2) }}</b>
                            </div>
                        </div>
                        <div class="shrink-0 flex items-center gap-2">
                            <button type="button" data-dental-modal="{{ route('practice.dental-lab.orders.show', [$o, 'embed' => 1]) }}" class="btn btn-secondary btn-sm">{{ __('app.dental.order.view') }}</button>
                            @unless ($o->status->isFinal())
                                <button type="button" data-dental-modal="{{ route('practice.dental-lab.orders.edit', [$o, 'embed' => 1]) }}" class="btn btn-secondary btn-sm">{{ __('app.dental.actions.edit') }}</button>
                            @endunless
                        </div>
                    </summary>

                    {{-- Full order data, revealed on expand. --}}
                    <div class="mt-3 ms-5 space-y-3 text-xs">
                        {{-- Dates + notes --}}
                        <div class="flex flex-wrap gap-x-4 gap-y-1 text-slate-500">
                            <span>{{ __('app.dental.order.sent') }}: {{ $o->created_at?->translatedFormat('d M Y') ?? '—' }}</span>
                            <span>{{ __('app.dental.order.received_at') }}: {{ $o->received_at?->translatedFormat('d M Y') ?? '—' }}</span>
                            @if (filled($o->notes))<span class="text-slate-600">📝 {{ $o->notes }}</span>@endif
                        </div>

                        {{-- Line items --}}
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="text-[11px] text-slate-400">
                                    <tr>
                                        <th class="text-start font-medium py-1">{{ __('app.dental.line.name') }}</th>
                                        <th class="text-start font-medium py-1">{{ __('app.dental.line.teeth') }}</th>
                                        <th class="text-end font-medium py-1">{{ __('app.dental.line.qty') }}</th>
                                        <th class="text-end font-medium py-1">{{ __('app.dental.line.total') }}</th>
                                        <th class="text-end font-medium py-1">{{ __('app.dental.patient_account.patient_price') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($o->items as $it)
                                        <tr>
                                            <td class="py-1 text-slate-700">{{ $it->name }} <span class="text-slate-400">· {{ $it->level->label() }}</span></td>
                                            <td class="py-1">
                                                @php $teeth = $it->effectiveTeeth(); @endphp
                                                @if ($teeth)
                                                    <span class="flex flex-wrap gap-1">@foreach ($teeth as $t)<span class="px-1 rounded bg-indigo-50 text-indigo-700 font-mono">{{ $t }}</span>@endforeach</span>
                                                @else — @endif
                                            </td>
                                            <td class="py-1 text-end text-slate-600">{{ rtrim(rtrim(number_format($it->quantity, 2), '0'), '.') }} {{ $it->unit }}</td>
                                            <td class="py-1 text-end text-slate-700">{{ number_format($it->total(), 2) }}</td>
                                            <td class="py-1 text-end text-indigo-700">{{ number_format($it->patientTotal(), 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Two ledgers: clinic ↔ lab, and patient ↔ clinic --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div class="rounded-lg bg-slate-50 p-2">
                                <div class="font-semibold text-slate-600 mb-1">{{ __('app.dental.order.lab') }}</div>
                                <div class="flex justify-between"><span class="text-slate-400">{{ __('app.dental.patient_account.lab_price') }}</span><span>{{ number_format($o->total(), 2) }}</span></div>
                                <div class="flex justify-between"><span class="text-slate-400">{{ __('app.dental.patient_account.lab_paid') }}</span><span class="text-emerald-700">{{ number_format($o->paidTotal(), 2) }}</span></div>
                                <div class="flex justify-between font-semibold"><span class="text-slate-500">{{ __('app.dental.patient_account.lab_due') }}</span><span class="{{ $o->balance() > 0 ? 'text-red-600' : 'text-slate-500' }}">{{ number_format($o->balance(), 2) }}</span></div>
                            </div>
                            @if ($o->isPatientBilled())
                                <div class="rounded-lg bg-slate-50 p-2">
                                    <div class="font-semibold text-slate-600 mb-1">{{ __('app.dental.order.patient') }}</div>
                                    <div class="flex justify-between"><span class="text-slate-400">{{ __('app.dental.patient_account.charge') }}</span><span>{{ number_format($o->patientCharge(), 2) }}</span></div>
                                    <div class="flex justify-between"><span class="text-slate-400">{{ __('app.dental.patient_account.paid') }}</span><span class="text-emerald-700">{{ number_format($o->patientPaidTotal(), 2) }}</span></div>
                                    <div class="flex justify-between font-semibold"><span class="text-slate-500">{{ __('app.dental.patient_account.clinic_due') }}</span><span class="{{ $o->patientBalance() > 0 ? 'text-amber-700' : 'text-slate-500' }}">{{ number_format($o->patientBalance(), 2) }}</span></div>
                                </div>
                            @endif
                        </div>
                    </div>
                </details>
            @empty
                <p class="text-sm text-slate-400 py-2">{{ __('app.dental.order.empty') }}</p>
            @endforelse
        </div>
    </section>

    {{-- The modal + controller: rendered once, kept outside #dental-lab so a
         section refresh never removes it. --}}
    @once
        <div id="dental-modal" class="fixed inset-0 z-[90] items-center justify-center p-3" aria-modal="true" role="dialog">
            <div class="absolute inset-0 bg-slate-900/50" data-dm-close></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl h-[90vh] flex flex-col overflow-hidden">
                <div class="flex items-center justify-between gap-2 px-4 py-2 border-b border-slate-100">
                    <span class="text-sm font-semibold text-slate-700">🦷 {{ __('app.dental.patient_account.file_title') }}</span>
                    <button type="button" data-dm-close class="btn btn-secondary btn-sm">✕</button>
                </div>
                <iframe id="dental-modal-frame" title="{{ __('app.dental.patient_account.file_title') }}" class="flex-1 w-full" style="border:0"></iframe>
            </div>
        </div>

        @push('styles')
        <style>
            #dental-modal { display: none; }
            #dental-modal.open { display: flex; }
        </style>
        @endpush

        @push('scripts')
        <script>
        (function () {
            if (window.__dentalModalInit) return; window.__dentalModalInit = true;

            var el = function () { return document.getElementById('dental-modal'); };
            var frame = function () { return document.getElementById('dental-modal-frame'); };

            function open(url) {
                var f = frame(); if (!f) return;
                f.src = url;
                el().classList.add('open');
                document.body.style.overflow = 'hidden';
            }
            function close() {
                var m = el(); if (!m) return;
                m.classList.remove('open');
                var f = frame(); if (f) f.src = 'about:blank';
                document.body.style.overflow = '';
                refreshSection();
            }
            // Re-fetch only the dental-lab section, so the exam form (and any
            // unsaved input) is left completely alone.
            function refreshSection() {
                fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { return r.text(); })
                    .then(function (html) {
                        var fresh = new DOMParser().parseFromString(html, 'text/html').getElementById('dental-lab');
                        var cur = document.getElementById('dental-lab');
                        if (fresh && cur) cur.replaceWith(fresh);
                    })
                    .catch(function () {});
            }

            document.addEventListener('click', function (e) {
                var trigger = e.target.closest('[data-dental-modal]');
                if (trigger) { e.preventDefault(); open(trigger.getAttribute('data-dental-modal')); return; }
                if (e.target.closest('[data-dm-close]')) { close(); }
            });

            // Inline status change: POST in the background, then refresh only the
            // dental section so the exam form keeps any unsaved input.
            document.addEventListener('change', function (e) {
                var sel = e.target.closest('[data-dental-status] select[name="status"]');
                if (!sel) return;
                var form = sel.closest('form');
                sel.disabled = true;
                fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                    body: new FormData(form),
                    cache: 'no-store',
                })
                    .then(function () { refreshSection(); })
                    .catch(function () { sel.disabled = false; });
            });
            document.addEventListener('keydown', function (e) {
                var m = el();
                if (e.key === 'Escape' && m && m.classList.contains('open')) close();
            });
            // iframe load doesn't bubble — catch it in the capture phase. When the
            // embedded page lands on a saved order, close (which refreshes).
            document.addEventListener('load', function (e) {
                if (!e.target || e.target.id !== 'dental-modal-frame') return;
                try {
                    if ((e.target.contentWindow.location.search || '').indexOf('saved=1') !== -1) close();
                } catch (err) {}
            }, true);
        })();
        </script>
        @endpush
    @endonce
@endif
