@extends('admin.layouts.admin')

@php
    $l = app()->getLocale() === 'ar';
@endphp

@section('title', $l ? 'الأطباء' : 'Doctors')
@section('page-title', $l ? 'الأطباء' : 'Doctors')
@section('page-description', $l ? 'إدارة الأطباء' : 'Manage doctors')

@section('header-actions')
    <x-admin.ui.button href="{{ route('admin.doctors.create') }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'>
        {{ $l ? 'إضافة طبيب' : 'Add Doctor' }}
    </x-admin.ui.button>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300 max-lg:overflow-visible lg:overflow-hidden">
    <div class="p-4 sm:p-6 border-b border-slate-100">
        <form method="GET" action="{{ route('admin.doctors.index') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end sm:justify-between">
            <div class="flex-1 w-full min-w-0">
                <label for="doctors-search" class="block text-xs font-medium text-slate-500 mb-1">{{ $l ? 'بحث' : 'Search' }}</label>
                <input type="search" name="search" id="doctors-search" value="{{ request('search') }}"
                       placeholder="{{ $l ? 'المعرف، الاسم، التخصص، البريد…' : 'ID, name, specialization, email…' }}"
                       class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary">
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:opacity-90">{{ $l ? 'بحث' : 'Search' }}</button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.doctors.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm text-center">{{ $l ? 'مسح' : 'Clear' }}</a>
                @endif
            </div>
        </form>
    </div>

    @if($doctors->count() === 0)
        <div class="p-10 text-center text-slate-500">{{ $l ? 'لا يوجد أطباء.' : 'No doctors found.' }}</div>
    @else
        <div class="lg:hidden space-y-3 p-4">
            @foreach($doctors as $doctor)
                @php $img = $doctor->getFirstMediaUrl('profile_image'); @endphp
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start gap-3 mb-3">
                        @if($img)
                            <img src="{{ $img }}" alt="" class="w-14 h-14 rounded-full object-cover shrink-0 border border-slate-200">
                        @else
                            <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 text-xs shrink-0">{{ $l ? '—' : '—' }}</div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">#{{ $doctor->id }}</p>
                            <p class="text-lg font-bold text-slate-900">{{ $doctor->name }}</p>
                            <p class="text-xs text-slate-500 font-mono">{{ $doctor->slug }}</p>
                        </div>
                    </div>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">{{ $l ? 'التخصص' : 'Specialization' }}</dt>
                            <dd class="text-slate-800 text-end">{{ $doctor->specialization->name ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 pt-1">
                            <dt class="text-slate-500">{{ $l ? 'البريد' : 'User email' }}</dt>
                            <dd class="text-slate-800 text-end text-xs break-all">{{ $doctor->user->email ?? '—' }}</dd>
                        </div>
                    </dl>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        @include('admin.doctors.actions', ['doctor' => $doctor])
                    </div>
                </div>
            @endforeach
        </div>

        <div class="hidden lg:block overflow-x-auto p-4 sm:p-6 pt-0">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الصورة' : 'Image' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الاسم' : 'Name' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'التخصص' : 'Specialization' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الحساب' : 'User' }}</th>
                        <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'إجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($doctors as $doctor)
                        @php $img = $doctor->getFirstMediaUrl('profile_image'); @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm font-medium text-slate-800">#{{ $doctor->id }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if($img)
                                    <img src="{{ $img }}" alt="" class="w-10 h-10 rounded-full object-cover border border-slate-200">
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $doctor->name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600 font-mono text-xs">{{ $doctor->slug }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $doctor->specialization->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $doctor->user->email ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-end">@include('admin.doctors.actions', ['doctor' => $doctor])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 sm:px-6 pb-6 border-t border-slate-100 pt-4">{{ $doctors->links() }}</div>
    @endif
</div>
@endsection
