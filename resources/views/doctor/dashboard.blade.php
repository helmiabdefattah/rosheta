@extends('doctor.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'لوحة تحكم الطبيب' : 'Doctor Dashboard')
@section('page-title', app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard')
@section('page-description', $doctor->name . ' – ' . ($doctor->specialization?->name ?? '-'))

@php $isAr = app()->getLocale() === 'ar'; @endphp

@section('content')
<div class="space-y-6">
    {{-- Clinic Management System (in-clinic workspace: queue, kiosk, examination).
         The whole banner is the link; the arrow nudges on hover (group). --}}
    <a href="{{ route('practice.doctor.dashboard') }}"
       class="group relative block overflow-hidden rounded-2xl p-6 text-white bg-gradient-to-r from-indigo-600 to-violet-600 shadow-lg shadow-indigo-500/20 ring-1 ring-white/10 transition-all hover:shadow-xl hover:shadow-indigo-500/30 focus:outline-none focus-visible:ring-4 focus-visible:ring-white/40">
        {{-- Soft decorative glow, purely visual --}}
        <span aria-hidden="true" class="pointer-events-none absolute -top-10 -end-10 h-40 w-40 rounded-full bg-white/10 blur-2xl"></span>
        <div class="relative flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center text-2xl backdrop-blur-sm">🩺</div>
                <div>
                    <p class="text-lg font-bold">{{ $isAr ? 'صفحة العمل داخل العيادة' : 'Open my clinic workspace' }}</p>
                    <p class="text-sm text-white/80">{{ $isAr ? 'الطابور والكشف والروشتات وشاشة الانتظار' : 'Queue, examination, prescriptions & waiting-room display' }}</p>
                </div>
            </div>
            <span class="text-2xl transition-transform duration-200 {{ $isAr ? 'group-hover:-translate-x-1' : 'group-hover:translate-x-1' }}">{{ $isAr ? '←' : '→' }}</span>
        </div>
    </a>

    {{-- Overview: two live stats and two quick links, on one even row. --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Appointments today --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div class="w-12 h-12 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600">
                    <i class="bi bi-calendar-check text-xl"></i>
                </div>
            </div>
            <p class="mt-4 text-3xl font-extrabold text-slate-900 leading-none">{{ $todayCount }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ $isAr ? 'مواعيد اليوم' : 'Appointments today' }}</p>
        </div>

        {{-- Clinics --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                    <i class="bi bi-building text-xl"></i>
                </div>
            </div>
            <p class="mt-4 text-3xl font-extrabold text-slate-900 leading-none">{{ $clinicsWhereDoctor->count() }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ $isAr ? 'العيادات' : 'Clinics' }}</p>
        </div>

        {{-- Working & off days (quick link) --}}
        <a href="{{ route('doctor.calendar.index') }}"
           class="group bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex flex-col focus:outline-none">
            <div class="flex items-center justify-between">
                <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                    <i class="bi bi-calendar-week text-xl"></i>
                </div>
                <i class="bi {{ $isAr ? 'bi-chevron-left' : 'bi-chevron-right' }} text-slate-300 transition-all group-hover:text-amber-500 {{ $isAr ? 'group-hover:-translate-x-0.5' : 'group-hover:translate-x-0.5' }}"></i>
            </div>
            <p class="mt-4 text-sm font-semibold text-slate-800">{{ $isAr ? 'أيام العمل والإجازة' : 'Working & off days' }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $isAr ? 'عدّل أيام الشهر القادم' : 'Edit next month' }}</p>
        </a>

        {{-- Assistants (quick link) --}}
        <a href="{{ route('doctor.staff.index') }}"
           class="group bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex flex-col focus:outline-none">
            <div class="flex items-center justify-between">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="bi bi-people text-xl"></i>
                </div>
                <i class="bi {{ $isAr ? 'bi-chevron-left' : 'bi-chevron-right' }} text-slate-300 transition-all group-hover:text-indigo-500 {{ $isAr ? 'group-hover:-translate-x-0.5' : 'group-hover:translate-x-0.5' }}"></i>
            </div>
            <p class="mt-4 text-sm font-semibold text-slate-800">{{ $isAr ? 'المساعدون' : 'Assistants' }}</p>
            <p class="mt-1 text-xs text-slate-500">
                {{ $isAr
                    ? 'حتى ' . $doctor->assistantLimit() . ' مساعدين لكل عيادة'
                    : 'Up to ' . $doctor->assistantLimit() . ' per clinic' }}
            </p>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- My Clinics --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-semibold text-slate-800 flex items-center gap-2">
                    <i class="bi bi-hospital text-teal-600"></i>
                    {{ $isAr ? 'عياداتي' : 'My Clinics' }}
                </h3>
                <a href="{{ route('doctor.clinics.index') }}" class="group text-sm font-medium text-teal-600 hover:text-teal-700 inline-flex items-center gap-1">
                    {{ $isAr ? 'عرض الكل' : 'View all' }}
                    <i class="bi {{ $isAr ? 'bi-arrow-left' : 'bi-arrow-right' }} text-xs transition-transform {{ $isAr ? 'group-hover:-translate-x-0.5' : 'group-hover:translate-x-0.5' }}"></i>
                </a>
            </div>
            <ul class="divide-y divide-gray-100">
                @forelse($clinicsWhereDoctor->take(5) as $c)
                    <li>
                        <a href="{{ route('doctor.clinics.show', $c) }}" class="flex items-center gap-3 p-4 hover:bg-slate-50 transition-colors">
                            <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                                <i class="bi bi-building"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="font-medium text-slate-800 truncate">{{ $c->name }}</p>
                                <p class="text-sm text-slate-500 truncate">{{ $c->governorate?->name ?? $c->governorate?->name_ar }} / {{ $c->city?->name ?? $c->city?->name_ar }}</p>
                            </div>
                        </a>
                    </li>
                @empty
                    <li class="p-8 text-center">
                        <i class="bi bi-hospital text-3xl text-slate-300"></i>
                        <p class="mt-2 text-sm text-slate-500">{{ $isAr ? 'لا توجد عيادات مرتبطة' : 'No clinics linked.' }}</p>
                    </li>
                @endforelse
            </ul>
        </div>

        {{-- Upcoming Appointments --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-semibold text-slate-800 flex items-center gap-2">
                    <i class="bi bi-calendar-event text-teal-600"></i>
                    {{ $isAr ? 'القادمة من المواعيد' : 'Upcoming Appointments' }}
                </h3>
                <a href="{{ route('doctor.appointments.index') }}" class="group text-sm font-medium text-teal-600 hover:text-teal-700 inline-flex items-center gap-1">
                    {{ $isAr ? 'عرض الكل' : 'View all' }}
                    <i class="bi {{ $isAr ? 'bi-arrow-left' : 'bi-arrow-right' }} text-xs transition-transform {{ $isAr ? 'group-hover:-translate-x-0.5' : 'group-hover:translate-x-0.5' }}"></i>
                </a>
            </div>
            <ul class="divide-y divide-gray-100">
                @forelse($upcomingAppointments as $apt)
                    <li class="p-4 hover:bg-slate-50 transition-colors">
                        <div class="flex justify-between items-start gap-3">
                            <div class="min-w-0">
                                <p class="font-medium text-slate-800 truncate">{{ $apt->clinic?->name }}</p>
                                <p class="text-sm text-slate-600 flex items-center gap-1.5">
                                    <i class="bi bi-clock text-xs text-slate-400"></i>
                                    {{ $apt->appointment_date->format('Y-m-d') }} · {{ \Carbon\Carbon::parse($apt->appointment_time)->format('g:i A') }}
                                </p>
                                @if($apt->client)<p class="text-xs text-slate-500 truncate">{{ $apt->client->name ?? '–' }}</p>@endif
                            </div>
                            <span class="shrink-0 text-xs font-medium px-2.5 py-1 rounded-full inline-flex items-center gap-1 {{ $apt->status === 'confirmed' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $apt->status === 'confirmed' ? 'bg-green-500' : 'bg-amber-500' }}"></span>
                                {{ $apt->status }}
                            </span>
                        </div>
                    </li>
                @empty
                    <li class="p-8 text-center">
                        <i class="bi bi-calendar-x text-3xl text-slate-300"></i>
                        <p class="mt-2 text-sm text-slate-500">{{ $isAr ? 'لا توجد مواعيد قادمة' : 'No upcoming appointments.' }}</p>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
