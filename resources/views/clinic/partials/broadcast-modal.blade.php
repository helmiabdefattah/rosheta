{{-- Broadcast-a-message modal. Included on the doctor + assistant dashboards.
     Trigger it from anywhere with onclick="toggle('broadcast-modal')". --}}
@php
    $bLocale = app()->getLocale() === 'ar' ? 'ar' : 'en';
    // Today's patients, resolved from the same place the controller checks the
    // submitted ids against, so the list and the allow-list are one thing.
    $bDoctor = request()->attributes->get('clinic_doctor') ?? auth()->user()?->clinicDoctor();
    $bAudience = $bDoctor
        ? \App\Support\ClinicBroadcastAudience::forDoctor($bDoctor)
        : collect();
@endphp

<div id="broadcast-modal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800">📣 {{ __('app.notify.title') }}</h2>
            <button type="button" onclick="toggle('broadcast-modal')"
                    class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
        </div>

        {{-- Queue position: push each waiting patient how many are ahead of them. --}}
        <form method="POST" action="{{ route('practice.notifications.queue') }}"
              class="px-5 pt-4 pb-4 border-b border-slate-100">
            @csrf
            <div class="flex items-center justify-between gap-3 bg-teal-50 border border-teal-100 rounded-xl px-4 py-3">
                <div>
                    <p class="text-sm font-semibold text-teal-900">🔔 {{ __('app.notify.queue_button') }}</p>
                    <p class="text-xs text-teal-700 mt-0.5">{{ __('app.notify.queue_button_hint') }}</p>
                </div>
                <button type="submit"
                        class="shrink-0 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                    {{ __('app.notify.queue_send') }}
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('practice.notifications.broadcast') }}" class="p-5 space-y-4">
            @csrf
            <p class="text-xs text-slate-500">{{ __('app.notify.subtitle') }}</p>

            <input type="hidden" name="template" id="broadcast-template" value="">

            {{-- Ready-made messages: click to fill, or type your own below. --}}
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-2">{{ __('app.notify.ready_made') }}</label>
                <div class="flex flex-wrap gap-2">
                    @foreach (config('clinic_broadcast.templates') as $key => $t)
                        @php $text = $t[$bLocale] ?? $t['en']; @endphp
                        <button type="button"
                                data-broadcast-chip
                                onclick="selectBroadcastTemplate(this, '{{ $key }}', @js($text))"
                                class="text-start text-xs px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-indigo-50 hover:border-indigo-300 text-slate-700 transition">
                            {{ $text }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Recipients: everyone today, ticked by default, so sending to the
                 whole room stays one click while a smaller group is possible. --}}
            <div>
                <div class="flex items-center justify-between gap-3 mb-1">
                    <label class="block text-xs font-medium text-slate-500">{{ __('app.notify.recipients') }}</label>
                    <div class="flex items-center gap-2 text-xs">
                        <span id="broadcast-count" class="text-slate-400"></span>
                        <button type="button" onclick="setBroadcastRecipients(true)"
                                class="px-2 py-1 rounded border border-slate-200 hover:bg-slate-50">{{ __('app.notify.select_all') }}</button>
                        <button type="button" onclick="setBroadcastRecipients(false)"
                                class="px-2 py-1 rounded border border-slate-200 hover:bg-slate-50">{{ __('app.notify.select_none') }}</button>
                    </div>
                </div>

                @if ($bAudience->isEmpty())
                    <p class="text-sm text-slate-400 border border-dashed border-slate-200 rounded-lg px-3 py-4 text-center">
                        {{ __('app.notify.no_patients_today') }}
                    </p>
                @else
                    <input type="hidden" name="audience" value="selected">
                    <p class="text-[11px] text-slate-400 mb-2">{{ __('app.notify.recipients_hint') }}</p>

                    <input type="search" id="broadcast-filter" autocomplete="off"
                           oninput="filterBroadcastRecipients(this.value)"
                           placeholder="{{ __('app.notify.search_patients') }}"
                           class="w-full border rounded-lg px-3 py-1.5 text-sm mb-2">

                    <ul id="broadcast-recipients"
                        class="max-h-48 overflow-y-auto rounded-lg border border-slate-200 divide-y divide-slate-100">
                        @foreach ($bAudience as $bAppt)
                            @php $bClient = $bAppt->client; @endphp
                            <li data-broadcast-row
                                data-search="{{ Str::lower($bClient->name.' '.$bClient->phone_number) }}">
                                <label class="flex items-center gap-2 px-3 py-2 cursor-pointer hover:bg-slate-50">
                                    <input type="checkbox" name="client_ids[]" value="{{ $bClient->id }}" checked
                                           onchange="updateBroadcastCount()"
                                           class="broadcast-recipient h-4 w-4 rounded border-slate-300">
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm text-slate-800 truncate">{{ $bClient->name }}</span>
                                        <span class="block text-[11px] text-slate-400 truncate">
                                            @if ($bAppt->queue_number)#{{ $bAppt->queue_number }} · @endif
                                            {{ $bAppt->scheduled_at?->format('H:i') }}
                                            @if ($bClient->phone_number) · {{ $bClient->phone_number }}@endif
                                        </span>
                                    </span>
                                </label>
                            </li>
                        @endforeach
                        <li id="broadcast-no-match" class="hidden px-3 py-3 text-sm text-slate-400">
                            {{ __('app.notify.no_patient_matches') }}
                        </li>
                    </ul>
                @endif

                @error('client_ids')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Free text (also used to preview / edit a chosen template). --}}
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('app.notify.custom_label') }}</label>
                <textarea name="message" id="broadcast-message" rows="3"
                          oninput="document.getElementById('broadcast-template').value=''; clearBroadcastChips();"
                          placeholder="{{ __('app.notify.custom_placeholder') }}"
                          class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('message') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-1">
                <button type="button" onclick="toggle('broadcast-modal')"
                        class="text-sm text-slate-500 px-3 py-2">{{ __('app.notify.cancel') }}</button>
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                    📨 {{ __('app.notify.send') }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function clearBroadcastChips() {
        document.querySelectorAll('[data-broadcast-chip]').forEach(function (c) {
            c.classList.remove('bg-indigo-100', 'border-indigo-400', 'text-indigo-800');
        });
    }
    function selectBroadcastTemplate(chip, key, text) {
        document.getElementById('broadcast-template').value = key;
        document.getElementById('broadcast-message').value = text;
        clearBroadcastChips();
        chip.classList.add('bg-indigo-100', 'border-indigo-400', 'text-indigo-800');
    }

    function broadcastBoxes() {
        return Array.prototype.slice.call(document.querySelectorAll('.broadcast-recipient'));
    }

    /** Only ever tick rows the filter is currently showing. */
    function setBroadcastRecipients(checked) {
        broadcastBoxes().forEach(function (box) {
            if (box.closest('[data-broadcast-row]').classList.contains('hidden')) return;
            box.checked = checked;
        });
        updateBroadcastCount();
    }

    function updateBroadcastCount() {
        var boxes = broadcastBoxes();
        var label = document.getElementById('broadcast-count');
        if (!label || !boxes.length) return;
        var selected = boxes.filter(function (b) { return b.checked; }).length;
        label.textContent = @json(__('app.notify.selected_count', ['selected' => ':s', 'total' => ':t']))
            .replace(':s', selected).replace(':t', boxes.length);
    }

    function filterBroadcastRecipients(term) {
        var needle = (term || '').trim().toLowerCase();
        var shown = 0;
        document.querySelectorAll('[data-broadcast-row]').forEach(function (row) {
            var hit = !needle || row.dataset.search.indexOf(needle) !== -1;
            row.classList.toggle('hidden', !hit);
            if (hit) shown++;
        });
        var empty = document.getElementById('broadcast-no-match');
        if (empty) empty.classList.toggle('hidden', shown > 0);
    }

    updateBroadcastCount();

    @if ($errors->has('message') || $errors->has('client_ids'))
        document.getElementById('broadcast-modal')?.classList.remove('hidden');
    @endif
</script>
@endpush
