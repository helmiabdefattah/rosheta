@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'الشروط والأحكام - مستشفي اون' : 'Terms & Conditions - Mostashfa-on')

@section('content')
    <header class="bg-slate-900 text-white pt-40 pb-20 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-tr from-primary/20 to-teal-500/20 opacity-30"></div>
        <div class="absolute -bottom-1 left-0 w-full h-10 bg-slate-50" style="clip-path: polygon(0 100%, 100% 100%, 100% 0);"></div>
        
        <div class="container mx-auto px-4 relative z-10 text-center">
            <h1 class="text-4xl md:text-5xl font-black mb-4">
                {{ app()->getLocale() === 'ar' ? 'الشروط والأحكام' : 'Terms & Conditions' }}
            </h1>
            <p class="text-slate-400">
                {{ app()->getLocale() === 'ar' ? 'آخر تحديث: ' . date('d/m/Y') : 'Last Updated: ' . date('F d, Y') }}
            </p>
        </div>
    </header>

    <main class="py-16">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">
            
            <div class="bg-amber-50 border-l-4 border-amber-500 p-6 rounded-r-lg mb-12 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="text-amber-500 mt-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-amber-800 text-lg mb-2">
                            {{ app()->getLocale() === 'ar' ? 'إخلاء مسؤولية طبية هام' : 'Important Medical Disclaimer' }}
                        </h3>
                        <p class="text-amber-700 text-sm leading-relaxed">
                            {{ app()->getLocale() === 'ar' 
                                ? 'مستشفي اون هو منصة تقنية للربط بين مقدمي الخدمة والمرضى. نحن لا نقدم خدمات طبية مباشرة ولا نتحمل مسؤولية التشخيص أو العلاج. في حالات الطوارئ الطبية، يرجى الاتصال بالإسعاف (123) فوراً.' 
                                : 'Mostashfa-on is a technical platform connecting providers with patients. We do not provide direct medical services and are not responsible for diagnosis or treatment. In case of medical emergencies, please call ambulance (123) immediately.' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-12 text-slate-700 leading-loose">
                
                <section>
                    <h2 class="text-2xl font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <span class="text-primary">1.</span>
                        {{ app()->getLocale() === 'ar' ? 'مقدمة' : 'Introduction' }}
                    </h2>
                    <p>
                        {{ app()->getLocale() === 'ar' 
                            ? 'مرحباً بكم في مستشفي اون. باستخدامك لهذا التطبيق، فإنك توافق على الالتزام بهذه الشروط والأحكام. إذا كنت لا توافق على أي جزء من هذه الشروط، يرجى التوقف عن استخدام التطبيق فوراً.' 
                            : 'Welcome to Mostashfa-on. By using this application, you agree to be bound by these terms and conditions. If you do not agree with any part of these terms, please stop using the application immediately.' }}
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <span class="text-primary">2.</span>
                        {{ app()->getLocale() === 'ar' ? 'الحسابات والتسجيل' : 'Accounts & Registration' }}
                    </h2>
                    <ul class="list-disc ps-5 space-y-2 marker:text-primary">
                        <li>
                            {{ app()->getLocale() === 'ar' 
                                ? 'يجب أن تكون المعلومات المقدمة أثناء التسجيل دقيقة وحديثة.' 
                                : 'Information provided during registration must be accurate and up-to-date.' }}
                        </li>
                        <li>
                            {{ app()->getLocale() === 'ar' 
                                ? 'أنت مسؤول عن الحفاظ على سرية كلمة المرور الخاصة بحسابك.' 
                                : 'You are responsible for maintaining the confidentiality of your account password.' }}
                        </li>
                        <li>
                            {{ app()->getLocale() === 'ar' 
                                ? 'يجب ألا يقل عمر المستخدم عن 18 عاماً لإنشاء حساب مستقل.' 
                                : 'Users must be at least 18 years old to create an independent account.' }}
                        </li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <span class="text-primary">3.</span>
                        {{ app()->getLocale() === 'ar' ? 'حجز المواعيد والمدفوعات' : 'Appointments & Payments' }}
                    </h2>
                    <p class="mb-4">
                        {{ app()->getLocale() === 'ar' 
                            ? 'نحن نعمل كوسيط بينك وبين مقدمي الخدمات الطبية (الأطباء، المستشفيات، المعامل).' 
                            : 'We act as an intermediary between you and medical service providers (doctors, hospitals, labs).' }}
                    </p>
                    <ul class="list-disc ps-5 space-y-2 marker:text-primary">
                        <li>
                            {{ app()->getLocale() === 'ar' 
                                ? 'يتم تحديد رسوم الكشف من قبل الطبيب أو المركز الطبي.' 
                                : 'Consultation fees are set by the doctor or medical center.' }}
                        </li>
                        <li>
                            {{ app()->getLocale() === 'ar' 
                                ? 'في حالة إلغاء الموعد، تطبق سياسة الاسترجاع الخاصة بمقدم الخدمة.' 
                                : 'In case of cancellation, the service provider\'s refund policy applies.' }}
                        </li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <span class="text-primary">4.</span>
                        {{ app()->getLocale() === 'ar' ? 'حقوق الملكية الفكرية' : 'Intellectual Property' }}
                    </h2>
                    <p>
                        {{ app()->getLocale() === 'ar' 
                            ? 'جميع الحقوق المتعلقة بالتصميم، الشعار، البرمجيات، والمحتوى داخل تطبيق مستشفي اون محفوظة للشركة المالكة. يمنع نسخ أو إعادة استخدام أي جزء بدون إذن كتابي.' 
                            : 'All rights related to the design, logo, software, and content within the Mostashfa-on app are reserved to the owning company. Copying or reusing any part without written permission is prohibited.' }}
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <span class="text-primary">5.</span>
                        {{ app()->getLocale() === 'ar' ? 'إنهاء الخدمة' : 'Termination' }}
                    </h2>
                    <p>
                        {{ app()->getLocale() === 'ar' 
                            ? 'نحتفظ بالحق في تعليق أو إنهاء حسابك فوراً ودون سابق إنذار إذا انتهكت هذه الشروط، أو في حالة إساءة استخدام الخدمة (مثل الحجوزات الوهمية المتكررة).' 
                            : 'We reserve the right to suspend or terminate your account immediately without notice if you violate these terms, or in case of service abuse (such as repeated fake bookings).' }}
                    </p>
                </section>

                 <section>
                    <h2 class="text-2xl font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <span class="text-primary">6.</span>
                        {{ app()->getLocale() === 'ar' ? 'القانون الواجب التطبيق' : 'Governing Law' }}
                    </h2>
                    <p>
                        {{ app()->getLocale() === 'ar' 
                            ? 'تخضع هذه الشروط والأحكام لقوانين جمهورية مصر العربية، وتختص محاكم القاهرة بالفصل في أي نزاع ينشأ عنها.' 
                            : 'These terms and conditions are governed by the laws of the Arab Republic of Egypt, and Cairo courts shall have jurisdiction over any dispute arising therefrom.' }}
                    </p>
                </section>

            </div>

            <div class="mt-16 p-8 bg-slate-100 rounded-2xl text-center">
                <h3 class="font-bold text-xl mb-2">{{ app()->getLocale() === 'ar' ? 'لديك أسئلة؟' : 'Have Questions?' }}</h3>
                <p class="text-slate-600 mb-4">
                    {{ app()->getLocale() === 'ar' ? 'فريقنا القانوني جاهز للرد على استفساراتك.' : 'Our legal team is ready to answer your inquiries.' }}
                </p>
                <a href="mailto:legal@mostashfaon.com" class="text-primary font-bold hover:underline">legal@mostashfaon.com</a>
            </div>

        </div>
    </main>
@endsection
