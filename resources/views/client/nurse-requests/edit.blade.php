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
					<select name="service_type" class="mt-1 block w-full border rounded-md p-2" required>
						<option value="">{{ app()->getLocale() === 'ar' ? 'اختر نوع الخدمة' : 'Select service type' }}</option>
						@foreach($serviceTypesWithTranslations as $serviceType)
							<option value="{{ $serviceType['value'] }}" @selected(old('service_type', $request->service_type) === $serviceType['value'])>
								{{ $serviceType['label'] }}
							</option>
						@endforeach
					</select>
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
							{{ app()->getLocale() === 'ar' ? 'عمر المريض (اختياري)' : 'Patient age (optional)' }}
						</label>
						<input type="number" name="patient_age" min="0" max="150" class="mt-1 block w-full border rounded-md p-2"
							   value="{{ old('patient_age', $request->patient_age) }}">
					</div>
					<div>
						<label class="block text-sm font-medium text-slate-700 mb-1">
							{{ app()->getLocale() === 'ar' ? 'الحالة الطبية (اختياري)' : 'Medical condition (optional)' }}
						</label>
						<input type="text" name="medical_condition" class="mt-1 block w-full border rounded-md p-2"
							   value="{{ old('medical_condition', $request->medical_condition) }}">
					</div>
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
							<input type="number" id="visits_count" name="visits_count" min="1" max="60" class="mt-1 block w-full border rounded-md p-2" value="{{ old('visits_count', $request->visits_count) }}">
						</div>
						<div>
							<label class="block text-sm font-medium text-slate-700 mb-1">
								{{ app()->getLocale() === 'ar' ? 'تكرار الزيارات' : 'Frequency' }}
							</label>
							<select id="visit_frequency" name="visit_frequency" class="mt-1 block w-full border rounded-md p-2">
								@php $freq = old('visit_frequency', $request->visit_frequency); @endphp
								<option value="daily" @selected($freq==='daily')>{{ app()->getLocale() === 'ar' ? 'يومياً' : 'Daily' }}</option>
								<option value="every_two_days" @selected($freq==='every_two_days')>{{ app()->getLocale() === 'ar' ? 'كل يومين' : 'Every 2 days' }}</option>
								<option value="weekly" @selected($freq==='weekly')>{{ app()->getLocale() === 'ar' ? 'أسبوعياً' : 'Weekly' }}</option>
								<option value="custom" @selected($freq==='custom')>{{ app()->getLocale() === 'ar' ? 'مخصص' : 'Custom' }}</option>
							</select>
						</div>
					</div>
				</div>

				{{-- Custom Days Selection --}}
				<div id="custom_days_container" class="{{ ($freq === 'custom' && $request->visits_count > 1) ? '' : 'hidden' }}">
					<label class="block text-sm font-medium text-slate-700 mb-2">
						{{ app()->getLocale() === 'ar' ? 'اختر أيام الأسبوع' : 'Select days of the week' }}
					</label>
					<div class="grid grid-cols-2 md:grid-cols-4 gap-3">
						@php
							$days = [
								0 => ['en' => 'Sunday', 'ar' => 'الأحد'],
								1 => ['en' => 'Monday', 'ar' => 'الإثنين'],
								2 => ['en' => 'Tuesday', 'ar' => 'الثلاثاء'],
								3 => ['en' => 'Wednesday', 'ar' => 'الأربعاء'],
								4 => ['en' => 'Thursday', 'ar' => 'الخميس'],
								5 => ['en' => 'Friday', 'ar' => 'الجمعة'],
								6 => ['en' => 'Saturday', 'ar' => 'السبت'],
							];
							$oldDays = old('custom_visit_days', $request->custom_visit_days ?? []);
						@endphp
						@foreach($days as $dayNum => $dayNames)
							<label class="flex items-center gap-2 p-3 border rounded-md cursor-pointer hover:bg-slate-50">
								<input type="checkbox" name="custom_visit_days[]" value="{{ $dayNum }}" 
									   class="rounded" 
									   @checked(in_array($dayNum, $oldDays))>
								<span class="text-sm text-slate-700">{{ app()->getLocale() === 'ar' ? $dayNames['ar'] : $dayNames['en'] }}</span>
							</label>
						@endforeach
					</div>
					<div id="custom_days_error" class="text-red-600 text-sm mt-2 hidden"></div>
				</div>

				<div>
					<label class="block text-sm font-medium text-slate-700 mb-1">
						{{ app()->getLocale() === 'ar' ? 'تفضيل نوع الممرض/ـة (اختياري)' : 'Preferred nurse gender (optional)' }}
					</label>
					@php $pg = old('preferred_gender', $request->preferred_gender); @endphp
					<select name="preferred_gender" class="mt-1 block w-full border rounded-md p-2">
						<option value="">
							{{ app()->getLocale() === 'ar' ? 'بدون تفضيل' : 'No preference' }}
						</option>
						<option value="male" @selected($pg==='male')>
							{{ app()->getLocale() === 'ar' ? 'ذكر' : 'Male' }}
						</option>
						<option value="female" @selected($pg==='female')>
							{{ app()->getLocale() === 'ar' ? 'أنثى' : 'Female' }}
						</option>
					</select>
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

	@push('scripts')
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const visitsCountInput = document.getElementById('visits_count');
			const visitFrequencySelect = document.getElementById('visit_frequency');
			const customDaysContainer = document.getElementById('custom_days_container');
			const customDaysCheckboxes = document.querySelectorAll('input[name="custom_visit_days[]"]');
			const errorMessage = document.getElementById('custom_days_error');

			function getSelectedDays() {
				return Array.from(customDaysCheckboxes)
					.filter(cb => cb.checked)
					.map(cb => parseInt(cb.value))
					.sort((a, b) => a - b);
			}

			function hasConsecutiveDays(days) {
				if (days.length < 2) return false;
				
				// Check consecutive days (including wrap-around: 6 and 0)
				for (let i = 0; i < days.length; i++) {
					const current = days[i];
					const next = days[(i + 1) % days.length];
					const diff = (next - current + 7) % 7;
					
					if (diff === 1) return true;
					// Check wrap-around: Saturday (6) and Sunday (0)
					if (current === 6 && next === 0) return true;
				}
				return false;
			}

			function validateCustomDays() {
				const selectedDays = getSelectedDays();
				const isArabic = {{ app()->getLocale() === 'ar' ? 'true' : 'false' }};

				// Clear previous error
				errorMessage.classList.add('hidden');
				errorMessage.textContent = '';

				if (selectedDays.length === 0) {
					errorMessage.textContent = isArabic 
						? 'يجب اختيار يوم واحد على الأقل'
						: 'Please select at least one day';
					errorMessage.classList.remove('hidden');
					return false;
				}

				if (selectedDays.length === 7) {
					errorMessage.textContent = isArabic 
						? 'لا يمكن اختيار جميع أيام الأسبوع'
						: 'Cannot select all days of the week';
					errorMessage.classList.remove('hidden');
					return false;
				}

				if (hasConsecutiveDays(selectedDays)) {
					errorMessage.textContent = isArabic 
						? 'لا يمكن اختيار يومين متتاليين'
						: 'Cannot select two consecutive days';
					errorMessage.classList.remove('hidden');
					return false;
				}

				return true;
			}

			function updateFrequencyField() {
				const visitsCount = parseInt(visitsCountInput.value) || 0;
				
				if (visitsCount === 1) {
					visitFrequencySelect.disabled = true;
					visitFrequencySelect.removeAttribute('required');
					visitFrequencySelect.value = '';
					customDaysContainer.classList.add('hidden');
					customDaysCheckboxes.forEach(cb => cb.removeAttribute('required'));
					errorMessage.classList.add('hidden');
				} else {
					visitFrequencySelect.disabled = false;
					visitFrequencySelect.setAttribute('required', 'required');
					updateCustomDaysVisibility();
				}
			}

			// Add event listeners once
			customDaysCheckboxes.forEach(cb => {
				cb.addEventListener('change', function(e) {
					const checkbox = e.target;
					const currentChecked = checkbox.checked;
					
					// Temporarily toggle to check validation
					checkbox.checked = currentChecked;
					const selectedDays = getSelectedDays();
					
					// Check if selecting this day would create consecutive days
					if (currentChecked) {
						if (selectedDays.length === 7) {
							checkbox.checked = false;
							const isArabic = {{ app()->getLocale() === 'ar' ? 'true' : 'false' }};
							errorMessage.textContent = isArabic 
								? 'لا يمكن اختيار جميع أيام الأسبوع'
								: 'Cannot select all days of the week';
							errorMessage.classList.remove('hidden');
							return;
						}
						
						if (hasConsecutiveDays(selectedDays)) {
							checkbox.checked = false;
							const isArabic = {{ app()->getLocale() === 'ar' ? 'true' : 'false' }};
							errorMessage.textContent = isArabic 
								? 'لا يمكن اختيار يومين متتاليين'
								: 'Cannot select two consecutive days';
							errorMessage.classList.remove('hidden');
							return;
						}
					}
					
					// Validate after change
					validateCustomDays();
				});
			});

			function updateCustomDaysVisibility() {
				if (visitFrequencySelect.value === 'custom') {
					customDaysContainer.classList.remove('hidden');
				} else {
					customDaysContainer.classList.add('hidden');
					customDaysCheckboxes.forEach(cb => {
						cb.removeAttribute('required');
						cb.checked = false;
					});
					errorMessage.classList.add('hidden');
				}
			}

			// Add validation on form submit
			const form = document.querySelector('form');
			form.addEventListener('submit', function(e) {
				if (visitFrequencySelect.value === 'custom' && !visitFrequencySelect.disabled) {
					if (!validateCustomDays()) {
						e.preventDefault();
						return false;
					}
				}
			});

			visitsCountInput.addEventListener('input', updateFrequencyField);
			visitFrequencySelect.addEventListener('change', updateCustomDaysVisibility);

			// Initialize on page load
			updateFrequencyField();
		});
	</script>
	@endpush
@endsection


