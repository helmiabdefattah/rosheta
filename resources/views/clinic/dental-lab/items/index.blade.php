@extends('clinic.layouts.app')

@php use App\Enums\DentalItemLevel; $inp = 'w-full border rounded-lg px-3 py-2 text-sm'; @endphp

@section('content')
    @include('clinic.dental-lab.partials._tabs', ['active' => 'catalog'])

    {{-- Suggested names from the shared catalog. Picking an English name fills in
         its Arabic translation + unit below. --}}
    @php
        $flatShared = $shared->flatten();
        $catalogMap = $flatShared->mapWithKeys(fn ($it) => [mb_strtolower($it->name) => ['ar' => $it->name_ar, 'unit' => $it->default_unit]]);
        $catalogMapAr = $flatShared->filter(fn ($it) => $it->name_ar)->mapWithKeys(fn ($it) => [mb_strtolower($it->name_ar) => ['en' => $it->name, 'unit' => $it->default_unit]]);
    @endphp
    <datalist id="dental-item-names">
        @foreach($shared->flatten() as $it)
            <option value="{{ $it->name }}">{{ $it->name_ar ? $it->name.' — '.$it->name_ar : $it->name }}</option>
        @endforeach
    </datalist>
    <datalist id="dental-item-names-ar">
        @foreach($shared->flatten() as $it)
            @if($it->name_ar)<option value="{{ $it->name_ar }}"></option>@endif
        @endforeach
    </datalist>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Add custom item --}}
        <div class="lg:col-span-1">
            <div class="card p-5">
                <h2 class="font-bold text-slate-800 mb-3">{{ __('app.dental.catalog.add') }}</h2>
                <form method="POST" action="{{ route('practice.dental-lab.items.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-sm font-semibold text-slate-700">{{ __('app.dental.catalog.level') }}</label>
                        <select name="level" class="{{ $inp }}">
                            @foreach(DentalItemLevel::cases() as $lvl)
                                <option value="{{ $lvl->value }}">{{ $lvl->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">{{ __('app.dental.catalog.name_en') }}</label>
                        <input type="text" name="name" id="cat-name" value="{{ old('name') }}" required class="{{ $inp }}" list="dental-item-names" autocomplete="off">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">{{ __('app.dental.catalog.name_ar') }}</label>
                        <input type="text" name="name_ar" id="cat-name-ar" value="{{ old('name_ar') }}" class="{{ $inp }}" dir="rtl" list="dental-item-names-ar" autocomplete="off">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">{{ __('app.dental.catalog.unit') }}</label>
                        <input type="text" name="default_unit" id="cat-unit" value="{{ old('default_unit', 'piece') }}" required class="{{ $inp }}">
                    </div>
                    <button class="btn btn-primary btn-block">{{ __('app.dental.actions.add') }}</button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            {{-- Clinic custom items --}}
            <div class="card p-5">
                <h2 class="font-bold text-slate-800 mb-1">{{ __('app.dental.catalog.custom') }}</h2>
                @php $hasCustom = $custom->flatten()->isNotEmpty(); @endphp
                @if(! $hasCustom)
                    <p class="text-sm text-slate-400 py-4">{{ __('app.dental.catalog.empty_custom') }}</p>
                @else
                    @foreach(DentalItemLevel::cases() as $lvl)
                        @php $rows = $custom->get($lvl->value, collect()); @endphp
                        @if($rows->isNotEmpty())
                            <div class="mt-3">
                                <div class="text-xs font-bold uppercase text-slate-400 mb-1">{{ $lvl->label() }}</div>
                                <div class="divide-y divide-slate-100">
                                    @foreach($rows as $item)
                                        <details class="py-2">
                                            <summary class="flex items-center justify-between cursor-pointer list-none">
                                                <span class="text-sm text-slate-800">{{ $item->displayName() }}
                                                    <span class="text-xs text-slate-400">· {{ $item->default_unit }}</span>
                                                    @unless($item->is_active)<span class="text-xs text-slate-400">({{ __('app.dental.common.inactive') }})</span>@endunless
                                                </span>
                                                <span class="text-xs text-indigo-600">{{ __('app.dental.actions.edit') }}</span>
                                            </summary>
                                            <div class="mt-3">
                                                <form method="POST" action="{{ route('practice.dental-lab.items.update', $item) }}" class="grid sm:grid-cols-2 gap-2">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="level" value="{{ $item->level->value }}">
                                                    <input type="text" name="name" value="{{ $item->name }}" required class="{{ $inp }}" placeholder="EN">
                                                    <input type="text" name="name_ar" value="{{ $item->name_ar }}" class="{{ $inp }}" dir="rtl" placeholder="ع">
                                                    <input type="text" name="default_unit" value="{{ $item->default_unit }}" required class="{{ $inp }}">
                                                    <label class="flex items-center gap-2 text-sm text-slate-700">
                                                        <input type="checkbox" name="is_active" value="1" @checked($item->is_active) class="w-4 h-4 rounded"> {{ __('app.dental.common.active') }}
                                                    </label>
                                                    <div class="sm:col-span-2">
                                                        <button class="btn btn-primary btn-sm">{{ __('app.dental.actions.save') }}</button>
                                                    </div>
                                                </form>
                                                <form method="POST" action="{{ route('practice.dental-lab.items.destroy', $item) }}" class="mt-2"
                                                      onsubmit="return confirm('{{ __('app.dental.actions.confirm_delete') }}')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-danger btn-sm">{{ __('app.dental.actions.delete') }}</button>
                                                </form>
                                            </div>
                                        </details>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>

            {{-- Shared catalog (read-only) --}}
            <div class="card p-5">
                <h2 class="font-bold text-slate-800 mb-1">{{ __('app.dental.catalog.shared') }}</h2>
                <p class="text-xs text-slate-400 mb-3">{{ __('app.dental.catalog.shared_hint') }}</p>
                @foreach(DentalItemLevel::cases() as $lvl)
                    @php $rows = $shared->get($lvl->value, collect()); @endphp
                    @if($rows->isNotEmpty())
                        <div class="mt-3">
                            <div class="text-xs font-bold uppercase text-slate-400 mb-1">{{ $lvl->label() }}</div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($rows as $item)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-sm text-slate-700">
                                        {{ $item->displayName() }}
                                        <span class="text-xs text-slate-400">· {{ $item->default_unit }}</span>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        var map = @json($catalogMap);
        var mapAr = @json($catalogMapAr);
        var name = document.getElementById('cat-name');
        var nameAr = document.getElementById('cat-name-ar');
        var unit = document.getElementById('cat-unit');
        if (!name || !nameAr) return;

        // Picking a known name in either field fills its counterpart + the unit.
        name.addEventListener('input', function () {
            var hit = map[(name.value || '').trim().toLowerCase()];
            if (!hit) return;
            if (hit.ar) nameAr.value = hit.ar;
            if (unit && hit.unit) unit.value = hit.unit;
        });
        nameAr.addEventListener('input', function () {
            var hit = mapAr[(nameAr.value || '').trim().toLowerCase()];
            if (!hit) return;
            if (hit.en) name.value = hit.en;
            if (unit && hit.unit) unit.value = hit.unit;
        });
    })();
    </script>
    @endpush
@endsection
