@extends('admin.layouts.admin')

@php
    $l = app()->getLocale() === 'ar';
@endphp

@section('title', 'Areas')
@section('page-title', $l ? 'المناطق' : 'Areas')
@section('page-description', $l ? 'إدارة المناطق' : 'Manage Areas')

@section('header-actions')
    <a href="{{ route('admin.areas.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg hover:opacity-90 transition-all inline-flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        {{ $l ? 'إضافة منطقة' : 'Add Area' }}
    </a>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300 max-lg:overflow-visible lg:overflow-hidden">
    <div class="p-4 sm:p-6 border-b border-slate-100">
        <form method="GET" action="{{ route('admin.areas.index') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end sm:justify-between">
            <div class="flex-1 w-full min-w-0">
                <label for="areas-search" class="block text-xs font-medium text-slate-500 mb-1">{{ $l ? 'بحث' : 'Search' }}</label>
                <input type="search" name="search" id="areas-search" value="{{ request('search') }}"
                       placeholder="{{ $l ? 'المعرف، الاسم، المدينة، المحافظة…' : 'ID, name, city, governorate…' }}"
                       class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary">
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:opacity-90">{{ $l ? 'بحث' : 'Search' }}</button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.areas.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm text-center">{{ $l ? 'مسح' : 'Clear' }}</a>
                @endif
            </div>
        </form>
    </div>

    @if($areas->count() === 0)
        <div class="p-10 text-center text-slate-500">{{ $l ? 'لا توجد مناطق.' : 'No areas found.' }}</div>
    @else
        <div class="lg:hidden space-y-3 p-4">
            @foreach($areas as $area)
                @php
                    $city = $area->city;
                    $gov = $city?->governorate;
                    $cityLabel = $city ? ($l ? ($city->name_ar ?? $city->name) : ($city->name ?? $city->name_ar)) : '—';
                    $govLabel = $gov ? ($l ? ($gov->name_ar ?? $gov->name) : ($gov->name ?? $gov->name_ar)) : '—';
                @endphp
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">#{{ $area->id }}</p>
                            <p class="text-base font-bold text-slate-900">{{ $l ? ($area->name_ar ?? $area->name) : ($area->name ?? $area->name_ar) }}</p>
                        </div>
                        @if($area->is_active)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ $l ? 'نشط' : 'Active' }}</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">{{ $l ? 'غير نشط' : 'Inactive' }}</span>
                        @endif
                    </div>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">{{ $l ? 'المدينة' : 'City' }}</dt>
                            <dd class="text-slate-800 text-end text-xs">{{ $cityLabel }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">{{ $l ? 'المحافظة' : 'Governorate' }}</dt>
                            <dd class="text-slate-800 text-end text-xs">{{ $govLabel }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 pt-1">
                            <dt class="text-slate-500">{{ $l ? 'الصيدليات' : 'Pharmacies' }}</dt>
                            <dd class="text-slate-900 font-semibold text-end">{{ $area->pharmacies_count }}</dd>
                        </div>
                    </dl>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        @include('admin.areas.actions', ['area' => $area])
                    </div>
                </div>
            @endforeach
        </div>

        <div class="hidden lg:block overflow-x-auto p-4 sm:p-6 pt-0">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'المعرف' : 'ID' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الاسم (إنجليزي)' : 'Name (English)' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الاسم (عربي)' : 'Name (Arabic)' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'المدينة' : 'City' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'المحافظة' : 'Governorate' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'عدد الصيدليات' : 'Pharmacies' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'ترتيب' : 'Sort Order' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الحالة' : 'Status' }}</th>
                        <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'إجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($areas as $area)
                        @php
                            $city = $area->city;
                            $gov = $city?->governorate;
                            $cityLabel = $city ? ($l ? ($city->name_ar ?? $city->name) : ($city->name ?? $city->name_ar)) : '—';
                            $govLabel = $gov ? ($l ? ($gov->name_ar ?? $gov->name) : ($gov->name ?? $gov->name_ar)) : '—';
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm font-medium text-slate-800">#{{ $area->id }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $area->name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $area->name_ar }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $cityLabel }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $govLabel }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $area->pharmacies_count }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $area->sort_order ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if($area->is_active)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ $l ? 'نشط' : 'Active' }}</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">{{ $l ? 'غير نشط' : 'Inactive' }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-end">@include('admin.areas.actions', ['area' => $area])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 sm:px-6 pb-6 border-t border-slate-100 pt-4">{{ $areas->links() }}</div>
    @endif
</div>
@endsection
