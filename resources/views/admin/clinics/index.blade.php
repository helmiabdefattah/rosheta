@extends('admin.layouts.admin')

@php
    $l = app()->getLocale() === 'ar';
@endphp

@section('title', $l ? 'العيادات' : 'Clinics')
@section('page-title', $l ? 'العيادات' : 'Clinics')
@section('page-description', $l ? 'إدارة العيادات' : 'Manage clinics')

@section('header-actions')
    <x-admin.ui.button href="{{ route('admin.clinics.create') }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'>
        {{ $l ? 'إضافة عيادة' : 'Add Clinic' }}
    </x-admin.ui.button>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300 max-lg:overflow-visible lg:overflow-hidden">
    <div class="p-4 sm:p-6 border-b border-slate-100">
        <form method="GET" action="{{ route('admin.clinics.index') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end sm:justify-between">
            <div class="flex-1 w-full min-w-0">
                <label for="clinics-search" class="block text-xs font-medium text-slate-500 mb-1">{{ $l ? 'بحث' : 'Search' }}</label>
                <input type="search" name="search" id="clinics-search" value="{{ request('search') }}"
                       placeholder="{{ $l ? 'الاسم، الطبيب، الهاتف، الموقع…' : 'Name, doctor, phone, location…' }}"
                       class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary">
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:opacity-90">{{ $l ? 'بحث' : 'Search' }}</button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.clinics.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm text-center">{{ $l ? 'مسح' : 'Clear' }}</a>
                @endif
            </div>
        </form>
    </div>

    @if($clinics->count() === 0)
        <div class="p-10 text-center text-slate-500">{{ $l ? 'لا توجد عيادات.' : 'No clinics found.' }}</div>
    @else
        <div class="lg:hidden space-y-3 p-4">
            @foreach($clinics as $clinic)
                @php
                    $location = collect([
                        $clinic->governorate ? ($l ? ($clinic->governorate->name_ar ?? $clinic->governorate->name) : ($clinic->governorate->name ?? $clinic->governorate->name_ar)) : null,
                        $clinic->city ? ($l ? ($clinic->city->name_ar ?? $clinic->city->name) : ($clinic->city->name ?? $clinic->city->name_ar)) : null,
                        $clinic->area ? ($l ? ($clinic->area->name_ar ?? $clinic->area->name) : ($clinic->area->name ?? $clinic->area->name_ar)) : null,
                    ])->filter()->join(', ') ?: '—';
                @endphp
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="mb-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">#{{ $clinic->id }}</p>
                        <p class="text-lg font-bold text-slate-900">{{ $clinic->name }}</p>
                    </div>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">{{ $l ? 'الطبيب' : 'Doctor' }}</dt>
                            <dd class="text-slate-800 text-end">{{ $clinic->doctor->name ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">{{ $l ? 'التخصص' : 'Specialization' }}</dt>
                            <dd class="text-slate-800 text-end text-xs">{{ $clinic->doctor->specialization->name ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">{{ $l ? 'الهاتف' : 'Phone' }}</dt>
                            <dd class="text-slate-800 text-end">{{ $clinic->phone_number ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 pt-1">
                            <dt class="text-slate-500">{{ $l ? 'الموقع' : 'Location' }}</dt>
                            <dd class="text-slate-800 text-end text-xs max-w-[60%]">{{ $location }}</dd>
                        </div>
                    </dl>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        @include('admin.clinics.actions', ['clinic' => $clinic])
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
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الطبيب' : 'Doctor' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'التخصص' : 'Specialization' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الهاتف' : 'Phone' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الموقع' : 'Location' }}</th>
                        <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'إجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($clinics as $clinic)
                        @php
                            $location = collect([
                                $clinic->governorate ? ($l ? ($clinic->governorate->name_ar ?? $clinic->governorate->name) : ($clinic->governorate->name ?? $clinic->governorate->name_ar)) : null,
                                $clinic->city ? ($l ? ($clinic->city->name_ar ?? $clinic->city->name) : ($clinic->city->name ?? $clinic->city->name_ar)) : null,
                                $clinic->area ? ($l ? ($clinic->area->name_ar ?? $clinic->area->name) : ($clinic->area->name ?? $clinic->area->name_ar)) : null,
                            ])->filter()->join(', ') ?: '—';
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm font-medium text-slate-800">#{{ $clinic->id }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $clinic->name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $clinic->doctor->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $clinic->doctor->specialization->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $clinic->phone_number ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 max-w-md truncate" title="{{ $location }}">{{ $location }}</td>
                            <td class="px-4 py-3 text-sm text-end">@include('admin.clinics.actions', ['clinic' => $clinic])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 sm:px-6 pb-6 border-t border-slate-100 pt-4">{{ $clinics->links() }}</div>
    @endif
</div>
@endsection
