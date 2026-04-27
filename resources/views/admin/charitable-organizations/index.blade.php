@extends('admin.layouts.admin')

@php
    $l = app()->getLocale() === 'ar';
@endphp

@section('title', 'Charitable Organizations')
@section('page-title', $l ? 'المنظمات الخيرية' : 'Charitable Organizations')
@section('page-description', $l ? 'إدارة المنظمات الخيرية' : 'Manage Charitable Organizations')

@section('header-actions')
    <x-admin.ui.button href="{{ route('admin.charitable-organizations.create') }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'>
        {{ $l ? 'إضافة منظمة خيرية' : 'Add Charitable Organization' }}
    </x-admin.ui.button>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300 max-lg:overflow-visible lg:overflow-hidden">
    <div class="p-4 sm:p-6 border-b border-slate-100">
        <form method="GET" action="{{ route('admin.charitable-organizations.index') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end sm:justify-between">
            <div class="flex-1 w-full min-w-0">
                <label for="co-search" class="block text-xs font-medium text-slate-500 mb-1">{{ $l ? 'بحث' : 'Search' }}</label>
                <input type="search" name="search" id="co-search" value="{{ request('search') }}"
                       placeholder="{{ $l ? 'المعرف، الاسم، العنوان، الموقع…' : 'ID, name, address, location…' }}"
                       class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary">
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:opacity-90">{{ $l ? 'بحث' : 'Search' }}</button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.charitable-organizations.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm text-center">{{ $l ? 'مسح' : 'Clear' }}</a>
                @endif
            </div>
        </form>
    </div>

    @if($charitableOrganizations->count() === 0)
        <div class="p-10 text-center text-slate-500">{{ $l ? 'لا توجد منظمات.' : 'No organizations found.' }}</div>
    @else
        <div class="lg:hidden space-y-3 p-4">
            @foreach($charitableOrganizations as $organization)
                @php
                    $loc = collect([$organization->governorate, $organization->city, $organization->area])
                        ->map(fn ($m) => $m ? ($l ? ($m->name_ar ?? $m->name) : ($m->name ?? $m->name_ar)) : null)
                        ->filter()->join(', ') ?: '—';
                    $phones = (is_array($organization->phone_numbers) && count($organization->phone_numbers)) ? implode(', ', $organization->phone_numbers) : '—';
                    $services = (is_array($organization->services) && count($organization->services)) ? implode(', ', $organization->services) : '—';
                @endphp
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="mb-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">#{{ $organization->id }}</p>
                        <p class="text-lg font-bold text-slate-900">{{ $organization->name }}</p>
                    </div>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'الموقع' : 'Location' }}</dt>
                            <dd class="text-slate-800 text-end text-xs max-w-[60%]">{{ $loc }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'العنوان' : 'Address' }}</dt>
                            <dd class="text-slate-800 text-end text-xs">{{ $organization->address }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'الهاتف' : 'Phones' }}</dt>
                            <dd class="text-slate-800 text-end text-xs break-all">{{ $phones }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 pt-1">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'الخدمات' : 'Services' }}</dt>
                            <dd class="text-slate-800 text-end text-xs max-w-[60%]">{{ $services }}</dd>
                        </div>
                    </dl>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        @include('admin.charitable-organizations.actions', ['organization' => $organization])
                    </div>
                </div>
            @endforeach
        </div>

        <div class="hidden lg:block overflow-x-auto p-4 sm:p-6 pt-0">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'المعرف' : 'ID' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الاسم' : 'Name' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الموقع' : 'Location' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'العنوان' : 'Address' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'أرقام الهاتف' : 'Phone Numbers' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الخدمات' : 'Services' }}</th>
                        <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'إجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($charitableOrganizations as $organization)
                        @php
                            $loc = collect([$organization->governorate, $organization->city, $organization->area])
                                ->map(fn ($m) => $m ? ($l ? ($m->name_ar ?? $m->name) : ($m->name ?? $m->name_ar)) : null)
                                ->filter()->join(', ') ?: '—';
                            $phones = (is_array($organization->phone_numbers) && count($organization->phone_numbers)) ? implode(', ', $organization->phone_numbers) : '—';
                            $services = (is_array($organization->services) && count($organization->services)) ? implode(', ', $organization->services) : '—';
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm font-medium text-slate-800">#{{ $organization->id }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $organization->name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 max-w-xs truncate" title="{{ $loc }}">{{ $loc }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 max-w-xs truncate" title="{{ $organization->address }}">{{ $organization->address }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 max-w-xs truncate" title="{{ $phones }}">{{ $phones }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 max-w-xs truncate" title="{{ $services }}">{{ $services }}</td>
                            <td class="px-4 py-3 text-sm text-end">@include('admin.charitable-organizations.actions', ['organization' => $organization])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 sm:px-6 pb-6 border-t border-slate-100 pt-4">{{ $charitableOrganizations->links() }}</div>
    @endif
</div>
@endsection
