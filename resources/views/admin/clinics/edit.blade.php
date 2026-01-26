@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'تعديل عيادة' : 'Edit Clinic')
@section('page-title', app()->getLocale() === 'ar' ? 'تعديل عيادة' : 'Edit Clinic')
@section('page-description', app()->getLocale() === 'ar' ? 'تعديل بيانات العيادة ومواعيد العمل' : 'Edit clinic details and working hours')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
#locationMap { height: 350px; width: 100%; border-radius: 0.5rem; border: 1px solid #e2e8f0; }
.select2-container--default .select2-selection--multiple { min-height: 42px; border-radius: 0.5rem; border-color: #cbd5e1; }
.select2-container--default .select2-selection--multiple .select2-selection__rendered { min-height: 40px; line-height: 40px; }
.select2-container--default .select2-selection--multiple .select2-selection__choice { background-color: #0d9488; border-color: #0d9488; color: #fff; }
[dir="rtl"] .select2-container--default .select2-selection--multiple .select2-selection__rendered { padding-right: 8px; padding-left: 8px; }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('admin.clinics.update', $clinic) }}" method="POST">
        @csrf
        @method('PUT')

        <x-admin.ui.form-card :title="app()->getLocale() === 'ar' ? 'معلومات العيادة' : 'Clinic Information'">
            <div class="space-y-6">
                <div>
                    <x-admin.ui.label for="doctor_ids" required>{{ app()->getLocale() === 'ar' ? 'الأطباء' : 'Doctors' }}</x-admin.ui.label>
                    <p class="text-sm text-slate-500 mb-2">{{ app()->getLocale() === 'ar' ? 'يمكنك اختيار أكثر من طبيب للعيادة' : 'You can select more than one doctor for this clinic.' }}</p>
                    <select name="doctor_ids[]" id="doctor_ids" multiple class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent min-h-[120px]">
                        @foreach($doctors as $d)
                            @php $selectedIds = old('doctor_ids', $clinic->doctors->isNotEmpty() ? $clinic->doctors->pluck('id')->toArray() : [$clinic->doctor_id]); @endphp
                            <option value="{{ $d->id }}" {{ in_array($d->id, $selectedIds) ? 'selected' : '' }}>{{ $d->name }} ({{ $d->specialization?->name }})</option>
                        @endforeach
                    </select>
                    @error('doctor_ids')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    @error('doctor_ids.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-admin.ui.label for="name" required>{{ app()->getLocale() === 'ar' ? 'اسم العيادة' : 'Clinic Name' }}</x-admin.ui.label>
                    <x-admin.ui.input name="name" :value="old('name', $clinic->name)" required />
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-admin.ui.label for="address">{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}</x-admin.ui.label>
                    <textarea name="address" id="address" rows="2" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('address', $clinic->address) }}</textarea>
                    @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-admin.ui.label for="phone_number">{{ app()->getLocale() === 'ar' ? 'رقم الهاتف' : 'Phone number' }}</x-admin.ui.label>
                    <x-admin.ui.input name="phone_number" :value="old('phone_number', $clinic->phone_number)" placeholder="e.g. 01234567890" />
                    @error('phone_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <x-admin.ui.label for="governorate_id">{{ app()->getLocale() === 'ar' ? 'المحافظة' : 'Governorate' }}</x-admin.ui.label>
                        <select name="governorate_id" id="governorate_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر المحافظة' : 'Select' }}</option>
                            @foreach($governorates as $g)
                                <option value="{{ $g->id }}" {{ old('governorate_id', $clinic->governorate_id) == $g->id ? 'selected' : '' }}>{{ $g->name_ar ?? $g->name }}</option>
                            @endforeach
                        </select>
                        @error('governorate_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <x-admin.ui.label for="city_id">{{ app()->getLocale() === 'ar' ? 'المدينة' : 'City' }}</x-admin.ui.label>
                        <select name="city_id" id="city_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر المدينة' : 'Select' }}</option>
                            @foreach($cities as $c)
                                <option value="{{ $c->id }}" {{ old('city_id', $clinic->city_id) == $c->id ? 'selected' : '' }}>{{ $c->name_ar ?? $c->name }}</option>
                            @endforeach
                        </select>
                        @error('city_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <x-admin.ui.label for="area_id">{{ app()->getLocale() === 'ar' ? 'المنطقة' : 'Area' }}</x-admin.ui.label>
                        <select name="area_id" id="area_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر المنطقة' : 'Select' }}</option>
                            @foreach($areas as $a)
                                <option value="{{ $a->id }}" {{ old('area_id', $clinic->area_id) == $a->id ? 'selected' : '' }}>{{ $a->name_ar ?? $a->name }}</option>
                            @endforeach
                        </select>
                        @error('area_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <x-admin.ui.label>{{ app()->getLocale() === 'ar' ? 'موقع العيادة على الخريطة' : 'Location on Map' }}</x-admin.ui.label>
                    <p class="text-sm text-slate-500 mb-2">{{ app()->getLocale() === 'ar' ? 'انقر على الخريطة أو اسحب العلامة لتحديد الموقع' : 'Click on the map or drag the marker to set location' }}</p>
                    <div id="locationMap"></div>
                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $clinic->latitude) }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $clinic->longitude) }}">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-admin.ui.label for="medical_examination_price" required>{{ app()->getLocale() === 'ar' ? 'سعر الكشف' : 'Examination price' }}</x-admin.ui.label>
                        <x-admin.ui.input type="number" name="medical_examination_price" :value="old('medical_examination_price', $clinic->medical_examination_price)" step="0.01" min="0" required />
                        @error('medical_examination_price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <x-admin.ui.label for="follow_up_price" required>{{ app()->getLocale() === 'ar' ? 'سعر المتابعة' : 'Follow-up price' }}</x-admin.ui.label>
                        <x-admin.ui.input type="number" name="follow_up_price" :value="old('follow_up_price', $clinic->follow_up_price)" step="0.01" min="0" required />
                        @error('follow_up_price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </x-admin.ui.form-card>

        <div class="mt-6">
        <x-admin.ui.form-card :title="app()->getLocale() === 'ar' ? 'مواعيد العمل' : 'Working Hours'">
            <p class="text-sm text-slate-500 mb-3">{{ app()->getLocale() === 'ar' ? 'حدد المواعيد لليوم الأول ثم انسخها لبقية الأيام، أو عدّل كل يوم بعد ذلك' : 'Set hours for the first day, apply to all, then edit any day as needed.' }}</p>

            <div class="mb-6 p-4 bg-slate-50 rounded-xl border border-slate-200">
                <p class="text-sm font-medium text-slate-700 mb-3">{{ app()->getLocale() === 'ar' ? 'تعيين مواعيد ثم تطبيقها على كل الأيام' : 'Set hours then apply to all days' }}</p>
                <div class="flex flex-wrap items-center gap-2 md:gap-4">
                    <span class="text-slate-600">{{ app()->getLocale() === 'ar' ? 'من' : 'From' }}</span>
                    <select id="template-from-hour" class="hour-select px-3 py-2 border border-slate-300 rounded-lg">
                        @for($h = 1; $h <= 12; $h++)
                            <option value="{{ $h }}" {{ $h == 9 ? 'selected' : '' }}>{{ $h }}</option>
                        @endfor
                    </select>
                    <select id="template-from-ampm" class="ampm-select px-3 py-2 border border-slate-300 rounded-lg">
                        <option value="am" selected>AM</option>
                        <option value="pm">PM</option>
                    </select>
                    <span class="text-slate-400 mx-1">–</span>
                    <span class="text-slate-600">{{ app()->getLocale() === 'ar' ? 'إلى' : 'To' }}</span>
                    <select id="template-to-hour" class="hour-select px-3 py-2 border border-slate-300 rounded-lg">
                        @for($h = 1; $h <= 12; $h++)
                            <option value="{{ $h }}" {{ $h == 5 ? 'selected' : '' }}>{{ $h }}</option>
                        @endfor
                    </select>
                    <select id="template-to-ampm" class="ampm-select px-3 py-2 border border-slate-300 rounded-lg">
                        <option value="am">AM</option>
                        <option value="pm" selected>PM</option>
                    </select>
                    <button type="button" id="apply-to-all-days" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-teal-600 text-sm font-medium">
                        {{ app()->getLocale() === 'ar' ? 'تطبيق على كل الأيام' : 'Apply to all days' }}
                    </button>
                </div>
            </div>

            @php
                $daysOrder = ['saturday','sunday','monday','tuesday','wednesday','thursday','friday'];
                $daysLabel = ['saturday'=>'Sat','sunday'=>'Sun','monday'=>'Mon','tuesday'=>'Tue','wednesday'=>'Wed','thursday'=>'Thu','friday'=>'Fri'];
                if (app()->getLocale() === 'ar') $daysLabel = ['saturday'=>'السبت','sunday'=>'الأحد','monday'=>'الإثنين','tuesday'=>'الثلاثاء','wednesday'=>'الأربعاء','thursday'=>'الخميس','friday'=>'الجمعة'];
                $whByDay = $clinic->workingHours->keyBy('day');
            @endphp
            <div id="working-hours-container" class="space-y-3">
                @foreach($daysOrder as $idx => $dayKey)
                    @php
                        $wh = $whByDay->get($dayKey);
                        $fromVal = old("working_hours.{$idx}.from", $wh && $wh->from ? $wh->from->format('H:i') : '');
                        $toVal = old("working_hours.{$idx}.to", $wh && $wh->to ? $wh->to->format('H:i') : '');
                    @endphp
                    <div class="working-hour-row flex flex-wrap items-center gap-2 md:gap-4 py-2 border-b border-slate-100 last:border-0" data-index="{{ $idx }}">
                        <input type="hidden" name="working_hours[{{ $idx }}][day]" value="{{ $dayKey }}">
                        <span class="w-24 font-medium text-slate-700">{{ $daysLabel[$dayKey] }}</span>
                        <select class="from-hour px-2 py-1.5 border border-slate-300 rounded-lg text-sm" data-day-index="{{ $idx }}">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'ساعة' : 'Hour' }}</option>
                            @for($h = 1; $h <= 12; $h++)
                                <option value="{{ $h }}">{{ $h }}</option>
                            @endfor
                        </select>
                        <select class="from-ampm px-2 py-1.5 border border-slate-300 rounded-lg text-sm" data-day-index="{{ $idx }}">
                            <option value="am">AM</option>
                            <option value="pm">PM</option>
                        </select>
                        <span class="text-slate-400">–</span>
                        <select class="to-hour px-2 py-1.5 border border-slate-300 rounded-lg text-sm" data-day-index="{{ $idx }}">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'ساعة' : 'Hour' }}</option>
                            @for($h = 1; $h <= 12; $h++)
                                <option value="{{ $h }}">{{ $h }}</option>
                            @endfor
                        </select>
                        <select class="to-ampm px-2 py-1.5 border border-slate-300 rounded-lg text-sm" data-day-index="{{ $idx }}">
                            <option value="am">AM</option>
                            <option value="pm">PM</option>
                        </select>
                        <input type="hidden" name="working_hours[{{ $idx }}][from]" class="hidden-from" value="{{ $fromVal }}">
                        <input type="hidden" name="working_hours[{{ $idx }}][to]" class="hidden-to" value="{{ $toVal }}">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="working_hours[{{ $idx }}][is_closed]" value="1" class="closed-cb rounded border-slate-300" {{ old("working_hours.{$idx}.is_closed", $wh && $wh->is_closed) ? 'checked' : '' }}>
                            <span class="text-sm text-slate-600">{{ app()->getLocale() === 'ar' ? 'مغلق' : 'Closed' }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
        </x-admin.ui.form-card>
        </div>

        <div class="mt-8 flex items-center justify-end gap-3">
            <a href="{{ route('admin.clinics.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</a>
            <x-admin.ui.button type="submit">{{ app()->getLocale() === 'ar' ? 'تحديث العيادة' : 'Update Clinic' }}</x-admin.ui.button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@if(app()->getLocale() === 'ar')
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/i18n/ar.min.js"></script>
@endif
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
$(document).ready(function() {
    const isRTL = {{ app()->getLocale() === 'ar' ? 'true' : 'false' }};

    $('#doctor_ids').select2({
        placeholder: '{{ app()->getLocale() === "ar" ? "اختر الأطباء" : "Select doctors" }}',
        allowClear: true,
        width: '100%',
        language: isRTL ? 'ar' : 'en',
        dir: isRTL ? 'rtl' : 'ltr',
        closeOnSelect: false
    });

    const oldGov = {{ old('governorate_id', $clinic->governorate_id ?? 'null') }};
    const oldCity = {{ old('city_id', $clinic->city_id ?? 'null') }};
    const oldArea = {{ old('area_id', $clinic->area_id ?? 'null') }};

    $('#governorate_id').on('change', function() {
        const gid = $(this).val();
        const $city = $('#city_id'); const $area = $('#area_id');
        if (!$area.length) return;
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
        const cid = $(this).val();
        const $area = $('#area_id');
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
        map.setView([la, ln], 15);
        $('#latitude').val(la.toFixed(8));
        $('#longitude').val(ln.toFixed(8));
    }
    marker.on('dragend', function(e) { var p = marker.getLatLng(); updateLoc(p.lat, p.lng); });
    map.on('click', function(e) { updateLoc(e.latlng.lat, e.latlng.lng); });

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
        if (closed) {
            $hiddenFrom.val('');
            $hiddenTo.val('');
            return;
        }
        var fromH = row.find('.from-hour').val();
        var fromA = row.find('.from-ampm').val();
        var toH = row.find('.to-hour').val();
        var toA = row.find('.to-ampm').val();
        $hiddenFrom.val(hourAmPmTo24(fromH, fromA));
        $hiddenTo.val(hourAmPmTo24(toH, toA));
    }
    function applyTemplateToAll() {
        var fromH = $('#template-from-hour').val();
        var fromA = $('#template-from-ampm').val();
        var toH = $('#template-to-hour').val();
        var toA = $('#template-to-ampm').val();
        $('#working-hours-container .working-hour-row').each(function() {
            var $row = $(this);
            $row.find('.closed-cb').prop('checked', false);
            $row.find('.from-hour').val(fromH);
            $row.find('.from-ampm').val(fromA);
            $row.find('.to-hour').val(toH);
            $row.find('.to-ampm').val(toA);
            updateRowHidden($row);
        });
    }
    $('#apply-to-all-days').on('click', applyTemplateToAll);
    $('#working-hours-container').on('change', '.from-hour, .from-ampm, .to-hour, .to-ampm, .closed-cb', function() {
        var $row = $(this).closest('.working-hour-row');
        updateRowHidden($row);
    });
    $('#working-hours-container .working-hour-row').each(function() {
        var $row = $(this);
        var $hiddenFrom = $row.find('.hidden-from');
        var $hiddenTo = $row.find('.hidden-to');
        var fromVal = $hiddenFrom.val();
        var toVal = $hiddenTo.val();
        if (fromVal) {
            var parts = fromVal.split(':');
            var h = parseInt(parts[0], 10);
            var ampm = h >= 12 ? 'pm' : 'am';
            var hour12 = h === 0 ? 12 : (h > 12 ? h - 12 : h);
            $row.find('.from-hour').val(hour12);
            $row.find('.from-ampm').val(ampm);
        }
        if (toVal) {
            var parts = toVal.split(':');
            var h = parseInt(parts[0], 10);
            var ampm = h >= 12 ? 'pm' : 'am';
            var hour12 = h === 0 ? 12 : (h > 12 ? h - 12 : h);
            $row.find('.to-hour').val(hour12);
            $row.find('.to-ampm').val(ampm);
        }
    });
});
</script>
@endpush
