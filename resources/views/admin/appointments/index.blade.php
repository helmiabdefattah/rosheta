@extends('admin.layouts.admin')

@php
    $l = app()->getLocale() === 'ar';
@endphp

@section('title', $l ? 'حجز المواعيد' : 'Appointments')
@section('page-title', $l ? 'حجز المواعيد' : 'Appointments')
@section('page-description', $l ? 'إدارة المواعيد' : 'Manage appointments')

@section('header-actions')
    <x-admin.ui.button href="{{ route('admin.appointments.create') }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'>
        {{ $l ? 'حجز موعد جديد' : 'Book Appointment' }}
    </x-admin.ui.button>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300 max-lg:overflow-visible lg:overflow-hidden">
    <div class="p-4 sm:p-6 border-b border-slate-100">
        <form method="GET" action="{{ route('admin.appointments.index') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end sm:justify-between">
            <div class="flex-1 w-full min-w-0">
                <label for="appt-search" class="block text-xs font-medium text-slate-500 mb-1">{{ $l ? 'بحث' : 'Search' }}</label>
                <input type="search" name="search" id="appt-search" value="{{ request('search') }}"
                       placeholder="{{ $l ? 'المعرف، التاريخ، الطبيب، العيادة، المريض، الحالة…' : 'ID, date, doctor, clinic, patient, status…' }}"
                       class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary">
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:opacity-90">{{ $l ? 'بحث' : 'Search' }}</button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.appointments.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm text-center">{{ $l ? 'مسح' : 'Clear' }}</a>
                @endif
            </div>
        </form>
    </div>

    @if($appointments->count() === 0)
        <div class="p-10 text-center text-slate-500">{{ $l ? 'لا توجد مواعيد.' : 'No appointments found.' }}</div>
    @else
        <div class="lg:hidden space-y-3 p-4">
            @foreach($appointments as $appointment)
                @php
                    $timeStr = $appointment->appointment_time instanceof \DateTimeInterface
                        ? $appointment->appointment_time->format('H:i')
                        : \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i');
                    $typeLabel = $appointment->type === 'medical_examination'
                        ? ($l ? 'كشف' : 'Examination')
                        : ($l ? 'متابعة' : 'Follow-up');
                    $statusColors = [
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'confirmed' => 'bg-blue-100 text-blue-800',
                        'completed' => 'bg-green-100 text-green-800',
                        'cancelled' => 'bg-red-100 text-red-800',
                    ];
                    $sc = $statusColors[$appointment->status] ?? 'bg-gray-100 text-gray-800';
                @endphp
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">#{{ $appointment->id }}</p>
                            <p class="text-base font-bold text-slate-900">{{ $appointment->appointment_date?->format('Y-m-d') }} · {{ $timeStr }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $sc }}">{{ ucfirst($appointment->status) }}</span>
                    </div>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">{{ $l ? 'الطبيب' : 'Doctor' }}</dt>
                            <dd class="text-slate-800 text-end">{{ $appointment->doctor->name ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">{{ $l ? 'العيادة' : 'Clinic' }}</dt>
                            <dd class="text-slate-800 text-end text-xs">{{ $appointment->clinic->name ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">{{ $l ? 'المريض' : 'Patient' }}</dt>
                            <dd class="text-slate-800 text-end">{{ $appointment->user->name ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 pt-1">
                            <dt class="text-slate-500">{{ $l ? 'النوع' : 'Type' }}</dt>
                            <dd class="text-slate-800 text-end font-medium">{{ $typeLabel }}</dd>
                        </div>
                    </dl>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        @include('admin.appointments.actions', ['appointment' => $appointment])
                    </div>
                </div>
            @endforeach
        </div>

        <div class="hidden lg:block overflow-x-auto p-4 sm:p-6 pt-0">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'التاريخ' : 'Date' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الوقت' : 'Time' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الطبيب' : 'Doctor' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'العيادة' : 'Clinic' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'المريض' : 'Patient' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'النوع' : 'Type' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الحالة' : 'Status' }}</th>
                        <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'إجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($appointments as $appointment)
                        @php
                            $timeStr = $appointment->appointment_time instanceof \DateTimeInterface
                                ? $appointment->appointment_time->format('H:i')
                                : \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i');
                            $typeLabel = $appointment->type === 'medical_examination'
                                ? ($l ? 'كشف' : 'Examination')
                                : ($l ? 'متابعة' : 'Follow-up');
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'confirmed' => 'bg-blue-100 text-blue-800',
                                'completed' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                            ];
                            $sc = $statusColors[$appointment->status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm font-medium text-slate-800">#{{ $appointment->id }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $appointment->appointment_date?->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $timeStr }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $appointment->doctor->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 max-w-xs truncate" title="{{ $appointment->clinic->name ?? '' }}">{{ $appointment->clinic->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $appointment->user->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $typeLabel }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $sc }}">{{ ucfirst($appointment->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-end">@include('admin.appointments.actions', ['appointment' => $appointment])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 sm:px-6 pb-6 border-t border-slate-100 pt-4">{{ $appointments->links() }}</div>
    @endif
</div>
@endsection
