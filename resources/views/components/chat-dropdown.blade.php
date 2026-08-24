@props(['side' => 'client'])

@php
    // Both sides render the same widget; only the endpoints differ. The server
    // already mirrors "mine" / the other party's name per side, so nothing here
    // needs to know who is logged in.
    //
    // Colours are literal Tailwind shades, never the `primary` theme token: this
    // component also renders in the practice workspace, whose layout ships no
    // tailwind.config, so `bg-primary` there would silently render as nothing.
    $prefix = $side === 'doctor' ? 'doctor' : 'client';
    $threadsUrl = route($prefix . '.chat.threads');
    $chatBase = url($prefix . '/chat');
    $inboxUrl = route($prefix . '.chat.index');
    $openThreadId = request('thread');

    $labelExpand = __('app.chat.expand');
    $labelMinimize = __('app.chat.minimize');
@endphp

<div class="relative"
     x-data="chatWidget({
        threadsUrl: '{{ $threadsUrl }}',
        chatBase: '{{ $chatBase }}',
        openThreadId: {{ $openThreadId ? (int) $openThreadId : 'null' }},
     })"
     x-init="init()">

    <!-- Chat Icon Button -->
    <button @click="open = !open; if (open) loadThreads()"
            class="relative p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors"
            aria-label="{{ __('app.chat.title') }}">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 4v-4z"/>
        </svg>
        <span x-show="unreadTotal > 0"
              x-text="unreadTotal > 99 ? '99+' : unreadTotal"
              class="absolute top-0 ltr:right-0 rtl:left-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform ltr:translate-x-1/2 rtl:-translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full"
              style="display: none;"></span>
    </button>

    <!-- Threads Dropdown -->
    <div x-show="open"
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="absolute ltr:right-0 rtl:left-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-slate-200 z-50 max-h-96 overflow-hidden flex flex-col ltr:origin-top-right rtl:origin-top-left"
         style="display: none;">

        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-900">{{ __('app.chat.title') }}</h3>
            <span class="text-xs text-slate-400" x-show="threads.length > 0" x-text="threads.length"></span>
        </div>

        <div class="overflow-y-auto flex-1">
            <template x-if="loadingThreads && threads.length === 0">
                <div class="p-4 text-center text-slate-500">
                    <svg class="animate-spin h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>
            </template>

            <template x-if="!loadingThreads && threads.length === 0">
                <div class="p-4 text-center text-slate-500 text-sm">{{ __('app.chat.empty') }}</div>
            </template>

            <template x-for="thread in threads" :key="thread.id">
                <div class="px-4 py-3 border-b border-slate-100 hover:bg-slate-50 cursor-pointer transition-colors"
                     :class="{ 'bg-teal-50': thread.unread > 0 }"
                     @click="openThread(thread.id)">
                    <div class="flex items-start gap-3">
                        <img :src="thread.avatar" :alt="thread.name" class="w-9 h-9 rounded-full object-cover shrink-0">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-medium text-slate-900 truncate" x-text="thread.name"></p>
                                <span x-show="thread.unread > 0" x-text="thread.unread"
                                      class="shrink-0 inline-flex items-center justify-center min-w-[18px] px-1.5 text-[11px] font-bold text-white bg-red-500 rounded-full"
                                      style="display: none;"></span>
                            </div>
                            <p class="text-xs text-slate-600 mt-1 truncate">
                                <span x-show="thread.preview_mine" class="text-slate-400" style="display:none;">{{ __('app.chat.you') }}: </span>
                                <span x-text="thread.preview || '{{ __('app.chat.no_messages') }}'"></span>
                            </p>
                            <p class="text-[11px] text-slate-400 mt-1" x-text="thread.last_at"></p>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="px-4 py-2 border-t border-slate-200 text-center">
            <a href="{{ $inboxUrl }}" class="text-xs text-teal-600 hover:text-teal-700 font-medium">{{ __('app.chat.view_all') }}</a>
        </div>
    </div>

    {{-- Docked chat windows, Messenger style: pinned to the bottom inline-end
         corner, newest nearest that edge. The row is laid out with a plain
         direction-aware flex and anchored with `end-4`, so RTL docks to the
         bottom-left without a single mirrored utility. --}}
    <template x-teleport="body">
        <div class="fixed bottom-0 end-4 z-[100] flex items-end gap-3 pointer-events-none"
             dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

            <template x-for="w in windows" :key="w.id">
                <div class="pointer-events-auto bg-white rounded-t-xl shadow-2xl border border-slate-200 border-b-0 flex flex-col w-[22rem] max-w-[calc(100vw-2rem)] overflow-hidden"
                     :style="w.minimized ? '' : 'height: min(28rem, calc(100vh - 6rem));'">

                    <!-- Header: clicking it folds the window down to just this bar -->
                    <div class="flex items-center gap-2 px-3 py-2 bg-slate-50 border-b border-slate-100 shrink-0 cursor-pointer select-none"
                         @click="toggleMinimize(w.id)">
                        <div class="relative shrink-0">
                            <img :src="w.thread?.avatar" alt="" class="w-8 h-8 rounded-full object-cover">
                            <span x-show="w.minimized && w.thread?.unread > 0"
                                  x-text="w.thread?.unread"
                                  class="absolute -top-1 ltr:-right-1 rtl:-left-1 inline-flex items-center justify-center min-w-[16px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full"
                                  style="display: none;"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-800 truncate" x-text="w.thread?.name"></p>
                            <p class="text-[11px] leading-tight" x-show="w.thread?.window_ends" style="display:none;">
                                <span x-show="w.thread?.window_open" class="text-slate-400">
                                    {{ __('app.chat.window_until') }} <span x-text="w.thread?.window_ends"></span>
                                </span>
                                <span x-show="!w.thread?.window_open" class="text-amber-600" style="display:none;">{{ __('app.chat.window_closed_short') }}</span>
                            </p>
                        </div>
                        <button type="button" @click.stop="toggleMinimize(w.id)"
                                class="shrink-0 w-7 h-7 flex items-center justify-center rounded-full text-slate-500 hover:bg-slate-200 transition-colors"
                                :aria-label="w.minimized ? @js($labelExpand) : @js($labelMinimize)">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path x-show="!w.minimized" stroke-linecap="round" d="M5 12h14"/>
                                <path x-show="w.minimized" stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                            </svg>
                        </button>
                        <button type="button" @click.stop="closeWindow(w.id)"
                                class="shrink-0 w-7 h-7 flex items-center justify-center rounded-full text-slate-500 hover:bg-slate-200 transition-colors"
                                aria-label="{{ __('app.chat.close') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Messages -->
                    <div x-show="!w.minimized"
                         :id="'chat-scroll-' + w.id"
                         class="flex-1 overflow-y-auto px-3 py-3 bg-slate-50 flex flex-col gap-2">
                        <template x-if="w.loading && w.messages.length === 0">
                            <p class="text-center text-sm text-slate-400 my-auto">{{ __('app.chat.loading') }}</p>
                        </template>
                        <template x-if="!w.loading && w.messages.length === 0">
                            <p class="text-center text-sm text-slate-400 my-auto">{{ __('app.chat.no_messages') }}</p>
                        </template>

                        <template x-for="(m, i) in w.messages" :key="m.id">
                            <div class="flex flex-col" :class="m.mine ? 'items-end' : 'items-start'">
                                <template x-if="i === 0 || w.messages[i - 1].date !== m.date">
                                    <span class="self-center my-1 px-2 py-0.5 text-[11px] text-slate-500 bg-white border border-slate-200 rounded-full"
                                          x-text="m.date"></span>
                                </template>
                                <div class="max-w-[85%] px-3 py-2 rounded-2xl text-sm whitespace-pre-wrap break-words"
                                     :class="m.mine ? 'bg-teal-600 text-white rounded-br-sm' : 'bg-white text-slate-800 border border-slate-200 rounded-bl-sm'">
                                    <span x-text="m.body"></span>
                                </div>
                                <span class="text-[10px] text-slate-400 mt-0.5" x-text="m.at"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Composer -->
                    <form x-show="!w.minimized" @submit.prevent="send(w.id)" class="p-2 border-t border-slate-100 shrink-0 bg-white">
                        <template x-if="w.thread && !w.thread.can_send">
                            <p class="text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-2 py-2 text-center">
                                {{ __('app.chat.window_closed') }}
                            </p>
                        </template>
                        <template x-if="!w.thread || w.thread.can_send">
                            <div class="flex items-end gap-2">
                                <textarea x-model="w.draft" rows="1" maxlength="2000"
                                          @keydown.enter.exact.prevent="send(w.id)"
                                          @input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 96) + 'px'"
                                          placeholder="{{ __('app.chat.placeholder') }}"
                                          class="flex-1 resize-none rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500"></textarea>
                                <button type="submit" :disabled="w.sending || w.draft.trim() === ''"
                                        class="shrink-0 h-9 w-9 flex items-center justify-center rounded-full bg-teal-600 text-white disabled:opacity-40 hover:bg-teal-700 transition-colors">
                                    <svg class="w-4 h-4 rtl:-scale-x-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </form>
                </div>
            </template>
        </div>
    </template>
</div>

@once
@push('scripts')
<script>
/**
 * Shared doctor/patient chat widget, docked bottom-end like Messenger.
 *
 * Threads refresh on a slow poll so the badge stays live; every open, expanded
 * window polls its own messages faster so a reply lands without a page reload.
 * Minimised windows stop polling — they only carry an unread badge.
 *
 * X-Requested-With matters: without it Laravel treats these polls as ordinary
 * page visits and stores them as the session's previous URL, so a later
 * redirect()->back() (the language switch, a failed form) lands the user on raw
 * JSON. That is invisible in a browser, which still sends a Referer, but the
 * mobile WebView does not.
 */
const CHAT_POLL_HEADERS = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

function chatWidget(config) {
    return {
        open: false,
        threads: [],
        unreadTotal: 0,
        loadingThreads: false,
        windows: [],
        threadTimer: null,
        windowTimer: null,

        /** How many chats may sit docked at once (one at a time on phones). */
        get maxWindows() {
            return window.matchMedia('(min-width: 768px)').matches ? 3 : 1;
        },

        init() {
            this.loadThreads();
            this.threadTimer = setInterval(() => this.loadThreads(), 30000);
            this.windowTimer = setInterval(() => this.refreshWindows(), 10000);

            if (config.openThreadId) {
                this.openThread(config.openThreadId);
            }
            window.addEventListener('chat-open-thread', (e) => this.openThread(e.detail.id));
        },

        csrf() {
            return document.querySelector('meta[name=csrf-token]')?.content ?? '';
        },

        async loadThreads() {
            this.loadingThreads = true;
            try {
                const res = await fetch(config.threadsUrl, { headers: CHAT_POLL_HEADERS });
                const data = await res.json();
                this.threads = data.threads || [];
                this.unreadTotal = data.unread_total || 0;

                // Keep minimised windows' headers (name, unread badge) current.
                this.windows.forEach(w => {
                    const fresh = this.threads.find(t => t.id === w.id);
                    if (fresh) w.thread = fresh;
                });
            } catch (e) {
                console.error('chat: loading threads failed', e);
            } finally {
                this.loadingThreads = false;
            }
        },

        /**
         * The docked window for a thread, looked up by id every time.
         *
         * Always go through this rather than keeping hold of a window object:
         * this.windows hands out Alpine's reactive proxies, so writing to the
         * raw object we pushed would update the data without redrawing.
         */
        windowFor(id) {
            return this.windows.find(w => w.id === id);
        },

        /** Open a chat, or bring an already-docked one back up. */
        openThread(id) {
            this.open = false;

            const existing = this.windowFor(id);
            if (existing) {
                existing.minimized = false;
                this.loadMessages(id);
                return;
            }

            // Oldest window gives up its slot once the dock is full.
            while (this.windows.length >= this.maxWindows) {
                this.windows.shift();
            }

            this.windows.push({
                id,
                thread: this.threads.find(t => t.id === id) ?? null,
                messages: [],
                draft: '',
                minimized: false,
                sending: false,
                loading: true,
            });
            this.loadMessages(id);
        },

        closeWindow(id) {
            this.windows = this.windows.filter(w => w.id !== id);
            this.loadThreads();
        },

        toggleMinimize(id) {
            const win = this.windowFor(id);
            if (!win) return;

            win.minimized = !win.minimized;
            if (win.minimized) {
                this.loadThreads();
            } else {
                this.loadMessages(id);
            }
        },

        /** Poll every expanded window; a folded one costs nothing. */
        refreshWindows() {
            this.windows.filter(w => !w.minimized).forEach(w => this.loadMessages(w.id, true));
        },

        async loadMessages(id, quiet = false) {
            const win = this.windowFor(id);
            if (!win) return;
            if (!quiet) win.loading = true;

            try {
                const res = await fetch(`${config.chatBase}/${id}/messages`, { headers: CHAT_POLL_HEADERS });
                if (!res.ok) throw new Error(res.status);
                const data = await res.json();

                // Re-resolve: the window may have been closed while we waited.
                const live = this.windowFor(id);
                if (!live) return;

                const grew = data.messages.length !== live.messages.length;
                live.messages = data.messages;
                live.thread = data.thread;
                if (grew) this.scrollToEnd(id);
            } catch (e) {
                console.error('chat: loading messages failed', e);
            } finally {
                const done = this.windowFor(id);
                if (done) done.loading = false;
            }
        },

        async send(id) {
            const win = this.windowFor(id);
            if (!win) return;

            const body = win.draft.trim();
            if (!body || win.sending) return;
            win.sending = true;

            try {
                const res = await fetch(`${config.chatBase}/${id}/messages`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrf(),
                    },
                    body: JSON.stringify({ body }),
                });
                if (!res.ok) throw new Error(res.status);
                const data = await res.json();

                const live = this.windowFor(id);
                if (live) {
                    live.messages.push(data.message);
                    live.draft = '';
                    this.scrollToEnd(id);
                }
                this.loadThreads();
            } catch (e) {
                console.error('chat: sending failed', e);
            } finally {
                const done = this.windowFor(id);
                if (done) done.sending = false;
            }
        },

        scrollToEnd(id) {
            this.$nextTick(() => {
                const el = document.getElementById('chat-scroll-' + id);
                if (el) el.scrollTop = el.scrollHeight;
            });
        },
    };
}
</script>
@endpush
@endonce
