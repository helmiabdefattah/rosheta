@extends('admin.layouts.admin')

@php
    $l = app()->getLocale() === 'ar';
@endphp

@section('title', 'Clients')
@section('page-title', $l ? 'العملاء' : 'Clients')
@section('page-description', $l ? 'إدارة العملاء' : 'Manage Clients')

@section('header-actions')
    <x-admin.ui.button href="{{ route('admin.clients.create') }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'>
        {{ $l ? 'إضافة عميل' : 'Add Client' }}
    </x-admin.ui.button>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300 max-lg:overflow-visible lg:overflow-hidden">
    <div class="p-4 sm:p-6 border-b border-slate-100">
        <form method="GET" action="{{ route('admin.clients.index') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end sm:justify-between">
            <div class="flex-1 w-full min-w-0">
                <label for="clients-search" class="block text-xs font-medium text-slate-500 mb-1">{{ $l ? 'بحث' : 'Search' }}</label>
                <input type="search" name="search" id="clients-search" value="{{ request('search') }}"
                       placeholder="{{ $l ? 'المعرف، الاسم، الهاتف، البريد، العنوان…' : 'ID, name, phone, email, address…' }}"
                       class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary">
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:opacity-90">{{ $l ? 'بحث' : 'Search' }}</button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.clients.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm text-center">{{ $l ? 'مسح' : 'Clear' }}</a>
                @endif
            </div>
        </form>
    </div>

    @if($clients->count() === 0)
        <div class="p-10 text-center text-slate-500">{{ $l ? 'لا يوجد عملاء.' : 'No clients found.' }}</div>
    @else
        <div class="lg:hidden space-y-3 p-4">
            @foreach($clients as $client)
                @php
                    $addressesList = $client->addresses->pluck('address')->filter()->join(', ') ?: '—';
                    $citiesList = $client->addresses->map(fn ($a) => $l ? ($a->city?->name_ar ?? $a->city?->name) : ($a->city?->name ?? $a->city?->name_ar))->filter()->unique()->values()->join(', ') ?: '—';
                    $areasList = $client->addresses->map(fn ($a) => $l ? ($a->area?->name_ar ?? $a->area?->name) : ($a->area?->name ?? $a->area?->name_ar))->filter()->unique()->values()->join(', ') ?: '—';
                @endphp
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="mb-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $l ? 'المعرف' : 'ID' }}</p>
                        <p class="text-lg font-bold text-slate-900">#{{ $client->id }}</p>
                    </div>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'الاسم' : 'Name' }}</dt>
                            <dd class="text-slate-800 font-medium text-end">{{ $client->name }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'الهاتف' : 'Phone' }}</dt>
                            <dd class="text-slate-800 text-end">{{ $client->phone_number }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'البريد' : 'Email' }}</dt>
                            <dd class="text-slate-800 text-end text-xs break-all">{{ $client->email ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'العناوين' : 'Addresses' }}</dt>
                            <dd class="text-slate-800 text-end text-xs max-w-[60%]">{{ $addressesList }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'المدن' : 'Cities' }}</dt>
                            <dd class="text-slate-800 text-end text-xs">{{ $citiesList }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 pt-1">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'المناطق' : 'Areas' }}</dt>
                            <dd class="text-slate-800 text-end text-xs">{{ $areasList }}</dd>
                        </div>
                    </dl>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        @include('admin.clients.actions', ['client' => $client])
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
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الهاتف' : 'Phone' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'البريد' : 'Email' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'العناوين' : 'Addresses' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'المدن' : 'Cities' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'المناطق' : 'Areas' }}</th>
                        <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'إجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($clients as $client)
                        @php
                            $addressesList = $client->addresses->pluck('address')->filter()->join(', ') ?: '—';
                            $citiesList = $client->addresses->map(fn ($a) => $l ? ($a->city?->name_ar ?? $a->city?->name) : ($a->city?->name ?? $a->city?->name_ar))->filter()->unique()->values()->join(', ') ?: '—';
                            $areasList = $client->addresses->map(fn ($a) => $l ? ($a->area?->name_ar ?? $a->area?->name) : ($a->area?->name ?? $a->area?->name_ar))->filter()->unique()->values()->join(', ') ?: '—';
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm text-slate-800 font-medium">#{{ $client->id }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $client->name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $client->phone_number }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $client->email ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 max-w-xs truncate" title="{{ $addressesList }}">{{ $addressesList }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 max-w-xs truncate" title="{{ $citiesList }}">{{ $citiesList }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 max-w-xs truncate" title="{{ $areasList }}">{{ $areasList }}</td>
                            <td class="px-4 py-3 text-sm text-end">@include('admin.clients.actions', ['client' => $client])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 sm:px-6 pb-6 border-t border-slate-100 pt-4">{{ $clients->links() }}</div>
    @endif
</div>
@endsection
