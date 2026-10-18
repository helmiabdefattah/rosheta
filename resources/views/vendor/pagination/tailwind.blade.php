{{--
    Shared pagination view, published to override Laravel's default.
    Every `->links()` call across the admin and the role dashboards renders
    this, so pagination looks the same everywhere. It uses standard `teal-*`
    utilities (never the `primary` colour, which each layout defines
    differently — the pharmacy layout, for instance, has no `primary` at all)
    so the teal accent resolves identically under the Tailwind CDN in all
    layouts, and it is right-to-left aware for the Arabic locale.
--}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col sm:flex-row items-center justify-between gap-4">
        {{-- Results summary (locale-aware wording) --}}
        @php $pgAr = app()->getLocale() === 'ar'; @endphp
        <p class="text-sm text-slate-500 order-2 sm:order-1">
            @if ($paginator->firstItem())
                {{ $pgAr ? 'عرض' : 'Showing' }}
                <span class="font-medium text-slate-700">{{ $paginator->firstItem() }}</span>
                {{ $pgAr ? 'إلى' : 'to' }}
                <span class="font-medium text-slate-700">{{ $paginator->lastItem() }}</span>
                {{ $pgAr ? 'من' : 'of' }}
                <span class="font-medium text-slate-700">{{ $paginator->total() }}</span>
                <span class="text-slate-400">{{ $pgAr ? 'نتيجة' : 'results' }}</span>
            @else
                <span class="font-medium text-slate-700">{{ $paginator->count() }}</span>
                <span class="text-slate-400">{{ $pgAr ? 'نتيجة' : 'results' }}</span>
            @endif
        </p>

        <div class="flex items-center gap-1 order-1 sm:order-2">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-slate-200 text-slate-300 cursor-not-allowed bg-white">
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-teal-50 hover:text-teal-700 hover:border-teal-200 transition-colors">
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
            @endif

            {{-- Page numbers (hidden on the smallest screens to save room) --}}
            <div class="hidden sm:flex items-center gap-1">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span aria-disabled="true" class="inline-flex items-center justify-center min-w-9 h-9 px-2 text-slate-400">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="inline-flex items-center justify-center min-w-9 h-9 px-3 rounded-lg bg-teal-600 text-white text-sm font-semibold shadow-sm">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}" class="inline-flex items-center justify-center min-w-9 h-9 px-3 rounded-lg border border-slate-200 bg-white text-slate-600 text-sm font-medium hover:bg-teal-50 hover:text-teal-700 hover:border-teal-200 transition-colors">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Compact "page X of Y" for very small screens --}}
            <span class="inline-flex sm:hidden items-center px-3 h-9 text-sm text-slate-500">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-teal-50 hover:text-teal-700 hover:border-teal-200 transition-colors">
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span aria-disabled="true" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-slate-200 text-slate-300 cursor-not-allowed bg-white">
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif
        </div>
    </nav>
@endif
