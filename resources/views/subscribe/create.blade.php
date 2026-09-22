@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'الاشتراك في باقة - مستشفي اون' : 'Subscribe to a plan - Mostashfa-on')

@php
    $isAr = app()->getLocale() === 'ar';
    $input = 'w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all bg-white';
    $label = 'block text-sm font-bold text-slate-700 mb-1.5';

    $planNames = ['starter' => ($isAr ? 'الأساسية' : 'Starter'), 'equipped' => ($isAr ? 'المجهّزة' : 'Equipped'), 'multi' => ($isAr ? 'متعددة العيادات' : 'Multi-Clinic')];

    // Repopulate assistant rows after a validation error, else show two blanks
    // (the Starter plan includes two assistants).
    $oldAssistants = old('assistants');
    if (!is_array($oldAssistants) || count($oldAssistants) === 0) {
        $oldAssistants = [['name' => '', 'username' => '', 'phone' => '', 'password' => ''], ['name' => '', 'username' => '', 'phone' => '', 'password' => '']];
    }
@endphp

@section('content')
    <header class="bg-slate-900 text-white pt-40 pb-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-tr from-primary/20 to-teal-500/20 opacity-40"></div>
        <div class="absolute -bottom-1 left-0 w-full h-10 bg-slate-50" style="clip-path: polygon(0 100%, 100% 100%, 100% 0);"></div>
        <div class="container mx-auto px-4 relative z-10 text-center">
            <h1 class="text-3xl md:text-5xl font-black mb-4">
                {{ $isAr ? 'اشترك في باقتك' : 'Subscribe to your plan' }}
            </h1>
            <p class="text-slate-300 max-w-2xl mx-auto">
                {{ $isAr
                    ? 'أدخل بيانات الطبيب والعيادة والمساعدين، وسنتواصل معك خلال 24 ساعة لتفعيل حسابك.'
                    : 'Enter the doctor, clinic and assistant details, and we\'ll contact you within 24 hours to activate your account.' }}
            </p>
        </div>
    </header>

    <main class="bg-slate-50 py-16">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-3xl">

            @if ($errors->any())
                <div class="mb-8 bg-red-50 border border-red-200 text-red-700 rounded-2xl p-5">
                    <p class="font-bold mb-2">{{ $isAr ? 'يرجى تصحيح الأخطاء التالية:' : 'Please fix the following:' }}</p>
                    <ul class="list-disc {{ $isAr ? 'pr-5' : 'pl-5' }} text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('subscribe.store') }}" method="POST" class="space-y-8">
                @csrf

                {{-- Plan --}}
                <section class="bg-white rounded-[2rem] border border-slate-100 shadow-sm p-6 sm:p-8">
                    <h2 class="text-xl font-black text-slate-900 mb-5">{{ $isAr ? 'الباقة المختارة' : 'Chosen plan' }}</h2>
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label class="{{ $label }}">{{ $isAr ? 'الباقة' : 'Plan' }}</label>
                            <select name="plan" class="{{ $input }}">
                                <option value="">{{ $isAr ? 'غير محدد' : 'Not sure yet' }}</option>
                                @foreach ($planNames as $key => $name)
                                    <option value="{{ $key }}" @selected(old('plan', $plan) === $key)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $label }}">{{ $isAr ? 'دورة الفوترة' : 'Billing' }}</label>
                            <select name="billing" class="{{ $input }}">
                                <option value="monthly" @selected(old('billing', $billing) === 'monthly')>{{ $isAr ? 'شهري' : 'Monthly' }}</option>
                                <option value="annual" @selected(old('billing', $billing) === 'annual')>{{ $isAr ? 'سنوي (خصم 20%)' : 'Annual (20% off)' }}</option>
                            </select>
                        </div>
                    </div>
                    <label class="flex items-center gap-3 mt-5 cursor-pointer">
                        <input type="checkbox" name="with_profile_site" value="1" @checked(old('with_profile_site', $withProfileSite)) class="w-5 h-5 rounded border-slate-300 text-primary focus:ring-primary/30">
                        <span class="text-sm text-slate-700">{{ $isAr ? 'أضف موقعًا تعريفيًا خاصًا (‎+200 ج.م/شهر)' : 'Add a profile website (+200 EGP/mo)' }}</span>
                    </label>
                    <div class="mt-3">
                        <label class="{{ $label }}">{{ $isAr ? 'النطاق الفرعي المطلوب (اختياري)' : 'Preferred subdomain (optional)' }}</label>
                        <div class="flex items-center rounded-xl border border-slate-200 overflow-hidden" dir="ltr">
                            <span class="px-3 text-slate-400 text-sm bg-slate-50">https://</span>
                            <input type="text" name="profile_subdomain" value="{{ old('profile_subdomain') }}" placeholder="name" class="flex-1 px-2 py-3 outline-none">
                            <span class="px-3 text-slate-400 text-sm bg-slate-50">.mostashfaon.com</span>
                        </div>
                    </div>
                </section>

                {{-- Contact --}}
                <section class="bg-white rounded-[2rem] border border-slate-100 shadow-sm p-6 sm:p-8">
                    <h2 class="text-xl font-black text-slate-900 mb-1">{{ $isAr ? 'بيانات التواصل' : 'Contact details' }}</h2>
                    <p class="text-sm text-slate-500 mb-5">{{ $isAr ? 'سنتصل بك على هذا الرقم لتفعيل الحساب.' : 'We\'ll call you on this number to activate the account.' }}</p>
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label class="{{ $label }}">{{ $isAr ? 'الاسم' : 'Your name' }} <span class="text-red-500">*</span></label>
                            <input type="text" name="contact_name" value="{{ old('contact_name') }}" required class="{{ $input }}">
                        </div>
                        <div>
                            <label class="{{ $label }}">{{ $isAr ? 'رقم التواصل' : 'Contact number' }} <span class="text-red-500">*</span></label>
                            <input type="tel" name="contact_phone" value="{{ old('contact_phone') }}" required class="{{ $input }}" dir="ltr">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="{{ $label }}">{{ $isAr ? 'البريد الإلكتروني (اختياري)' : 'Email (optional)' }}</label>
                            <input type="email" name="contact_email" value="{{ old('contact_email') }}" class="{{ $input }}" dir="ltr">
                        </div>
                    </div>
                </section>

                {{-- Doctor --}}
                <section class="bg-white rounded-[2rem] border border-slate-100 shadow-sm p-6 sm:p-8">
                    <h2 class="text-xl font-black text-slate-900 mb-5">{{ $isAr ? 'حساب الطبيب' : 'Doctor account' }}</h2>
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label class="{{ $label }}">{{ $isAr ? 'اسم الطبيب' : 'Doctor name' }} <span class="text-red-500">*</span></label>
                            <input type="text" name="doctor_name" value="{{ old('doctor_name') }}" required class="{{ $input }}">
                        </div>
                        <div>
                            <label class="{{ $label }}">{{ $isAr ? 'التخصص' : 'Specialty' }}</label>
                            <input type="text" name="doctor_specialty" value="{{ old('doctor_specialty') }}" class="{{ $input }}">
                        </div>
                        <div>
                            <label class="{{ $label }}">{{ $isAr ? 'اسم المستخدم' : 'Username' }}</label>
                            <input type="text" name="doctor_username" value="{{ old('doctor_username') }}" class="{{ $input }}" dir="ltr">
                        </div>
                        <div>
                            <label class="{{ $label }}">{{ $isAr ? 'رقم الجوال' : 'Mobile phone' }}</label>
                            <input type="tel" name="doctor_phone" value="{{ old('doctor_phone') }}" class="{{ $input }}" dir="ltr">
                        </div>
                        <div>
                            <label class="{{ $label }}">{{ $isAr ? 'البريد الإلكتروني' : 'Email' }}</label>
                            <input type="email" name="doctor_email" value="{{ old('doctor_email') }}" class="{{ $input }}" dir="ltr">
                        </div>
                        <div>
                            <label class="{{ $label }}">{{ $isAr ? 'كلمة المرور' : 'Password' }}</label>
                            <input type="password" name="doctor_password" class="{{ $input }}" dir="ltr" autocomplete="new-password">
                        </div>
                    </div>
                </section>

                {{-- Clinic --}}
                <section class="bg-white rounded-[2rem] border border-slate-100 shadow-sm p-6 sm:p-8">
                    <h2 class="text-xl font-black text-slate-900 mb-5">{{ $isAr ? 'بيانات العيادة' : 'Clinic details' }}</h2>
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label class="{{ $label }}">{{ $isAr ? 'اسم العيادة' : 'Clinic name' }}</label>
                            <input type="text" name="clinic_name" value="{{ old('clinic_name') }}" class="{{ $input }}">
                        </div>
                        <div>
                            <label class="{{ $label }}">{{ $isAr ? 'المدينة' : 'City' }}</label>
                            <input type="text" name="clinic_city" value="{{ old('clinic_city') }}" class="{{ $input }}">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="{{ $label }}">{{ $isAr ? 'العنوان' : 'Address' }}</label>
                            <input type="text" name="clinic_address" value="{{ old('clinic_address') }}" class="{{ $input }}">
                        </div>
                        <div>
                            <label class="{{ $label }}">{{ $isAr ? 'هاتف العيادة' : 'Clinic phone' }}</label>
                            <input type="tel" name="clinic_phone" value="{{ old('clinic_phone') }}" class="{{ $input }}" dir="ltr">
                        </div>
                    </div>
                </section>

                {{-- Assistants --}}
                <section class="bg-white rounded-[2rem] border border-slate-100 shadow-sm p-6 sm:p-8">
                    <div class="flex items-center justify-between mb-1">
                        <h2 class="text-xl font-black text-slate-900">{{ $isAr ? 'حسابات المساعدين' : 'Assistant accounts' }}</h2>
                    </div>
                    <p class="text-sm text-slate-500 mb-5">{{ $isAr ? 'أضف مساعدًا واحدًا أو أكثر حسب باقتك.' : 'Add one or more assistants, depending on your plan.' }}</p>

                    <div id="assistants-wrap" class="space-y-5">
                        @foreach ($oldAssistants as $i => $a)
                            <div class="assistant-row rounded-2xl border border-slate-100 bg-slate-50 p-5">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-sm font-bold text-slate-500 assistant-index">{{ $isAr ? 'مساعد' : 'Assistant' }} {{ $i + 1 }}</span>
                                    <button type="button" class="remove-assistant text-red-500 text-sm font-semibold hover:underline {{ $loop->count <= 1 ? 'hidden' : '' }}">{{ $isAr ? 'إزالة' : 'Remove' }}</button>
                                </div>
                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="{{ $label }}">{{ $isAr ? 'الاسم' : 'Name' }}</label>
                                        <input type="text" name="assistants[{{ $i }}][name]" value="{{ $a['name'] ?? '' }}" class="{{ $input }}">
                                    </div>
                                    <div>
                                        <label class="{{ $label }}">{{ $isAr ? 'اسم المستخدم' : 'Username' }}</label>
                                        <input type="text" name="assistants[{{ $i }}][username]" value="{{ $a['username'] ?? '' }}" class="{{ $input }}" dir="ltr">
                                    </div>
                                    <div>
                                        <label class="{{ $label }}">{{ $isAr ? 'رقم الجوال' : 'Mobile phone' }}</label>
                                        <input type="tel" name="assistants[{{ $i }}][phone]" value="{{ $a['phone'] ?? '' }}" class="{{ $input }}" dir="ltr">
                                    </div>
                                    <div>
                                        <label class="{{ $label }}">{{ $isAr ? 'كلمة المرور' : 'Password' }}</label>
                                        <input type="password" name="assistants[{{ $i }}][password]" class="{{ $input }}" dir="ltr" autocomplete="new-password">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" id="add-assistant" class="mt-5 inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border-2 border-dashed border-slate-300 text-slate-600 font-semibold hover:border-primary hover:text-primary transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ $isAr ? 'إضافة مساعد' : 'Add assistant' }}
                    </button>
                </section>

                {{-- Notes --}}
                <section class="bg-white rounded-[2rem] border border-slate-100 shadow-sm p-6 sm:p-8">
                    <label class="{{ $label }}">{{ $isAr ? 'ملاحظات إضافية (اختياري)' : 'Anything else (optional)' }}</label>
                    <textarea name="notes" rows="3" class="{{ $input }}">{{ old('notes') }}</textarea>
                </section>

                <p class="text-xs text-slate-400 text-center px-4">
                    {{ $isAr
                        ? 'تُحفظ كلمات المرور مشفّرة ولن تظهر لأحد سوى فريق التفعيل. بإرسالك النموذج فأنت توافق على تواصلنا معك.'
                        : 'Passwords are stored encrypted and shown only to the activation team. By submitting, you agree that we may contact you.' }}
                </p>

                <button type="submit" class="w-full py-4 bg-slate-900 text-white rounded-xl font-bold text-lg hover:bg-slate-800 transition-all shadow-xl hover:-translate-y-0.5">
                    {{ $isAr ? 'إرسال الطلب' : 'Submit request' }}
                </button>
            </form>
        </div>
    </main>

    <template id="assistant-template">
        <div class="assistant-row rounded-2xl border border-slate-100 bg-slate-50 p-5">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-bold text-slate-500 assistant-index">{{ $isAr ? 'مساعد' : 'Assistant' }}</span>
                <button type="button" class="remove-assistant text-red-500 text-sm font-semibold hover:underline">{{ $isAr ? 'إزالة' : 'Remove' }}</button>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $label }}">{{ $isAr ? 'الاسم' : 'Name' }}</label>
                    <input type="text" data-name="name" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">{{ $isAr ? 'اسم المستخدم' : 'Username' }}</label>
                    <input type="text" data-name="username" class="{{ $input }}" dir="ltr">
                </div>
                <div>
                    <label class="{{ $label }}">{{ $isAr ? 'رقم الجوال' : 'Mobile phone' }}</label>
                    <input type="tel" data-name="phone" class="{{ $input }}" dir="ltr">
                </div>
                <div>
                    <label class="{{ $label }}">{{ $isAr ? 'كلمة المرور' : 'Password' }}</label>
                    <input type="password" data-name="password" class="{{ $input }}" dir="ltr" autocomplete="new-password">
                </div>
            </div>
        </div>
    </template>

    <script>
        (function () {
            var wrap = document.getElementById('assistants-wrap');
            var tpl = document.getElementById('assistant-template');
            var addBtn = document.getElementById('add-assistant');
            var maxRows = 12;
            var idxLabel = @json($isAr ? 'مساعد' : 'Assistant');

            function reindex() {
                var rows = wrap.querySelectorAll('.assistant-row');
                rows.forEach(function (row, i) {
                    row.querySelector('.assistant-index').textContent = idxLabel + ' ' + (i + 1);
                    row.querySelectorAll('input').forEach(function (input) {
                        var key = input.getAttribute('data-name');
                        if (key) input.setAttribute('name', 'assistants[' + i + '][' + key + ']');
                        else {
                            var m = input.getAttribute('name') && input.getAttribute('name').match(/\]\[(\w+)\]$/);
                            if (m) input.setAttribute('name', 'assistants[' + i + '][' + m[1] + ']');
                        }
                    });
                    var rm = row.querySelector('.remove-assistant');
                    if (rm) rm.classList.toggle('hidden', rows.length <= 1);
                });
            }

            addBtn.addEventListener('click', function () {
                if (wrap.querySelectorAll('.assistant-row').length >= maxRows) return;
                var node = tpl.content.firstElementChild.cloneNode(true);
                wrap.appendChild(node);
                reindex();
            });

            wrap.addEventListener('click', function (e) {
                if (e.target.closest('.remove-assistant')) {
                    var row = e.target.closest('.assistant-row');
                    if (wrap.querySelectorAll('.assistant-row').length > 1) { row.remove(); reindex(); }
                }
            });

            reindex();
        })();
    </script>
@endsection
