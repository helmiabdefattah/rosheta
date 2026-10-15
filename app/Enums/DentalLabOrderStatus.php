<?php

namespace App\Enums;

/**
 * The lifecycle of a dental lab order: sent to the lab, being prepared, ready at
 * the lab, then received back at the clinic. `next()` advances one step; the
 * clinic deducts materials from stock when an order first reaches Received.
 */
enum DentalLabOrderStatus: string
{
    case Sent = 'sent';
    case Preparing = 'preparing';
    case Ready = 'ready';
    case Received = 'received';
    case Used = 'used';

    /** Localised label for badges and selects. */
    public function label(): string
    {
        $ar = app()->getLocale() === 'ar';

        return match ($this) {
            self::Sent => $ar ? 'تم الإرسال' : 'Sent',
            self::Preparing => $ar ? 'قيد التحضير' : 'Preparing',
            self::Ready => $ar ? 'جاهز' : 'Ready',
            self::Received => $ar ? 'تم الاستلام' : 'Received',
            self::Used => $ar ? 'مُستخدم' : 'Used',
        };
    }

    /** Tailwind badge classes (background + text) for this status. */
    public function color(): string
    {
        return match ($this) {
            self::Sent => 'bg-slate-100 text-slate-700',
            self::Preparing => 'bg-amber-100 text-amber-800',
            self::Ready => 'bg-sky-100 text-sky-800',
            self::Received => 'bg-emerald-100 text-emerald-800',
            self::Used => 'bg-violet-100 text-violet-800',
        };
    }

    /** The next status in the workflow, or null when already final. */
    public function next(): ?self
    {
        return match ($this) {
            self::Sent => self::Preparing,
            self::Preparing => self::Ready,
            self::Ready => self::Received,
            self::Received => self::Used,
            self::Used => null,
        };
    }

    /** Received or used: the work is in (or has left) the clinic, not editable. */
    public function isFinal(): bool
    {
        return in_array($this, [self::Received, self::Used], true);
    }

    /** Materials for the order should be sitting in clinic stock in this state. */
    public function holdsStock(): bool
    {
        return in_array($this, [self::Received, self::Used], true);
    }

    /** The order's materials have been consumed on the patient in this state. */
    public function consumesStock(): bool
    {
        return $this === self::Used;
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $s) => $s->value, self::cases());
    }
}
