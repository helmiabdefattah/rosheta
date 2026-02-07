@extends('doctor.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'تعديل العيادة' : 'Edit Clinic')
@section('page-title', app()->getLocale() === 'ar' ? 'تعديل العيادة' : 'Edit Clinic')
@section('page-description', $clinic->name)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>#locationMap { height: 300px; width: 100%; border-radius: 0.5rem; border: 1px solid #e2e8f0; }</style>
@endpush

@section('content')
<div class="max-w-4xl">
    <form action="{{ route('doctor.clinics.update', $clinic) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ app()->getLocale() === 'ar' ? 'معلومات العيادة' : 'Clinic Information' }}</h3>
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'اسم العيادة' : 'Clinic Name' }} <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $clinic->name) }}" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="address" class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}</label>
                    <textarea name="address" id="address" rows="2" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500">{{ old('address', $clinic->address) }}</textarea>
                    @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone_number" class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'رقم الهاتف' : 'Phone number' }}</label>
                    <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number', $clinic->phone_number) }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                    @error('phone_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="governorate_id" class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'المحافظة' : 'Governorate' }}</label>
                        <select name="governorate_id" id="governorate_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر المحافظة' : 'Select' }}</option>
                            @foreach($governorates as $g)
                                <option value="{{ $g->id }}" {{ old('governorate_id', $clinic->governorate_id) == $g->id ? 'selected' : '' }}>{{ $g->name_ar ?? $g->name }}</option>
                            @endforeach
                        </select>
                        @error('governorate_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="city_id" class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'المدينة' : 'City' }}</label>
                        <select name="city_id" id="city_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر المدينة' : 'Select' }}</option>
                            @foreach($cities as $c)
                                <option value="{{ $c->id }}" {{ old('city_id', $clinic->city_id) == $c->id ? 'selected' : '' }}>{{ $c->name_ar ?? $c->name }}</option>
                            @endforeach
                        </select>
                        @error('city_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="area_id" class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'المنطقة' : 'Area' }}</label>
                        <select name="area_id" id="area_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر المنطقة' : 'Select' }}</option>
                            @foreach($areas as $a)
                                <option value="{{ $a->id }}" {{ old('area_id', $clinic->area_id) == $a->id ? 'selected' : '' }}>{{ $a->name_ar ?? $a->name }}</option>
                            @endforeach
                        </select>
                        @error('area_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'موقع العيادة على الخريطة (اختياري)' : 'Location on map (optional)' }}</label>
                    <div id="locationMap"></div>
                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $clinic->latitude) }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $clinic->longitude) }}">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="medical_examination_price" class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'سعر الكشف' : 'Examination price' }} <span class="text-red-500">*</span></label>
                        <input type="number" name="medical_examination_price" id="medical_examination_price" value="{{ old('medical_examination_price', $clinic->medical_examination_price) }}" step="0.01" min="0" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                        @error('medical_examination_price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="follow_up_price" class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'سعر المتابعة' : 'Follow-up price' }} <span class="text-red-500">*</span></label>
                        <input type="number" name="follow_up_price" id="follow_up_price" value="{{ old('follow_up_price', $clinic->follow_up_price) }}" step="0.01" min="0" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                        @error('follow_up_price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="slots_per_interval" class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'عدد الحجوزات لكل فاصل (30 دقيقة)' : 'Reservations per 30-min slot' }}</label>
                        <input type="number" name="slots_per_interval" id="slots_per_interval" value="{{ old('slots_per_interval', $clinic->getSlotsPerInterval()) }}" min="1" max="20" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                        @error('slots_per_interval')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        <p class="mt-1 text-xs text-slate-500">{{ app()->getLocale() === 'ar' ? 'كم مريض يمكن حجزهم في نفس الـ 30 دقيقة (افتراضي 1)' : 'How many patients per 30-min slot (default 1).' }}</p>
                    </div>
                </div>
            </div>
        </div>

        @php $whByDay = $clinic->workingHours->keyBy('day'); $days = ['saturday' => app()->getLocale() === 'ar' ? 'السبت' : 'Sat', 'sunday' => app()->getLocale() === 'ar' ? 'الأحد' : 'Sun', 'monday' => app()->getLocale() === 'ar' ? 'الإثنين' : 'Mon', 'tuesday' => app()->getLocale() === 'ar' ? 'الثلاثاء' : 'Tue', 'wednesday' => app()->getLocale() === 'ar' ? 'الأربعاء' : 'Wed', 'thursday' => app()->getLocale() === 'ar' ? 'الخميس' : 'Thu', 'friday' => app()->getLocale() === 'ar' ? 'الجمعة' : 'Fri']; @endphp
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
                @foreach($days as $dayKey => $dayLabel)
                    @php
                        $idx = $loop->index;
                        $wh = $whByDay->get($dayKey);
                        $oldFrom = old("working_hours.{$idx}.from", $wh && $wh->from ? (\Carbon\Carbon::parse($wh->from)->format('H:i')) : '');
                        $oldTo = old("working_hours.{$idx}.to", $wh && $wh->to ? (\Carbon\Carbon::parse($wh->to)->format('H:i')) : '');
                        $isClosed = old("working_hours.{$idx}.is_closed", $wh && $wh->is_closed);
                    @endphp
                    <div class="working-hour-row flex flex-wrap items-center gap-2 py-2 border-b border-slate-100 last:border-0" data-index="{{ $idx }}" data-from="{{ $oldFrom }}" data-to="{{ $oldTo }}" data-closed="{{ $isClosed ? '1' : '0' }}">
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
                            <input type="checkbox" name="working_hours[{{ $idx }}][is_closed]" value="1" class="closed-cb rounded border-slate-300" {{ $isClosed ? 'checked' : '' }}>
                            <span class="text-sm text-slate-600">{{ app()->getLocale() === 'ar' ? 'مغلق' : 'Closed' }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('doctor.clinics.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</a>
            <button type="submit" class="px-5 py-2.5 bg-teal-600 text-white rounded-xl hover:bg-teal-700 font-medium">{{ app()->getLocale() === 'ar' ? 'حفظ التغييرات' : 'Save changes' }}</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
$(function() {
    var isRTL = {{ app()->getLocale() === 'ar' ? 'true' : 'false' }};
    var oldGov = {{ json_encode(old('governorate_id', $clinic->governorate_id)) }};
    var oldCity = {{ json_encode(old('city_id', $clinic->city_id)) }};
    var oldArea = {{ json_encode(old('area_id', $clinic->area_id)) }};
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

    var lat = parseFloat($('#latitude').val()) || 30.0444, lng = parseFloat($('#longitude').val()) || 31.2357;
    var map = L.map('locationMap').setView([lat, lng], 13);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap', maxZoom: 19 }).addTo(map);
    var marker = L.marker([lat, lng], { draggable: true }).addTo(map);
    function updateLoc(la, ln) {
        marker.setLatLng([la, ln]);
        $('#latitude').val(la.toFixed(8));
        $('#longitude').val(ln.toFixed(8));
    }
    marker.on('dragend', function(e) { var p = marker.getLatLng(); updateLoc(p.lat, p.lng); });
    map.on('click', function(e) { updateLoc(e.latlng.lat, e.latlng.lng); });

    function parseH24(timeStr) {
        if (!timeStr || timeStr.length < 5) return { hour: null, ampm: 'am' };
        var parts = timeStr.split(':');
        var h = parseInt(parts[0], 10);
        var ampm = h >= 12 ? 'pm' : 'am';
        h = h % 12; if (h === 0) h = 12;
        return { hour: h, ampm: ampm };
    }
    $('#working-hours-container .working-hour-row').each(function() {
        var row = $(this);
        if (row.data('closed') === 1) return;
        var from = parseH24(row.data('from')), to = parseH24(row.data('to'));
        if (from.hour) { row.find('.from-hour').val(from.hour); row.find('.from-ampm').val(from.ampm); }
        if (to.hour) { row.find('.to-hour').val(to.hour); row.find('.to-ampm').val(to.ampm); }
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
    $('#working-hours-container .working-hour-row').each(function() { updateRowHidden($(this)); });
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
