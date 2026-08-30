<?php

namespace App\Support;

/**
 * The platform's own identity, for documents that carry it alongside the
 * clinic's.
 *
 * A prescription is the clinic's document first — the doctor and clinic stay
 * the letterhead. This is the "issued through" mark that goes in the footer,
 * next to the QR.
 */
class SiteBrand
{
    /**
     * The mark alone, not full-logo.png: that one already carries the
     * "mostashfaOn" wordmark, which would read twice next to name().
     */
    private const LOGO = 'images/mo-logo.png';

    /** Encoded once per request; the file never changes within one. */
    private static ?string $cachedLogo = null;

    /**
     * @param  string|null  $locale  Overrides the request locale — the thermal
     *                               printer follows the clinic's own printer
     *                               language, not whoever triggered the print.
     */
    public static function name(?string $locale = null): string
    {
        return ($locale ?? app()->getLocale()) === 'ar' ? 'مستشفى-أون' : 'mostashfaOn';
    }

    /** For the browser sheet, where a normal URL is cacheable. */
    public static function logoUrl(): string
    {
        return asset(self::LOGO);
    }

    /**
     * For mPDF, which cannot be relied on to resolve a web path: the logo as a
     * `data:image/png;base64,…` URI, or an empty string if it is unreadable —
     * a missing logo must never take a prescription down with it.
     */
    public static function logoDataUri(): string
    {
        if (self::$cachedLogo !== null) {
            return self::$cachedLogo;
        }

        $path = public_path(self::LOGO);

        if (! is_readable($path)) {
            return self::$cachedLogo = '';
        }

        $bytes = @file_get_contents($path);

        return self::$cachedLogo = $bytes === false
            ? ''
            : 'data:image/png;base64,'.base64_encode($bytes);
    }
}
