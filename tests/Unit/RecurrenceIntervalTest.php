<?php

namespace Tests\Unit;

use App\Enums\RecurrenceInterval;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * The date math behind recurring expenses: advance() moves a date forward by
 * exactly one period, without month-end overflow.
 */
class RecurrenceIntervalTest extends TestCase
{
    public function test_weekly_adds_seven_days(): void
    {
        $this->assertSame('2026-01-08', RecurrenceInterval::Weekly->advance(Carbon::parse('2026-01-01'))->toDateString());
    }

    public function test_monthly_adds_one_month(): void
    {
        $this->assertSame('2026-02-15', RecurrenceInterval::Monthly->advance(Carbon::parse('2026-01-15'))->toDateString());
    }

    public function test_monthly_does_not_overflow_month_end(): void
    {
        // Jan 31 + 1 month should land on Feb 28, not spill into March.
        $this->assertSame('2026-02-28', RecurrenceInterval::Monthly->advance(Carbon::parse('2026-01-31'))->toDateString());
    }

    public function test_quarterly_adds_three_months(): void
    {
        $this->assertSame('2026-04-10', RecurrenceInterval::Quarterly->advance(Carbon::parse('2026-01-10'))->toDateString());
    }

    public function test_yearly_adds_one_year(): void
    {
        $this->assertSame('2027-03-01', RecurrenceInterval::Yearly->advance(Carbon::parse('2026-03-01'))->toDateString());
    }

    public function test_advance_does_not_mutate_input(): void
    {
        $date = Carbon::parse('2026-01-01');
        RecurrenceInterval::Monthly->advance($date);

        $this->assertSame('2026-01-01', $date->toDateString());
    }
}
