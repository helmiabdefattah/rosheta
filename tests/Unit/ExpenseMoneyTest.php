<?php

namespace Tests\Unit;

use App\Enums\ExpenseStatus;
use App\Models\Expense;
use App\Models\ExpensePayment;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;

/**
 * Pure (DB-free) checks of the expense money math: gross = amount + tax, the
 * payments total, the remaining balance, and the derived status. Payments are
 * attached as an in-memory relation so no database is needed.
 */
class ExpenseMoneyTest extends TestCase
{
    private function expense(float $amount, ?float $tax, array $payments): Expense
    {
        $expense = new Expense(['amount' => $amount, 'tax_amount' => $tax]);
        $expense->setRelation('payments', new Collection(
            array_map(fn ($a) => new ExpensePayment(['amount' => $a]), $payments)
        ));

        return $expense;
    }

    public function test_gross_is_amount_plus_tax(): void
    {
        $this->assertSame(114.0, $this->expense(100, 14, [])->gross());
        $this->assertSame(100.0, $this->expense(100, null, [])->gross());
    }

    public function test_paid_total_and_balance(): void
    {
        $e = $this->expense(100, 0, [30, 20]);

        $this->assertSame(50.0, $e->paidTotal());
        $this->assertSame(50.0, $e->balance());
    }

    public function test_balance_never_negative_on_overpayment(): void
    {
        $e = $this->expense(100, 0, [120]);

        $this->assertSame(0.0, $e->balance());
    }

    public function test_status_unpaid_when_nothing_paid(): void
    {
        $this->assertSame(ExpenseStatus::Unpaid, $this->expense(100, 0, [])->recomputeStatus(false));
    }

    public function test_status_partial_when_some_paid(): void
    {
        $this->assertSame(ExpenseStatus::Partial, $this->expense(100, 0, [40])->recomputeStatus(false));
    }

    public function test_status_paid_when_fully_covered(): void
    {
        $e = $this->expense(100, 14, [114]);

        $this->assertSame(ExpenseStatus::Paid, $e->recomputeStatus(false));
    }
}
