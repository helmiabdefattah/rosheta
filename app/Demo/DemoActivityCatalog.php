<?php

namespace App\Demo;

/**
 * What each route MEANS, in a sentence a non-developer can read.
 *
 * The admin page is read by whoever decides what to build next, not by whoever
 * wrote the routes, so "practice.doctor.prescriptions.store" is worth nothing
 * there and "كتب روشتة" is worth everything.
 *
 * Each entry is [kind, feature, arabic, english]:
 *   kind    — 'action' (they changed something) or 'page' (they looked).
 *   feature — the part of the product it belongs to, so the analysis can say
 *             "reached billing" instead of listing twelve route names.
 *
 * Anything not listed is still recorded, with a label derived from its route
 * name: a new feature should appear in the analysis the day it ships, not the
 * day somebody remembers to add it here.
 */
class DemoActivityCatalog
{
    /** Features, in the order a clinic actually works through them. */
    public const FEATURES = [
        'reception' => ['الاستقبال والحجز', 'Reception & booking'],
        'queue' => ['الطابور وشاشة الانتظار', 'Queue & waiting display'],
        'examination' => ['الكشف والتشخيص', 'Examination & diagnosis'],
        'prescribing' => ['الروشتة والأدوية', 'Prescribing'],
        'requests' => ['التحاليل والأشعة', 'Tests & imaging'],
        'records' => ['ملفات المرضى', 'Patient records'],
        'billing' => ['الحساب والتحصيل', 'Billing & collections'],
        'insurance' => ['التأمين', 'Insurance'],
        'printing' => ['الطباعة', 'Printing'],
        'setup' => ['إعداد العيادة', 'Clinic setup'],
        'reports' => ['التقارير والإدارة', 'Reports & management'],
        'demo' => ['التجربة نفسها', 'The trial itself'],
    ];

    /**
     * Requests that say nothing about intent: pollers and heartbeats the page
     * fires on a timer. Recording these would drown the journey in noise and
     * multiply the row count of every run.
     */
    public const IGNORED_ROUTES = [
        'demo.status',

        // Covered by the milestones the controller raises instead, which say
        // the same thing in the visitor's terms.
        'demo.start',
        'demo.build',

        'practice.assistant.printer.status',
        'practice.display.current',
    ];

    /** Paths with nothing worth keeping (assets, dev tooling, probes). */
    public const IGNORED_PATH_PATTERNS = [
        'build/*', 'storage/*', 'images/*', 'css/*', 'js/*', 'fonts/*',
        'favicon.ico', 'robots.txt', 'up', '_debugbar/*', 'livewire/*',
        'sanctum/*',
    ];

    /** @var array<string, array{0:string,1:string,2:string,3:string}> */
    public const ROUTES = [
        // --- the trial itself -------------------------------------------------
        'demo.preparing' => ['page', 'demo', 'انتظر تجهيز العيادة', 'Waited for the clinic to be built'],
        'demo.ended' => ['page', 'demo', 'وصل لصفحة انتهاء التجربة', 'Reached the "trial ended" page'],
        'demo.survey' => ['action', 'demo', 'أرسل رأيه في التجربة', 'Submitted the exit survey'],
        'demo.switch-role' => ['action', 'demo', 'بدّل دوره', 'Switched role'],
        'demo.reset' => ['action', 'demo', 'أعاد بناء العيادة من الصفر', 'Reset the clinic'],
        'demo.end' => ['action', 'demo', 'أنهى التجربة بنفسه', 'Ended the trial'],

        // --- reception --------------------------------------------------------
        'practice.assistant.dashboard' => ['page', 'reception', 'فتح شاشة المساعد', 'Opened the assistant desk'],
        'practice.assistant.appointments.store' => ['action', 'reception', 'حجز موعداً لمريض', 'Booked an appointment'],
        'practice.assistant.pending.confirm' => ['action', 'reception', 'أكّد حجزاً معلّقاً', 'Confirmed a pending booking'],
        'practice.assistant.pending.cancel' => ['action', 'reception', 'ألغى حجزاً معلّقاً', 'Cancelled a pending booking'],
        'practice.kiosk.welcome' => ['page', 'reception', 'فتح كشك المريض', 'Opened the patient kiosk'],
        'practice.kiosk.lookup' => ['action', 'reception', 'بحث عن مريض من الكشك', 'Looked a patient up at the kiosk'],
        'practice.kiosk.register' => ['page', 'reception', 'فتح تسجيل مريض جديد بالكشك', 'Opened kiosk registration'],
        'practice.kiosk.store' => ['action', 'reception', 'سجّل مريضاً جديداً من الكشك', 'Registered a patient at the kiosk'],
        'practice.kiosk.book' => ['action', 'reception', 'حجز دوراً من الكشك', 'Took a queue number at the kiosk'],
        'practice.kiosk.ticket' => ['page', 'reception', 'عرض تذكرة الدور', 'Viewed the queue ticket'],
        'practice.kiosk.prescription' => ['page', 'reception', 'فتح روشتة من الكشك', 'Opened a prescription at the kiosk'],
        'practice.kiosk.prescription.print' => ['action', 'printing', 'طبع روشتة من الكشك', 'Printed a prescription at the kiosk'],

        // --- queue ------------------------------------------------------------
        'practice.display.screen' => ['page', 'queue', 'فتح شاشة غرفة الانتظار', 'Opened the waiting-room display'],
        'practice.display.next' => ['action', 'queue', 'نادى على المريض التالي', 'Called the next patient'],
        'practice.appointments.status' => ['action', 'queue', 'غيّر حالة الحجز', 'Changed an appointment status'],
        'practice.notifications.broadcast' => ['action', 'queue', 'أرسل تنبيهاً للمرضى', 'Broadcast a notification'],
        'practice.notifications.queue' => ['action', 'queue', 'أرسل ترتيب الدور لمريض', 'Sent a queue position'],

        // --- examination ------------------------------------------------------
        'practice.doctor.dashboard' => ['page', 'examination', 'فتح شاشة عمل الطبيب', 'Opened the doctor workspace'],
        'practice.doctor.examine' => ['page', 'examination', 'بدأ الكشف على مريض', 'Opened a patient examination'],
        'practice.doctor.examination-values.store' => ['action', 'examination', 'ملأ حقول الكشف', 'Filled in examination fields'],
        'practice.doctor.diagnosis.store' => ['action', 'examination', 'كتب تشخيصاً', 'Wrote a diagnosis'],
        'practice.doctor.diagnoses.update' => ['action', 'examination', 'عدّل تشخيصاً', 'Edited a diagnosis'],
        'practice.doctor.chart-notes.store' => ['action', 'examination', 'أضاف ملاحظة على الملف', 'Added a chart note'],
        'practice.doctor.chart-notes.destroy' => ['action', 'examination', 'حذف ملاحظة من الملف', 'Deleted a chart note'],

        // --- prescribing ------------------------------------------------------
        'practice.doctor.medicines.search' => ['action', 'prescribing', 'بحث عن دواء', 'Searched for a medicine'],
        'practice.doctor.prescriptions.store' => ['action', 'prescribing', 'كتب روشتة', 'Wrote a prescription'],
        'practice.prescriptions.print' => ['page', 'printing', 'فتح الروشتة للطباعة', 'Opened a prescription to print'],
        'practice.prescriptions.pdf' => ['action', 'printing', 'حمّل الروشتة PDF', 'Downloaded a prescription PDF'],
        'practice.prescriptions.print-thermal' => ['action', 'printing', 'طبع الروشتة على الطابعة الحرارية', 'Printed a prescription (thermal)'],

        // --- tests & imaging --------------------------------------------------
        'practice.doctor.requests.store' => ['action', 'requests', 'طلب تحاليل أو أشعة', 'Ordered tests or imaging'],
        'practice.doctor.requests.destroy' => ['action', 'requests', 'ألغى طلب تحاليل', 'Cancelled a test order'],
        'practice.doctor.tests.store' => ['action', 'requests', 'سجّل نتيجة تحليل', 'Recorded a test result'],
        'practice.doctor.tests.destroy' => ['action', 'requests', 'حذف نتيجة تحليل', 'Deleted a test result'],

        // --- patient records --------------------------------------------------
        'practice.patients.show' => ['page', 'records', 'فتح ملف مريض', 'Opened a patient file'],
        'practice.patients.update' => ['action', 'records', 'عدّل بيانات مريض', 'Edited patient details'],
        'practice.doctor.allergies.update' => ['action', 'records', 'سجّل حساسية للمريض', 'Recorded an allergy'],
        'practice.doctor.chronic.update' => ['action', 'records', 'سجّل مرضاً مزمناً', 'Recorded a chronic condition'],
        'practice.attachments.store' => ['action', 'records', 'رفع مرفقاً لملف المريض', 'Uploaded an attachment'],
        'practice.attachments.destroy' => ['action', 'records', 'حذف مرفقاً', 'Deleted an attachment'],

        // --- billing ----------------------------------------------------------
        'practice.appointment-items.store' => ['action', 'billing', 'أضاف بنداً للحساب', 'Added a billable item'],
        'practice.appointment-items.destroy' => ['action', 'billing', 'حذف بنداً من الحساب', 'Removed a billable item'],
        'practice.collections.store' => ['action', 'billing', 'سجّل تحصيل مبلغ', 'Recorded a payment'],
        'practice.collections.destroy' => ['action', 'billing', 'ألغى تحصيلاً', 'Reversed a payment'],
        'practice.appointments.price' => ['action', 'billing', 'عدّل سعر الكشف', 'Changed the visit price'],
        'practice.appointments.type' => ['action', 'billing', 'غيّر نوع الزيارة', 'Changed the visit type'],

        // --- insurance --------------------------------------------------------
        'practice.appointments.insurance.store' => ['action', 'insurance', 'ربط الزيارة بتأمين', 'Attached insurance to a visit'],
        'practice.appointments.insurance.destroy' => ['action', 'insurance', 'فكّ ربط التأمين', 'Detached insurance'],
        'practice.doctor.manager.insurance-collections' => ['page', 'insurance', 'راجع تحصيلات التأمين', 'Reviewed insurance collections'],
        'practice.doctor.manager.insurance-collections.store' => ['action', 'insurance', 'سجّل تحصيل تأمين', 'Recorded an insurance collection'],
        'practice.doctor.manager.insurance-report' => ['page', 'insurance', 'فتح تقرير التأمين', 'Opened the insurance report'],

        // --- printing ---------------------------------------------------------
        'practice.appointments.ticket' => ['page', 'printing', 'عرض تذكرة الحجز', 'Viewed a booking ticket'],
        'practice.assistant.appointments.print-ticket' => ['action', 'printing', 'طبع تذكرة الحجز', 'Printed a booking ticket'],
        'demo.print.ticket' => ['page', 'printing', 'عاين تذكرة الطباعة', 'Previewed a printed ticket'],
        'demo.print.prescription' => ['page', 'printing', 'عاين طباعة الروشتة', 'Previewed a printed prescription'],
        'demo.print.sheet' => ['page', 'printing', 'عاين ورقة الروشتة', 'Previewed the prescription sheet'],

        // --- setup ------------------------------------------------------------
        'practice.doctor.setup.index' => ['page', 'setup', 'فتح إعدادات العيادة', 'Opened clinic setup'],
        'practice.doctor.clinic.edit' => ['page', 'setup', 'فتح بيانات العيادة', 'Opened the clinic profile'],
        'practice.doctor.clinic.update' => ['action', 'setup', 'عدّل بيانات العيادة', 'Edited the clinic profile'],
        'practice.doctor.clinic.switch' => ['action', 'setup', 'بدّل بين العيادات', 'Switched clinic'],
        'practice.doctor.setup.examination-fields' => ['page', 'setup', 'فتح حقول الكشف', 'Opened examination fields'],
        'practice.doctor.setup.examination-fields.store' => ['action', 'setup', 'أضاف حقل كشف', 'Added an examination field'],
        'practice.doctor.setup.examination-fields.update' => ['action', 'setup', 'عدّل حقل كشف', 'Edited an examination field'],
        'practice.doctor.setup.examination-fields.destroy' => ['action', 'setup', 'حذف حقل كشف', 'Deleted an examination field'],
        'practice.doctor.setup.medical-plans' => ['page', 'setup', 'فتح الخطط العلاجية', 'Opened medical plans'],
        'practice.doctor.setup.medical-plans.edit' => ['page', 'setup', 'فتح خطة علاجية', 'Opened a medical plan'],
        'practice.doctor.setup.medical-plans.store' => ['action', 'setup', 'أنشأ خطة علاجية', 'Created a medical plan'],
        'practice.doctor.setup.medical-plans.update' => ['action', 'setup', 'عدّل خطة علاجية', 'Edited a medical plan'],
        'practice.doctor.setup.medical-plans.destroy' => ['action', 'setup', 'حذف خطة علاجية', 'Deleted a medical plan'],

        // --- reports ----------------------------------------------------------
        'practice.doctor.manager.index' => ['page', 'reports', 'فتح لوحة الإدارة', 'Opened the manager dashboard'],
        'practice.doctor.manager.collections' => ['page', 'reports', 'راجع تقرير التحصيلات', 'Reviewed the collections report'],
        'practice.doctor.manager.patients' => ['page', 'reports', 'تصفّح سجل المرضى', 'Browsed the patient register'],
    ];

    /** Milestones the controllers raise directly, not derived from a route. */
    public const SYSTEM = [
        'demo.started' => ['system', 'demo', 'بدأ التجربة', 'Started the trial'],
        'demo.built' => ['system', 'demo', 'جُهّزت العيادة ودخل إليها', 'Clinic built and opened'],
        'demo.role.switched' => ['system', 'demo', 'بدّل دوره', 'Switched role'],
        'demo.reset' => ['system', 'demo', 'أعاد بناء العيادة', 'Rebuilt the clinic'],
        'demo.ended.user_ended' => ['system', 'demo', 'أنهى التجربة بنفسه', 'Ended the trial themselves'],
        'demo.ended.expired' => ['system', 'demo', 'انتهى وقت التجربة', 'The trial window ran out'],
        'demo.ended.idle' => ['system', 'demo', 'تركها بلا نشاط حتى أُغلقت', 'Went idle until it closed'],
        'demo.ended.purged' => ['system', 'demo', 'أُغلقت التجربة', 'The trial was closed'],
        'demo.ended.converted' => ['system', 'demo', 'أنشأ حساباً حقيقياً', 'Opened a real account'],
    ];

    /** @return array{0:string,1:string,2:string,3:string}|null */
    public static function forRoute(?string $routeName): ?array
    {
        return $routeName === null ? null : (self::ROUTES[$routeName] ?? null);
    }

    /** @return array{0:string,1:string,2:string,3:string}|null */
    public static function forSystem(string $action): ?array
    {
        return self::SYSTEM[$action] ?? null;
    }

    public static function featureLabel(string $key, bool $arabic = true): string
    {
        return self::FEATURES[$key][$arabic ? 0 : 1] ?? $key;
    }

    /** @return array<int,string> */
    public static function featureKeys(): array
    {
        return array_keys(self::FEATURES);
    }
}
