@extends('admin.layouts.admin')

@php $l = app()->getLocale() === 'ar'; @endphp

@section('title', 'Providers Network')
@section('page-title', $l ? 'شبكة مقدمي الخدمة' : 'Providers Network')
@section('page-description', $l ? 'ابحث في الدليل الخارجي واستورد الأطباء والعيادات' : 'Search the external directory and import doctors & clinics')

@section('content')

@if (session('status'))
    <div class="mb-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">
        {{ session('status') }}
    </div>
@endif
@if ($errors->any())
    <div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-red-700 text-sm">
        {{ $errors->first() }}
    </div>
@endif

{{-- Search form --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 sm:p-6 mb-5">
    <form method="GET" action="{{ route('admin.providers-network.index') }}"
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">{{ $l ? 'المحافظة' : 'Governorate' }}</label>
            <select name="governorate" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">
                <option value="">{{ $l ? 'الكل' : 'All' }}</option>
                @foreach ($governorates as $g)
                    <option value="{{ $g }}" @selected(($filters['governorate'] ?? '') === $g)>{{ $g }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">{{ $l ? 'نوع مقدم الخدمة' : 'Provider type' }}</label>
            <select name="provider_type" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">
                <option value="">{{ $l ? 'الكل' : 'All' }}</option>
                @foreach ($providerTypes as $t)
                    <option value="{{ $t }}" @selected(($filters['provider_type'] ?? '') === $t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">{{ $l ? 'المدينة' : 'City' }}</label>
            <input type="text" name="city" value="{{ $filters['city'] ?? '' }}"
                   placeholder="{{ $l ? 'مثال: قليوب' : 'e.g. Qalyub' }}"
                   class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">{{ $l ? 'بحث بالاسم' : 'Search by name' }}</label>
            <input type="text" name="search_query" value="{{ $filters['search_query'] ?? '' }}"
                   placeholder="{{ $l ? 'اسم الطبيب أو المركز' : 'Doctor / centre name' }}"
                   class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">
        </div>
        <div class="sm:col-span-2 lg:col-span-4 flex gap-2">
            <button type="submit" class="px-5 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:opacity-90">
                {{ $l ? 'بحث' : 'Search' }}
            </button>
            <a href="{{ route('admin.providers-network.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm">
                {{ $l ? 'مسح' : 'Clear' }}
            </a>
        </div>
    </form>
</div>

@if ($searched)
    @if ($error === 'no_filter' || $error)
        <div class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-amber-800 text-sm">
            {{ $l ? 'اختر محافظة أو نوعًا أو اكتب اسمًا للبحث.' : 'Choose a governorate, a type, or type a name to search.' }}
        </div>
    @elseif (empty($results))
        <div class="rounded-xl bg-white border border-slate-100 shadow-sm p-10 text-center text-slate-500">
            {{ $l ? 'لا توجد نتائج مطابقة.' : 'No matching providers.' }}
        </div>
    @else
        <form method="POST" action="{{ route('admin.providers-network.import') }}"
              onsubmit="return confirmImport(event)">
            @csrf
            {{-- Carry the same filters so the server re-runs the exact search on import. --}}
            @foreach (['governorate', 'city', 'provider_type', 'search_query', 'network_tier'] as $f)
                <input type="hidden" name="{{ $f }}" value="{{ $filters[$f] ?? '' }}">
            @endforeach

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    <div class="text-sm text-slate-600">
                        {{ $l ? 'عدد النتائج' : 'Results' }}: <strong>{{ $count }}</strong>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" name="scope" value="selected"
                                class="px-4 py-2 bg-white border border-primary text-primary rounded-lg text-sm font-medium hover:bg-primary/5">
                            {{ $l ? 'استيراد المحدد' : 'Import selected' }}
                        </button>
                        <button type="submit" name="scope" value="all"
                                class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:opacity-90">
                            {{ $l ? 'استيراد الكل' : 'Import all' }} ({{ $count }})
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500 text-xs">
                            <tr>
                                <th class="p-3 text-start"><input type="checkbox" onclick="toggleAll(this)"></th>
                                <th class="p-3 text-start">{{ $l ? 'الاسم' : 'Name' }}</th>
                                <th class="p-3 text-start">{{ $l ? 'التخصص' : 'Specialty' }}</th>
                                <th class="p-3 text-start">{{ $l ? 'النوع' : 'Type' }}</th>
                                <th class="p-3 text-start">{{ $l ? 'المدينة' : 'City' }}</th>
                                <th class="p-3 text-start">{{ $l ? 'الهاتف' : 'Phone' }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($results as $r)
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3">
                                        <input type="checkbox" name="provider_ids[]"
                                               value="{{ $r['provider_id'] ?? $r['Provider_ID'] ?? '' }}">
                                    </td>
                                    <td class="p-3 font-medium text-slate-800">{{ $r['provider_name_ar'] ?? $r['provider_name'] ?? '—' }}</td>
                                    <td class="p-3 text-slate-600">{{ $r['provider_specialty'] ?? '—' }}</td>
                                    <td class="p-3 text-slate-600">{{ $r['provider_type'] ?? '—' }}</td>
                                    <td class="p-3 text-slate-600">{{ $r['city'] ?? '—' }}</td>
                                    <td class="p-3 text-slate-600 whitespace-nowrap" dir="ltr">{{ $r['phone'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    @endif
@else
    <div class="rounded-xl bg-white border border-slate-100 shadow-sm p-10 text-center text-slate-500">
        {{ $l ? 'اختر فلترًا وابدأ البحث لعرض مقدمي الخدمة القابلين للاستيراد.' : 'Choose a filter and search to list importable providers.' }}
    </div>
@endif

@push('scripts')
<script>
    function toggleAll(box) {
        document.querySelectorAll('input[name="provider_ids[]"]').forEach(cb => { cb.checked = box.checked; });
    }
    function confirmImport(e) {
        const scope = e.submitter && e.submitter.value;
        const total = {{ (int) ($count ?? 0) }};
        const selected = document.querySelectorAll('input[name="provider_ids[]"]:checked').length;
        const n = scope === 'all' ? total : selected;
        if (scope !== 'all' && selected === 0) {
            alert(@json($l ? 'حدد مقدمي خدمة أولًا.' : 'Select some providers first.'));
            return false;
        }
        return confirm(@json($l ? 'استيراد ' : 'Import ') + n + @json($l ? ' مقدم خدمة كأطباء وعيادات؟' : ' providers as doctors & clinics?'));
    }
</script>
@endpush
@endsection
