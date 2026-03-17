@extends('client.layouts.dashboard')

@section('title', __('Edit Nursing Request'))
@section('page-title', __('Edit Nursing Request'))

@section('content')
	<div class="max-w-3xl mx-auto">
		<form method="POST" action="{{ route('client.nurse-requests.update', $request) }}" class="space-y-6">
			@csrf
			@method('PUT')

			<div class="bg-white rounded-lg shadow p-6 space-y-6">
				<div>
					<label class="block text-sm font-medium text-slate-700 mb-1">
						{{ __('Service type') }}
					</label>
					<select name="service_type" class="mt-1 block w-full border rounded-md p-2" required>
						<option value="">{{ __('Select service type') }}</option>
						@foreach($serviceTypesWithTranslations as $serviceType)
							<option value="{{ $serviceType['value'] }}" @selected(old('service_type', $request->service_type) === $serviceType['value'])>
								{{ $serviceType['label'] }}
							</option>
						@endforeach
					</select>
				</div>

				<div>
					<label class="block text-sm font-medium text-slate-700 mb-1">
						{{ __('Medical notes (optional)') }}
					</label>
					<textarea name="medical_notes" rows="3" class="mt-1 block w-full border rounded-md p-2">{{ old('medical_notes', $request->medical_notes) }}</textarea>
				</div>

				<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
					<div>
						<label class="block text-sm font-medium text-slate-700 mb-1">
							{{ __('Patient age (optional)') }}
						</label>
						<input type="number" name="patient_age" min="0" max="150" class="mt-1 block w-full border rounded-md p-2"
							   value="{{ old('patient_age', $request->patient_age) }}">
					</div>
					<div>
						<label class="block text-sm font-medium text-slate-700 mb-1">
							{{ __('Medical condition (optional)') }}
						</label>
						<input type="text" name="medical_condition" class="mt-1 block w-full border rounded-md p-2"
							   value="{{ old('medical_condition', $request->medical_condition) }}">
					</div>
				</div>

				<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
					<div>
						<label class="block text-sm font-medium text-slate-700 mb-1">
							{{ __('Select address (optional)') }}
						</label>
						<select name="address_id" class="mt-1 block w-full border rounded-md p-2">
							<option value="">
								{{ __('Choose an address (optional)') }}
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
								{{ __('Visits count') }}
							</label>
							<input type="number" id="visits_count" name="visits_count" min="1" max="60" class="mt-1 block w-full border rounded-md p-2" value="{{ old('visits_count', $request->visits_count) }}">
						</div>
						<div>
							<label class="block text-sm font-medium text-slate-700 mb-1">
								{{ __('Frequency') }}
							</label>
							<select id="visit_frequency" name="visit_frequency" class="mt-1 block w-full border rounded-md p-2">
								@php $freq = old('visit_frequency', $request->visit_frequency); @endphp
								<option value="daily" @selected($freq==='daily')>{{ __('Daily') }}</option>
								<option value="every_two_days" @selected($freq==='every_two_days')>{{ __('Every 2 days') }}</option>
								<option value="weekly" @selected($freq==='weekly')>{{ __('Weekly') }}</option>
								<option value="custom" @selected($freq==='custom')>{{ __('Custom') }}</option>
							</select>
						</div>
					</div>
				</div>

				{{-- Custom Days Selection --}}
				<div id="custom_days_container" class="{{ ($freq === 'custom' && $request->visits_count > 1) ? '' : 'hidden' }}">
					<label class="block text-sm font-medium text-slate-700 mb-2">
						{{ __('Select days of the week') }}
					</label>
					<div class="grid grid-cols-2 md:grid-cols-4 gap-3">
						@php
							$dayKeys = [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];
							$oldDays = old('custom_visit_days', $request->custom_visit_days ?? []);
						@endphp
						@foreach($dayKeys as $dayNum => $dayKey)
							<label class="flex items-center gap-2 p-3 border rounded-md cursor-pointer hover:bg-slate-50">
								<input type="checkbox" name="custom_visit_days[]" value="{{ $dayNum }}"
									   class="rounded"
									   @checked(in_array($dayNum, $oldDays))>
								<span class="text-sm text-slate-700">{{ __($dayKey) }}</span>
							</label>
						@endforeach
					</div>
					<div id="custom_days_error" class="text-red-600 text-sm mt-2 hidden"></div>
				</div>

				<div>
					<label class="block text-sm font-medium text-slate-700 mb-1">
						{{ __('Preferred nurse gender (optional)') }}
					</label>
					@php $pg = old('preferred_gender', $request->preferred_gender); @endphp
					<select name="preferred_gender" class="mt-1 block w-full border rounded-md p-2">
						<option value="">
							{{ __('No preference') }}
						</option>
						<option value="male" @selected($pg==='male')>
							{{ __('Male') }}
						</option>
						<option value="female" @selected($pg==='female')>
							{{ __('Female') }}
						</option>
					</select>
				</div>

				<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
					<div>
						<label class="block text-sm font-medium text-slate-700 mb-1">
							{{ __('Start date') }}
						</label>
						<input type="date" name="visit_start_date" class="mt-1 block w-full border rounded-md p-2" value="{{ old('visit_start_date', optional($request->visit_start_date)->format('Y-m-d')) }}" required>
					</div>
					<div>
						<label class="block text-sm font-medium text-slate-700 mb-1">
							{{ __('Preferred time') }}
						</label>
						<div class="grid grid-cols-2 gap-2">
							<div>
								<select name="visit_time_hour" id="visit_time_hour" class="mt-1 block w-full border rounded-md p-2" required>
									<option value="">{{ __('Hour') }}</option>
									@php
										$oldTime = old('visit_time', $request->visit_time);
										$timeParts = $oldTime ? explode(':', $oldTime) : [null, null];
										$selectedHour = isset($timeParts[0]) ? (int)$timeParts[0] : null;
									@endphp
									@for($h = 0; $h < 24; $h++)
										<option value="{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}" @selected($selectedHour === $h)>
											{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}
										</option>
									@endfor
								</select>
							</div>
							<div>
								<select name="visit_time_minute" id="visit_time_minute" class="mt-1 block w-full border rounded-md p-2" required>
									<option value="">{{ __('Minute') }}</option>
									@php
										$selectedMinute = isset($timeParts[1]) ? (int)$timeParts[1] : null;
										if ($selectedMinute !== null) {
											$selectedMinute = round($selectedMinute / 15) * 15;
											if ($selectedMinute === 60) $selectedMinute = 0;
										}
									@endphp
									<option value="00" @selected($selectedMinute === 0 || $selectedMinute === null)>00</option>
									<option value="15" @selected($selectedMinute === 15)>15</option>
									<option value="30" @selected($selectedMinute === 30)>30</option>
									<option value="45" @selected($selectedMinute === 45)>45</option>
								</select>
							</div>
						</div>
						<input type="hidden" name="visit_time" id="visit_time" required>
					</div>
				</div>

				<div>
					<div class="flex items-center gap-2">
						<input id="needs_overnight" type="checkbox" name="needs_overnight" value="1" @checked(old('needs_overnight', $request->needs_overnight)) class="rounded">
						<label for="needs_overnight" class="text-sm text-slate-700">
							{{ __('Requires overnight stay') }}
						</label>
					</div>
					<div class="mt-2">
						<label class="block text-sm text-slate-700 mb-1">
							{{ __('Number of overnight days') }}
						</label>
						<input id="overnight_days" type="number" name="overnight_days" class="mt-1 block w-full border rounded-md p-2" min="1" max="30" value="{{ old('overnight_days', $request->overnight_days) }}" @if(!old('needs_overnight', $request->needs_overnight)) disabled @endif>
						<p class="text-xs text-slate-500 mt-1">
							{{ __('Fill only if overnight is required') }}
						</p>
					</div>
				</div>

				<div>
					<label class="block text-sm font-medium text-slate-700 mb-1">
						{{ __('Budget (optional)') }}
					</label>
					<input type="number" step="0.01" name="total_price" class="mt-1 block w-full border rounded-md p-2" value="{{ old('total_price', $request->total_price) }}">
				</div>
			</div>

			<div class="flex justify-end gap-3">
				<a href="{{ route('client.nurse-requests.show', $request) }}" class="px-4 py-2 rounded-md border">
					{{ __('Cancel') }}
				</a>
				<button type="submit" class="px-4 py-2 rounded-md bg-green-600 text-white hover:bg-green-700">
					{{ __('Save Changes') }}
				</button>
			</div>
		</form>
	</div>

	@push('scripts')
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const nurseReqI18n = {
				selectAtLeastOne: @json(__('Please select at least one day')),
				cannotSelectAll: @json(__('Cannot select all days of the week')),
				cannotConsecutive: @json(__('Cannot select two consecutive days')),
			};
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

				for (let i = 0; i < days.length; i++) {
					const current = days[i];
					const next = days[(i + 1) % days.length];
					const diff = (next - current + 7) % 7;

					if (diff === 1) return true;
					if (current === 6 && next === 0) return true;
				}
				return false;
			}

			function validateCustomDays() {
				const selectedDays = getSelectedDays();

				errorMessage.classList.add('hidden');
				errorMessage.textContent = '';

				if (selectedDays.length === 0) {
					errorMessage.textContent = nurseReqI18n.selectAtLeastOne;
					errorMessage.classList.remove('hidden');
					return false;
				}

				if (selectedDays.length === 7) {
					errorMessage.textContent = nurseReqI18n.cannotSelectAll;
					errorMessage.classList.remove('hidden');
					return false;
				}

				if (hasConsecutiveDays(selectedDays)) {
					errorMessage.textContent = nurseReqI18n.cannotConsecutive;
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

			customDaysCheckboxes.forEach(cb => {
				cb.addEventListener('change', function(e) {
					const checkbox = e.target;
					const currentChecked = checkbox.checked;

					checkbox.checked = currentChecked;
					const selectedDays = getSelectedDays();

					if (currentChecked) {
						if (selectedDays.length === 7) {
							checkbox.checked = false;
							errorMessage.textContent = nurseReqI18n.cannotSelectAll;
							errorMessage.classList.remove('hidden');
							return;
						}

						if (hasConsecutiveDays(selectedDays)) {
							checkbox.checked = false;
							errorMessage.textContent = nurseReqI18n.cannotConsecutive;
							errorMessage.classList.remove('hidden');
							return;
						}
					}

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

			updateFrequencyField();

			const visitTimeHour = document.getElementById('visit_time_hour');
			const visitTimeMinute = document.getElementById('visit_time_minute');
			const visitTimeHidden = document.getElementById('visit_time');

			function updateVisitTime() {
				const hour = visitTimeHour.value;
				const minute = visitTimeMinute.value;
				if (hour && minute) {
					visitTimeHidden.value = hour + ':' + minute + ':00';
				} else {
					visitTimeHidden.value = '';
				}
			}

			visitTimeHour.addEventListener('change', updateVisitTime);
			visitTimeMinute.addEventListener('change', updateVisitTime);

			updateVisitTime();
		});
	</script>
	@endpush
@endsection
