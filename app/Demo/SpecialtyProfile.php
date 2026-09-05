<?php

namespace App\Demo;

use Illuminate\Support\Facades\File;

/**
 * Specialty-specific demo content, loaded from JSON.
 *
 * A dentist who presses "try the system" should land in a dental clinic —
 * dental services, dental cases, a tooth number on the examination form — not
 * an internal-medicine clinic with chest infections. Each profile in
 * resources/demo-templates/specialties/ describes one specialty's clinic, and
 * DemoSeeder overlays it onto the tenant after the generic onboarding run.
 *
 * Adding a specialty is a new JSON file and nothing else — no code change.
 * A specialization with no profile falls back to the general internal-medicine
 * content that ClinicOnboardingService already produces.
 */
class SpecialtyProfile
{
    /** @var array<string, array>|null Loaded profiles, keyed by slug. */
    private static ?array $bySlug = null;

    public static function path(): string
    {
        return resource_path('demo-templates/specialties');
    }

    /**
     * The profile covering this specialization slug, or null to use the
     * general content.
     */
    public static function forSlug(?string $slug): ?array
    {
        if ($slug === null || $slug === '') {
            return null;
        }

        return static::all()[$slug] ?? null;
    }

    /**
     * Every profile, indexed by each slug it covers. A single file can serve
     * several specializations — "أسنان", "تقويم أسنان" and "جراحة فم وأسنان"
     * all map to the dental clinic.
     *
     * @return array<string, array>
     */
    public static function all(): array
    {
        if (static::$bySlug !== null) {
            return static::$bySlug;
        }

        static::$bySlug = [];

        if (! File::isDirectory(static::path())) {
            return static::$bySlug;
        }

        foreach (File::glob(static::path().'/*.json') as $file) {
            $profile = json_decode(File::get($file), true);

            if (! is_array($profile)) {
                throw new \RuntimeException("Demo specialty profile is not valid JSON: {$file}");
            }

            static::validate($profile, $file);

            $slugs = $profile['slugs'] ?? [basename($file, '.json')];

            foreach ($slugs as $slug) {
                static::$bySlug[$slug] = $profile;
            }
        }

        return static::$bySlug;
    }

    /** Fail loudly at load time rather than half-way through seeding a tenant. */
    protected static function validate(array $profile, string $file): void
    {
        foreach (['label', 'cases'] as $required) {
            if (! isset($profile[$required])) {
                throw new \RuntimeException("Demo specialty profile {$file} is missing '{$required}'.");
            }
        }

        if ($profile['cases'] === []) {
            throw new \RuntimeException("Demo specialty profile {$file} has no cases.");
        }

        foreach ($profile['cases'] as $i => $case) {
            foreach (['diagnosis', 'treatment_plan', 'medicines'] as $required) {
                if (! isset($case[$required])) {
                    throw new \RuntimeException("Demo specialty profile {$file}, case {$i}: missing '{$required}'.");
                }
            }

            // medical_requests.type is an enum in the database.
            foreach ($case['requests'] ?? [] as $request) {
                if (! in_array($request['type'] ?? null, ['examination', 'lab_test', 'radiology'], true)) {
                    throw new \RuntimeException(
                        "Demo specialty profile {$file}, case {$i}: request type must be "
                        ."examination, lab_test or radiology."
                    );
                }
            }
        }

        // patient_tests.type only has Arabic labels for these two
        // (lang/*/app.php -> test_types); anything else renders as raw English
        // in an Arabic workspace.
        foreach ($profile['results'] ?? [] as $i => $result) {
            if (! in_array($result['type'] ?? null, ['lab', 'radiology'], true)) {
                throw new \RuntimeException(
                    "Demo specialty profile {$file}, result {$i}: type must be 'lab' or 'radiology'."
                );
            }
        }

        // examination_fields.type is an enum too.
        foreach ($profile['examination_fields'] ?? [] as $i => $field) {
            if (! in_array($field['type'] ?? null, ['text', 'select', 'number', 'percentage', 'file'], true)) {
                throw new \RuntimeException(
                    "Demo specialty profile {$file}, examination field {$i}: unsupported type."
                );
            }
        }
    }

    /** Only used by tests and tooling; lets a fresh read pick up edits. */
    public static function flush(): void
    {
        static::$bySlug = null;
    }
}
