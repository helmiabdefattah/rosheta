@php
    $l = app()->getLocale() === 'ar';
    $center = $center ?? null;
    $val = fn ($key, $default = null) => old($key, $center->{$key} ?? $default);

    // Rows for the clinics repeater: old input, else the center's clinics, else one blank.
    $clinicRows = old('clinics');
    if (!is_array($clinicRows)) {
        $clinicRows = $center && $center->clinics->count()
            ? $center->clinics->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'doctor_id' => $c->doctor_id, 'medical_examination_price' => $c->medical_examination_price])->all()
            : [['id' => '', 'name' => '', 'doctor_id' => '', 'medical_examination_price' => '']];
    }
    $inp = 'w-full border border-slate-300 rounded-lg p-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary/30 outline-none';
    $lbl = 'block text-sm font-semibold text-slate-700 mb-1';
@endphp

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

@if ($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 text-sm">
        <ul class="list-disc {{ $l ? 'pr-5' : 'pl-5' }} space-y-1">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="grid lg:grid-cols-3 gap-6">
    {{-- Center details --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="font-black text-slate-900 mb-4">{{ $l ? 'بيانات المركز' : 'Center details' }}</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="{{ $lbl }}">{{ $l ? 'اسم المركز' : 'Center name' }} <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ $val('name') }}" required class="{{ $inp }}">
                </div>
                <div>
                    <label class="{{ $lbl }}">{{ $l ? 'الهاتف' : 'Phone' }}</label>
                    <input type="text" name="phone_number" value="{{ $val('phone_number') }}" class="{{ $inp }}" dir="ltr">
                </div>
                <div>
                    <label class="{{ $lbl }}">{{ $l ? 'الحالة' : 'Status' }}</label>
                    <label class="flex items-center gap-2 mt-2.5">
                        <input type="checkbox" name="is_active" value="1" @checked($val('is_active', true)) class="w-5 h-5 rounded border-slate-300 text-primary">
                        <span class="text-sm text-slate-700">{{ $l ? 'نشط' : 'Active' }}</span>
                    </label>
                </div>
                <div class="sm:col-span-2">
                    <label class="{{ $lbl }}">{{ $l ? 'العنوان' : 'Address' }}</label>
                    <input type="text" name="address" value="{{ $val('address') }}" class="{{ $inp }}">
                </div>
                <div>
                    <label class="{{ $lbl }}">{{ $l ? 'المحافظة' : 'Governorate' }}</label>
                    <select name="governorate_id" id="mc_gov" class="{{ $inp }}">
                        <option value="">{{ $l ? 'اختر' : 'Select' }}</option>
                        @foreach($governorates as $g)
                            <option value="{{ $g->id }}" @selected($val('governorate_id') == $g->id)>{{ $l ? ($g->name_ar ?? $g->name) : $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $lbl }}">{{ $l ? 'المدينة' : 'City' }}</label>
                    <select name="city_id" id="mc_city" class="{{ $inp }}" data-selected="{{ $val('city_id') }}">
                        <option value="">{{ $l ? 'اختر' : 'Select' }}</option>
                    </select>
                </div>
                <div>
                    <label class="{{ $lbl }}">{{ $l ? 'المنطقة' : 'Area' }}</label>
                    <select name="area_id" id="mc_area" class="{{ $inp }}" data-selected="{{ $val('area_id') }}">
                        <option value="">{{ $l ? 'اختر' : 'Select' }}</option>
                    </select>
                </div>
                <div>
                    <label class="{{ $lbl }}">{{ $l ? 'وقت الفتح' : 'Open time' }}</label>
                    <input type="time" name="open_time" value="{{ $val('open_time') ? \Carbon\Carbon::parse($val('open_time'))->format('H:i') : '' }}" class="{{ $inp }}">
                </div>
                <div>
                    <label class="{{ $lbl }}">{{ $l ? 'وقت الإغلاق' : 'Close time' }}</label>
                    <input type="time" name="close_time" value="{{ $val('close_time') ? \Carbon\Carbon::parse($val('close_time'))->format('H:i') : '' }}" class="{{ $inp }}">
                </div>
                <div>
                    <label class="{{ $lbl }}">{{ $l ? 'خط العرض (Latitude)' : 'Latitude' }}</label>
                    <input type="text" name="latitude" id="mc_lat" value="{{ $val('latitude') }}" class="{{ $inp }}" dir="ltr">
                </div>
                <div>
                    <label class="{{ $lbl }}">{{ $l ? 'خط الطول (Longitude)' : 'Longitude' }}</label>
                    <input type="text" name="longitude" id="mc_lng" value="{{ $val('longitude') }}" class="{{ $inp }}" dir="ltr">
                </div>
            </div>
            <div class="mt-4">
                <label class="{{ $lbl }}">{{ $l ? 'الموقع على الخريطة (اضغط لتحديد)' : 'Pick location on the map (click to set)' }}</label>
                <div id="mc_map" style="height: 320px; border-radius: 12px; overflow: hidden;"></div>
            </div>
        </div>

        {{-- Clinics inside the center --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-1">
                <h3 class="font-black text-slate-900">{{ $l ? 'العيادات داخل المركز' : 'Clinics in this center' }}</h3>
            </div>
            <p class="text-sm text-slate-500 mb-4">{{ $l ? 'كل عيادة يمكن أن يكون لها طبيب أو بدون طبيب.' : 'Each clinic may have a doctor assigned or none.' }}</p>

            <div id="clinics-wrap" class="space-y-4">
                @foreach($clinicRows as $i => $row)
                    <div class="clinic-row rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-bold text-slate-500 clinic-index">{{ $l ? 'عيادة' : 'Clinic' }} {{ $i + 1 }}</span>
                            <button type="button" class="remove-clinic text-red-500 text-sm font-semibold hover:underline">{{ $l ? 'إزالة' : 'Remove' }}</button>
                        </div>
                        <input type="hidden" name="clinics[{{ $i }}][id]" value="{{ $row['id'] ?? '' }}" data-key="id">
                        <div class="grid sm:grid-cols-3 gap-3">
                            <div>
                                <label class="{{ $lbl }}">{{ $l ? 'اسم العيادة' : 'Clinic name' }}</label>
                                <input type="text" name="clinics[{{ $i }}][name]" value="{{ $row['name'] ?? '' }}" class="{{ $inp }}" data-key="name">
                            </div>
                            <div>
                                <label class="{{ $lbl }}">{{ $l ? 'الطبيب' : 'Doctor' }}</label>
                                <select name="clinics[{{ $i }}][doctor_id]" class="{{ $inp }}" data-key="doctor_id">
                                    <option value="">{{ $l ? 'بدون طبيب' : 'No doctor' }}</option>
                                    @foreach($doctors as $doc)
                                        <option value="{{ $doc->id }}" @selected(($row['doctor_id'] ?? '') == $doc->id)>{{ $doc->name }}@if($doc->specialization) — {{ $doc->specialization->name }}@endif</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="{{ $lbl }}">{{ $l ? 'سعر الكشف' : 'Exam price' }}</label>
                                <input type="number" step="0.01" min="0" name="clinics[{{ $i }}][medical_examination_price]" value="{{ $row['medical_examination_price'] ?? '' }}" class="{{ $inp }}" data-key="medical_examination_price">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" id="add-clinic" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-lg border-2 border-dashed border-slate-300 text-slate-600 font-semibold hover:border-primary hover:text-primary transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ $l ? 'إضافة عيادة' : 'Add clinic' }}
            </button>
        </div>
    </div>

    {{-- Actions --}}
    <div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sticky top-6">
            <button type="submit" class="w-full py-3 bg-primary text-white rounded-xl font-bold hover:opacity-90 transition-all">
                {{ $l ? 'حفظ' : 'Save' }}
            </button>
            <a href="{{ route('admin.medical-centers.index') }}" class="block text-center mt-3 py-2.5 bg-slate-100 text-slate-700 rounded-xl font-semibold hover:bg-slate-200 transition-all">
                {{ $l ? 'إلغاء' : 'Cancel' }}
            </a>
        </div>
    </div>
</div>

{{-- Hidden template for a new clinic row --}}
<template id="clinic-template">
    <div class="clinic-row rounded-xl border border-slate-100 bg-slate-50 p-4">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-bold text-slate-500 clinic-index">{{ $l ? 'عيادة' : 'Clinic' }}</span>
            <button type="button" class="remove-clinic text-red-500 text-sm font-semibold hover:underline">{{ $l ? 'إزالة' : 'Remove' }}</button>
        </div>
        <input type="hidden" data-key="id" value="">
        <div class="grid sm:grid-cols-3 gap-3">
            <div>
                <label class="{{ $lbl }}">{{ $l ? 'اسم العيادة' : 'Clinic name' }}</label>
                <input type="text" data-key="name" class="{{ $inp }}">
            </div>
            <div>
                <label class="{{ $lbl }}">{{ $l ? 'الطبيب' : 'Doctor' }}</label>
                <select data-key="doctor_id" class="{{ $inp }}">
                    <option value="">{{ $l ? 'بدون طبيب' : 'No doctor' }}</option>
                    @foreach($doctors as $doc)
                        <option value="{{ $doc->id }}">{{ $doc->name }}@if($doc->specialization) — {{ $doc->specialization->name }}@endif</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $lbl }}">{{ $l ? 'سعر الكشف' : 'Exam price' }}</label>
                <input type="number" step="0.01" min="0" data-key="medical_examination_price" class="{{ $inp }}">
            </div>
        </div>
    </div>
</template>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    var isAr = @json($l);
    var cities = @json($cities->map(fn ($c) => ['id' => $c->id, 'name' => $l ? ($c->name_ar ?? $c->name) : $c->name, 'gov' => $c->governorate_id]));
    var areas = @json($areas->map(fn ($a) => ['id' => $a->id, 'name' => $l ? ($a->name_ar ?? $a->name) : $a->name, 'city' => $a->city_id]));

    // ── Cascading governorate → city → area ─────────────────────────────
    var govSel = document.getElementById('mc_gov');
    var citySel = document.getElementById('mc_city');
    var areaSel = document.getElementById('mc_area');

    function fill(sel, items, placeholder, preselect) {
        var cur = preselect != null ? preselect : sel.value;
        sel.innerHTML = '<option value="">' + placeholder + '</option>';
        items.forEach(function (it) {
            var o = document.createElement('option');
            o.value = it.id; o.textContent = it.name;
            if (String(it.id) === String(cur)) o.selected = true;
            sel.appendChild(o);
        });
    }
    function refreshCities(preselect) {
        var gov = govSel.value;
        fill(citySel, cities.filter(function (c) { return !gov || String(c.gov) === String(gov); }), isAr ? 'اختر' : 'Select', preselect);
        refreshAreas();
    }
    function refreshAreas(preselect) {
        var city = citySel.value;
        fill(areaSel, areas.filter(function (a) { return !city || String(a.city) === String(city); }), isAr ? 'اختر' : 'Select', preselect);
    }
    govSel.addEventListener('change', function () { refreshCities(); });
    citySel.addEventListener('change', function () { refreshAreas(); });
    refreshCities(citySel.dataset.selected || '');
    refreshAreas(areaSel.dataset.selected || '');

    // ── Map picker ──────────────────────────────────────────────────────
    var latInput = document.getElementById('mc_lat');
    var lngInput = document.getElementById('mc_lng');
    var startLat = parseFloat(latInput.value) || 30.0444;
    var startLng = parseFloat(lngInput.value) || 31.2357;
    var map = L.map('mc_map').setView([startLat, startLng], (latInput.value ? 14 : 11));
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png?api_key={{ config('services.carto.api_key') }}', {
        attribution: '&copy; OpenStreetMap contributors &copy; CARTO', maxZoom: 19
    }).addTo(map);
    var marker = (latInput.value && lngInput.value) ? L.marker([startLat, startLng]).addTo(map) : null;
    map.on('click', function (e) {
        var lat = e.latlng.lat.toFixed(7), lng = e.latlng.lng.toFixed(7);
        latInput.value = lat; lngInput.value = lng;
        if (marker) { marker.setLatLng(e.latlng); } else { marker = L.marker(e.latlng).addTo(map); }
    });
    setTimeout(function () { map.invalidateSize(); }, 200);

    // ── Clinics repeater ────────────────────────────────────────────────
    var wrap = document.getElementById('clinics-wrap');
    var tpl = document.getElementById('clinic-template');
    var addBtn = document.getElementById('add-clinic');

    function reindex() {
        var rows = wrap.querySelectorAll('.clinic-row');
        rows.forEach(function (row, i) {
            row.querySelector('.clinic-index').textContent = (isAr ? 'عيادة ' : 'Clinic ') + (i + 1);
            row.querySelectorAll('[data-key]').forEach(function (el) {
                el.setAttribute('name', 'clinics[' + i + '][' + el.getAttribute('data-key') + ']');
            });
            row.querySelector('.remove-clinic').classList.toggle('hidden', rows.length <= 1);
        });
    }
    addBtn.addEventListener('click', function () {
        var node = tpl.content.firstElementChild.cloneNode(true);
        wrap.appendChild(node);
        reindex();
    });
    wrap.addEventListener('click', function (e) {
        if (e.target.closest('.remove-clinic')) {
            if (wrap.querySelectorAll('.clinic-row').length > 1) { e.target.closest('.clinic-row').remove(); reindex(); }
        }
    });
    reindex();
})();
</script>
