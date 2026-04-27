@extends('admin.layouts.admin')

@php
    $l = app()->getLocale() === 'ar';
@endphp

@section('title', 'Users')
@section('page-title', $l ? 'المستخدمون' : 'Users')
@section('page-description', $l ? 'إدارة المستخدمين' : 'Manage Users')

@section('header-actions')
    <x-admin.ui.button href="{{ route('admin.users.create') }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'>
        {{ $l ? 'إضافة مستخدم' : 'Add User' }}
    </x-admin.ui.button>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300 max-lg:overflow-visible lg:overflow-hidden">
    <div class="p-4 sm:p-6 border-b border-slate-100">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end sm:justify-between">
            <div class="flex-1 w-full min-w-0">
                <label for="users-search" class="block text-xs font-medium text-slate-500 mb-1">{{ $l ? 'بحث' : 'Search' }}</label>
                <input type="search" name="search" id="users-search" value="{{ request('search') }}"
                       placeholder="{{ $l ? 'المعرف، الاسم، البريد، الصيدلية، المعمل…' : 'ID, name, email, pharmacy, laboratory…' }}"
                       class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary">
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:opacity-90">{{ $l ? 'بحث' : 'Search' }}</button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm text-center">{{ $l ? 'مسح' : 'Clear' }}</a>
                @endif
            </div>
        </form>
    </div>

    @if($users->count() === 0)
        <div class="p-10 text-center text-slate-500">{{ $l ? 'لا يوجد مستخدمون.' : 'No users found.' }}</div>
    @else
        <div class="lg:hidden space-y-3 p-4">
            @foreach($users as $user)
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $l ? 'المعرف' : 'ID' }}</p>
                            <p class="text-lg font-bold text-slate-900">#{{ $user->id }}</p>
                        </div>
                    </div>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'الاسم' : 'Name' }}</dt>
                            <dd class="text-slate-800 font-medium text-end">{{ $user->name }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'البريد' : 'Email' }}</dt>
                            <dd class="text-slate-800 text-end text-xs break-all">{{ $user->email }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'الصيدلية' : 'Pharmacy' }}</dt>
                            <dd class="text-slate-800 text-end text-xs">{{ $user->pharmacy->name ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 pt-1">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'المعمل' : 'Laboratory' }}</dt>
                            <dd class="text-slate-800 text-end text-xs">{{ $user->laboratory->name ?? '—' }}</dd>
                        </div>
                    </dl>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        @include('admin.users.actions', ['user' => $user])
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
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'البريد' : 'Email' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الصيدلية' : 'Pharmacy' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'المعمل' : 'Laboratory' }}</th>
                        <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'إجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm text-slate-800 font-medium">#{{ $user->id }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $user->email }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $user->pharmacy->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $user->laboratory->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-end">@include('admin.users.actions', ['user' => $user])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 sm:px-6 pb-6 border-t border-slate-100 pt-4">{{ $users->links() }}</div>
    @endif
</div>
@endsection
