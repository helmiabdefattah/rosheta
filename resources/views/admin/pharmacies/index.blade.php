@extends('admin.layouts.admin')

@php
    $l = app()->getLocale() === 'ar';
@endphp

@section('title', $l ? 'الصيدليات' : 'Pharmacies')
@section('page-title', $l ? 'الصيدليات' : 'Pharmacies')

@section('header-actions')
    <x-admin.ui.button href="{{ route('admin.pharmacies.create') }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'>
        {{ $l ? 'إضافة صيدلية' : 'Add Pharmacy' }}
    </x-admin.ui.button>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 max-lg:overflow-visible lg:overflow-hidden hover:shadow-md transition-shadow duration-300">
    <div class="p-4 sm:p-6 border-b border-slate-100">
        <form method="GET" action="{{ route('admin.pharmacies.index') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end sm:justify-between">
            <div class="flex-1 w-full min-w-0">
                <label for="pharmacies-search" class="block text-xs font-medium text-slate-500 mb-1">{{ $l ? 'بحث' : 'Search' }}</label>
                <input type="search" name="search" id="pharmacies-search" value="{{ request('search') }}"
                       placeholder="{{ $l ? 'المعرف، الاسم، الهاتف، البريد، المنطقة…' : 'ID, name, phone, email, area…' }}"
                       class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary">
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:opacity-90">{{ $l ? 'بحث' : 'Search' }}</button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.pharmacies.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm text-center">{{ $l ? 'مسح' : 'Clear' }}</a>
                @endif
            </div>
        </form>
    </div>

    @if($pharmacies->count() === 0)
        <div class="p-10 text-center text-slate-500">{{ $l ? 'لا توجد صيدليات.' : 'No pharmacies found.' }}</div>
    @else
        <div class="lg:hidden space-y-3 p-4">
            @foreach($pharmacies as $pharmacy)
                @php
                    $areaLabel = '—';
                    if ($pharmacy->area) {
                        $areaLabel = $l
                            ? ($pharmacy->area->name_ar ?? $pharmacy->area->name ?? '—')
                            : ($pharmacy->area->name ?? $pharmacy->area->name_ar ?? '—');
                    }
                @endphp
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="mb-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">#{{ $pharmacy->id }}</p>
                        <p class="text-lg font-bold text-slate-900">{{ $pharmacy->name }}</p>
                    </div>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'الهاتف' : 'Phone' }}</dt>
                            <dd class="text-slate-800 text-end break-all">{{ $pharmacy->phone ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'المنطقة' : 'Area' }}</dt>
                            <dd class="text-slate-800 text-end">{{ $areaLabel }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 pt-1">
                            <dt class="text-slate-500">{{ $l ? 'الحالة' : 'Status' }}</dt>
                            <dd>
                                @if($pharmacy->is_active)
                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ $l ? 'نشط' : 'Active' }}</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">{{ $l ? 'غير نشط' : 'Inactive' }}</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        @include('admin.pharmacies.actions', ['pharmacy' => $pharmacy])
                    </div>
                </div>
            @endforeach
        </div>

        <div class="hidden lg:block overflow-x-auto p-4 sm:p-6 pt-0">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الاسم' : 'Name' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الهاتف' : 'Phone' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'المنطقة' : 'Area' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الحالة' : 'Status' }}</th>
                        <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'إجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($pharmacies as $pharmacy)
                        @php
                            $areaLabel = '—';
                            if ($pharmacy->area) {
                                $areaLabel = $l
                                    ? ($pharmacy->area->name_ar ?? $pharmacy->area->name ?? '—')
                                    : ($pharmacy->area->name ?? $pharmacy->area->name_ar ?? '—');
                            }
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm font-medium text-slate-800">#{{ $pharmacy->id }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $pharmacy->name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $pharmacy->phone ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $areaLabel }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if($pharmacy->is_active)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ $l ? 'نشط' : 'Active' }}</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">{{ $l ? 'غير نشط' : 'Inactive' }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-end">@include('admin.pharmacies.actions', ['pharmacy' => $pharmacy])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 sm:px-6 pb-6 border-t border-slate-100 pt-4">{{ $pharmacies->links() }}</div>
    @endif
</div>
@endsection
