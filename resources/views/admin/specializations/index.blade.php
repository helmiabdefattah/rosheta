@extends('admin.layouts.admin')

@php
    $l = app()->getLocale() === 'ar';
@endphp

@section('title', $l ? 'التخصصات' : 'Specializations')
@section('page-title', $l ? 'التخصصات' : 'Specializations')
@section('page-description', $l ? 'إدارة التخصصات الطبية' : 'Manage medical specializations')

@section('header-actions')
    <x-admin.ui.button href="{{ route('admin.specializations.create') }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'>
        {{ $l ? 'إضافة تخصص' : 'Add Specialization' }}
    </x-admin.ui.button>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300 max-lg:overflow-visible lg:overflow-hidden">
    <div class="p-4 sm:p-6 border-b border-slate-100">
        <form method="GET" action="{{ route('admin.specializations.index') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end sm:justify-between">
            <div class="flex-1 w-full min-w-0">
                <label for="spec-search" class="block text-xs font-medium text-slate-500 mb-1">{{ $l ? 'بحث' : 'Search' }}</label>
                <input type="search" name="search" id="spec-search" value="{{ request('search') }}"
                       placeholder="{{ $l ? 'المعرف، الاسم، الرابط…' : 'ID, name, slug…' }}"
                       class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary">
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:opacity-90">{{ $l ? 'بحث' : 'Search' }}</button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.specializations.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm text-center">{{ $l ? 'مسح' : 'Clear' }}</a>
                @endif
            </div>
        </form>
    </div>

    @if($specializations->count() === 0)
        <div class="p-10 text-center text-slate-500">{{ $l ? 'لا توجد تخصصات.' : 'No specializations found.' }}</div>
    @else
        <div class="lg:hidden space-y-3 p-4">
            @foreach($specializations as $specialization)
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="mb-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">#{{ $specialization->id }}</p>
                        <p class="text-lg font-bold text-slate-900">{{ $specialization->name }}</p>
                        <p class="text-xs text-slate-500 mt-1 font-mono">{{ $specialization->slug }}</p>
                    </div>
                    <p class="text-sm text-slate-600 mb-3">{{ $l ? 'عدد الأطباء' : 'Doctors' }}: <span class="font-semibold text-slate-900">{{ $specialization->doctors_count }}</span></p>
                    <div class="pt-3 border-t border-slate-100">
                        @include('admin.specializations.actions', ['specialization' => $specialization])
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
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'عدد الأطباء' : 'Doctors' }}</th>
                        <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'إجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($specializations as $specialization)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm font-medium text-slate-800">#{{ $specialization->id }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $specialization->name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600 font-mono text-xs">{{ $specialization->slug }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $specialization->doctors_count }}</td>
                            <td class="px-4 py-3 text-sm text-end">@include('admin.specializations.actions', ['specialization' => $specialization])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 sm:px-6 pb-6 border-t border-slate-100 pt-4">{{ $specializations->links() }}</div>
    @endif
</div>
@endsection
