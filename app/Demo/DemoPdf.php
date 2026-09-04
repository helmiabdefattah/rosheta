<?php

namespace App\Demo;

/**
 * A minimal one-page PDF, so a demo lab or radiology result opens to something
 * real when the doctor clicks "view result".
 *
 * This mirrors the generator inside ClinicOnboardingService rather than calling
 * it, because that method is private and the service is production onboarding
 * code the demo must not modify (brief, Appendix B.6). It is ~30 lines of
 * well-understood PDF structure and no dependency, which is a better trade than
 * widening the service's public surface for the demo's benefit.
 *
 * Lines must be Latin text: the base-14 Helvetica font has no Arabic glyphs.
 * Arabic belongs in the report's title and notes, which are database columns.
 */
class DemoPdf
{
    /** @param  array<int, string>  $lines */
    public static function render(array $lines): string
    {
        $stream = "BT\n/F1 13 Tf\n56 780 Td\n14 TL\n";

        foreach ($lines as $index => $line) {
            $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $stream .= $index === 0 ? "($escaped) Tj\n" : "T*\nT*\n($escaped) Tj\n";
        }

        $stream .= 'ET';

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] '
                .'/Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
            '<< /Length '.strlen($stream)." >>\nstream\n".$stream."\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n".$object."\nendobj\n";
        }

        $startXref = strlen($pdf);
        $size = count($objects) + 1;

        $pdf .= "xref\n0 {$size}\n0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer\n<< /Size {$size} /Root 1 0 R >>\nstartxref\n{$startXref}\n%%EOF";

        return $pdf;
    }
}
