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
                    ? 'قم بتحديث معلومات ملفك الشخصي، بما في ذلك صورة الملف.' 
                    : 'Update your profile information, including your profile photo.' }}
            </p>
        </div>

        {{-- Bonus points: read-only (same total as header; not editable here) --}}
        <div class="mb-8 p-4 rounded-xl bg-gradient-to-r from-amber-50 to-yellow-50 border border-amber-200 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-amber-900">{{ app()->getLocale() === 'ar' ? 'نقاط المكافآت' : 'Reward points' }}</p>
            </div>
            <div class="flex items-center gap-2 bg-white/80 px-3 py-1.5 rounded-lg border border-amber-200/80 shadow-sm" aria-live="polite">
                <svg class="w-5 h-5 text-amber-600 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                </svg>
                <span class="text-xl font-bold text-amber-600 tabular-nums">{{ number_format($availableBonusPoints ?? 0) }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('client.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            @php
                $avatarPreviewUrl = $client->avatar
                    ? asset('storage/' . $client->avatar)
                    : 'https://ui-avatars.com/api/?name=' . urlencode($client->name) . '&background=0d9488&color=fff&size=128';
            @endphp
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    {{ app()->getLocale() === 'ar' ? 'صورة الملف الشخصي' : 'Profile photo' }}
                </label>
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="flex-shrink-0">
                        <img id="client_avatar_preview" src="{{ $avatarPreviewUrl }}" alt="" class="w-24 h-24 rounded-full object-cover border-2 border-gray-200 shadow-sm">
                    </div>
                    <div class="flex-1 min-w-0">
                        <input
                            type="file"
                            id="avatar"
                            name="avatar"
                            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                            class="block w-full text-sm text-gray-600 file:me-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer"
                        >
                        <p class="mt-2 text-xs text-gray-500">
                            {{ app()->getLocale() === 'ar' 
                                ? 'صيغ مسموحة: JPG أو PNG أو GIF أو WebP — بحد أقصى 2 ميجابايت.' 
                                : 'Allowed: JPG, PNG, GIF, or WebP — max 2 MB.' }}
                        </p>
                        @error('avatar')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

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

            <div class="py-4 border-t border-gray-200">
                <label class="flex items-center gap-3 cursor-pointer group">
                    <div class="relative inline-flex items-center">
                        <input type="checkbox" name="notification_sound" value="1" {{ old('notification_sound', $client->notification_sound) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </div>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-primary transition-colors">
                        {{ app()->getLocale() === 'ar' ? 'تفعيل صوت الإشعارات' : 'Enable Notification Sound' }}
                    </span>
                </label>
                <p class="text-xs text-gray-500 mt-1 ps-14">
                    {{ app()->getLocale() === 'ar' ? 'تشغيل صوت مميز عند استلام إشعارات جديدة' : 'Play a distinct sound when new notifications arrive' }}
                </p>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 pt-6 border-t border-gray-200">
                <a href="{{ route('client.dashboard') }}" class="px-6 py-3 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">
                    {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                </a>
                <button 
                    type="submit" 
                    class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 font-medium"
                >
                    {{ app()->getLocale() === 'ar' ? 'حفظ التغييرات' : 'Save Changes' }}
                </button>
            </div>
        </form>

        {{-- My medical files: view / add / edit / delete --}}
        @php $isAr = app()->getLocale() === 'ar'; @endphp
        <div class="mt-8 pt-8 border-t border-gray-200">
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between gap-2 mb-1">
                    <h3 class="text-lg font-bold text-slate-900">{{ $isAr ? 'ملفاتي الطبية' : 'My medical files' }}</h3>
                    <button type="button"
                            onclick="document.getElementById('client-attach-form').classList.toggle('hidden')"
                            class="text-sm bg-teal-600 hover:bg-teal-700 text-white px-3 py-1.5 rounded-lg whitespace-nowrap">
                        📎 {{ $isAr ? 'إرفاق ملف' : 'Attach file' }}
                    </button>
                </div>
                <p class="text-xs text-slate-500 mb-4">
                    {{ $isAr ? 'أرفق تحاليل أو أشعة أو صوراً ليطّلع عليها الطبيب أثناء الكشف' : 'Attach lab results, scans or photos for the doctor to review during your visit' }}
                </p>

                {{-- Add file --}}
                <form id="client-attach-form" method="POST" action="{{ route('client.attachments.store') }}" enctype="multipart/form-data"
                      class="{{ $errors->has('file') ? '' : 'hidden' }} mb-4 space-y-2 border border-slate-200 rounded-lg p-3 bg-slate-50">
                    @csrf
                    <input type="text" name="title" value="{{ old('title') }}"
                           placeholder="{{ $isAr ? 'عنوان (اختياري)' : 'Title (optional)' }}"
                           class="w-full border rounded px-2 py-1.5 text-sm">

                    @if ($clinicAppointments->count())
                        <select name="appointment_id" class="w-full border rounded px-2 py-1.5 text-sm">
                            <option value="">{{ $isAr ? 'لكل مواعيدي (ملف عام)' : 'For all my visits (general file)' }}</option>
                            @foreach ($clinicAppointments as $appt)
                                <option value="{{ $appt->id }}" @selected(old('appointment_id') == $appt->id)>
                                    {{ $appt->clinic->name ?? ($isAr ? 'العيادة' : 'Clinic') }}
                                    — {{ optional($appt->scheduled_at)->translatedFormat('d M Y • H:i') }}
                                </option>
                            @endforeach
                        </select>
                    @endif

                    {{-- On mobile this lets the patient choose a file OR take a photo. --}}
                    <input type="file" name="file" required accept="image/*,application/pdf" class="w-full text-sm">
                    <p class="text-[11px] text-slate-400">
                        {{ $isAr ? 'يمكنك اختيار ملف أو التقاط صورة بالكاميرا (صور أو PDF حتى 10 ميجابايت)' : 'Choose a file or take a photo with your camera (images or PDF, up to 10 MB)' }}
                    </p>
                    <button class="w-full bg-teal-600 hover:bg-teal-700 text-white text-sm py-1.5 rounded-lg">{{ $isAr ? 'رفع' : 'Upload' }}</button>
                </form>

                {{-- Existing files --}}
                @if ($myAttachments->count())
                    <div class="space-y-2">
                        @foreach ($myAttachments as $att)
                            <div class="rounded-lg border border-slate-100 p-2">
                                <div class="flex items-center gap-3">
                                    <a href="{{ $att->url }}" target="_blank" class="shrink-0">
                                        @if ($att->isImage())
                                            <img src="{{ $att->url }}" alt="" class="w-12 h-12 rounded object-cover border border-slate-200">
                                        @else
                                            <span class="w-12 h-12 rounded bg-slate-100 flex items-center justify-center text-xl">{{ $att->isPdf() ? '📄' : '📎' }}</span>
                                        @endif
                                    </a>
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ $att->url }}" target="_blank" class="block text-sm text-teal-700 hover:underline truncate">
                                            {{ $att->title ?? $att->file_name }}
                                        </a>
                                        @if ($att->appointment)
                                            <div class="text-[11px] text-slate-400 truncate">
                                                {{ $att->appointment->clinic->name ?? '' }}
                                                · {{ optional($att->appointment->scheduled_at)->translatedFormat('d M Y') }}
                                            </div>
                                        @endif
                                    </div>
                                    @if (is_null($att->uploaded_by))
                                        <button type="button" onclick="document.getElementById('edit-att-{{ $att->id }}').classList.toggle('hidden')"
                                                class="text-slate-500 hover:text-slate-700 px-2 py-1" title="{{ $isAr ? 'تعديل' : 'Edit' }}">✏️</button>
                                        <form method="POST" action="{{ route('client.attachments.destroy', $att) }}"
                                              onsubmit="return confirm('{{ $isAr ? 'حذف هذا الملف؟' : 'Remove this file?' }}')">
                                            @csrf @method('DELETE')
                                            <button class="text-red-500 hover:text-red-700 px-2 py-1" title="{{ $isAr ? 'حذف' : 'Remove' }}">🗑️</button>
                                        </form>
                                    @else
                                        <span class="text-[10px] text-slate-400 px-2 whitespace-nowrap">{{ $isAr ? 'من العيادة' : 'By clinic' }}</span>
                                    @endif
                                </div>

                                {{-- Inline rename (patient uploads only) --}}
                                @if (is_null($att->uploaded_by))
                                    <form id="edit-att-{{ $att->id }}" method="POST" action="{{ route('client.attachments.update', $att) }}"
                                          class="hidden mt-2 flex items-center gap-2">
                                        @csrf @method('PUT')
                                        <input type="text" name="title" value="{{ $att->title ?? $att->file_name }}"
                                               class="flex-1 border rounded px-2 py-1 text-sm">
                                        <button class="text-sm bg-teal-600 hover:bg-teal-700 text-white px-3 py-1 rounded">{{ $isAr ? 'حفظ' : 'Save' }}</button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-400 italic">{{ $isAr ? 'لا توجد ملفات بعد.' : 'No files yet.' }}</p>
                @endif
            </div>
        </div>

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
    document.getElementById('avatar')?.addEventListener('change', function (e) {
        const f = e.target.files && e.target.files[0];
        if (!f || !f.type.startsWith('image/')) return;
        const url = URL.createObjectURL(f);
        const img = document.getElementById('client_avatar_preview');
        if (img) {
            img.src = url;
            img.onload = function () { URL.revokeObjectURL(url); };
        }
    });
</script>
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

