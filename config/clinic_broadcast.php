<?php

/*
|--------------------------------------------------------------------------
| Clinic broadcast messages
|--------------------------------------------------------------------------
| Ready-made messages a clinic can push to the patients who have an
| appointment today. Each template carries both languages so every patient
| is notified in their own locale, regardless of the staff member's locale.
| The dashboard modal reads these to render the quick-pick buttons; the
| controller reads them to resolve a chosen key into ar + en text.
*/

return [
    // The notification title shown to patients (per language).
    'title' => [
        'ar' => 'رسالة من العيادة',
        'en' => 'Message from the clinic',
    ],

    'templates' => [
        'doctor_arrived' => [
            'ar' => 'وصل الطبيب إلى العيادة.',
            'en' => 'The doctor has arrived at the clinic.',
        ],
        'arrive_30' => [
            'ar' => 'سيصل الطبيب خلال 30 دقيقة تقريباً.',
            'en' => 'The doctor will arrive in about 30 minutes.',
        ],
        'arrive_60' => [
            'ar' => 'سيصل الطبيب خلال ساعة تقريباً.',
            'en' => 'The doctor will arrive in about 1 hour.',
        ],
        'running_late' => [
            'ar' => 'المواعيد تسير متأخرة قليلاً اليوم، نشكر لكم حسن تفهمكم.',
            'en' => 'Appointments are running a little late today. Thank you for your patience.',
        ],
        'your_turn_soon' => [
            'ar' => 'اقترب دوركم، يُرجى التواجد في العيادة.',
            'en' => 'Your turn is coming up soon. Please be at the clinic.',
        ],
        'cancelled_today' => [
            'ar' => 'نعتذر، تم إلغاء مواعيد اليوم. سنتواصل معكم لإعادة الجدولة.',
            'en' => "We're sorry, today's appointments have been cancelled. We'll contact you to reschedule.",
        ],
    ],
];
