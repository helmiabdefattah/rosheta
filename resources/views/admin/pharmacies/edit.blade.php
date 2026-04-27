@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'تعديل صيدلية' : 'Edit Pharmacy')
@section('page-title', app()->getLocale() === 'ar' ? 'تعديل الصيدلية' : 'Edit Pharmacy')
@section('page-description', app()->getLocale() === 'ar' ? 'تحديث تفاصيل الصيدلية' : 'Update pharmacy details')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
#pharmacyMap { height: 350px; width: 100%; border-radius: 0.5rem; border: 1px solid #e2e8f0; z-index: 0; }
#pharmacyMap .leaflet-container { touch-action: pan-x pan-y; }
@media (max-width: 1023px) {
    #pharmacyMap { height: 220px; }
}
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto pb-6 max-lg:pb-28">
    <form id="pharmacy-form" action="{{ route('admin.pharmacies.update', $pharmacy) }}" method="POST">
        @csrf
        @method('PUT')

        <x-admin.ui.form-card :title="app()->getLocale() === 'ar' ? 'معلومات الصيدلية' : 'Pharmacy Information'">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div class="md:col-span-2">
                     <x-admin.ui.label for="name" required>{{ app()->getLocale() === 'ar' ? 'اسم الصيدلية' : 'Pharmacy Name' }}</x-admin.ui.label>
                     <x-admin.ui.input name="name" :value="old('name', $pharmacy->name)" required />
                </div>

                <!-- Owner User -->
                <div>
                    <x-admin.ui.label for="user_id">{{ app()->getLocale() === 'ar' ? 'المالك' : 'Owner' }}</x-admin.ui.label>
                    <x-admin.ui.select name="user_id" :selected="old('user_id', $pharmacy->user_id)" placeholder="{{ app()->getLocale() === 'ar' ? 'اختر المالك (اختياري)' : 'Select Owner (Optional)' }}">
                         @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id', $pharmacy->user_id) == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </x-admin.ui.select>
                </div>

                <!-- Area -->
                <div>
                    <x-admin.ui.label for="area_id">{{ app()->getLocale() === 'ar' ? 'المنطقة' : 'Area' }}</x-admin.ui.label>
                    <x-admin.ui.select name="area_id" :selected="old('area_id', $pharmacy->area_id)" placeholder="{{ app()->getLocale() === 'ar' ? 'اختر المنطقة' : 'Select Area' }}">
                         @foreach($areas as $area)
                            <option value="{{ $area->id }}" {{ old('area_id', $pharmacy->area_id) == $area->id ? 'selected' : '' }}>
                                @if(app()->getLocale() === 'ar')
                                    {{ $area->name_ar }} - {{ $area->city->name_ar ?? '' }}
                                @else
                                    {{ $area->name }} - {{ $area->city->name ?? '' }}
                                @endif
                            </option>
                        @endforeach
                    </x-admin.ui.select>
                </div>

                <!-- Phone -->
                <div>
                     <x-admin.ui.label for="phone">{{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}</x-admin.ui.label>
                     <x-admin.ui.input name="phone" :value="old('phone', $pharmacy->phone)" />
                </div>

                 <!-- Email -->
                <div>
                     <x-admin.ui.label for="email">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }}</x-admin.ui.label>
                     <x-admin.ui.input type="email" name="email" :value="old('email', $pharmacy->email)" />
                </div>

                <!-- Address -->
                <div class="md:col-span-2">
                     <x-admin.ui.label for="address">{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}</x-admin.ui.label>
                     <x-admin.ui.input name="address" :value="old('address', $pharmacy->address)" />
                </div>

                <!-- Is Active -->
                <div class="flex items-center h-full pt-6">
                    <label class="inline-flex items-center cursor-pointer relative">
                         <input type="checkbox" name="is_active" value="1" {{ old('is_active', $pharmacy->is_active) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                        <span class="ml-3 text-sm font-medium text-slate-700">{{ app()->getLocale() === 'ar' ? 'نشط' : 'Is Active' }}</span>
                    </label>
                </div>
            </div>
        </x-admin.ui.form-card>
        
        <x-admin.ui.form-card :title="app()->getLocale() === 'ar' ? 'الموقع الجغرافي' : 'Location Coordinates'" class="mt-6">
            <div class="space-y-6">
                <div>
                    <x-admin.ui.label>{{ app()->getLocale() === 'ar' ? 'موقع الصيدلية على الخريطة' : 'Location on map' }}</x-admin.ui.label>
                    <p class="text-sm text-slate-500 mb-2">{{ app()->getLocale() === 'ar' ? 'انقر على الخريطة أو اسحب العلامة لتحديد الموقع' : 'Click on the map or drag the marker to set location' }}</p>
                    <div id="pharmacyMap"></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-admin.ui.label for="lat">{{ app()->getLocale() === 'ar' ? 'خط العرض (Latitude)' : 'Latitude' }}</x-admin.ui.label>
                        <x-admin.ui.input name="lat" :value="old('lat', $pharmacy->lat)" />
                    </div>
                    <div>
                        <x-admin.ui.label for="lng">{{ app()->getLocale() === 'ar' ? 'خط الطول (Longitude)' : 'Longitude' }}</x-admin.ui.label>
                        <x-admin.ui.input name="lng" :value="old('lng', $pharmacy->lng)" />
                    </div>
                </div>
            </div>
        </x-admin.ui.form-card>

        <div class="mt-8 hidden lg:flex items-center justify-end gap-3">
             <a href="{{ route('admin.pharmacies.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-all">
                {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
            </a>
            <x-admin.ui.button type="submit">
                {{ app()->getLocale() === 'ar' ? 'تحديث الصيدلية' : 'Update Pharmacy' }}
            </x-admin.ui.button>
        </div>
    </form>
</div>

<div class="lg:hidden fixed bottom-0 inset-x-0 z-[45] border-t border-slate-200 bg-white/95 backdrop-blur-md shadow-[0_-4px_20px_rgba(15,23,42,0.08)] px-4 py-3 flex items-center justify-end gap-3"
     style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom, 0px));">
    <a href="{{ route('admin.pharmacies.index') }}" class="px-4 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50">
        {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
    </a>
    <button type="submit" form="pharmacy-form" class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-white bg-primary rounded-xl hover:opacity-90 shadow-sm">
        {{ app()->getLocale() === 'ar' ? 'تحديث الصيدلية' : 'Update Pharmacy' }}
    </button>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var latEl = document.getElementById('lat');
    var lngEl = document.getElementById('lng');
    var lat = parseFloat(latEl && latEl.value) || 30.0444;
    var lng = parseFloat(lngEl && lngEl.value) || 31.2357;
    var map = L.map('pharmacyMap').setView([lat, lng], 13);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap', maxZoom: 19 }).addTo(map);
    var marker = L.marker([lat, lng], { draggable: true }).addTo(map);
    function updateLoc(la, ln) {
        marker.setLatLng([la, ln]);
        map.setView([la, ln], map.getZoom() < 14 ? 14 : map.getZoom());
        if (latEl) latEl.value = la.toFixed(8);
        if (lngEl) lngEl.value = ln.toFixed(8);
    }
    marker.on('dragend', function () {
        var p = marker.getLatLng();
        updateLoc(p.lat, p.lng);
    });
    map.on('click', function (e) {
        updateLoc(e.latlng.lat, e.latlng.lng);
    });
    if (latEl && lngEl && latEl.value && lngEl.value) {
        map.setView([lat, lng], 15);
    }
    function tryApplyFromInputs() {
        var la = parseFloat(latEl && latEl.value);
        var ln = parseFloat(lngEl && lngEl.value);
        if (!isNaN(la) && !isNaN(ln) && la >= -90 && la <= 90 && ln >= -180 && ln <= 180) {
            marker.setLatLng([la, ln]);
            map.setView([la, ln], 15);
        }
    }
    if (latEl) latEl.addEventListener('change', tryApplyFromInputs);
    if (lngEl) lngEl.addEventListener('change', tryApplyFromInputs);
    setTimeout(function () { map.invalidateSize(); }, 100);
});
</script>
@endpush
