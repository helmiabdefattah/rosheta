@extends('client.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'تعديل طلب تمريض منزلي' : 'Edit Nursing Request')
@section('page-title', app()->getLocale() === 'ar' ? 'تعديل طلب تمريض منزلي' : 'Edit Nursing Request')

@section('content')
	<div class="max-w-3xl mx-auto">
		<form method="POST" action="{{ route('client.nurse-requests.update', $request) }}" class="space-y-6">
			@csrf
			@method('PUT')

			<div class="bg-white rounded-lg shadow p-6 space-y-6">
				<div>
					<label class="block text-sm font-medium text-slate-700 mb-1">
						{{ app()->getLocale() === 'ar' ? 'نوع الخدمة' : 'Service type' }}
					</label>
					<input type="text" name="service_type" class="mt-1 block w-full border rounded-md p-2" required
						   value="{{ old('service_type', $request->service_type) }}">
				</div>

				<div>
					<label class="block text-sm font-medium text-slate-700 mb-1">
						{{ app()->getLocale() === 'ar' ? 'ملاحظات طبية (اختياري)' : 'Medical notes (optional)' }}
					</label>
					<textarea name="medical_notes" rows="3" class="mt-1 block w-full border rounded-md p-2">{{ old('medical_notes', $request->medical_notes) }}</textarea>
				</div>

				<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
					<div>
						<label class="block text-sm font-medium text-slate-700 mb-1">
							{{ app()->getLocale() === 'ar' ? 'اختر العنوان (اختياري)' : 'Select address (optional)' }}
						</label>
						<select name="address_id" class="mt-1 block w-full border rounded-md p-2">
							<option value="">
								{{ app()->getLocale() === 'ar' ? 'اختر عنواناً (اختياري)' : 'Choose an address (optional)' }}
							</option>
							@foreach($addresses as $addr)
								<option value="{{ $addr->id }}" @selected(old('address_id', $request->address_id) == $addr->id)>
									{{ $addr->address }} @if($addr->area) — {{ $addr->area->name ?? '' }} @endif
								</option>
							@endforeach
						</select>
					</div>
					<div class="grid grid-cols-2 gap-4">
						<div>
							<label class="block text-sm font-medium text-slate-700 mb-1">
								{{ app()->getLocale() === 'ar' ? 'عدد الزيارات' : 'Visits count' }}
							</label>
							<input type="number" name="visits_count" min="1" max="60" class="mt-1 block w-full border rounded-md p-2" value="{{ old('visits_count', $request->visits_count) }}">
						</div>
						<div>
							<label class="block text-sm font-medium text-slate-700 mb-1">
								{{ app()->getLocale() === 'ar' ? 'تكرار الزيارات' : 'Frequency' }}
							</label>
							<select name="visit_frequency" class="mt-1 block w-full border rounded-md p-2" required>
								@php $freq = old('visit_frequency', $request->visit_frequency); @endphp
								<option value="daily" @selected($freq==='daily')>{{ app()->getLocale() === 'ar' ? 'يومياً' : 'Daily' }}</option>
								<option value="every_two_days" @selected($freq==='every_two_days')>{{ app()->getLocale() === 'ar' ? 'كل يومين' : 'Every 2 days' }}</option>
								<option value="once_weekly" @selected($freq==='once_weekly')>{{ app()->getLocale() === 'ar' ? 'مرة أسبوعياً' : 'Once weekly' }}</option>
								<option value="twice_weekly" @selected($freq==='twice_weekly')>{{ app()->getLocale() === 'ar' ? 'مرتان أسبوعياً' : 'Twice weekly' }}</option>
							</select>
						</div>
					</div>
				</div>

				<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
					<div>
						<label class="block text-sm font-medium text-slate-700 mb-1">
							{{ app()->getLocale() === 'ar' ? 'تاريخ البدء' : 'Start date' }}
						</label>
						<input type="date" name="visit_start_date" class="mt-1 block w-full border rounded-md p-2" value="{{ old('visit_start_date', optional($request->visit_start_date)->format('Y-m-d')) }}" required>
					</div>
					<div>
						<label class="block text-sm font-medium text-slate-700 mb-1">
							{{ app()->getLocale() === 'ar' ? 'الوقت المفضل' : 'Preferred time' }}
						</label>
						<input type="time" name="visit_time" class="mt-1 block w-full border rounded-md p-2" value="{{ old('visit_time', $request->visit_time) }}" required>
					</div>
				</div>

				<div>
					<div class="flex items-center gap-2">
						<input id="needs_overnight" type="checkbox" name="needs_overnight" value="1" @checked(old('needs_overnight', $request->needs_overnight)) class="rounded">
						<label for="needs_overnight" class="text-sm text-slate-700">
							{{ app()->getLocale() === 'ar' ? 'يتطلب مبيت' : 'Requires overnight stay' }}
						</label>
					</div>
					<div class="mt-2">
						<label class="block text-sm text-slate-700 mb-1">
							{{ app()->getLocale() === 'ar' ? 'عدد أيام المبيت' : 'Number of overnight days' }}
						</label>
						<input id="overnight_days" type="number" name="overnight_days" class="mt-1 block w-full border rounded-md p-2" min="1" max="30" value="{{ old('overnight_days', $request->overnight_days) }}" @if(!old('needs_overnight', $request->needs_overnight)) disabled @endif>
						<p class="text-xs text-slate-500 mt-1">
							{{ app()->getLocale() === 'ar' ? 'املأ هذا الحقل فقط إذا كان المبيت مطلوباً' : 'Fill only if overnight is required' }}
						</p>
					</div>
				</div>

				<div>
					<label class="block text-sm font-medium text-slate-700 mb-1">
						{{ app()->getLocale() === 'ar' ? 'الميزانية (اختياري)' : 'Budget (optional)' }}
					</label>
					<input type="number" step="0.01" name="total_price" class="mt-1 block w-full border rounded-md p-2" value="{{ old('total_price', $request->total_price) }}">
				</div>
			</div>

			<div class="flex justify-end gap-3">
				<a href="{{ route('client.nurse-requests.show', $request) }}" class="px-4 py-2 rounded-md border">
					{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
				</a>
				<button type="submit" class="px-4 py-2 rounded-md bg-primary text-white">
					{{ app()->getLocale() === 'ar' ? 'حفظ التغييرات' : 'Save Changes' }}
				</button>
			</div>
		</form>
	</div>
@endsection


