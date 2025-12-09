@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'سياسة الخصوصية - مستشفي اون' : 'Privacy Policy - Mostashfa-on')

@section('content')
    <header class="bg-slate-900 text-white pt-40 pb-20 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-bl from-accent/20 to-blue-600/20 opacity-30"></div>
        <div class="absolute -bottom-1 left-0 w-full h-10 bg-slate-50" style="clip-path: polygon(0 100%, 100% 100%, 100% 0);"></div>
        
        <div class="container mx-auto px-4 relative z-10 text-center">
            <div class="w-16 h-16 bg-white/10 rounded-2xl mx-auto flex items-center justify-center mb-6 backdrop-blur-sm">
                <svg class="w-8 h-8 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h1 class="text-4xl md:text-5xl font-black mb-4">
                {{ app()->getLocale() === 'ar' ? 'سياسة الخصوصية' : 'Privacy Policy' }}
            </h1>
            <p class="text-slate-400">
                {{ app()->getLocale() === 'ar' ? 'كيف نقوم بجمع واستخدام وحماية بياناتك الصحية' : 'How we collect, use, and protect your health data' }}
            </p>
        </div>
    </header>

    <main class="py-16">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">
            
            <div class="bg-white border border-slate-200 p-8 rounded-2xl mb-12 shadow-sm text-center">
                <h3 class="font-bold text-slate-900 text-xl mb-3">
                    {{ app()->getLocale() === 'ar' ? 'بياناتك الصحية ملك لك وحدك' : 'Your Health Data Belongs to You' }}
                </h3>
                <p class="text-slate-600 leading-relaxed max-w-2xl mx-auto">
                    {{ app()->getLocale() === 'ar' 
                        ? 'في مستشفي اون، نتعامل مع معلوماتك الطبية بأقصى درجات السرية. نحن نلتزم بقانون حماية البيانات الشخصية المصري رقم 151 لسنة 2020 والمعايير العالمية.' 
                        : 'At Mostashfa-on, we treat your medical information with the highest level of confidentiality. We comply with the Egyptian Personal Data Protection Law No. 151 of 2020 and global standards.' }}
                </p>
            </div>

            <div class="space-y-12 text-slate-700 leading-loose">
                
                <section>
                    <h2 class="text-2xl font-bold text-slate-900 mb-4 flex items-center gap-3">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-primary text-sm font-bold">1</span>
                        {{ app()->getLocale() === 'ar' ? 'البيانات التي نجمعها' : 'Data We Collect' }}
                    </h2>
                    <div class="grid md:grid-cols-2 gap-4 mt-4">
                        <div class="bg-white p-4 rounded-lg border border-slate-100 shadow-sm">
                            <h4 class="font-bold text-slate-900 mb-2">{{ app()->getLocale() === 'ar' ? 'معلومات شخصية' : 'Personal Information' }}</h4>
                            <p class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'الاسم، رقم الهاتف، البريد الإلكتروني، العمر، والنوع.' : 'Name, phone number, email, age, and gender.' }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg border border-slate-100 shadow-sm">
                            <h4 class="font-bold text-slate-900 mb-2">{{ app()->getLocale() === 'ar' ? 'معلومات طبية' : 'Medical Information' }}</h4>
                            <p class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'السجل المرضي، الوصفات الطبية، نتائج التحاليل، والملاحظات التي يضيفها الأطباء.' : 'Medical history, prescriptions, lab results, and doctor notes.' }}</p>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-slate-900 mb-4 flex items-center gap-3">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-primary text-sm font-bold">2</span>
                        {{ app()->getLocale() === 'ar' ? 'كيف نستخدم بياناتك' : 'How We Use Your Data' }}
                    </h2>
                    <ul class="list-disc ps-10 space-y-2 marker:text-accent">
                        <li>
                            {{ app()->getLocale() === 'ar' 
                                ? 'لتمكين الأطباء من تشخيص حالتك وتقديم العلاج المناسب.' 
                                : 'To enable doctors to diagnose your condition and provide appropriate treatment.' }}
                        </li>
                        <li>
                            {{ app()->getLocale() === 'ar' 
                                ? 'لإدارة حجوزاتك وإرسال التذكيرات بمواعيد الأدوية.' 
                                : 'To manage your appointments and send medication reminders.' }}
                        </li>
                        <li>
                            {{ app()->getLocale() === 'ar' 
                                ? 'لتحسين جودة الخدمة واقتراح خدمات طبية تناسبك.' 
                                : 'To improve service quality and suggest relevant medical services.' }}
                        </li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-slate-900 mb-4 flex items-center gap-3">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-primary text-sm font-bold">3</span>
                        {{ app()->getLocale() === 'ar' ? 'مشاركة البيانات' : 'Data Sharing' }}
                    </h2>
                    <p class="bg-blue-50 p-4 rounded-lg border border-blue-100 text-blue-800 text-sm">
                        {{ app()->getLocale() === 'ar' 
                            ? 'نحن لا نقوم ببيع بياناتك لأي طرف ثالث بغرض التسويق أبداً.' 
                            : 'We NEVER sell your data to any third party for marketing purposes.' }}
                    </p>
                    <p class="mt-4">
                        {{ app()->getLocale() === 'ar' 
                            ? 'نشارك بياناتك فقط مع مقدمي الرعاية الصحية (الأطباء، المعامل، الصيدليات) الذين تختار التعامل معهم عبر التطبيق لإتمام الخدمة الطبية.' 
                            : 'We only share your data with healthcare providers (doctors, labs, pharmacies) that you choose to interact with via the app to fulfill the medical service.' }}
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-slate-900 mb-4 flex items-center gap-3">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-primary text-sm font-bold">4</span>
                        {{ app()->getLocale() === 'ar' ? 'أمان البيانات' : 'Data Security' }}
                    </h2>
                    <p>
                        {{ app()->getLocale() === 'ar' 
                            ? 'نستخدم تقنيات تشفير متقدمة (End-to-End Encryption) لحماية المحادثات والملفات الطبية. يتم تخزين البيانات على خوادم مؤمنة ومراقبة على مدار الساعة.' 
                            : 'We use advanced End-to-End Encryption to protect chats and medical files. Data is stored on secure servers monitored 24/7.' }}
                    </p>
                </section>

                 <section>
                    <h2 class="text-2xl font-bold text-slate-900 mb-4 flex items-center gap-3">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-primary text-sm font-bold">5</span>
                        {{ app()->getLocale() === 'ar' ? 'حقوقك' : 'Your Rights' }}
                    </h2>
                    <p>
                        {{ app()->getLocale() === 'ar' 
                            ? 'لديك الحق في طلب نسخة من بياناتك، أو تصحيح أي أخطاء، أو طلب حذف حسابك وبياناتك بالكامل في أي وقت عبر إعدادات التطبيق أو التواصل معنا.' 
                            : 'You have the right to request a copy of your data, correct any errors, or request the complete deletion of your account and data at any time via app settings or by contacting us.' }}
                    </p>
                </section>

            </div>

            <div class="mt-16 border-t border-slate-200 pt-8">
                <p class="text-slate-600 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'إذا كان لديك أي استفسار حول خصوصيتك، يرجى التواصل مع مسؤول حماية البيانات:' : 'If you have any questions about your privacy, please contact our Data Protection Officer:' }}
                </p>
                <a href="mailto:privacy@mostashfaon.com" class="text-accent font-bold hover:underline">privacy@mostashfaon.com</a>
            </div>

        </div>
    </main>
@endsection
