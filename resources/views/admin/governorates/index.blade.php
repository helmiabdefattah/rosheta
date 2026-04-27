@extends('admin.layouts.admin')

@php
    $l = app()->getLocale() === 'ar';
@endphp

@section('title', 'Governorates')
@section('page-title', $l ? 'المحافظات' : 'Governorates')
@section('page-description', $l ? 'إدارة المحافظات' : 'Manage Governorates')

@section('header-actions')
    <x-admin.ui.button href="{{ route('admin.governorates.create') }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'>
        {{ $l ? 'إضافة محافظة' : 'Add Governorate' }}
    </x-admin.ui.button>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300 max-lg:overflow-visible lg:overflow-hidden">
    <div class="p-4 sm:p-6 border-b border-slate-100">
        <form method="GET" action="{{ route('admin.governorates.index') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end sm:justify-between">
            <div class="flex-1 w-full min-w-0">
                <label for="governorates-search" class="block text-xs font-medium text-slate-500 mb-1">{{ $l ? 'بحث' : 'Search' }}</label>
                <input type="search" name="search" id="governorates-search" value="{{ request('search') }}"
                       placeholder="{{ $l ? 'المعرف، الاسم، الترتيب…' : 'ID, name, sort order…' }}"
                       class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary">
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:opacity-90">{{ $l ? 'بحث' : 'Search' }}</button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.governorates.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm text-center">{{ $l ? 'مسح' : 'Clear' }}</a>
                @endif
            </div>
        </form>
    </div>

    @if($governorates->count() === 0)
        <div class="p-10 text-center text-slate-500">{{ $l ? 'لا توجد محافظات.' : 'No governorates found.' }}</div>
    @else
        <div class="lg:hidden space-y-3 p-4">
            @foreach($governorates as $governorate)
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">#{{ $governorate->id }}</p>
                            <p class="text-base font-bold text-slate-900">{{ $l ? ($governorate->name_ar ?? $governorate->name) : ($governorate->name ?? $governorate->name_ar) }}</p>
                        </div>
                        @if($governorate->is_active)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ $l ? 'نشط' : 'Active' }}</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">{{ $l ? 'غير نشط' : 'Inactive' }}</span>
                        @endif
                    </div>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">{{ $l ? 'الاسم (إنجليزي)' : 'Name (EN)' }}</dt>
                            <dd class="text-slate-800 text-end">{{ $governorate->name }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">{{ $l ? 'الاسم (عربي)' : 'Name (AR)' }}</dt>
                            <dd class="text-slate-800 text-end">{{ $governorate->name_ar }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 pt-1">
                            <dt class="text-slate-500">{{ $l ? 'عدد المدن' : 'Cities' }}</dt>
                            <dd class="text-slate-900 font-semibold text-end">{{ $governorate->cities_count }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">{{ $l ? 'ترتيب' : 'Sort' }}</dt>
                            <dd class="text-slate-800 text-end">{{ $governorate->sort_order ?? '—' }}</dd>
                        </div>
                    </dl>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        @include('admin.governorates.actions', ['governorate' => $governorate])
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
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'عدد المدن' : 'Cities' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'ترتيب' : 'Sort Order' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الحالة' : 'Status' }}</th>
                        <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'إجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($governorates as $governorate)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm font-medium text-slate-800">#{{ $governorate->id }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $governorate->name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $governorate->name_ar }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $governorate->cities_count }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $governorate->sort_order ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if($governorate->is_active)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ $l ? 'نشط' : 'Active' }}</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">{{ $l ? 'غير نشط' : 'Inactive' }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-end">@include('admin.governorates.actions', ['governorate' => $governorate])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 sm:px-6 pb-6 border-t border-slate-100 pt-4">{{ $governorates->links() }}</div>
    @endif
</div>
@endsection
