@props(['title' => null, 'description' => null])

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    @if($title)
        <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/50">
            <h3 class="text-base font-bold text-slate-800">{{ $title }}</h3>
            @if($description)
                <p class="text-sm text-slate-500 mt-0.5">{{ $description }}</p>
            @endif
        </div>
    @endif
    <div class="p-6">
        {{ $slot }}
    </div>
</div>
