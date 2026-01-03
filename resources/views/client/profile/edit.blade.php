@extends('client.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'الملف الشخصي' : 'My Profile')

@section('page-title', app()->getLocale() === 'ar' ? 'الملف الشخصي' : 'My Profile')
@section('page-description', app()->getLocale() === 'ar' ? 'إدارة معلوماتك الشخصية' : 'Manage your personal information')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 lg:p-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-slate-900">
                {{ app()->getLocale() === 'ar' ? 'معلومات الملف الشخصي' : 'Profile Information' }}
            </h2>
            <p class="text-sm text-gray-600 mt-2">
                {{ app()->getLocale() === 'ar' 
                    ? 'قم بتحديث معلومات ملفك الشخصي. يمكنك تغيير الاسم ورقم الهاتف فقط.' 
                    : 'Update your profile information. You can only change your name and phone number.' }}
            </p>
        </div>

        <form method="POST" action="{{ route('client.profile.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'الاسم الكامل' : 'Full Name' }}
                </label>
                <input 
                    id="name" 
                    type="text" 
                    name="name" 
                    value="{{ old('name', $client->name) }}" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition duration-200 outline-none"
                    placeholder="{{ app()->getLocale() === 'ar' ? 'أدخل اسمك الكامل' : 'Enter your full name' }}"
                >
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'رقم الهاتف' : 'Phone Number' }}
                </label>
                <input 
                    id="phone_number" 
                    type="text" 
                    name="phone_number" 
                    value="{{ old('phone_number', $client->phone_number) }}" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition duration-200 outline-none"
                    placeholder="{{ app()->getLocale() === 'ar' ? 'أدخل رقم هاتفك' : 'Enter your phone number' }}"
                >
                <p class="mt-1 text-xs text-gray-500">
                    {{ app()->getLocale() === 'ar' 
                        ? 'يمكنك استخدام رقم الهاتف أو البريد الإلكتروني لتسجيل الدخول' 
                        : 'You can use phone number or email to login' }}
                </p>
                @error('phone_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email Address' }}
                    <span class="text-gray-500 text-xs">({{ app()->getLocale() === 'ar' ? 'اختياري' : 'Optional' }})</span>
                </label>
                <input 
                    id="email" 
                    type="email" 
                    name="email" 
                    value="{{ old('email', $client->email) }}" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition duration-200 outline-none"
                    placeholder="{{ app()->getLocale() === 'ar' ? 'أدخل بريدك الإلكتروني' : 'Enter your email address' }}"
                >
                <p class="mt-1 text-xs text-gray-500">
                    {{ app()->getLocale() === 'ar' 
                        ? 'يمكنك استخدام رقم الهاتف أو البريد الإلكتروني لتسجيل الدخول' 
                        : 'You can use phone number or email to login' }}
                </p>
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="insurance_company" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'شركة التأمين' : 'Insurance Company' }}
                    <span class="text-gray-500 text-xs">({{ app()->getLocale() === 'ar' ? 'اختياري' : 'Optional' }})</span>
                </label>
                <div class="space-y-2">
                    @if($client->insuranceCompany)
                        <div class="mb-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <p class="text-sm text-gray-700">
                                <span class="font-medium">{{ app()->getLocale() === 'ar' ? 'الحالية:' : 'Current:' }}</span>
                                {{ app()->getLocale() === 'ar' 
                                    ? ($client->insuranceCompany->name_ar ?? $client->insuranceCompany->name) 
                                    : $client->insuranceCompany->name }}
                            </p>
                        </div>
                    @endif
                    <select 
                        id="insurance_company_id" 
                        name="insurance_company_id" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition duration-200 outline-none"
                    >
                        <option value="">{{ app()->getLocale() === 'ar' ? 'لا يوجد' : 'None' }}</option>
                        @foreach($insuranceCompanies ?? [] as $company)
                            <option value="{{ $company->id }}" {{ old('insurance_company_id', $client->insurance_company_id) == $company->id ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? ($company->name_ar ?? $company->name) : $company->name }}
                            </option>
                        @endforeach
                        <option value="new">{{ app()->getLocale() === 'ar' ? '+ إضافة شركة جديدة' : '+ Add New Company' }}</option>
                    </select>
                    <div id="new_insurance_company_container" style="display: none;">
                        <input 
                            type="text" 
                            id="insurance_company_name" 
                            name="insurance_company_name" 
                            value="{{ old('insurance_company_name') }}" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition duration-200 outline-none"
                            placeholder="{{ app()->getLocale() === 'ar' ? 'أدخل اسم شركة التأمين' : 'Enter insurance company name' }}"
                        >
                    </div>
                </div>
                <p class="mt-1 text-xs text-gray-500">
                    {{ app()->getLocale() === 'ar' 
                        ? 'سيتم حفظ شركة التأمين في كل طلب جديد تقوم بإنشائه' 
                        : 'Your insurance company will be saved with each new request you create' }}
                </p>
                @error('insurance_company_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('insurance_company_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                <a href="{{ route('client.dashboard') }}" class="px-6 py-3 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">
                    {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                </a>
                <button 
                    type="submit" 
                    class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-teal-700 transition duration-200 font-medium"
                >
                    {{ app()->getLocale() === 'ar' ? 'حفظ التغييرات' : 'Save Changes' }}
                </button>
            </div>
        </form>

        <!-- Delete Account Section -->
        <div class="mt-8 pt-8 border-t border-red-200">
            <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                <h3 class="text-lg font-bold text-red-900 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'حذف الحساب' : 'Delete Account' }}
                </h3>
                <p class="text-sm text-red-700 mb-4">
                    {{ app()->getLocale() === 'ar' 
                        ? 'بمجرد حذف حسابك، لن تتمكن من استعادة أي من معلوماتك. يرجى التأكد من أنك تريد المتابعة.' 
                        : 'Once you delete your account, you will not be able to recover any of your information. Please make sure you want to proceed.' }}
                </p>
                <button 
                    type="button" 
                    id="deleteAccountBtn"
                    class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 font-medium"
                >
                    {{ app()->getLocale() === 'ar' ? 'حذف حسابي' : 'Delete My Account' }}
                </button>
            </div>
        </div>

        <!-- Delete Account Modal -->
        <div id="deleteAccountModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" style="display: none;">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
                <h3 class="text-xl font-bold text-red-900 mb-4">
                    {{ app()->getLocale() === 'ar' ? 'تأكيد حذف الحساب' : 'Confirm Account Deletion' }}
                </h3>
                <p class="text-sm text-gray-700 mb-6">
                    {{ app()->getLocale() === 'ar' 
                        ? 'لحذف حسابك، يرجى إدخال كلمة المرور الخاصة بك للتأكيد.' 
                        : 'To delete your account, please enter your password to confirm.' }}
                </p>
                <form method="POST" action="{{ route('client.profile.destroy') }}" id="deleteAccountForm">
                    @csrf
                    @method('DELETE')
                    <div class="mb-4">
                        <label for="delete_password" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'كلمة المرور' : 'Password' }}
                        </label>
                        <input 
                            type="password" 
                            id="delete_password" 
                            name="password" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition duration-200 outline-none"
                            placeholder="{{ app()->getLocale() === 'ar' ? 'أدخل كلمة المرور' : 'Enter your password' }}"
                        >
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center justify-end gap-4">
                        <button 
                            type="button" 
                            id="cancelDeleteBtn"
                            class="px-6 py-3 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200"
                        >
                            {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                        </button>
                        <button 
                            type="submit" 
                            class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 font-medium"
                        >
                            {{ app()->getLocale() === 'ar' ? 'حذف الحساب نهائياً' : 'Delete Account Permanently' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Handle insurance company selection
        $('#insurance_company_id').on('change', function() {
            const container = $('#new_insurance_company_container');
            const nameInput = $('#insurance_company_name');
            
            if ($(this).val() === 'new') {
                container.slideDown();
                nameInput.prop('required', false);
                $(this).val(''); // Clear the select value
            } else {
                container.slideUp();
                nameInput.val('').prop('required', false);
            }
        });

            // Initialize insurance company field if old input exists
            @if(old('insurance_company_name'))
                $('#insurance_company_id').val('new').trigger('change');
            @endif

            // Delete Account Modal
            $('#deleteAccountBtn').on('click', function() {
                $('#deleteAccountModal').removeClass('hidden').addClass('flex');
            });

            $('#cancelDeleteBtn').on('click', function() {
                $('#deleteAccountModal').removeClass('flex').addClass('hidden');
                $('#delete_password').val('');
            });

            // Close modal when clicking outside
            $('#deleteAccountModal').on('click', function(e) {
                if ($(e.target).is('#deleteAccountModal')) {
                    $(this).removeClass('flex').addClass('hidden');
                    $('#delete_password').val('');
                }
            });
        });
    </script>
@endpush
@endsection

