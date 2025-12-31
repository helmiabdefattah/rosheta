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
				<label class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'تاريخ الميلاد' : 'Date of Birth' }}</label>
				<input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" class="w-full border border-gray-300 rounded-lg p-2">
			</div>
			<div class="md:col-span-2">
				<label class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}</label>
				<input name="address" value="{{ old('address') }}" class="w-full border border-gray-300 rounded-lg p-2">
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


