{{-- Lab & radiology results from the labs system (same data the patient sees
     at client/test-results). Read-only, collapsed by default, with client-side
     type/test filtering and pagination. Expects: $labResults (Collection of
     accepted lab/radiology Offers with laboratory, attachments, testLines). --}}
@php
    $labTestOptions = $labResults
        ->flatMap(fn ($o) => $o->testLines->map(fn ($l) => optional($l->medicalTest)->test_name_ar ?: optional($l->medicalTest)->test_name_en))
        ->filter()->unique()->sort(SORT_NATURAL | SORT_FLAG_CASE)->values();
@endphp
<div class="bg-white rounded-xl shadow-sm p-5">
    <button type="button" onclick="toggleLabResults()"
            class="w-full flex items-center justify-between gap-2 text-start">
        <h2 class="font-semibold text-slate-800">🔬 {{ __('app.examine.lab_results_section') }}</h2>
        <span class="flex items-center gap-2">
            <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ $labResults->count() }}</span>
            <span id="lab-chevron" class="text-slate-400 text-sm">▸</span>
        </span>
    </button>

    <div id="lab-results-body" class="hidden mt-4">
        @if ($labResults->isEmpty())
            <p class="text-sm text-slate-400 italic">{{ __('app.examine.no_lab_results') }}</p>
        @else
            {{-- Filters --}}
            <div class="flex flex-wrap items-end gap-2 mb-4">
                <div>
                    <label class="block text-xs text-slate-500 mb-1">{{ __('app.examine.test_type') }}</label>
                    <select id="lab-type-filter" class="border rounded px-2 py-1.5 text-sm">
                        <option value="">{{ __('app.report.all') }}</option>
                        <option value="test">{{ __('app.test_types.lab') }}</option>
                        <option value="radiology">{{ __('app.test_types.radiology') }}</option>
                    </select>
                </div>
                <div class="grow min-w-[12rem]">
                    <label class="block text-xs text-slate-500 mb-1">{{ __('app.examine.lab_filter_test') }}</label>
                    <select id="lab-test-filter" class="w-full border rounded px-2 py-1.5 text-sm">
                        <option value="">{{ __('app.report.all') }}</option>
                        @foreach ($labTestOptions as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Result list (filtered + paginated in JS) --}}
            <div id="lab-results-list" class="space-y-3">
                @foreach ($labResults as $offer)
                    @php
                        $isRadiology = $offer->request_type === 'radiology';
                        $statusClass = match ($offer->vendor_status) {
                            'test_completed' => 'bg-emerald-100 text-emerald-700',
                            'sample_collected' => 'bg-blue-100 text-blue-700',
                            default => 'bg-amber-100 text-amber-700',
                        };
                        $statusLabel = match ($offer->vendor_status) {
                            'test_completed' => __('app.examine.lab_status_completed'),
                            'sample_collected' => __('app.examine.lab_status_sample'),
                            default => __('app.examine.lab_status_preparing'),
                        };
                        $offerTests = $offer->testLines
                            ->map(fn ($l) => optional($l->medicalTest)->test_name_ar ?: optional($l->medicalTest)->test_name_en)
                            ->filter()->values();
                    @endphp
                    <div class="lab-result border border-slate-100 rounded-lg p-3"
                         data-type="{{ $offer->request_type }}"
                         data-tests="{{ $offerTests->join('|') }}">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-xs px-2 py-0.5 rounded {{ $isRadiology ? 'bg-purple-100 text-purple-700' : 'bg-teal-100 text-teal-700' }}">
                                    {{ $isRadiology ? __('app.test_types.radiology') : __('app.test_types.lab') }}
                                </span>
                                <span class="font-medium text-slate-700">🏥 {{ $offer->laboratory->name ?? '—' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $statusClass }}">{{ $statusLabel }}</span>
                                <span class="text-xs text-slate-400">{{ $offer->updated_at?->format('Y-m-d') }}</span>
                            </div>
                        </div>

                        @if ($offerTests->isNotEmpty())
                            <p class="text-xs text-slate-500 mb-2">{{ $offerTests->join('، ') }}</p>
                        @endif

                        @if ($offer->attachments->isNotEmpty())
                            <ul class="flex flex-wrap gap-2">
                                @foreach ($offer->attachments as $file)
                                    <li>
                                        <a href="{{ $file->url }}" target="_blank"
                                           class="inline-flex items-center gap-1 text-indigo-600 hover:underline bg-slate-50 border border-slate-100 rounded px-2 py-1 text-xs">
                                            📎 {{ __('app.examine.view_result') }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-xs text-slate-400 italic">{{ __('app.examine.lab_waiting_results') }}</p>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- No matches for the active filter --}}
            <p id="lab-no-match" class="hidden text-sm text-slate-400 italic mt-3">{{ __('app.examine.no_lab_results') }}</p>

            {{-- Pagination --}}
            <div id="lab-pagination" class="flex items-center justify-between gap-2 mt-4 hidden">
                <button type="button" id="lab-prev"
                        class="text-sm px-3 py-1.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">
                    ‹ {{ __('app.examine.lab_prev') }}
                </button>
                <span id="lab-page-info" class="text-xs text-slate-500"></span>
                <button type="button" id="lab-next"
                        class="text-sm px-3 py-1.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">
                    {{ __('app.examine.lab_next') }} ›
                </button>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // ---- Lab & radiology results: collapse + filter (type/test) + paginate ----
    function toggleLabResults() {
        const body = document.getElementById('lab-results-body');
        const chevron = document.getElementById('lab-chevron');
        if (!body) return;
        body.classList.toggle('hidden');
        if (chevron) chevron.textContent = body.classList.contains('hidden') ? '▸' : '▾';
    }

    (function () {
        const list = document.getElementById('lab-results-list');
        if (!list) return; // no results / empty state

        const items = Array.from(list.querySelectorAll('.lab-result'));
        const typeSel = document.getElementById('lab-type-filter');
        const testSel = document.getElementById('lab-test-filter');
        const pager = document.getElementById('lab-pagination');
        const prevBtn = document.getElementById('lab-prev');
        const nextBtn = document.getElementById('lab-next');
        const pageInfo = document.getElementById('lab-page-info');
        const noMatch = document.getElementById('lab-no-match');
        const PAGE_SIZE = 5;
        let page = 1;

        function matches(el) {
            const type = typeSel.value;
            const test = testSel.value;
            if (type && el.dataset.type !== type) return false;
            if (test) {
                const tests = (el.dataset.tests || '').split('|');
                if (!tests.includes(test)) return false;
            }
            return true;
        }

        function render() {
            const filtered = items.filter(matches);
            const pages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
            if (page > pages) page = pages;
            const start = (page - 1) * PAGE_SIZE;
            const end = start + PAGE_SIZE;

            items.forEach(el => { el.style.display = 'none'; });
            filtered.slice(start, end).forEach(el => { el.style.display = ''; });

            noMatch.classList.toggle('hidden', filtered.length !== 0);
            pager.classList.toggle('hidden', filtered.length <= PAGE_SIZE);
            pageInfo.textContent = @json(__('app.examine.lab_page_info'))
                .replace(':page', page).replace(':pages', pages).replace(':total', filtered.length);
            prevBtn.disabled = page <= 1;
            nextBtn.disabled = page >= pages;
        }

        typeSel.addEventListener('change', () => { page = 1; render(); });
        testSel.addEventListener('change', () => { page = 1; render(); });
        prevBtn.addEventListener('click', () => { if (page > 1) { page--; render(); } });
        nextBtn.addEventListener('click', () => { page++; render(); });
        render();
    })();
</script>
@endpush
