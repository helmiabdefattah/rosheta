@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'لوحة العيادة' : 'Clinic dashboard')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="max-w-6xl mx-auto">
        <div class="mb-8 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $clinic->name }}</h1>
                <p class="text-slate-600 mt-1">{{ app()->getLocale() === 'ar' ? 'مواعيد العيادة' : 'Clinic appointments' }}</p>
            </div>
            <form method="post" action="{{ route('logout') }}" class="shrink-0">
                @csrf
                <button type="submit" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50">
                    {{ app()->getLocale() === 'ar' ? 'تسجيل الخروج' : 'Log out' }}
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-700 text-left">
                        <tr>
                            <th class="px-4 py-3 font-semibold">{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</th>
                            <th class="px-4 py-3 font-semibold">{{ app()->getLocale() === 'ar' ? 'الوقت' : 'Time' }}</th>
                            <th class="px-4 py-3 font-semibold">{{ app()->getLocale() === 'ar' ? 'المريض' : 'Patient' }}</th>
                            <th class="px-4 py-3 font-semibold">{{ app()->getLocale() === 'ar' ? 'الطبيب' : 'Doctor' }}</th>
                            <th class="px-4 py-3 font-semibold">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($appointments as $appointment)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-4 py-3 text-slate-800">{{ $appointment->appointment_date?->format('Y-m-d') ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-800">@if($appointment->appointment_time){{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('H:i') }}@else—@endif</td>
                                <td class="px-4 py-3 text-slate-800">{{ $appointment->client?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-800">{{ $appointment->doctor?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $appointment->status ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-slate-500">
                                    {{ app()->getLocale() === 'ar' ? 'لا توجد مواعيد بعد.' : 'No appointments yet.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($appointments->hasPages())
                <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/50">
                    {{ $appointments->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
