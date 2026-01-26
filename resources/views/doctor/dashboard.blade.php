@extends('doctor.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'لوحة تحكم الطبيب' : 'Doctor Dashboard')
@section('page-title', app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard')
@section('page-description', $doctor->name . ' – ' . ($doctor->specialization?->name ?? '-'))

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-teal-100 flex items-center justify-center text-teal-600">
                    <i class="bi bi-calendar-check text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-800">{{ $todayCount }}</p>
                    <p class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'مواعيد اليوم' : 'Appointments today' }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                    <i class="bi bi-building text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-800">{{ $clinicsWhereDoctor->count() }}</p>
                    <p class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'العيادات' : 'Clinics' }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <a href="{{ route('doctor.calendar.index') }}" class="flex items-center gap-4 hover:opacity-90">
                <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                    <i class="bi bi-calendar-week text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'أيام العمل والإجازة' : 'Working & off days' }}</p>
                    <p class="text-xs text-slate-500">{{ app()->getLocale() === 'ar' ? 'عدّل أيام الشهر القادم' : 'Edit next month' }}</p>
                </div>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'عياداتي' : 'My Clinics' }}</h3>
                <a href="{{ route('doctor.clinics.index') }}" class="text-sm text-teal-600 hover:underline">{{ app()->getLocale() === 'ar' ? 'عرض الكل' : 'View all' }}</a>
            </div>
            <ul class="divide-y divide-gray-100">
                @forelse($clinicsWhereDoctor->take(5) as $c)
                    <li class="p-4 hover:bg-gray-50">
                        <a href="{{ route('doctor.clinics.show', $c) }}" class="block">
                            <p class="font-medium text-slate-800">{{ $c->name }}</p>
                            <p class="text-sm text-slate-500">{{ $c->governorate?->name ?? $c->governorate?->name_ar }} / {{ $c->city?->name ?? $c->city?->name_ar }}</p>
                        </a>
                    </li>
                @empty
                    <li class="p-6 text-center text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد عيادات مرتبطة' : 'No clinics linked.' }}</li>
                @endforelse
            </ul>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'القادمة من المواعيد' : 'Upcoming Appointments' }}</h3>
                <a href="{{ route('doctor.appointments.index') }}" class="text-sm text-teal-600 hover:underline">{{ app()->getLocale() === 'ar' ? 'عرض الكل' : 'View all' }}</a>
            </div>
            <ul class="divide-y divide-gray-100">
                @forelse($upcomingAppointments as $apt)
                    <li class="p-4 hover:bg-gray-50">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium text-slate-800">{{ $apt->clinic?->name }}</p>
                                <p class="text-sm text-slate-600">{{ $apt->appointment_date->format('Y-m-d') }} {{ \Carbon\Carbon::parse($apt->appointment_time)->format('g:i A') }}</p>
                                @if($apt->client)<p class="text-xs text-slate-500">{{ $apt->client->name ?? '–' }}</p>@endif
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full {{ $apt->status === 'confirmed' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">{{ $apt->status }}</span>
                        </div>
                    </li>
                @empty
                    <li class="p-6 text-center text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد مواعيد قادمة' : 'No upcoming appointments.' }}</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
