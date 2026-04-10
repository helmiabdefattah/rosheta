@extends('client.layouts.dashboard')

@php
	$isAr = app()->getLocale() === 'ar';
	$time = $appointment->appointment_time;
	$timeFormatted = $time instanceof \DateTimeInterface ? $time->format('H:i') : (string) $time;
	$typeLabel = match ($appointment->type) {
		'medical_examination' => $isAr ? 'كشف' : 'Examination',
		'follow_up' => $isAr ? 'متابعة' : 'Follow-up',
		default => $appointment->type,
	};
@endphp

@section('title', $isAr ? 'تفاصيل الموعد' : 'Appointment details')
@section('page-title', $isAr ? 'تفاصيل الموعد' : 'Appointment details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
	@if(session('success'))
		<div class="p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">{{ session('success') }}</div>
	@endif

	<div class="flex flex-wrap gap-2">
		<a href="{{ route('client.requests.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-100 text-gray-800 text-sm hover:bg-gray-200">
			{{ $isAr ? '← جميع الطلبات' : '← All requests' }}
		</a>
		@if($canMutate)
			<a href="{{ route('client.requests.clinic.edit', $appointment) }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-primary text-white text-sm hover:opacity-90">
				{{ $isAr ? 'تعديل الملاحظات' : 'Edit notes' }}
			</a>
		@endif
		@if($canCancel)
			<form action="{{ route('client.requests.clinic.cancel', $appointment) }}" method="POST" class="inline" onsubmit="return confirm(@json($isAr ? 'إلغاء هذا الموعد؟' : 'Cancel this appointment?'));">
				@csrf
				@method('DELETE')
				<button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm hover:bg-red-700">
					{{ $isAr ? 'إلغاء الموعد' : 'Cancel appointment' }}
				</button>
			</form>
		@endif
	</div>

	<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
		<div class="flex flex-wrap gap-2 items-center">
			<span class="text-xs text-gray-500">#{{ $appointment->id }}</span>
			<span class="px-2 py-1 rounded text-xs font-medium bg-cyan-50 text-cyan-900">{{ $isAr ? 'حجز عيادة' : 'Clinic' }}</span>
			<span class="px-2 py-1 rounded text-xs font-medium bg-slate-100">{{ $typeLabel }}</span>
			<span class="px-2 py-1 rounded text-xs font-medium bg-amber-50 text-amber-900">{{ ucfirst(str_replace('_', ' ', $appointment->status)) }}</span>
		</div>

		<div class="grid sm:grid-cols-2 gap-4 text-sm">
			<div>
				<h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ $isAr ? 'التاريخ' : 'Date' }}</h3>
				<p class="text-gray-900 font-medium">{{ $appointment->appointment_date->format('Y-m-d') }}</p>
			</div>
			<div>
				<h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ $isAr ? 'الوقت' : 'Time' }}</h3>
				<p class="text-gray-900 font-medium">{{ $timeFormatted }}</p>
			</div>
			<div class="sm:col-span-2">
				<h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ $isAr ? 'العيادة' : 'Clinic' }}</h3>
				<p class="text-gray-900">{{ $appointment->clinic->name ?? '—' }}</p>
				@if($appointment->clinic && ($appointment->clinic->address || $appointment->clinic->area))
					<p class="text-gray-600 text-sm mt-0.5">
						{{ $appointment->clinic->address }}
						@if($appointment->clinic->area) — {{ $appointment->clinic->area->name }} @endif
					</p>
				@endif
			</div>
			<div class="sm:col-span-2">
				<h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ $isAr ? 'الطبيب' : 'Doctor' }}</h3>
				<p class="text-gray-900">{{ $appointment->doctor->name ?? '—' }}</p>
				@if($appointment->doctor && $appointment->doctor->specialization)
					<p class="text-gray-600 text-sm">{{ $appointment->doctor->specialization->name ?? '' }}</p>
				@endif
			</div>
			<div>
				<h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ $isAr ? 'السعر' : 'Price' }}</h3>
				<p class="text-gray-900">{{ number_format((float) $appointment->price, 2) }}</p>
			</div>
		</div>

		@if($appointment->notes)
			<div>
				<h3 class="text-sm font-semibold text-gray-700 mb-1">{{ $isAr ? 'ملاحظاتك' : 'Your notes' }}</h3>
				<p class="text-gray-800 whitespace-pre-wrap">{{ $appointment->notes }}</p>
			</div>
		@endif
	</div>
</div>
@endsection
