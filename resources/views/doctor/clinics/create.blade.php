@extends('doctor.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'إضافة عيادة' : 'Create Clinic')
@section('page-title', app()->getLocale() === 'ar' ? 'إضافة عيادة' : 'Create Clinic')
@section('page-description', app()->getLocale() === 'ar' ? 'أدخل بيانات العيادة ومواعيد العمل' : 'Enter clinic details and working hours')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>#locationMap { height: 300px; width: 100%; border-radius: 0.5rem; border: 1px solid #e2e8f0; }</style>
@endpush

@section('content')
<div class="max-w-4xl">
    <form action="{{ route('doctor.clinics.store') }}" method="POST">
        @csrf
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ app()->getLocale() === 'ar' ? 'معلومات العيادة' : 'Clinic Information' }}</h3>
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'اسم العيادة' : 'Clinic Name' }} <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500" placeholder="e.g. Main Clinic">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="address" class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}</label>
                    <textarea name="address" id="address" rows="2" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500">{{ old('address') }}</textarea>
                    @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone_number" class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'رقم الهاتف' : 'Phone number' }}</label>
                    <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number') }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500" placeholder="e.g. 01234567890">
                    @error('phone_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="governorate_id" class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'المحافظة' : 'Governorate' }}</label>
                        <select name="governorate_id" id="governorate_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر المحافظة' : 'Select' }}</option>
                            @foreach($governorates as $g)
                                <option value="{{ $g->id }}" {{ old('governorate_id') == $g->id ? 'selected' : '' }}>{{ $g->name_ar ?? $g->name }}</option>
                            @endforeach
                        </select>
                        @error('governorate_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="city_id" class="block text-sm font-medium text-slate-700">{{ app()->getLocale() === 'ar' ? 'المدينة' : 'City' }}</label>
                            <button type="button" id="toggle-add-city" class="text-xs font-medium text-teal-600 hover:text-teal-700">+ {{ app()->getLocale() === 'ar' ? 'إضافة مدينة' : 'Add city' }}</button>
                        </div>
                        <select name="city_id" id="city_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر المدينة' : 'Select' }}</option>
                            @foreach($cities as $c)
                                <option value="{{ $c->id }}" {{ old('city_id') == $c->id ? 'selected' : '' }}>{{ $c->name_ar ?? $c->name }}</option>
                            @endforeach
                        </select>
                        <div id="add-city-box" class="hidden mt-2 p-3 bg-slate-50 border border-slate-200 rounded-lg space-y-2">
                            <input type="text" id="new_city_name" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500" placeholder="{{ app()->getLocale() === 'ar' ? 'اسم المدينة' : 'City name' }}">
                            <div class="flex items-center gap-2">
                                <button type="button" id="save-new-city" class="px-3 py-1.5 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-xs font-medium">{{ app()->getLocale() === 'ar' ? 'حفظ' : 'Save' }}</button>
                                <button type="button" id="cancel-new-city" class="px-3 py-1.5 text-slate-600 border border-slate-300 bg-white rounded-lg hover:bg-slate-50 text-xs">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</button>
                            </div>
                            <p id="new-city-msg" class="text-xs hidden"></p>
                        </div>
                        @error('city_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="area_id" class="block text-sm font-medium text-slate-700">{{ app()->getLocale() === 'ar' ? 'المنطقة' : 'Area' }}</label>
                            <button type="button" id="toggle-add-area" class="text-xs font-medium text-teal-600 hover:text-teal-700">+ {{ app()->getLocale() === 'ar' ? 'إضافة منطقة' : 'Add area' }}</button>
                        </div>
                        <select name="area_id" id="area_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر المنطقة' : 'Select' }}</option>
                            @foreach($areas as $a)
                                <option value="{{ $a->id }}" {{ old('area_id') == $a->id ? 'selected' : '' }}>{{ $a->name_ar ?? $a->name }}</option>
                            @endforeach
                        </select>
                        <div id="add-area-box" class="hidden mt-2 p-3 bg-slate-50 border border-slate-200 rounded-lg space-y-2">
                            <input type="text" id="new_area_name" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500" placeholder="{{ app()->getLocale() === 'ar' ? 'اسم المنطقة' : 'Area name' }}">
                            <div class="flex items-center gap-2">
                                <button type="button" id="save-new-area" class="px-3 py-1.5 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-xs font-medium">{{ app()->getLocale() === 'ar' ? 'حفظ' : 'Save' }}</button>
                                <button type="button" id="cancel-new-area" class="px-3 py-1.5 text-slate-600 border border-slate-300 bg-white rounded-lg hover:bg-slate-50 text-xs">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</button>
                            </div>
                            <p id="new-area-msg" class="text-xs hidden"></p>
                        </div>
                        @error('area_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'موقع العيادة على الخريطة (اختياري)' : 'Location on map (optional)' }}</label>
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <button type="button" id="use-live-location" class="inline-flex items-center gap-2 px-3 py-2 bg-teal-50 text-teal-700 border border-teal-200 rounded-lg hover:bg-teal-100 text-sm font-medium disabled:opacity-60">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><circle cx="12" cy="12" r="8"/><path stroke-linecap="round" d="M12 1.5v3m0 15v3M1.5 12h3m15 0h3"/></svg>
                            <span>{{ app()->getLocale() === 'ar' ? 'استخدم موقعي الحالي' : 'Use my current location' }}</span>
                        </button>
                        <button type="button" id="clear-location" class="px-3 py-2 text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 text-sm">{{ app()->getLocale() === 'ar' ? 'مسح الموقع' : 'Clear' }}</button>
                        <span id="location-status" class="text-xs text-slate-500"></span>
                    </div>
                    <div id="locationMap"></div>
                    <p class="mt-1 text-xs text-slate-500">{{ app()->getLocale() === 'ar' ? 'انقر على الخريطة أو اسحب العلامة لضبط الموقع بدقة.' : 'Click the map or drag the marker to fine-tune the position.' }}</p>
                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="medical_examination_price" class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'سعر الكشف' : 'Examination price' }} <span class="text-red-500">*</span></label>
                        <input type="number" name="medical_examination_price" id="medical_examination_price" value="{{ old('medical_examination_price', 0) }}" step="0.01" min="0" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                        @error('medical_examination_price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="follow_up_price" class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'سعر المتابعة' : 'Follow-up price' }} <span class="text-red-500">*</span></label>
                        <input type="number" name="follow_up_price" id="follow_up_price" value="{{ old('follow_up_price', 0) }}" step="0.01" min="0" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                        @error('follow_up_price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="slots_per_interval" class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'عدد الحجوزات لكل فاصل (30 دقيقة)' : 'Reservations per 30-min slot' }}</label>
                        <input type="number" name="slots_per_interval" id="slots_per_interval" value="{{ old('slots_per_interval', 1) }}" min="1" max="20" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500" placeholder="1">
                        @error('slots_per_interval')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        <p class="mt-1 text-xs text-slate-500">{{ app()->getLocale() === 'ar' ? 'كم مريض يمكن حجزهم في نفس الـ 30 دقيقة (افتراضي 1)' : 'How many patients per 30-min slot (default 1).' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-2">{{ app()->getLocale() === 'ar' ? 'مواعيد العمل' : 'Working Hours' }}</h3>
            <p class="text-sm text-slate-500 mb-4">{{ app()->getLocale() === 'ar' ? 'حدد المواعيد ثم انقر "تطبيق على كل الأيام" أو عدّل كل يوم' : 'Set hours then click "Apply to all days" or edit each day.' }}</p>
            <div class="mb-4 p-4 bg-slate-50 rounded-lg border border-slate-200 flex flex-wrap items-center gap-2">
                <span class="text-slate-600 text-sm">{{ app()->getLocale() === 'ar' ? 'من' : 'From' }}</span>
                <select id="template-from-hour" class="px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    @for($h = 1; $h <= 12; $h++)<option value="{{ $h }}" {{ $h == 9 ? 'selected' : '' }}>{{ $h }}</option>@endfor
                </select>
                <select id="template-from-ampm" class="px-3 py-2 border border-slate-300 rounded-lg text-sm"><option value="am" selected>AM</option><option value="pm">PM</option></select>
                <span class="text-slate-400">–</span>
                <span class="text-slate-600 text-sm">{{ app()->getLocale() === 'ar' ? 'إلى' : 'To' }}</span>
                <select id="template-to-hour" class="px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    @for($h = 1; $h <= 12; $h++)<option value="{{ $h }}" {{ $h == 5 ? 'selected' : '' }}>{{ $h }}</option>@endfor
                </select>
                <select id="template-to-ampm" class="px-3 py-2 border border-slate-300 rounded-lg text-sm"><option value="am">AM</option><option value="pm" selected>PM</option></select>
                <button type="button" id="apply-to-all-days" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-sm font-medium">{{ app()->getLocale() === 'ar' ? 'تطبيق على كل الأيام' : 'Apply to all days' }}</button>
            </div>
            <div id="working-hours-container" class="space-y-2">
                @php
                    $days = ['saturday' => app()->getLocale() === 'ar' ? 'السبت' : 'Sat', 'sunday' => app()->getLocale() === 'ar' ? 'الأحد' : 'Sun', 'monday' => app()->getLocale() === 'ar' ? 'الإثنين' : 'Mon', 'tuesday' => app()->getLocale() === 'ar' ? 'الثلاثاء' : 'Tue', 'wednesday' => app()->getLocale() === 'ar' ? 'الأربعاء' : 'Wed', 'thursday' => app()->getLocale() === 'ar' ? 'الخميس' : 'Thu', 'friday' => app()->getLocale() === 'ar' ? 'الجمعة' : 'Fri'];
                @endphp
                @foreach($days as $dayKey => $dayLabel)
                    @php $idx = $loop->index; $oldFrom = old("working_hours.{$idx}.from"); $oldTo = old("working_hours.{$idx}.to"); @endphp
                    <div class="working-hour-row flex flex-wrap items-center gap-2 py-2 border-b border-slate-100 last:border-0" data-index="{{ $idx }}">
                        <input type="hidden" name="working_hours[{{ $idx }}][day]" value="{{ $dayKey }}">
                        <span class="w-24 font-medium text-slate-700 text-sm">{{ $dayLabel }}</span>
                        <select class="from-hour px-2 py-1.5 border border-slate-300 rounded-lg text-sm">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'ساعة' : 'Hour' }}</option>
                            @for($h = 1; $h <= 12; $h++)<option value="{{ $h }}">{{ $h }}</option>@endfor
                        </select>
                        <select class="from-ampm px-2 py-1.5 border border-slate-300 rounded-lg text-sm"><option value="am">AM</option><option value="pm">PM</option></select>
                        <span class="text-slate-400">–</span>
                        <select class="to-hour px-2 py-1.5 border border-slate-300 rounded-lg text-sm">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'ساعة' : 'Hour' }}</option>
                            @for($h = 1; $h <= 12; $h++)<option value="{{ $h }}">{{ $h }}</option>@endfor
                        </select>
                        <select class="to-ampm px-2 py-1.5 border border-slate-300 rounded-lg text-sm"><option value="am">AM</option><option value="pm">PM</option></select>
                        <input type="hidden" name="working_hours[{{ $idx }}][from]" class="hidden-from" value="{{ $oldFrom }}">
                        <input type="hidden" name="working_hours[{{ $idx }}][to]" class="hidden-to" value="{{ $oldTo }}">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="working_hours[{{ $idx }}][is_closed]" value="1" class="closed-cb rounded border-slate-300" {{ old("working_hours.{$idx}.is_closed") ? 'checked' : '' }}>
                            <span class="text-sm text-slate-600">{{ app()->getLocale() === 'ar' ? 'مغلق' : 'Closed' }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('doctor.clinics.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</a>
            <button type="submit" class="px-5 py-2.5 bg-teal-600 text-white rounded-xl hover:bg-teal-700 font-medium">{{ app()->getLocale() === 'ar' ? 'حفظ العيادة' : 'Save Clinic' }}</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
$(function() {
    var isRTL = {{ app()->getLocale() === 'ar' ? 'true' : 'false' }};
    var oldGov = {{ json_encode(old('governorate_id')) }};
    var oldCity = {{ json_encode(old('city_id')) }};
    var oldArea = {{ json_encode(old('area_id')) }};
    $('#governorate_id').on('change', function() {
        var gid = $(this).val();
        var $city = $('#city_id'); var $area = $('#area_id');
        $city.empty().append('<option value="">{{ app()->getLocale() === "ar" ? "اختر المدينة" : "Select City" }}</option>');
        $area.empty().append('<option value="">{{ app()->getLocale() === "ar" ? "اختر المنطقة" : "Select Area" }}</option>');
        if (gid) {
            $.get('/api/cities', { governorate_id: gid }, function(r) {
                if (r.data) r.data.forEach(function(c) {
                    $city.append($('<option></option>').attr('value', c.id).text(isRTL ? (c.name_ar || c.name) : (c.name || c.name_ar)).prop('selected', c.id == oldCity));
                });
                if (oldCity) $city.trigger('change');
            });
        }
    });
    $('#city_id').on('change', function() {
        var cid = $(this).val();
        var $area = $('#area_id');
        $area.empty().append('<option value="">{{ app()->getLocale() === "ar" ? "اختر المنطقة" : "Select Area" }}</option>');
        if (cid) {
            $.get('/api/areas', { city_id: cid }, function(r) {
                if (r.success && r.data) r.data.forEach(function(a) {
                    $area.append($('<option></option>').attr('value', a.id).text(isRTL ? (a.name_ar || a.name) : (a.name || a.name_ar)).prop('selected', a.id == oldArea));
                });
            });
        }
    });
    if (oldGov) $('#governorate_id').trigger('change');

    // ---- Quick-add city / area -------------------------------------------------
    var T = {
        pickGov: isRTL ? 'اختر المحافظة أولاً' : 'Select a governorate first.',
        pickCity: isRTL ? 'اختر المدينة أولاً' : 'Select a city first.',
        needName: isRTL ? 'أدخل الاسم' : 'Enter a name.',
        saving: isRTL ? 'جارٍ الحفظ…' : 'Saving…',
        failed: isRTL ? 'تعذر الحفظ، حاول مرة أخرى' : 'Could not save, please try again.',
        added: isRTL ? 'تمت الإضافة' : 'Added.',
        locating: isRTL ? 'جارٍ تحديد موقعك…' : 'Locating you…',
        located: isRTL ? 'تم تحديد الموقع' : 'Location set',
        noGeo: isRTL ? 'المتصفح لا يدعم تحديد الموقع' : 'Geolocation is not supported by this browser.',
        denied: isRTL ? 'تم رفض إذن الموقع. فعّله من إعدادات المتصفح أو حدد الموقع يدويًا على الخريطة.' : 'Location permission denied. Allow it in your browser or pick the spot on the map.',
        geoFail: isRTL ? 'تعذر تحديد الموقع، حدده يدويًا على الخريطة.' : 'Could not get your location, pick it on the map instead.',
        cleared: isRTL ? 'لم يتم تحديد موقع' : 'No location set'
    };
    function showMsg($el, text, ok) {
        $el.removeClass('hidden text-red-600 text-teal-700').addClass(ok ? 'text-teal-700' : 'text-red-600').text(text);
    }
    function labelOf(row) { return isRTL ? (row.name_ar || row.name) : (row.name || row.name_ar); }

    $('#toggle-add-city').on('click', function() {
        if (!$('#governorate_id').val()) { alert(T.pickGov); return; }
        $('#add-city-box').toggleClass('hidden');
        $('#new_city_name').focus();
    });
    $('#cancel-new-city').on('click', function() {
        $('#add-city-box').addClass('hidden');
        $('#new_city_name').val('');
        $('#new-city-msg').addClass('hidden');
    });
    $('#save-new-city').on('click', function() {
        var $btn = $(this), $msg = $('#new-city-msg');
        var gid = $('#governorate_id').val(), name = $.trim($('#new_city_name').val());
        if (!gid) { showMsg($msg, T.pickGov, false); return; }
        if (!name) { showMsg($msg, T.needName, false); return; }
        $btn.prop('disabled', true);
        showMsg($msg, T.saving, true);
        $.post('{{ route('doctor.clinics.quick-city') }}', { _token: '{{ csrf_token() }}', governorate_id: gid, name: name })
            .done(function(r) {
                var c = r.data;
                if (!$('#city_id option[value="' + c.id + '"]').length) {
                    $('#city_id').append($('<option></option>').attr('value', c.id).text(labelOf(c)));
                }
                $('#city_id').val(c.id).trigger('change');
                $('#new_city_name').val('');
                $('#add-city-box').addClass('hidden');
                $msg.addClass('hidden');
            })
            .fail(function(x) {
                var m = (x.responseJSON && x.responseJSON.message) || T.failed;
                showMsg($msg, m, false);
            })
            .always(function() { $btn.prop('disabled', false); });
    });

    $('#toggle-add-area').on('click', function() {
        if (!$('#city_id').val()) { alert(T.pickCity); return; }
        $('#add-area-box').toggleClass('hidden');
        $('#new_area_name').focus();
    });
    $('#cancel-new-area').on('click', function() {
        $('#add-area-box').addClass('hidden');
        $('#new_area_name').val('');
        $('#new-area-msg').addClass('hidden');
    });
    $('#save-new-area').on('click', function() {
        var $btn = $(this), $msg = $('#new-area-msg');
        var cid = $('#city_id').val(), name = $.trim($('#new_area_name').val());
        if (!cid) { showMsg($msg, T.pickCity, false); return; }
        if (!name) { showMsg($msg, T.needName, false); return; }
        $btn.prop('disabled', true);
        showMsg($msg, T.saving, true);
        $.post('{{ route('doctor.clinics.quick-area') }}', { _token: '{{ csrf_token() }}', city_id: cid, name: name })
            .done(function(r) {
                var a = r.data;
                if (!$('#area_id option[value="' + a.id + '"]').length) {
                    $('#area_id').append($('<option></option>').attr('value', a.id).text(labelOf(a)));
                }
                $('#area_id').val(a.id);
                $('#new_area_name').val('');
                $('#add-area-box').addClass('hidden');
                $msg.addClass('hidden');
            })
            .fail(function(x) {
                var m = (x.responseJSON && x.responseJSON.message) || T.failed;
                showMsg($msg, m, false);
            })
            .always(function() { $btn.prop('disabled', false); });
    });

    // ---- Map + live location ---------------------------------------------------
    var hasCoords = !!($('#latitude').val() && $('#longitude').val());
    var lat = parseFloat($('#latitude').val()) || 30.0444, lng = parseFloat($('#longitude').val()) || 31.2357;
    var map = L.map('locationMap').setView([lat, lng], hasCoords ? 16 : 13);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap', maxZoom: 19 }).addTo(map);
    var marker = L.marker([lat, lng], { draggable: true }).addTo(map);
    var accuracyCircle = null;
    var $status = $('#location-status');
    function setStatus(text, tone) {
        $status.removeClass('text-slate-500 text-red-600 text-teal-600')
            .addClass(tone === 'error' ? 'text-red-600' : (tone === 'ok' ? 'text-teal-600' : 'text-slate-500'))
            .text(text);
    }
    function updateLoc(la, ln) {
        marker.setLatLng([la, ln]);
        $('#latitude').val(la.toFixed(8));
        $('#longitude').val(ln.toFixed(8));
        setStatus(T.located + ': ' + la.toFixed(5) + ', ' + ln.toFixed(5), 'ok');
    }
    if (hasCoords) { setStatus(T.located + ': ' + lat.toFixed(5) + ', ' + lng.toFixed(5), 'ok'); }
    marker.on('dragend', function(e) {
        if (accuracyCircle) { map.removeLayer(accuracyCircle); accuracyCircle = null; }
        var p = marker.getLatLng();
        updateLoc(p.lat, p.lng);
    });
    map.on('click', function(e) {
        if (accuracyCircle) { map.removeLayer(accuracyCircle); accuracyCircle = null; }
        updateLoc(e.latlng.lat, e.latlng.lng);
    });

    // Fill the address field from the picked coordinates when it is still empty.
    function fillAddressFrom(la, ln) {
        var $address = $('#address');
        if ($.trim($address.val())) return;
        $.ajax({
            url: 'https://nominatim.openstreetmap.org/reverse',
            data: { format: 'jsonv2', lat: la, lon: ln, 'accept-language': isRTL ? 'ar' : 'en' },
            dataType: 'json'
        }).done(function(r) {
            if (r && r.display_name && !$.trim($address.val())) $address.val(r.display_name);
        });
    }

    $('#use-live-location').on('click', function() {
        var $btn = $(this);
        if (!navigator.geolocation) { setStatus(T.noGeo, 'error'); return; }
        $btn.prop('disabled', true);
        setStatus(T.locating, null);
        navigator.geolocation.getCurrentPosition(function(pos) {
            $btn.prop('disabled', false);
            var la = pos.coords.latitude, ln = pos.coords.longitude;
            updateLoc(la, ln);
            map.setView([la, ln], 17);
            if (accuracyCircle) map.removeLayer(accuracyCircle);
            accuracyCircle = L.circle([la, ln], {
                radius: Math.max(pos.coords.accuracy || 0, 15),
                color: '#0d9488', fillColor: '#0d9488', fillOpacity: 0.12, weight: 1
            }).addTo(map);
            fillAddressFrom(la, ln);
        }, function(err) {
            $btn.prop('disabled', false);
            setStatus(err && err.code === 1 ? T.denied : T.geoFail, 'error');
        }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
    });

    $('#clear-location').on('click', function() {
        $('#latitude').val('');
        $('#longitude').val('');
        if (accuracyCircle) { map.removeLayer(accuracyCircle); accuracyCircle = null; }
        setStatus(T.cleared, null);
    });

    function hourAmPmTo24(hour, ampm) {
        if (!hour || hour === '') return '';
        var h = parseInt(hour, 10);
        if (ampm === 'pm') { h = h === 12 ? 12 : h + 12; } else { h = h === 12 ? 0 : h; }
        return ('0' + h).slice(-2) + ':00';
    }
    function updateRowHidden(row) {
        var closed = row.find('.closed-cb').prop('checked');
        var $hiddenFrom = row.find('.hidden-from');
        var $hiddenTo = row.find('.hidden-to');
        if (closed) { $hiddenFrom.val(''); $hiddenTo.val(''); return; }
        var fromH = row.find('.from-hour').val(), fromA = row.find('.from-ampm').val();
        var toH = row.find('.to-hour').val(), toA = row.find('.to-ampm').val();
        $hiddenFrom.val(hourAmPmTo24(fromH, fromA));
        $hiddenTo.val(hourAmPmTo24(toH, toA));
    }
    $('#apply-to-all-days').on('click', function() {
        var fromH = $('#template-from-hour').val(), fromA = $('#template-from-ampm').val();
        var toH = $('#template-to-hour').val(), toA = $('#template-to-ampm').val();
        $('#working-hours-container .working-hour-row').each(function() {
            var $row = $(this);
            $row.find('.closed-cb').prop('checked', false);
            $row.find('.from-hour').val(fromH); $row.find('.from-ampm').val(fromA);
            $row.find('.to-hour').val(toH); $row.find('.to-ampm').val(toA);
            updateRowHidden($row);
        });
    });
    $('#working-hours-container').on('change', '.from-hour, .from-ampm, .to-hour, .to-ampm, .closed-cb', function() {
        updateRowHidden($(this).closest('.working-hour-row'));
    });
});
</script>
@endpush
