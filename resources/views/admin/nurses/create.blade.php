@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'إضافة ممرض/ة' : 'Create Nurse')
@section('page-title', app()->getLocale() === 'ar' ? 'إضافة ممرض/ة' : 'Create Nurse')

@section('content')
<form action="{{ route('admin.nurses.store') }}" method="POST" enctype="multipart/form-data" class="max-w-4xl mx-auto space-y-6">
	@csrf

	<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
		<h3 class="text-base font-semibold mb-4">{{ app()->getLocale() === 'ar' ? 'بيانات الحساب' : 'Account Info (Client)' }}</h3>
		<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
			<div>
				<label class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</label>
				<input name="name" value="{{ old('name') }}" class="w-full border border-gray-300 rounded-lg p-2" required>
				@error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
			</div>
			<div>
				<label class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone Number' }}</label>
				<input name="phone_number" value="{{ old('phone_number') }}" class="w-full border border-gray-300 rounded-lg p-2" required>
				@error('phone_number') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
			</div>
			<div>
				<label class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني (اختياري)' : 'Email (optional)' }}</label>
				<input type="email" name="email" value="{{ old('email') }}" class="w-full border border-gray-300 rounded-lg p-2">
				@error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
			</div>
			<div>
				<label class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الصورة' : 'Avatar' }}</label>
				<input type="file" name="avatar" accept="image/*" class="w-full border border-gray-300 rounded-lg p-2">
				@error('avatar') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
			</div>
		</div>
	</div>

	<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
		<h3 class="text-base font-semibold mb-4">{{ app()->getLocale() === 'ar' ? 'بيانات الممرض/ة' : 'Nurse Details' }}</h3>
		<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
			<div>
				<label class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'النوع' : 'Gender' }}</label>
				<select name="gender" class="w-full border border-gray-300 rounded-lg p-2">
					<option value="">{{ app()->getLocale() === 'ar' ? 'اختيار' : 'Select' }}</option>
					<option value="male" {{ old('gender')=='male' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'ذكر' : 'Male' }}</option>
					<option value="female" {{ old('gender')=='female' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'أنثى' : 'Female' }}</option>
				</select>
			</div>
			<div>
				<label class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</label>
				<select name="status" class="w-full border border-gray-300 rounded-lg p-2" required>
					<option value="active" {{ old('status','active')=='active' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}</option>
					<option value="inactive" {{ old('status')=='inactive' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive' }}</option>
				</select>
			</div>
			<div>
				<label class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'تاريخ الميلاد' : 'Date of Birth' }}</label>
				<input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" class="w-full border border-gray-300 rounded-lg p-2">
			</div>
			<div class="md:col-span-2">
				<label class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}</label>
				<input name="address" value="{{ old('address') }}" class="w-full border border-gray-300 rounded-lg p-2">
			</div>
			<div class="md:col-span-2">
				<label class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'المناطق المغطاة' : 'Covered Areas' }}</label>
				<div class="relative">
					<select name="area_ids[]" multiple class="w-full border border-gray-300 rounded-lg p-2 tags-multiselect" data-placeholder="{{ app()->getLocale() === 'ar' ? 'اختر منطقة/مناطق' : 'Select area(s)' }}">
						@foreach($areas as $area)
							<option value="{{ $area->id }}" {{ (collect(old('area_ids', []))->contains($area->id)) ? 'selected' : '' }}>
								{{ $area->name }} - {{ $area->city->name ?? '' }} @if($area->city?->governorate) ({{ $area->city->governorate->name }}) @endif
							</option>
						@endforeach
					</select>
				</div>
				<p class="text-xs text-gray-500 mt-1">{{ app()->getLocale() === 'ar' ? 'يمكن اختيار أكثر من منطقة' : 'You can select multiple areas' }}</p>
			</div>
			<div>
				<label class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'المؤهل' : 'Qualification' }}</label>
				<select name="qualification" class="w-full border border-gray-300 rounded-lg p-2">
					<option value="">{{ app()->getLocale() === 'ar' ? 'اختيار' : 'Select' }}</option>
					<option value="bachelor" {{ old('qualification')=='bachelor' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'بكالوريوس' : 'Bachelor' }}</option>
					<option value="diploma" {{ old('qualification')=='diploma' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'دبلوم' : 'Diploma' }}</option>
					<option value="technical_institute" {{ old('qualification')=='technical_institute' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'معهد فني' : 'Technical Institute' }}</option>
				</select>
			</div>
			<div>
				<label class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'جهة التعليم' : 'Education Place' }}</label>
				<input name="education_place" value="{{ old('education_place') }}" class="w-full border border-gray-300 rounded-lg p-2">
			</div>
			<div>
				<label class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'سنة التخرج' : 'Graduation Year' }}</label>
				<input type="number" name="graduation_year" value="{{ old('graduation_year') }}" class="w-full border border-gray-300 rounded-lg p-2" min="1950" max="{{ date('Y')+1 }}">
			</div>
			<div>
				<label class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'سنوات الخبرة' : 'Years of Experience' }}</label>
				<input type="number" name="years_of_experience" value="{{ old('years_of_experience') }}" class="w-full border border-gray-300 rounded-lg p-2" min="0" max="70">
			</div>
			<div class="md:col-span-2">
				<label class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'جهة العمل الحالية' : 'Current Workplace' }}</label>
				<input name="current_workplace" value="{{ old('current_workplace') }}" class="w-full border border-gray-300 rounded-lg p-2">
			</div>
			<div class="md:col-span-2">
				<label class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الشهادات (افصل بعلامة ,)' : 'Certifications (comma-separated)' }}</label>
				<textarea name="certifications" rows="2" class="w-full border border-gray-300 rounded-lg p-2" placeholder="BLS, CPR, Infection Control">{{ old('certifications') }}</textarea>
			</div>
			<div class="md:col-span-2">
				<label class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'المهارات (افصل بعلامة ,)' : 'Skills (comma-separated)' }}</label>
				<textarea name="skills" rows="2" class="w-full border border-gray-300 rounded-lg p-2" placeholder="IV Cannulation, Phlebotomy">{{ old('skills') }}</textarea>
			</div>
		</div>
	</div>

	<div class="flex items-center justify-end gap-2">
		<a href="{{ route('admin.nurses.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</a>
		<button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm">{{ app()->getLocale() === 'ar' ? 'حفظ' : 'Save' }}</button>
	</div>
</form>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
/* Custom tag styles */
.select2-container--default .select2-selection--multiple {
    min-height: 42px;
    border: 1px solid #d1d5db !important;
    border-radius: 0.5rem !important;
    padding: 0.25rem;
}

.select2-container--default.select2-container--focus .select2-selection--multiple {
    border-color: #3b82f6 !important;
    outline: 0;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.select2-container--default .select2-selection--multiple .select2-selection__rendered {
    display: flex;
    flex-wrap: wrap;
    gap: 0.375rem;
    padding: 0;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #3b82f6 !important;
    border: 1px solid #2563eb !important;
    border-radius: 0.375rem;
    color: white;
    padding: 0.125rem 0.5rem 0.125rem 1.75rem;
    position: relative;
    font-size: 0.875rem;
    line-height: 1.25rem;
    margin: 0;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: white !important;
    position: absolute;
    left: 0.375rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1rem;
    line-height: 1;
    cursor: pointer;
    opacity: 0.9;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    opacity: 1;
    background-color: transparent !important;
}

.select2-container--default .select2-search--inline .select2-search__field {
    margin-top: 0 !important;
    padding: 0.375rem !important;
    min-width: 150px !important;
}

.select2-container--default .select2-search--inline .select2-search__field::placeholder {
    color: #9ca3af;
}

/* RTL support */
[dir="rtl"] .select2-container--default .select2-selection--multiple .select2-selection__choice {
    padding: 0.125rem 1.75rem 0.125rem 0.5rem;
}

[dir="rtl"] .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    left: auto;
    right: 0.375rem;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@if(app()->getLocale() === 'ar')
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/i18n/ar.min.js"></script>
@endif
<script>
	$(function () {
		const isRTL = @json(app()->getLocale() === 'ar');
		
		$('.tags-multiselect').select2({
			width: '100%',
			placeholder: @json(app()->getLocale() === 'ar' ? 'اختر منطقة/مناطق' : 'Select area(s)'),
			closeOnSelect: false,
			tags: false,
			multiple: true,
			language: isRTL ? 'ar' : 'en',
			dir: isRTL ? 'rtl' : 'ltr',
			allowClear: true,
			theme: 'default',
			templateSelection: function(data) {
				// Show only the area name in the tag, not the full location string
				const text = data.text.split(' - ')[0];
				return $('<span class="selected-tag">' + text + '</span>');
			}
		});
		
		// Initialize with previously selected values (from old input)
		@if(old('area_ids'))
			const oldAreaIds = @json(old('area_ids', []));
			$('.tags-multiselect').val(oldAreaIds).trigger('change');
		@endif
	});
</script>
@endpush