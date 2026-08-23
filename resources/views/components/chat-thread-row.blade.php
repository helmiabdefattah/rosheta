@props(['thread'])

{{-- One inbox row. Clicking it hands the thread id to the chat widget in the
     header, which owns the modal — so the page never duplicates that markup. --}}
<button type="button"
        onclick="window.dispatchEvent(new CustomEvent('chat-open-thread', { detail: { id: {{ $thread['id'] }} } }))"
        class="w-full text-start px-6 py-4 border-b border-gray-50 last:border-0 hover:bg-slate-50 transition-colors flex items-center gap-4 {{ $thread['unread'] ? 'bg-teal-50/60' : '' }}">

    <img src="{{ $thread['avatar'] }}" alt="" class="w-11 h-11 rounded-full object-cover shrink-0">

    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
            <span class="font-semibold text-slate-800 truncate">{{ $thread['name'] }}</span>
            @if($thread['unread'])
                <span class="inline-flex items-center justify-center min-w-[20px] px-1.5 text-xs font-bold text-white bg-red-500 rounded-full">{{ $thread['unread'] }}</span>
            @endif
        </div>
        <p class="text-sm text-slate-500 truncate mt-0.5">
            @if($thread['preview'])
                @if($thread['preview_mine'])<span class="text-slate-400">{{ __('app.chat.you') }}: </span>@endif
                {{ $thread['preview'] }}
            @else
                <span class="text-slate-400">{{ __('app.chat.no_messages') }}</span>
            @endif
        </p>
    </div>

    <div class="shrink-0 text-end">
        <p class="text-xs text-slate-400">{{ $thread['last_at'] }}</p>
        @if($thread['window_open'])
            <p class="text-[11px] text-emerald-600 mt-1">{{ __('app.chat.window_until') }} {{ $thread['window_ends'] }}</p>
        @else
            <p class="text-[11px] text-amber-600 mt-1">{{ __('app.chat.window_closed_short') }}</p>
        @endif
    </div>
</button>
