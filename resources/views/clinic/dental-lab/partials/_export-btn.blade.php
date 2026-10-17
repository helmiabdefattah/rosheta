{{--
    A single export submit button, coloured by format: red for PDF, green for
    Excel, each with its own icon. Used inside a GET <form> that carries the
    filters; `formaction` points it at the right export route.

    Expects: $action (route url), $format ('pdf'|'excel'), $label (string).
--}}
@php $isPdf = ($format ?? 'pdf') === 'pdf'; @endphp
<button type="submit" formaction="{{ $action }}" name="format" value="{{ $format }}"
        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-semibold text-white shadow-sm
               {{ $isPdf ? 'bg-red-600 hover:bg-red-700' : 'bg-emerald-600 hover:bg-emerald-700' }}">
    @if ($isPdf)
        {{-- document icon --}}
        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm0 2 4 4h-4V4z"/>
        </svg>
    @else
        {{-- spreadsheet / grid icon --}}
        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M4 3h16a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm1 4v4h6V7H5zm8 0v4h6V7h-6zM5 13v4h6v-4H5zm8 0v4h6v-4h-6z"/>
        </svg>
    @endif
    <span>{{ $label }}</span>
</button>
