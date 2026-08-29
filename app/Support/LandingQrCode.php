<?php

namespace App\Support;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Log;

/**
 * The site's landing-page QR, printed on prescriptions so a patient can reach
 * the platform from a paper sheet.
 *
 * Rendered as a base64 PNG rather than an <svg> or a remote image: the same
 * markup then works in the browser print view, inside mPDF, and in an emailed
 * copy, with nothing to fetch at print time.
 */
class LandingQrCode
{
    /** Encoded once per request — the URL never changes within one. */
    private static ?string $cached = null;

    /** Where the code points. Kept in one place so it is a one-line change. */
    public static function url(): string
    {
        return route('welcome');
    }

    /**
     * A `data:image/png;base64,…` URI, or an empty string if encoding fails —
     * a missing QR must never take a prescription printout down with it.
     */
    public static function dataUri(): string
    {
        if (self::$cached !== null) {
            return self::$cached;
        }

        try {
            $options = new QROptions([
                'outputType' => QROutputInterface::GDIMAGE_PNG,
                'eccLevel' => EccLevel::M,
                'scale' => 4,
                'quietzoneSize' => 2,
                'outputBase64' => true,
            ]);

            return self::$cached = (new QRCode($options))->render(self::url());
        } catch (\Throwable $e) {
            report($e);

            return self::$cached = '';
        }
    }
}
