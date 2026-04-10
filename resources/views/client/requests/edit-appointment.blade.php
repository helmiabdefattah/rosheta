@extends('client.layouts.dashboard')

@php
	$isAr = app()->getLocale() === 'ar';
@endphp

@section('title', $isAr ? 'تعديل ملاحظات الموعد' : 'Edit appointment notes')
@section('page-title', $isAr ? 'تعديل ملاحظات الموعد' : 'Edit appointment notes')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
	<div class="flex flex-wrap gap-2">
		<a href="{{ route('client.requests.clinic.show', $appointment) }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-100 text-gray-800 text-sm hover:bg-gray-200">
			{{ $isAr ? '← العودة' : '← Back' }}
		</a>
	</div>

	<div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-900">
		{{ $isAr
			? 'يمكنك تعديل الملاحظات فقط. لتغيير التاريخ أو الوقت، ألغِ الموعد ثم احجز موعدًا جديدًا.'
			: 'You can only edit your notes. To change the date or time, cancel this appointment and book a new one.' }}
	</div>

	<form action="{{ route('client.requests.clinic.update', $appointment) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
		@csrf
		@method('PUT')

		<div>
			<label for="notes" class="block text-sm font-medium text-gray-700 mb-1">{{ $isAr ? 'ملاحظات' : 'Notes' }}</label>
			<textarea name="notes" id="notes" rows="5" maxlength="1000" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary">{{ old('notes', $appointment->notes) }}</textarea>
			@error('notes')
				<p class="text-red-600 text-xs mt-1">{{ $message }}</p>
			@enderror
		</div>

		<div class="flex flex-wrap gap-2">
			<button type="submit" class="px-5 py-2.5 rounded-lg bg-primary text-white text-sm font-medium hover:opacity-90">
				{{ $isAr ? 'حفظ' : 'Save' }}
			</button>
			<a href="{{ route('client.requests.clinic.show', $appointment) }}" class="px-5 py-2.5 rounded-lg bg-gray-200 text-gray-800 text-sm font-medium hover:bg-gray-300">
				{{ $isAr ? 'إلغاء' : 'Cancel' }}
			</a>
		</div>
	</form>
</div>
@endsection
