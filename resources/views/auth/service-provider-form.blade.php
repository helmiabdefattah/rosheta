@php $ar = app()->getLocale() === 'ar'; @endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $ar ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $ar ? 'تسجيل مقدم خدمة' : 'Service provider registration' }} - Mostashfa-on</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes blob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }
        .blob { animation: blob 7s infinite; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 via-sky-50 to-cyan-50 py-10 px-4">
    <div class="fixed top-6 {{ $ar ? 'left-6' : 'right-6' }} z-50">
        <a href="{{ route('locale', $ar ? 'en' : 'ar') }}"
           class="px-4 py-2 bg-white/80 backdrop-blur-md border border-sky-100 rounded-full shadow-sm text-sm font-semibold text-slate-700 hover:bg-white">
            {{ $ar ? 'English' : 'العربية' }}
        </a>
    </div>

    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="blob bg-sky-300/25 w-[30rem] h-[30rem] rounded-full absolute -top-28 -left-28 blur-3xl"></div>
        <div class="blob bg-cyan-300/25 w-[30rem] h-[30rem] rounded-full absolute -bottom-28 -right-28 blur-3xl" style="animation-delay: 2s;"></div>
    </div>

    <div class="max-w-2xl mx-auto relative z-10 bg-white/90 backdrop-blur-xl border border-sky-100 rounded-2xl shadow-xl p-8">
        <div class="mb-6 flex flex-col gap-2">
            <a href="{{ route('service-provider.register') }}" class="text-sm font-medium text-sky-600 hover:text-sky-500">
                {{ $ar ? '← اختيار نوع الخدمة' : '← Change service type' }}
            </a>
            <h1 class="text-2xl font-black text-slate-900">
                @if($type === 'pharmacy') {{ $ar ? 'تسجيل صيدلية' : 'Register pharmacy' }}
                @elseif($type === 'laboratory') {{ $ar ? 'تسجيل معمل تحاليل' : 'Register laboratory' }}
                @elseif($type === 'radiology') {{ $ar ? 'تسجيل مركز أشعة' : 'Register radiology lab' }}
                @elseif($type === 'nurse') {{ $ar ? 'تسجيل ممرض / ممرضة' : 'Register nurse' }}
                @elseif($type === 'doctor') {{ $ar ? 'تسجيل طبيب' : 'Register doctor' }}
                @elseif($type === 'charitable_organization') {{ $ar ? 'تسجيل منظمة خيرية' : 'Register charitable organization' }}
                @endif
            </h1>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('service-provider.register.store', $type) }}" class="space-y-5" enctype="multipart/form-data">
            @csrf

            <div class="border-b border-slate-100 pb-4 mb-4">
                <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wide">{{ $ar ? 'حساب الدخول' : 'Login account' }}</h2>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'الاسم' : 'Your name' }}</label>
                <input type="text" name="account_name" value="{{ old('account_name') }}" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'رقم الهاتف' : 'Phone number' }}</label>
                <input type="text" name="phone_number" value="{{ old('phone_number') }}" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'البريد الإلكتروني (اختياري)' : 'Email (optional)' }}</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'كلمة المرور' : 'Password' }}</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'تأكيد كلمة المرور' : 'Confirm password' }}</label>
                <input type="password" name="password_confirmation" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
            </div>

            @if(in_array($type, ['pharmacy', 'laboratory', 'radiology'], true))
                <div class="border-t border-slate-100 pt-4 mt-4">
                    <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wide mb-3">{{ $ar ? 'بيانات المنشأة' : 'Facility details' }}</h2>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'اسم المنشأة' : 'Facility name' }}</label>
                    <input type="text" name="business_name" value="{{ old('business_name') }}" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'هاتف المنشأة' : 'Facility phone' }} <span class="text-gray-400 font-normal">({{ $ar ? 'اختياري' : 'optional' }})</span></label>
                    <input type="text" name="business_phone" value="{{ old('business_phone') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'بريد المنشأة' : 'Facility email' }} <span class="text-gray-400 font-normal">({{ $ar ? 'اختياري' : 'optional' }})</span></label>
                    <input type="email" name="business_email" value="{{ old('business_email') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'العنوان' : 'Address' }} <span class="text-gray-400 font-normal">({{ $ar ? 'اختياري' : 'optional' }})</span></label>
                    <textarea name="address" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">{{ old('address') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'المحافظة' : 'Governorate' }} <span class="text-gray-400 font-normal">({{ $ar ? 'اختياري' : 'optional' }})</span></label>
                    <select id="governorate_id" name="governorate_id" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="">{{ $ar ? '—' : '—' }}</option>
                        @foreach($governorates as $g)
                            <option value="{{ $g->id }}" @selected(old('governorate_id') == $g->id)>{{ $ar ? ($g->name_ar ?? $g->name) : $g->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'المدينة' : 'City' }}</label>
                    <select id="city_id" name="city_id" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="">{{ $ar ? 'اختر المدينة' : 'Select city' }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'المنطقة' : 'Area' }}</label>
                    <select id="area_id" name="area_id" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="">{{ $ar ? 'اختر المنطقة' : 'Select area' }}</option>
                    </select>
                </div>

                @if(in_array($type, ['laboratory', 'radiology'], true))
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'اسم المدير' : 'Manager name' }} <span class="text-gray-400 font-normal">({{ $ar ? 'اختياري' : 'optional' }})</span></label>
                        <input type="text" name="manager_name" value="{{ old('manager_name') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                    </div>
                @endif
            @endif

            @if($type === 'charitable_organization')
                <div class="border-t border-slate-100 pt-4 mt-4">
                    <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wide mb-3">{{ $ar ? 'بيانات المنظمة' : 'Organization details' }}</h2>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'اسم المنظمة' : 'Organization name' }}</label>
                    <input type="text" name="organization_name" value="{{ old('organization_name') }}" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'هاتف المنظمة (للجمهور)' : 'Organization public phone' }} <span class="text-gray-400 font-normal">({{ $ar ? 'اختياري' : 'optional' }})</span></label>
                    <input type="text" name="organization_phone" value="{{ old('organization_phone') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'الخدمات (سطر لكل خدمة)' : 'Services (one per line)' }} <span class="text-gray-400 font-normal">({{ $ar ? 'اختياري' : 'optional' }})</span></label>
                    <textarea name="services_text" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">{{ old('services_text') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'المحافظة' : 'Governorate' }} <span class="text-red-500">*</span></label>
                    <select id="governorate_id" name="governorate_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="">{{ $ar ? 'اختر المحافظة' : 'Select governorate' }}</option>
                        @foreach($governorates as $g)
                            <option value="{{ $g->id }}" @selected(old('governorate_id') == $g->id)>{{ $ar ? ($g->name_ar ?? $g->name) : $g->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'المدينة' : 'City' }} <span class="text-red-500">*</span></label>
                    <select id="city_id" name="city_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="">{{ $ar ? 'اختر المدينة' : 'Select city' }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'المنطقة' : 'Area' }} <span class="text-red-500">*</span></label>
                    <select id="area_id" name="area_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="">{{ $ar ? 'اختر المنطقة' : 'Select area' }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'العنوان التفصيلي' : 'Full address' }} <span class="text-red-500">*</span></label>
                    <textarea name="address" rows="2" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">{{ old('address') }}</textarea>
                </div>
            @endif

            @if($type === 'nurse')
                <div class="border-t border-slate-100 pt-4 mt-4">
                    <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wide mb-3">{{ $ar ? 'بيانات التمريض' : 'Nursing profile' }}</h2>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'الجنس' : 'Gender' }}</label>
                    <select name="gender" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="">{{ $ar ? '—' : '—' }}</option>
                        <option value="male" @selected(old('gender') === 'male')>{{ $ar ? 'ذكر' : 'Male' }}</option>
                        <option value="female" @selected(old('gender') === 'female')>{{ $ar ? 'أنثى' : 'Female' }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'العنوان' : 'Address' }} <span class="text-gray-400 font-normal">({{ $ar ? 'اختياري' : 'optional' }})</span></label>
                    <textarea name="address" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">{{ old('address') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'المحافظة' : 'Governorate' }}</label>
                    <select id="nurse_governorate_id" name="governorate_id" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="">{{ $ar ? 'اختر المحافظة' : 'Select governorate' }}</option>
                        @foreach($governorates as $g)
                            <option value="{{ $g->id }}" @selected(old('governorate_id') == $g->id)>{{ $ar ? ($g->name_ar ?? $g->name) : $g->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'المدينة' : 'City' }}</label>
                    <select id="nurse_city_id" name="city_id" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="">{{ $ar ? 'اختر المدينة' : 'Select city' }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'مناطق العمل (واحدة أو أكثر)' : 'Service areas (one or more)' }}</label>
                    <select id="nurse_area_ids" name="area_ids[]" multiple size="6"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                    </select>
                    <p class="text-xs text-gray-500 mt-1">{{ $ar ? 'اضغط Ctrl أو Cmd لاختيار أكثر من منطقة.' : 'Hold Ctrl or Cmd to select multiple areas.' }}</p>
                </div>
            @endif

            @if($type === 'doctor')
                <div class="border-t border-slate-100 pt-4 mt-4">
                    <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wide mb-3">{{ $ar ? 'بيانات الطبيب' : 'Doctor profile' }}</h2>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'الاسم كما يظهر للمرضى' : 'Display name' }}</label>
                    <input type="text" name="doctor_name" value="{{ old('doctor_name') }}" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'التخصص' : 'Specialization' }}</label>
                    <select name="specialization_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="">{{ $ar ? 'اختر التخصص' : 'Select specialization' }}</option>
                        @foreach($specializations as $s)
                            <option value="{{ $s->id }}" @selected(old('specialization_id') == $s->id)>{{ $ar ? ($s->name_ar ?? $s->name) : $s->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'نبذة' : 'Brief' }} <span class="text-gray-400 font-normal">({{ $ar ? 'اختياري' : 'optional' }})</span></label>
                    <textarea name="brief" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">{{ old('brief') }}</textarea>
                </div>
            @endif

            @php
                $paperRows = isset($paperRows) && is_array($paperRows) && count($paperRows) > 0
                    ? array_values($paperRows)
                    : [['description' => '']];
            @endphp

            <div class="border-t border-slate-200 pt-5 mt-5">
                <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wide mb-1">{{ $ar ? 'الترخيص والمستندات' : 'License & documents' }}</h2>
                <p class="text-xs text-slate-500 mb-4">{{ $ar ? 'يُفضّل إرفاق رخصة العمل أو أي مستندات داعمة. إذا ظهرت أخطاء في النموذج، أعد اختيار الملفات.' : 'Upload your license or supporting documents. If the form shows errors, please select files again.' }}</p>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ $ar ? 'رقم الترخيص / التسجيل المهني' : 'License / professional registration no.' }} <span class="text-gray-400 font-normal">({{ $ar ? 'اختياري' : 'optional' }})</span></label>
                    <input type="text" name="license_number" value="{{ old('license_number') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none"
                           placeholder="{{ $ar ? 'مثال: رقم ترخيص وزارة الصحة' : 'e.g. ministry license number' }}">
                </div>

                <p class="text-sm font-semibold text-gray-700 mb-2">{{ $ar ? 'مرفقات (ملف + وصف لكل مرفق)' : 'Attachments (file + description each)' }}</p>
                <div id="papers-list" class="space-y-3">
                    @foreach($paperRows as $idx => $row)
                        <div class="paper-row border border-slate-200 rounded-xl p-4 space-y-3 bg-slate-50/90" data-paper-row>
                            <div class="flex justify-between items-center gap-2">
                                <span class="text-xs font-bold text-slate-500 uppercase">{{ $ar ? 'مستند' : 'Document' }} #{{ $idx + 1 }}</span>
                                <button type="button" class="remove-paper text-xs font-semibold text-red-600 hover:text-red-700 {{ count($paperRows) < 2 ? 'hidden' : '' }}">{{ $ar ? 'حذف' : 'Remove' }}</button>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $ar ? 'المرفق (PDF أو صورة)' : 'File (PDF or image)' }} <span class="text-gray-400 font-normal">({{ $ar ? 'اختياري' : 'optional' }})</span></label>
                                <input type="file" name="papers[{{ $idx }}][file]" accept=".pdf,.jpg,.jpeg,.png,.webp,image/*"
                                       class="w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $ar ? 'وصف المستند' : 'Description' }} <span class="text-gray-400 font-normal">({{ $ar ? 'اختياري' : 'optional' }})</span></label>
                                <textarea name="papers[{{ $idx }}][description]" rows="2" maxlength="1000"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none text-sm"
                                          placeholder="{{ $ar ? 'مثال: رخصة مزاولة المهنة' : 'e.g. practice license' }}">{{ is_array($row) ? ($row['description'] ?? '') : '' }}</textarea>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="add-paper-row"
                        class="mt-3 w-full py-2.5 rounded-xl text-sm font-bold border-2 border-dashed border-sky-300 text-sky-700 bg-sky-50/50 hover:bg-sky-50 transition">
                    {{ $ar ? '+ إضافة مرفق آخر' : '+ Add another attachment' }}
                </button>
            </div>

            <button type="submit"
                    class="w-full py-3.5 rounded-xl font-bold text-white bg-gradient-to-r from-sky-500 via-sky-600 to-cyan-500 hover:from-sky-600 hover:to-cyan-600 shadow-lg transition">
                {{ $ar ? 'إرسال الطلب' : 'Submit application' }}
            </button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        (function() {
            const isAr = @json($ar);
            const type = @json($type);

            function loadCities(governorateId, citySelectId, onDone) {
                const $city = $(citySelectId);
                const ph = isAr ? 'اختر المدينة' : 'Select city';
                $city.empty().append('<option value="">' + ph + '</option>');
                if (!governorateId) { onDone && onDone(); return; }
                $.getJSON('/api/cities', { governorate_id: governorateId }, function(res) {
                    if (res.success && res.data) {
                        res.data.forEach(function(city) {
                            const name = isAr ? (city.name_ar || city.name) : city.name;
                            $city.append($('<option></option>').attr('value', city.id).text(name));
                        });
                    }
                    onDone && onDone();
                });
            }

            function loadAreasSingle(cityId) {
                const $area = $('#area_id');
                const ph = isAr ? 'اختر المنطقة' : 'Select area';
                $area.empty().append('<option value="">' + ph + '</option>');
                if (!cityId) return;
                $.getJSON('/api/areas', { city_id: cityId }, function(res) {
                    if (res.success && res.data) {
                        res.data.forEach(function(a) {
                            const name = isAr ? (a.name_ar || a.name) : a.name;
                            $area.append($('<option></option>').attr('value', a.id).text(name));
                        });
                    }
                });
            }

            function loadAreasMulti(cityId, selectedIds) {
                const $m = $('#nurse_area_ids');
                $m.empty();
                if (!cityId) return;
                const set = (selectedIds && selectedIds.length) ? selectedIds.map(String) : [];
                $.getJSON('/api/areas', { city_id: cityId }, function(res) {
                    if (res.success && res.data) {
                        res.data.forEach(function(a) {
                            const name = isAr ? (a.name_ar || a.name) : a.name;
                            const opt = $('<option></option>').attr('value', a.id).text(name);
                            if (set.indexOf(String(a.id)) !== -1) opt.prop('selected', true);
                            $m.append(opt);
                        });
                    }
                });
            }

            if (['pharmacy', 'laboratory', 'radiology', 'charitable_organization'].indexOf(type) !== -1) {
                $('#governorate_id').on('change', function() {
                    loadCities($(this).val(), '#city_id', function() {
                        $('#area_id').empty().append('<option value="">' + (isAr ? 'اختر المنطقة' : 'Select area') + '</option>');
                    });
                });
                $('#city_id').on('change', function() {
                    loadAreasSingle($(this).val());
                });
                @if(old('governorate_id'))
                    $('#governorate_id').trigger('change');
                    setTimeout(function() {
                        $('#city_id').val(@json(old('city_id'))).trigger('change');
                        setTimeout(function() { $('#area_id').val(@json(old('area_id'))); }, 400);
                    }, 400);
                @endif
            }

            if (type === 'nurse') {
                const nurseOldAreaIds = @json(old('area_ids', []));
                $('#nurse_governorate_id').on('change', function() {
                    loadCities($(this).val(), '#nurse_city_id', function() {
                        $('#nurse_area_ids').empty();
                    });
                });
                $('#nurse_city_id').on('change', function() {
                    loadAreasMulti($(this).val(), null);
                });
                @if(old('governorate_id'))
                    $('#nurse_governorate_id').trigger('change');
                    setTimeout(function() {
                        var cid = @json(old('city_id'));
                        $('#nurse_city_id').val(cid);
                        loadAreasMulti(cid, nurseOldAreaIds);
                    }, 400);
                @endif
            }

            let paperIdx = {{ count($paperRows) }};
            function updateRemovePaperButtons() {
                const n = $('[data-paper-row]').length;
                $('.remove-paper').toggleClass('hidden', n <= 1);
            }
            $('#add-paper-row').on('click', function() {
                const i = paperIdx++;
                const docLabel = isAr ? 'مستند' : 'Document';
                const fileLbl = isAr ? 'المرفق (PDF أو صورة)' : 'File (PDF or image)';
                const opt = isAr ? 'اختياري' : 'optional';
                const descLbl = isAr ? 'وصف المستند' : 'Description';
                const rm = isAr ? 'حذف' : 'Remove';
                const ph = isAr ? 'مثال: رخصة مزاولة المهنة' : 'e.g. practice license';
                const row =
                    '<div class="paper-row border border-slate-200 rounded-xl p-4 space-y-3 bg-slate-50/90" data-paper-row>' +
                        '<div class="flex justify-between items-center gap-2">' +
                            '<span class="text-xs font-bold text-slate-500 uppercase">' + docLabel + ' #' + (i + 1) + '</span>' +
                            '<button type="button" class="remove-paper text-xs font-semibold text-red-600 hover:text-red-700">' + rm + '</button>' +
                        '</div>' +
                        '<div>' +
                            '<label class="block text-sm font-medium text-gray-700 mb-1">' + fileLbl + ' <span class="text-gray-400 font-normal">(' + opt + ')</span></label>' +
                            '<input type="file" name="papers[' + i + '][file]" accept=".pdf,.jpg,.jpeg,.png,.webp,image/*" class="w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">' +
                        '</div>' +
                        '<div>' +
                            '<label class="block text-sm font-medium text-gray-700 mb-1">' + descLbl + ' <span class="text-gray-400 font-normal">(' + opt + ')</span></label>' +
                            '<textarea name="papers[' + i + '][description]" rows="2" maxlength="1000" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none text-sm" placeholder="' + ph + '"></textarea>' +
                        '</div>' +
                    '</div>';
                $('#papers-list').append(row);
                updateRemovePaperButtons();
            });
            $('#papers-list').on('click', '.remove-paper', function() {
                if ($('[data-paper-row]').length <= 1) return;
                $(this).closest('[data-paper-row]').remove();
                updateRemovePaperButtons();
            });
            updateRemovePaperButtons();
        })();
    </script>
</body>
</html>
