<?php

namespace App\Support;

use App\Models\Appointment;

/**
 * Builds a ready-to-print ESC/POS byte buffer for a clinic queue ticket, so the
 * MostashfaOn mobile app can push it to the RONGTA RPP30 Bluetooth printer
 * verbatim (write the bytes to the printer characteristic — no rendering on the
 * app side). This keeps the printed paper under server control: the language
 * follows the clinic's printer_language and the QR code follows the clinic's
 * print_qr toggle, instead of whatever the app's device locale happens to be.
 *
 * Why raw ESC/POS text rather than a bitmap image: text mode + the printer's
 * native QR command (GS ( k) is dramatically faster to transmit and print than
 * a full-width raster, which is what the clinic asked for.
 *
 * Arabic: the RPP30 renders Arabic from its built-in Windows-1256 (WPC1256)
 * code page, selected with `ESC t`. Text is sent in logical order encoded to
 * CP1256; the printer firmware shapes/joins the glyphs. The code page number is
 * device-specific — override ARABIC_CODE_PAGE if a unit uses a different slot.
 */
class EscPosTicketRenderer
{
    // ---- ESC/POS control bytes ----
    private const ESC = "\x1B";
    private const GS = "\x1D";
    private const LF = "\x0A";

    /**
     * Printer code-page slot for Windows-1256 Arabic (ESC t n). RONGTA RPP-series
     * firmware commonly exposes WPC1256 here; adjust per device if Arabic prints
     * as garbage or Latin.
     */
    public const ARABIC_CODE_PAGE = 0x32; // 50 = WPC1256 on many RONGTA units

    /** Code-page slot for CP437 / USA-Standard Europe (the power-on default). */
    public const LATIN_CODE_PAGE = 0x00;

    public function __construct(
        private Appointment $appointment,
        private string $lang = 'en',
        private bool $withQr = true,
    ) {
    }

    public static function make(Appointment $appointment, string $lang, bool $withQr): self
    {
        return new self($appointment, $lang, $withQr);
    }

    /** The finished ESC/POS buffer, base64-encoded for transport in the FCM data. */
    public function toBase64(): string
    {
        return base64_encode($this->build());
    }

    /** Assemble the full ESC/POS byte stream for the ticket. */
    public function build(): string
    {
        $a = $this->appointment;
        $isAr = $this->lang === 'ar';
        $out = '';

        // Initialise, then pick the code page that matches the ticket language.
        $out .= self::ESC.'@';
        $out .= self::ESC.'t'.chr($isAr ? self::ARABIC_CODE_PAGE : self::LATIN_CODE_PAGE);

        // ---- Clinic header (centered) ----
        $out .= $this->align('center');
        $out .= $this->size(2, 2).$this->line($a->clinic->name ?? config('app.name')).$this->size(1, 1);
        if ($a->doctor?->name) {
            $out .= $this->bold(true).$this->line($a->doctor->name).$this->bold(false);
        }
        if ($a->clinic?->phone_number) {
            $out .= $this->line((string) $a->clinic->phone_number);
        }
        $out .= $this->divider();

        // ---- Queue number (big, centered) ----
        $out .= $this->align('center');
        $out .= $this->line($this->t('ticket.queue_number'));
        $out .= $this->size(4, 4).$this->line((string) ($a->queue_number ?? '-')).$this->size(1, 1);

        // Patients still waiting ahead of this one.
        $ahead = $a->patientsWaitingAhead();
        $out .= $this->line($this->plural('ticket.patients_ahead', $ahead, ['count' => $ahead]));
        $out .= $this->divider();

        // ---- Patient + visit details (aligned to the reading direction) ----
        $out .= $this->align($isAr ? 'right' : 'left');
        $out .= $this->line($this->t('ticket.patient').': '.($a->client->name ?? '-'));
        $out .= $this->line($this->t('ticket.type').': '.$a->typeLabel($this->lang));
        if ($a->scheduled_at) {
            $out .= $this->line($this->t('ticket.date').': '.$a->scheduled_at->format('Y-m-d'));
            $out .= $this->line($this->t('ticket.time').': '.$a->scheduled_at->format('H:i'));
        }
        $out .= $this->divider();

        // ---- QR code (native printer command), only when the clinic enabled it ----
        if ($this->withQr) {
            $out .= $this->align('center');
            $out .= $this->qr($this->qrPayload());
            $out .= self::LF;
        }

        // ---- Footer ----
        $out .= $this->align('center');
        $out .= $this->line($this->t('ticket.thanks'));

        // Feed clear of the tear bar.
        $out .= self::ESC.'d'.chr(4);

        return $out;
    }

    // ---- text helpers ----

    /** Encode one line of UI text to the printer code page and append a line feed. */
    private function line(string $text): string
    {
        return $this->encode($text).self::LF;
    }

    /** Convert UTF-8 to the active code page (CP1256 for Arabic, ASCII otherwise). */
    private function encode(string $text): string
    {
        if ($this->lang === 'ar') {
            $converted = @iconv('UTF-8', 'CP1256//TRANSLIT', $text);

            return $converted !== false ? $converted : $text;
        }

        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT', $text);

        return $converted !== false ? $converted : $text;
    }

    /** A localized ticket string in the printer language (not the request locale). */
    private function t(string $key): string
    {
        return __('app.'.$key, [], $this->lang);
    }

    /** A pluralized ticket string in the printer language. */
    private function plural(string $key, int $count, array $replace): string
    {
        return trans_choice('app.'.$key, $count, $replace, $this->lang);
    }

    // ---- ESC/POS command helpers ----

    private function align(string $where): string
    {
        return self::ESC.'a'.chr(['left' => 0, 'center' => 1, 'right' => 2][$where] ?? 0);
    }

    /** Character magnification 1..8 in each axis via GS ! n. */
    private function size(int $w, int $h): string
    {
        $w = max(1, min(8, $w)) - 1;
        $h = max(1, min(8, $h)) - 1;

        return self::GS.'!'.chr(($w << 4) | $h);
    }

    private function bold(bool $on): string
    {
        return self::ESC.'E'.chr($on ? 1 : 0);
    }

    private function divider(): string
    {
        return $this->align('center').$this->encode(str_repeat('-', 32)).self::LF;
    }

    /**
     * Native ESC/POS QR code (GS ( k, model 2). Far faster than sending the QR
     * as a raster image, and rendered at the printer's full resolution.
     */
    private function qr(string $data): string
    {
        $store = "\x31\x50\x30".$data;      // fn 80: store the symbol data
        $len = strlen($store);

        return
            // model 2
            self::GS.'(k'."\x04\x00\x31\x41\x32\x00"
            // module size (dots per cell)
            .self::GS.'(k'."\x03\x00\x31\x43\x06"
            // error correction level M
            .self::GS.'(k'."\x03\x00\x31\x45\x31"
            // store data (pL, pH little-endian length of the store block)
            .self::GS.'(k'.chr($len & 0xFF).chr(($len >> 8) & 0xFF).$store
            // print the stored symbol (fn 81)
            .self::GS.'(k'."\x03\x00\x31\x51\x30";
    }

    /** What the QR encodes: a link to this appointment's ticket page. */
    private function qrPayload(): string
    {
        return route('practice.kiosk.ticket', [
            'clinic' => $this->appointment->clinic_id,
            'appointment' => $this->appointment->id,
        ]);
    }
}
