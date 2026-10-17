<?php

namespace Tests\Unit;

use App\Models\Expense;
use App\Models\ExpensePayment;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;

/**
 * Pure (DB-free) checks of the vendor balance: opening balance + everything
 * billed to the vendor − everything paid against those expenses. Expenses and
 * their payments are attached as in-memory relations, so no database is needed.
 */
class VendorMoneyTest extends TestCase
{
    private function expense(float $amount, ?float $tax, array $payments): Expense
    {
        $e = new Expense(['amount' => $amount, 'tax_amount' => $tax]);
        $e->setRelation('payments', new Collection(
            array_map(fn ($a) => new ExpensePayment(['amount' => $a]), $payments)
        ));

        return $e;
    }

    private function vendor(float $opening, array $expenses): Vendor
    {
        $v = new Vendor(['opening_balance' => $opening]);
        $v->setRelation('expenses', new Collection($expenses));

        return $v;
    }

    public function test_totals_sum_across_expenses(): void
    {
        $v = $this->vendor(0, [
            $this->expense(100, 0, [40]),
            $this->expense(50, 10, [60]),
        ]);

        $this->assertSame(160.0, $v->totalBilled());  // 100 + (50+10)
        $this->assertSame(100.0, $v->totalPaid());     // 40 + 60
    }

    public function test_balance_includes_opening_balance(): void
    {
        $v = $this->vendor(200, [
            $this->expense(100, 0, [30]),
        ]);

        // 200 opening + 100 billed − 30 paid = 270
        $this->assertSame(270.0, $v->balance());
    }

    public function test_zero_balance_when_opening_zero_and_fully_paid(): void
    {
        $v = $this->vendor(0, [
            $this->expense(100, 0, [100]),
        ]);

        $this->assertSame(0.0, $v->balance());
    }
}
