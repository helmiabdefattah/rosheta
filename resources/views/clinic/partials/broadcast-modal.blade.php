{{-- Broadcast-a-message modal. Included on the doctor + assistant dashboards.
     Trigger it from anywhere with onclick="toggle('broadcast-modal')". --}}
@php $bLocale = app()->getLocale() === 'ar' ? 'ar' : 'en'; @endphp

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
    @if ($errors->has('message'))
        document.getElementById('broadcast-modal')?.classList.remove('hidden');
    @endif
</script>
@endpush
