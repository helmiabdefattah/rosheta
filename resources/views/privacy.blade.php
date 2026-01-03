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
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-primary text-sm font-bold">1.1</span>
                        {{ app()->getLocale() === 'ar' ? 'صلاحيات الكاميرا والصور' : 'Camera and Photo Permissions' }}
                    </h2>
                    <div class="bg-blue-50 p-6 rounded-lg border border-blue-100 mb-4">
                        <h4 class="font-bold text-slate-900 mb-3">{{ app()->getLocale() === 'ar' ? 'استخدام الكاميرا والصور' : 'Camera and Photo Usage' }}</h4>
                        <p class="text-sm text-slate-700 mb-3">
                            {{ app()->getLocale() === 'ar' 
                                ? 'قد يطلب التطبيق الوصول إلى الكاميرا ومعرض الصور على جهازك. نستخدم هذه الصلاحيات حصرياً للأغراض التالية:' 
                                : 'The app may request access to your device\'s camera and photo gallery. We use these permissions exclusively for the following purposes:' }}
                        </p>
                        <ul class="list-disc ps-6 space-y-2 text-sm text-slate-700 marker:text-primary">
                            <li>
                                {{ app()->getLocale() === 'ar' 
                                    ? 'التقاط صور للروشتات الطبية والوصفات لتقديمها لمقدمي الخدمات الطبية (المعامل، الصيدليات)' 
                                    : 'Capturing photos of medical prescriptions and documents to submit to healthcare providers (laboratories, pharmacies)' }}
                            </li>
                            <li>
                                {{ app()->getLocale() === 'ar' 
                                    ? 'رفع صور من معرض الصور الخاص بك للروشتات الطبية الموجودة مسبقاً' 
                                    : 'Uploading existing photos from your gallery for medical prescriptions' }}
                            </li>
                        </ul>
                    </div>
                    <div class="bg-white p-4 rounded-lg border border-slate-100 shadow-sm mb-4">
                        <h4 class="font-bold text-slate-900 mb-2">{{ app()->getLocale() === 'ar' ? 'ما لا نفعله' : 'What We Do NOT Do' }}</h4>
                        <ul class="list-disc ps-6 space-y-2 text-sm text-slate-700 marker:text-red-500">
                            <li>
                                {{ app()->getLocale() === 'ar' 
                                    ? 'لا نصل إلى جميع الصور في معرض الصور - فقط الصور التي تختارها أنت' 
                                    : 'We do NOT access all photos in your gallery - only the photos you specifically select' }}
                            </li>
                            <li>
                                {{ app()->getLocale() === 'ar' 
                                    ? 'لا نستخدم الكاميرا لأي غرض آخر غير التقاط صور الروشتات الطبية' 
                                    : 'We do NOT use the camera for any purpose other than capturing prescription photos' }}
                            </li>
                            <li>
                                {{ app()->getLocale() === 'ar' 
                                    ? 'لا نشارك الصور مع أي أطراف ثالثة لأغراض تسويقية أو إعلانية' 
                                    : 'We do NOT share photos with any third parties for marketing or advertising purposes' }}
                            </li>
                            <li>
                                {{ app()->getLocale() === 'ar' 
                                    ? 'لا نستخدم الصور لإنشاء ملفات تعريف أو محتوى إعلاني' 
                                    : 'We do NOT use photos to create profiles or advertising content' }}
                            </li>
                        </ul>
                    </div>
                    <div class="bg-white p-4 rounded-lg border border-slate-100 shadow-sm">
                        <h4 class="font-bold text-slate-900 mb-2">{{ app()->getLocale() === 'ar' ? 'تخزين الصور' : 'Photo Storage' }}</h4>
                        <p class="text-sm text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' 
                                ? 'الصور التي ترفعها يتم تخزينها بشكل آمن على خوادمنا المشفرة ويتم الوصول إليها فقط من قبل:' 
                                : 'Photos you upload are stored securely on our encrypted servers and are only accessible by:' }}
                        </p>
                        <ul class="list-disc ps-6 space-y-1 text-sm text-slate-700 marker:text-primary">
                            <li>{{ app()->getLocale() === 'ar' ? 'مقدمي الخدمات الطبية المعتمدين الذين تختار التعامل معهم' : 'Authorized healthcare providers you choose to interact with' }}</li>
                            <li>{{ app()->getLocale() === 'ar' ? 'فريق الدعم الفني لدينا عند الحاجة لحل مشكلة تقنية' : 'Our technical support team when needed to resolve technical issues' }}</li>
                        </ul>
                        <p class="text-sm text-slate-700 mt-3">
                            {{ app()->getLocale() === 'ar' 
                                ? 'يمكنك حذف الصور المرفوعة في أي وقت من خلال حذف الطلب المرتبط بها أو التواصل معنا.' 
                                : 'You can delete uploaded photos at any time by deleting the associated request or contacting us.' }}
                        </p>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200 mt-4">
                        <p class="text-sm text-yellow-800">
                            <strong>{{ app()->getLocale() === 'ar' ? 'ملاحظة مهمة:' : 'Important Note:' }}</strong>
                            {{ app()->getLocale() === 'ar' 
                                ? 'يمكنك رفض منح صلاحيات الكاميرا أو الصور في أي وقت من إعدادات جهازك. في هذه الحالة، يمكنك إدخال معلومات الروشتة يدوياً بدلاً من رفع الصور.' 
                                : 'You can deny camera or photo permissions at any time from your device settings. In this case, you can manually enter prescription information instead of uploading photos.' }}
                        </p>
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
                    <p class="mb-4">
                        {{ app()->getLocale() === 'ar' 
                            ? 'نستخدم تقنيات تشفير متقدمة (End-to-End Encryption) لحماية المحادثات والملفات الطبية. يتم تخزين البيانات على خوادم مؤمنة ومراقبة على مدار الساعة.' 
                            : 'We use advanced End-to-End Encryption to protect chats and medical files. Data is stored on secure servers monitored 24/7.' }}
                    </p>
                    <div class="bg-white p-4 rounded-lg border border-slate-100 shadow-sm">
                        <h4 class="font-bold text-slate-900 mb-2">{{ app()->getLocale() === 'ar' ? 'حماية الصور والملفات' : 'Photo and File Protection' }}</h4>
                        <p class="text-sm text-slate-700">
                            {{ app()->getLocale() === 'ar' 
                                ? 'جميع الصور والملفات الطبية المرفوعة يتم تشفيرها أثناء النقل والتخزين. نستخدم بروتوكولات SSL/TLS لتأمين نقل البيانات ونقوم بتخزين الملفات على خوادم آمنة مع وصول محدود.' 
                                : 'All uploaded photos and medical files are encrypted during transmission and storage. We use SSL/TLS protocols to secure data transfer and store files on secure servers with restricted access.' }}
                        </p>
                    </div>
                </section>

                 <section>
                    <h2 class="text-2xl font-bold text-slate-900 mb-4 flex items-center gap-3">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-primary text-sm font-bold">5</span>
                        {{ app()->getLocale() === 'ar' ? 'حقوقك' : 'Your Rights' }}
                    </h2>
                    <p class="mb-4">
                        {{ app()->getLocale() === 'ar' 
                            ? 'لديك الحق في طلب نسخة من بياناتك، أو تصحيح أي أخطاء، أو طلب حذف حسابك وبياناتك بالكامل في أي وقت عبر إعدادات التطبيق أو التواصل معنا.' 
                            : 'You have the right to request a copy of your data, correct any errors, or request the complete deletion of your account and data at any time via app settings or by contacting us.' }}
                    </p>
                    <div class="bg-white p-4 rounded-lg border border-slate-100 shadow-sm">
                        <h4 class="font-bold text-slate-900 mb-2">{{ app()->getLocale() === 'ar' ? 'حقوقك المتعلقة بالصور' : 'Your Rights Regarding Photos' }}</h4>
                        <ul class="list-disc ps-6 space-y-2 text-sm text-slate-700 marker:text-primary">
                            <li>
                                {{ app()->getLocale() === 'ar' 
                                    ? 'الحق في رفض منح صلاحيات الكاميرا أو الصور في أي وقت' 
                                    : 'The right to deny camera or photo permissions at any time' }}
                            </li>
                            <li>
                                {{ app()->getLocale() === 'ar' 
                                    ? 'الحق في حذف أي صور قمت برفعها من خلال حذف الطلب المرتبط بها' 
                                    : 'The right to delete any photos you uploaded by deleting the associated request' }}
                            </li>
                            <li>
                                {{ app()->getLocale() === 'ar' 
                                    ? 'الحق في طلب حذف جميع صورك المخزنة على خوادمنا' 
                                    : 'The right to request deletion of all your stored photos from our servers' }}
                            </li>
                            <li>
                                {{ app()->getLocale() === 'ar' 
                                    ? 'الحق في معرفة من يصل إلى صورك ومتى' 
                                    : 'The right to know who accesses your photos and when' }}
                            </li>
                        </ul>
                    </div>
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
