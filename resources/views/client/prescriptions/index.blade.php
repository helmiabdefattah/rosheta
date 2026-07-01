@extends('client.layouts.dashboard')

@php $isAr = app()->getLocale() === 'ar'; @endphp

@section('title', $isAr ? 'روشتاتي' : 'My Prescriptions')
@section('page-title', $isAr ? 'روشتاتي' : 'My Prescriptions')
@section('page-description', $isAr ? 'الروشتات التي كتبها الأطباء لك في العيادات' : 'Prescriptions written for you by doctors during clinic visits')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="text-lg font-bold text-slate-800">
                {{ $isAr ? 'الروشتات الطبية' : 'Medical Prescriptions' }}
            </h3>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm text-{{ $isAr ? 'right' : 'left' }}">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 font-bold">{{ $isAr ? 'الكود' : 'Code' }}</th>
                        <th class="px-6 py-4 font-bold">{{ $isAr ? 'الطبيب' : 'Doctor' }}</th>
                        <th class="px-6 py-4 font-bold">{{ $isAr ? 'العيادة' : 'Clinic' }}</th>
                        <th class="px-6 py-4 font-bold">{{ $isAr ? 'الأدوية' : 'Medicines' }}</th>
                        <th class="px-6 py-4 font-bold">{{ $isAr ? 'التاريخ' : 'Date' }}</th>
                        <th class="px-6 py-4 font-bold text-center">{{ $isAr ? 'فتح / طباعة' : 'Open / Print' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($prescriptions as $prescription)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-6 py-4 font-mono text-xs text-slate-500">{{ $prescription->code }}</td>
                            <td class="px-6 py-4 font-medium text-slate-800">{{ $prescription->doctor->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $prescription->appointment->clinic->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-slate-600">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-teal-50 text-teal-700 text-xs font-semibold">
                                    <i class="bi bi-capsule"></i>
                                    {{ $prescription->items_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $prescription->created_at->translatedFormat('d M Y') }}</td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('client.prescriptions.print', $prescription) }}" target="_blank"
                                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-teal-600 hover:bg-teal-700 text-white transition-colors"
                                   title="{{ $isAr ? 'فتح / طباعة' : 'Open / Print' }}">
                                    <i class="bi bi-printer text-base"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4 text-gray-300">
                                        <i class="bi bi-file-earmark-medical text-3xl"></i>
                                    </div>
                                    <p class="text-lg font-medium">{{ $isAr ? 'لا توجد روشتات بعد' : 'No prescriptions yet' }}</p>
                                    <p class="text-sm">{{ $isAr ? 'ستظهر روشتاتك هنا بعد زيارة الطبيب' : 'Your prescriptions will appear here after a doctor visit' }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden divide-y divide-gray-100">
            @forelse($prescriptions as $prescription)
                <div class="p-4 flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-800 truncate">{{ $prescription->doctor->name ?? '—' }}</p>
                        <p class="text-sm text-slate-500 truncate">{{ $prescription->appointment->clinic->name ?? '—' }}</p>
                        <div class="flex items-center gap-2 mt-1 text-xs text-slate-400">
                            <span>{{ $prescription->created_at->translatedFormat('d M Y') }}</span>
                            <span>&middot;</span>
                            <span class="inline-flex items-center gap-1">
                                <i class="bi bi-capsule"></i>{{ $prescription->items_count }} {{ $isAr ? 'دواء' : 'meds' }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('client.prescriptions.print', $prescription) }}" target="_blank"
                       class="shrink-0 inline-flex items-center justify-center w-10 h-10 rounded-lg bg-teal-600 hover:bg-teal-700 text-white transition-colors"
                       title="{{ $isAr ? 'فتح / طباعة' : 'Open / Print' }}">
                        <i class="bi bi-printer text-base"></i>
                    </a>
                </div>
            @empty
                <div class="p-6 text-center">
                    <div class="flex flex-col items-center justify-center text-gray-400">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4 text-gray-300">
                            <i class="bi bi-file-earmark-medical text-3xl"></i>
                        </div>
                        <p class="text-lg font-medium">{{ $isAr ? 'لا توجد روشتات بعد' : 'No prescriptions yet' }}</p>
                        <p class="text-sm">{{ $isAr ? 'ستظهر روشتاتك هنا بعد زيارة الطبيب' : 'Your prescriptions will appear here after a doctor visit' }}</p>
                    </div>
                </div>
            @endforelse
        </div>

        @if($prescriptions->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
                {{ $prescriptions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
