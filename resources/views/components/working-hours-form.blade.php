@php
    $workingHours = $workingHours ?? null;
    $namePrefix = $namePrefix ?? 'working_hours';
@endphp

<div class="working-hours-form">
    <!-- Default Hours Section -->
    <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
        <h5 class="font-semibold text-gray-800 mb-3">{{ app()->getLocale() === 'ar' ? 'الساعات الافتراضية' : 'Default Hours' }}</h5>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ app()->getLocale() === 'ar' ? 'وقت الفتح الافتراضي' : 'Default Opening Time' }}</label>
                <select 
                    name="{{ $namePrefix }}[default_opening_time]" 
                    class="form-control default-opening-time time-select"
                >
                    <option value="">{{ app()->getLocale() === 'ar' ? 'اختر الوقت' : 'Select Time' }}</option>
                    @php
                        $defaultOpening = old($namePrefix . '.default_opening_time', $workingHours?->default_opening_time ? \Carbon\Carbon::parse($workingHours->default_opening_time)->format('H:i') : '');
                    @endphp
                    @for($hour = 0; $hour < 24; $hour++)
                        @foreach(['00', '30'] as $minute)
                            @php
                                $timeValue = sprintf('%02d:%02d', $hour, $minute);
                                $timeDisplay = date('g:i A', strtotime($timeValue));
                            @endphp
                            <option value="{{ $timeValue }}" {{ $defaultOpening === $timeValue ? 'selected' : '' }}>
                                {{ $timeDisplay }}
                            </option>
                        @endforeach
                    @endfor
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ app()->getLocale() === 'ar' ? 'وقت الإغلاق الافتراضي' : 'Default Closing Time' }}</label>
                <select 
                    name="{{ $namePrefix }}[default_closing_time]" 
                    class="form-control default-closing-time time-select"
                >
                    <option value="">{{ app()->getLocale() === 'ar' ? 'اختر الوقت' : 'Select Time' }}</option>
                    @php
                        $defaultClosing = old($namePrefix . '.default_closing_time', $workingHours?->default_closing_time ? \Carbon\Carbon::parse($workingHours->default_closing_time)->format('H:i') : '');
                    @endphp
                    @for($hour = 0; $hour < 24; $hour++)
                        @foreach(['00', '30'] as $minute)
                            @php
                                $timeValue = sprintf('%02d:%02d', $hour, $minute);
                                $timeDisplay = date('g:i A', strtotime($timeValue));
                            @endphp
                            <option value="{{ $timeValue }}" {{ $defaultClosing === $timeValue ? 'selected' : '' }}>
                                {{ $timeDisplay }}
                            </option>
                        @endforeach
                    @endfor
                </select>
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-primary apply-default-to-selected" style="display: none;">
            <i class="bi bi-check-circle me-1"></i>
            {{ app()->getLocale() === 'ar' ? 'تطبيق الافتراضي على الأيام المحددة' : 'Apply Default to Selected Days' }}
        </button>
    </div>

    <!-- Days Section -->
    <div class="days-container">
        @php
            $days = [
                'sunday' => app()->getLocale() === 'ar' ? 'الأحد' : 'Sunday',
                'monday' => app()->getLocale() === 'ar' ? 'الإثنين' : 'Monday',
                'tuesday' => app()->getLocale() === 'ar' ? 'الثلاثاء' : 'Tuesday',
                'wednesday' => app()->getLocale() === 'ar' ? 'الأربعاء' : 'Wednesday',
                'thursday' => app()->getLocale() === 'ar' ? 'الخميس' : 'Thursday',
                'friday' => app()->getLocale() === 'ar' ? 'الجمعة' : 'Friday',
                'saturday' => app()->getLocale() === 'ar' ? 'السبت' : 'Saturday',
            ];
        @endphp

        @foreach($days as $dayKey => $dayName)
            @php
                $status = old($namePrefix . '.' . $dayKey . '_status', $workingHours?->{$dayKey . '_status'} ?? 'default');
                $openingTime = old($namePrefix . '.' . $dayKey . '_opening_time', $workingHours?->{$dayKey . '_opening_time'} ? \Carbon\Carbon::parse($workingHours->{$dayKey . '_opening_time'})->format('H:i') : '');
                $closingTime = old($namePrefix . '.' . $dayKey . '_closing_time', $workingHours?->{$dayKey . '_closing_time'} ? \Carbon\Carbon::parse($workingHours->{$dayKey . '_closing_time'})->format('H:i') : '');
            @endphp

            <div class="day-row mb-3 p-3 border rounded-lg" data-day="{{ $dayKey }}">
                <div class="row align-items-center">
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="form-label fw-bold">{{ $dayName }}</label>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <select 
                            name="{{ $namePrefix }}[{{ $dayKey }}_status]" 
                            class="form-select day-status"
                            data-day="{{ $dayKey }}"
                        >
                            <option value="off" {{ $status === 'off' ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? 'مغلق' : 'Off' }}
                            </option>
                            <option value="default" {{ $status === 'default' ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? 'افتراضي' : 'Default' }}
                            </option>
                            <option value="custom" {{ $status === 'custom' ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? 'مخصص' : 'Custom' }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-7 day-times" style="{{ $status !== 'custom' ? 'display: none;' : '' }}">
                        <div class="row">
                            <div class="col-6">
                                <select 
                                    name="{{ $namePrefix }}[{{ $dayKey }}_opening_time]" 
                                    class="form-control day-opening-time time-select" 
                                    {{ $status !== 'custom' ? 'disabled' : '' }}
                                >
                                    <option value="">{{ app()->getLocale() === 'ar' ? 'وقت الفتح' : 'Opening' }}</option>
                                    @for($hour = 0; $hour < 24; $hour++)
                                        @foreach(['00', '30'] as $minute)
                                            @php
                                                $timeValue = sprintf('%02d:%02d', $hour, $minute);
                                                $timeDisplay = date('g:i A', strtotime($timeValue));
                                            @endphp
                                            <option value="{{ $timeValue }}" {{ $openingTime === $timeValue ? 'selected' : '' }}>
                                                {{ $timeDisplay }}
                                            </option>
                                        @endforeach
                                    @endfor
                                </select>
                            </div>
                            <div class="col-6">
                                <select 
                                    name="{{ $namePrefix }}[{{ $dayKey }}_closing_time]" 
                                    class="form-control day-closing-time time-select" 
                                    {{ $status !== 'custom' ? 'disabled' : '' }}
                                >
                                    <option value="">{{ app()->getLocale() === 'ar' ? 'وقت الإغلاق' : 'Closing' }}</option>
                                    @for($hour = 0; $hour < 24; $hour++)
                                        @foreach(['00', '30'] as $minute)
                                            @php
                                                $timeValue = sprintf('%02d:%02d', $hour, $minute);
                                                $timeDisplay = date('g:i A', strtotime($timeValue));
                                            @endphp
                                            <option value="{{ $timeValue }}" {{ $closingTime === $timeValue ? 'selected' : '' }}>
                                                {{ $timeDisplay }}
                                            </option>
                                        @endforeach
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('styles')
<style>
    .working-hours-form .day-row {
        transition: all 0.2s ease;
    }
    .working-hours-form .day-row:hover {
        background-color: #f8f9fa;
    }
    .working-hours-form .day-status {
        cursor: pointer;
    }
    .working-hours-form .day-times {
        transition: opacity 0.2s ease;
    }
    .working-hours-form .time-select {
        cursor: pointer;
    }
</style>
@endpush

@push('scripts')
<script>
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.working-hours-form');
            if (!form) return;

            // Handle day status changes
            form.querySelectorAll('.day-status').forEach(function(select) {
                select.addEventListener('change', function() {
                    const day = this.dataset.day;
                    const dayRow = form.querySelector(`[data-day="${day}"]`);
                    const dayTimes = dayRow.querySelector('.day-times');
                    const timeSelects = dayTimes.querySelectorAll('select.time-select');
                    
                    if (this.value === 'custom') {
                        dayTimes.style.display = '';
                        timeSelects.forEach(select => select.disabled = false);
                    } else {
                        dayTimes.style.display = 'none';
                        timeSelects.forEach(select => {
                            select.disabled = true;
                            if (this.value === 'off') {
                                select.value = '';
                            }
                        });
                    }
                    
                    updateApplyDefaultButton();
                });
            });

            // Apply default to selected days
            const applyDefaultBtn = form.querySelector('.apply-default-to-selected');
            if (applyDefaultBtn) {
                applyDefaultBtn.addEventListener('click', function() {
                    const defaultOpening = form.querySelector('.default-opening-time').value;
                    const defaultClosing = form.querySelector('.default-closing-time').value;
                    
                    if (!defaultOpening || !defaultClosing) {
                        alert('{{ app()->getLocale() === "ar" ? "يرجى تحديد الساعات الافتراضية أولاً" : "Please set default hours first" }}');
                        return;
                    }
                    
                    form.querySelectorAll('.day-row').forEach(function(dayRow) {
                        const statusSelect = dayRow.querySelector('.day-status');
                        if (statusSelect.value === 'default') {
                            const openingSelect = dayRow.querySelector('.day-opening-time');
                            const closingSelect = dayRow.querySelector('.day-closing-time');
                            
                            // Set default values in the selects (for reference, though they'll use default when saved)
                            if (openingSelect && closingSelect) {
                                openingSelect.value = defaultOpening;
                                closingSelect.value = defaultClosing;
                            }
                        }
                    });
                    
                    alert('{{ app()->getLocale() === "ar" ? "تم تطبيق الساعات الافتراضية على الأيام المحددة" : "Default hours applied to selected days" }}');
                });
            }

            // Update apply default button visibility
            function updateApplyDefaultButton() {
                const hasDefaultDays = Array.from(form.querySelectorAll('.day-status'))
                    .some(select => select.value === 'default');
                
                if (applyDefaultBtn) {
                    applyDefaultBtn.style.display = hasDefaultDays ? 'inline-block' : 'none';
                }
            }

            // Initialize on load
            form.querySelectorAll('.day-status').forEach(function(select) {
                select.dispatchEvent(new Event('change'));
            });
            updateApplyDefaultButton();
        });
    })();
</script>
@endpush

