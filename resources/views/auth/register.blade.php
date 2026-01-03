<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ app()->getLocale() === 'ar' ? 'التسجيل' : 'Register' }} - Mostashfa-on</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes blob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }
        .blob {
            animation: blob 7s infinite;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 via-sky-50 to-cyan-50 relative flex items-center justify-center py-12 px-4">
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="blob bg-sky-300/25 w-[30rem] h-[30rem] rounded-full absolute -top-28 -left-28 blur-3xl"></div>
        <div class="blob bg-cyan-300/25 w-[30rem] h-[30rem] rounded-full absolute -bottom-28 -right-28 blur-3xl" style="animation-delay: 2s;"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <div class="bg-white/90 backdrop-blur-xl border border-sky-100 rounded-2xl shadow-xl p-8">
            <!-- Language Toggle -->
            <div class="flex justify-end mb-4">
                <a href="{{ route('locale', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" 
                   class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-600 hover:text-sky-600 hover:bg-sky-50 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</span>
                </a>
            </div>

            <div class="flex flex-col items-center justify-center gap-4 mb-8">
                <img src="{{ url('/images/mo-logo.png') }}" alt="Mostashfa-on" class="w-24 h-24 rounded-2xl ring-2 ring-sky-200 shadow-md object-contain">
                <div class="text-center leading-tight">
                    <div class="text-3xl font-black text-slate-900">Mostashfa-on</div>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">{{ app()->getLocale() === 'ar' ? 'فشل التسجيل' : 'Registration Failed' }}</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'الاسم الكامل' : 'Full Name' }}
                    </label>
                    <input 
                        id="name" 
                        type="text" 
                        name="name" 
                        value="{{ old('name') }}" 
                        required 
                        autofocus
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition duration-200 outline-none"
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
                        value="{{ old('phone_number') }}" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition duration-200 outline-none"
                        placeholder="{{ app()->getLocale() === 'ar' ? 'أدخل رقم هاتفك' : 'Enter your phone number' }}"
                    >
                    @error('phone_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="governorate_id" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'المحافظة' : 'Governorate' }}
                        <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="governorate_id" 
                        name="governorate_id" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition duration-200 outline-none"
                    >
                        <option value="">{{ app()->getLocale() === 'ar' ? 'اختر المحافظة' : 'Select Governorate' }}</option>
                        @foreach($governorates ?? [] as $governorate)
                            <option value="{{ $governorate->id }}" {{ old('governorate_id') == $governorate->id ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? $governorate->name_ar : $governorate->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('governorate_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="city_id" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'المدينة' : 'City' }}
                        <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="city_id" 
                        name="city_id" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition duration-200 outline-none"
                    >
                        <option value="">{{ app()->getLocale() === 'ar' ? 'اختر المدينة' : 'Select City' }}</option>
                    </select>
                    @error('city_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email Address' }}
                    </label>
                    <input 
                        id="email" 
                        type="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition duration-200 outline-none"
                        placeholder="{{ app()->getLocale() === 'ar' ? 'أدخل بريدك الإلكتروني' : 'Enter your email' }}"
                    >
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
                        <select 
                            id="insurance_company_id" 
                            name="insurance_company_id" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition duration-200 outline-none"
                        >
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر شركة التأمين' : 'Select Insurance Company' }}</option>
                            @foreach($insuranceCompanies ?? [] as $company)
                                <option value="{{ $company->id }}" {{ old('insurance_company_id') == $company->id ? 'selected' : '' }}>
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
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition duration-200 outline-none"
                                placeholder="{{ app()->getLocale() === 'ar' ? 'أدخل اسم شركة التأمين' : 'Enter insurance company name' }}"
                            >
                        </div>
                    </div>
                    @error('insurance_company_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('insurance_company_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'كلمة المرور' : 'Password' }}
                    </label>
                    <input 
                        id="password" 
                        type="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition duration-200 outline-none"
                        placeholder="{{ app()->getLocale() === 'ar' ? 'أدخل كلمة المرور' : 'Enter your password' }}"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'تأكيد كلمة المرور' : 'Confirm Password' }}
                    </label>
                    <input 
                        id="password_confirmation" 
                        type="password" 
                        name="password_confirmation" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition duration-200 outline-none"
                        placeholder="{{ app()->getLocale() === 'ar' ? 'أعد إدخال كلمة المرور' : 'Confirm your password' }}"
                    >
                </div>

                <div>
                    <button 
                        type="submit" 
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-sky-500 to-cyan-500 hover:from-sky-600 hover:to-cyan-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 transition duration-200"
                    >
                        {{ app()->getLocale() === 'ar' ? 'إنشاء حساب' : 'Create Account' }}
                    </button>
                </div>

                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        {{ app()->getLocale() === 'ar' ? 'لديك حساب بالفعل؟' : 'Already have an account?' }} 
                        <a href="{{ route('login') }}" class="font-medium text-sky-600 hover:text-sky-500">
                            {{ app()->getLocale() === 'ar' ? 'تسجيل الدخول' : 'Sign in' }}
                        </a>
                    </p>
                </div>
            </form>
        </div>

        <div class="text-center text-xs text-slate-500 mt-6">
            © {{ date('Y') }} Mostashfa-on. All rights reserved.
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            // Load cities when governorate changes
            $('#governorate_id').on('change', function() {
                const governorateId = $(this).val();
                const citySelect = $('#city_id');
                
                const cityPlaceholder = '{{ app()->getLocale() === "ar" ? "اختر المدينة" : "Select City" }}';
                citySelect.empty().append('<option value="">' + cityPlaceholder + '</option>');
                
                if (governorateId) {
                    $.ajax({
                        url: '/api/cities',
                        method: 'GET',
                        data: { governorate_id: governorateId },
                        success: function(response) {
                            if (response.success && response.data) {
                                const isArabic = '{{ app()->getLocale() }}' === 'ar';
                                response.data.forEach(function(city) {
                                    const cityName = isArabic ? city.name_ar : city.name;
                                    citySelect.append(
                                        $('<option></option>')
                                            .attr('value', city.id)
                                            .text(cityName)
                                    );
                                });
                            }
                        },
                        error: function() {
                            console.error('Failed to load cities');
                        }
                    });
                }
            });

            // Trigger change if governorate is pre-selected (from old input)
            @if(old('governorate_id'))
                $('#governorate_id').trigger('change');
                setTimeout(function() {
                    $('#city_id').val('{{ old("city_id") }}');
                }, 500);
            @endif

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
        });
    </script>
</body>
</html>

