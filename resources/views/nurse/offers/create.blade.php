@extends('nurse.dashboard')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6 space-y-6">
            <h2 class="text-lg font-semibold">{{ __('Create Nursing Offer') }}</h2>

            @if ($errors->any())
                <div class="p-3 rounded bg-red-50 text-red-700">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('nurse.offers.store') }}" class="space-y-4">
                @csrf

                @php
                    $req = $availableRequests->firstWhere('id', $preselectedRequestId);
                @endphp

                @if(!$req)
                    <div class="text-red-600">Request not found.</div>
                @else

                    <!-- Hidden request ID -->
                    <input type="hidden" name="home_nurse_request_id" value="{{ $req->id }}">

                    <!-- Request info -->
                    <div class="bg-gray-50 p-3 rounded border space-y-2">
                        <p><strong>{{ __('Request ID') }}:</strong> #{{ $req->id }}</p>
                        <p><strong>{{ __('Service') }}:</strong> {{ $req->getTranslatedServiceType() }}</p>
                        <p><strong>{{ __('Client') }}:</strong> {{ $req->client->name }}</p>
                        <p><strong>{{ __('Address') }}:</strong>
                            @if($req->address)
                                {{ $req->address->address }}
                                @if($req->address->area)
                                    , {{ $req->address->area->name }}
                                @endif
                                @if($req->address->area && $req->address->area->city)
                                    , {{ $req->address->area->city->name }}
                                @endif
                            @else
                                {{ __('N/A') }}
                            @endif
                        </p>
                        <p><strong>{{ __('Visits') }}:</strong> {{ $req->visits_count }}</p>
                        <p><strong>{{ app()->getLocale() === 'ar' ? 'تكرار الزيارات' : 'Frequency' }}:</strong>
                            @if($req->visit_frequency === 'custom' && !empty($req->custom_visit_days))
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
                                    $selectedDays = array_map('intval', $req->custom_visit_days);
                                    sort($selectedDays);
                                    $dayNames = array_map(function($dayNum) use ($days) {
                                        return app()->getLocale() === 'ar' ? $days[$dayNum]['ar'] : $days[$dayNum]['en'];
                                    }, $selectedDays);
                                @endphp
                                <span class="font-semibold">{{ app()->getLocale() === 'ar' ? 'أيام محددة' : 'Custom' }}:</span>
                                <span>{{ implode(', ', $dayNames) }}</span>
                            @elseif($req->visit_frequency)
                                @php
                                    $frequencyMap = [
                                        'daily' => app()->getLocale() === 'ar' ? 'يومياً' : 'Daily',
                                        'every_two_days' => app()->getLocale() === 'ar' ? 'كل يومين' : 'Every 2 days',
                                        'weekly' => app()->getLocale() === 'ar' ? 'أسبوعياً' : 'Weekly',
                                    ];
                                @endphp
                                {{ $frequencyMap[$req->visit_frequency] ?? ucfirst(str_replace('_', ' ', $req->visit_frequency)) }}
                            @else
                                {{ app()->getLocale() === 'ar' ? 'زيارة واحدة' : 'Single visit' }}
                            @endif
                        </p>
                        @if($req->visit_time)
                        <p><strong>{{ app()->getLocale() === 'ar' ? 'الوقت المفضل' : 'Preferred Time' }}:</strong> {{ $req->visit_time }}</p>
                        @endif
                        @if($req->preferred_gender)
                            <span class="inline-block mt-2 text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded">
                            {{ __('Preferred:') }} {{ ucfirst($req->preferred_gender) }}
                        </span>
                        @endif
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Notes (optional)') }}</label>
                        <textarea name="notes" rows="3" class="w-full border rounded-md p-2" placeholder="{{ __('Any notes for the client...') }}">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Visit period and count -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'تكرار الزيارات' : 'Visit period' }}</label>
                            <select id="visit_period" name="visit_period" class="w-full border rounded-md p-2" required>
                                @php 
                                    $vp = old('visit_period', $req->visit_frequency);
                                    // Map request frequency to offer period
                                    if ($req->visit_frequency === 'custom') {
                                        $vp = 'custom';
                                    } elseif ($req->visit_frequency === 'weekly') {
                                        $vp = 'weekly';
                                    }
                                @endphp
                                <option value="daily" @selected($vp==='daily')>{{ app()->getLocale() === 'ar' ? 'يومياً' : 'Daily' }}</option>
                                <option value="every_two_days" @selected($vp==='every_two_days')>{{ app()->getLocale() === 'ar' ? 'كل يومين' : 'Every 2 days' }}</option>
                                <option value="weekly" @selected($vp==='weekly')>{{ app()->getLocale() === 'ar' ? 'أسبوعياً' : 'Weekly' }}</option>
                                <option value="custom" @selected($vp==='custom')>{{ app()->getLocale() === 'ar' ? 'مخصص' : 'Custom' }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Visits count') }}</label>
                            <input type="number" min="1" max="60" name="visits_count" id="visits_count" class="w-full border rounded-md p-2" value="{{ old('visits_count', $req->visits_count) }}" required>
                        </div>
                    </div>

                    {{-- Custom Days Selection --}}
                    <div id="custom_days_container" class="{{ ($req->visit_frequency === 'custom' || old('visit_period') === 'custom') ? '' : 'hidden' }}">
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
                                // Pre-select days from request if custom, otherwise use old input
                                $oldDays = old('custom_visit_days', ($req->visit_frequency === 'custom' && !empty($req->custom_visit_days)) ? $req->custom_visit_days : []);
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

					<!-- Visit schedule -->
					<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
						<div>
							<label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Visit start time') }}</label>
							<div class="grid grid-cols-2 gap-2">
								<div>
									<select name="visit_start_time_hour" id="visit_start_time_hour" class="w-full border rounded-md p-2">
										<option value="">{{ app()->getLocale() === 'ar' ? 'ساعة' : 'Hour' }}</option>
										@php
											$oldTime = old('visit_start_time', $req->visit_time);
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
									<select name="visit_start_time_minute" id="visit_start_time_minute" class="w-full border rounded-md p-2">
										<option value="">{{ app()->getLocale() === 'ar' ? 'دقيقة' : 'Minute' }}</option>
										@php
											$selectedMinute = isset($timeParts[1]) ? (int)$timeParts[1] : null;
											// Round to nearest 15-minute interval if not already
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
							<input type="hidden" name="visit_start_time" id="visit_start_time">
						</div>
						<div>
							<label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Visit duration (hours)') }}</label>
							<input type="number" min="1" max="24" step="1" name="visit_duration" class="w-full border rounded-md p-2" value="{{ old('visit_duration') }}">
						</div>
					</div>

                    <!-- Price -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Visit price (EGP)') }}</label>
                            <input type="number" step="0.01" min="0" name="visit_price" id="visit_price" class="w-full border rounded-md p-2" value="{{ old('visit_price') }}" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Total price (EGP)') }}</label>
                            <input type="number" step="0.01" min="0" id="total_price" class="w-full border rounded-md p-2 bg-gray-100" value="0" readonly>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('nurse.offers.index') }}" class="px-4 py-2 rounded-md border">{{ __('Cancel') }}</a>
                        <button type="submit" class="px-4 py-2 rounded-md bg-primary text-white">{{ __('Create Offer') }}</button>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Auto calculate total price and custom days handling -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const visitsInput = document.getElementById('visits_count');
            const priceInput = document.getElementById('visit_price');
            const totalInput = document.getElementById('total_price');
            const visitPeriodSelect = document.getElementById('visit_period');
            const customDaysContainer = document.getElementById('custom_days_container');
            const customDaysCheckboxes = document.querySelectorAll('input[name="custom_visit_days[]"]');
            const errorMessage = document.getElementById('custom_days_error');

            function updateTotal() {
                const visits = parseFloat(visitsInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                totalInput.value = (visits * price).toFixed(2);
            }

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
                const isArabic = {{ app()->getLocale() === 'ar' ? 'true' : 'false' }};

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

            function updateCustomDaysVisibility() {
                if (visitPeriodSelect.value === 'custom') {
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

            // Add event listeners for custom days checkboxes
            customDaysCheckboxes.forEach(cb => {
                cb.addEventListener('change', function(e) {
                    const checkbox = e.target;
                    const selectedDays = getSelectedDays();
                    const isArabic = {{ app()->getLocale() === 'ar' ? 'true' : 'false' }};
                    
                    if (checkbox.checked) {
                        if (selectedDays.length === 7) {
                            checkbox.checked = false;
                            errorMessage.textContent = isArabic 
                                ? 'لا يمكن اختيار جميع أيام الأسبوع'
                                : 'Cannot select all days of the week';
                            errorMessage.classList.remove('hidden');
                            return;
                        }
                        
                        if (hasConsecutiveDays(selectedDays)) {
                            checkbox.checked = false;
                            errorMessage.textContent = isArabic 
                                ? 'لا يمكن اختيار يومين متتاليين'
                                : 'Cannot select two consecutive days';
                            errorMessage.classList.remove('hidden');
                            return;
                        }
                    }
                    
                    validateCustomDays();
                });
            });

            // Form submission validation
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                if (visitPeriodSelect.value === 'custom') {
                    if (!validateCustomDays()) {
                        e.preventDefault();
                        return false;
                    }
                }
            });

            visitsInput.addEventListener('input', updateTotal);
            priceInput.addEventListener('input', updateTotal);
            visitPeriodSelect.addEventListener('change', updateCustomDaysVisibility);

            updateTotal(); // initial calculation
            updateCustomDaysVisibility(); // initial visibility

            // Handle time input combination
            const visitStartTimeHour = document.getElementById('visit_start_time_hour');
            const visitStartTimeMinute = document.getElementById('visit_start_time_minute');
            const visitStartTimeHidden = document.getElementById('visit_start_time');

            function updateVisitStartTime() {
                const hour = visitStartTimeHour.value;
                const minute = visitStartTimeMinute.value;
                if (hour && minute) {
                    visitStartTimeHidden.value = hour + ':' + minute;
                } else {
                    visitStartTimeHidden.value = '';
                }
            }

            visitStartTimeHour.addEventListener('change', updateVisitStartTime);
            visitStartTimeMinute.addEventListener('change', updateVisitStartTime);
            
            // Initialize on page load
            updateVisitStartTime();
        });
    </script>
@endsection
