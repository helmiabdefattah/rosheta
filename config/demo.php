<?php

/*
|--------------------------------------------------------------------------
| Demo Sandbox
|--------------------------------------------------------------------------
|
| A visitor can open a fully populated clinic without signing up, try the
| doctor and assistant workspaces, and have every trace hard-deleted when the
| trial ends. All demo data lives in a SEPARATE database (see the "demo"
| connection in config/database.php) so the production database is never
| written to from a demo request.
|
| Every value has a safe default: with none of these set in .env the feature
| is disabled and the application behaves exactly as it did before.
|
*/

return [

    // Master switch. When false, the login-page button disappears and
    // POST /demo/start returns a friendly "temporarily unavailable" page.
    'enabled' => env('DEMO_ENABLED', false),

    // The database connection holding all demo tenants. Must exist in
    // config/database.php and have the same structure as production
    // (build it with: php artisan demo:setup).
    'connection' => env('DEMO_DB_CONNECTION', 'demo'),

    // Throws the moment a demo request attempts a write on the production
    // connection. This is the actual guarantee, not the second connection.
    'prod_write_guard' => env('DEMO_PROD_WRITE_GUARD', true),

    // Tables a demo request may still write on the PRODUCTION connection.
    // These are marketing records that must outlive the demo tenant.
    'prod_write_allowlist' => ['demo_sessions'],

    // Filesystem disk for demo uploads. Files are written under
    // demo/{doctor_id}/ so a purge can delete them by prefix.
    'disk' => env('DEMO_STORAGE_DISK', 'demo'),
    'storage_prefix' => env('DEMO_STORAGE_PREFIX', 'demo'),

    // Kept off the production cache/queue so demo entries never mix in.
    'cache_prefix' => env('DEMO_CACHE_PREFIX', 'demo:'),
    'queue' => env('DEMO_QUEUE', 'demo'),

    // The cookie that marks a request as a demo request. Read BEFORE the
    // session starts, so the connection switch never depends on the session.
    'cookie' => env('DEMO_COOKIE', 'mo_demo'),

    // Demo requests get their own session cookie so a demo session can never
    // be mistaken for a production one.
    'session_cookie' => env('DEMO_SESSION_COOKIE', 'rosheta_demo_session'),

    // Lifetime.
    'max_duration_minutes' => (int) env('DEMO_MAX_DURATION_MINUTES', 240),
    'idle_timeout_minutes' => (int) env('DEMO_IDLE_TIMEOUT_MINUTES', 45),

    // Abuse limits.
    'max_starts_per_ip_per_day' => (int) env('DEMO_MAX_STARTS_PER_IP_PER_DAY', 10),
    'max_concurrent_per_ip' => (int) env('DEMO_MAX_CONCURRENT_PER_IP', 3),
    'global_max_active' => (int) env('DEMO_GLOBAL_MAX_ACTIVE', 500),

    // Uploads.
    'max_file_mb' => (int) env('DEMO_MAX_FILE_MB', 5),
    'max_tenant_storage_mb' => (int) env('DEMO_MAX_TENANT_STORAGE_MB', 50),

    // Africa/Cairo is already the app timezone; kept explicit because every
    // seeded date is computed as an offset from "now" in this zone.
    // NOTE: Egypt reintroduced DST in April 2023, so this zone DOES shift.
    // Always use calendar arithmetic (addDays + setTimeFromTimeString),
    // never epoch arithmetic, when resolving template offsets.
    'timezone' => env('DEMO_TIMEZONE', 'Africa/Cairo'),

    // Reference data mirrored production -> demo, one way, by demo:setup and
    // demo:sync-reference. Never add patient, visit or user tables here; the
    // sync command refuses them.
    'reference_tables' => array_filter(explode(',', (string) env(
        'DEMO_REFERENCE_TABLES',
        'specializations,governorates,cities,areas,insurance_companies,laboratories,medical_tests,medicines'
    ))),

    // Tables the reference sync must never copy, whatever the env var says.
    // This is a legal requirement, not a preference.
    'reference_forbidden' => [
        'clients', 'users', 'appointments', 'prescriptions', 'prescription_items',
        'diagnoses', 'medical_requests', 'patient_tests', 'collections',
        'appointment_items', 'appointment_insurances', 'attachments',
        'client_addresses', 'client_requests', 'client_request_lines',
        'conversations', 'chat_messages', 'orders', 'order_lines', 'offers',
        'offer_lines', 'doctors', 'clinics', 'personal_access_tokens', 'sessions',
    ],

    // Tables that exist for infrastructure and are created empty in the demo
    // database rather than mirrored.
    'skip_tables' => [
        'telescope_entries', 'telescope_entries_tags', 'telescope_monitoring',
        'demo_sessions',
    ],
];
