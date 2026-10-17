<?php

namespace App\Support;

use App\Enums\ExpenseStatus;
use App\Enums\VendorType;
use App\Models\DentalLabPayment;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

/**
 * Mirrors a clinic→lab payment into the expenses ledger when the clinic has
 * opted in (Clinic::expenses_autocapture). The generated expense is tied to the
 * payment through its polymorphic source, so it can be removed cleanly if the
 * payment is deleted. The lab is reconciled to a vendor (created on demand).
 */
class DentalLabExpenseCapture
{
    /** Create the mirror expense for a payment, if the clinic opted in. */
    public function capture(DentalLabPayment $payment): void
    {
        $order = $payment->order;
        $clinic = $order?->clinic;

        if (! $clinic || ! $clinic->expenses_autocapture) {
            return;
        }

        // Don't double-create if one already exists for this payment.
        if ($this->existing($payment)) {
            return;
        }

        DB::transaction(function () use ($payment, $order, $clinic) {
            $category = ExpenseCategory::firstOrCreate(
                ['clinic_id' => $clinic->id, 'name' => $this->categoryName()],
                ['icon' => '🦷', 'is_active' => true],
            );

            $vendor = $this->vendorForLab($clinic->id, $order);

            $expense = new Expense([
                'clinic_id' => $clinic->id,
                'expense_category_id' => $category->id,
                'vendor_id' => $vendor?->id,
                'title' => $this->title($order),
                'amount' => $payment->amount,
                'expense_date' => ($payment->paid_at ?? now())->toDateString(),
                'status' => ExpenseStatus::Paid,
            ]);
            $expense->source()->associate($payment);
            $expense->save();

            // The clinic already paid the lab, so record it as settled.
            $expense->payments()->create([
                'amount' => $payment->amount,
                'paid_at' => $payment->paid_at ?? now(),
                'method' => $payment->method ?: null,
                'paid_by' => $payment->created_by,
            ]);

            $expense->load('payments')->recomputeStatus();
        });
    }

    /** Remove the mirror expense when its source payment is deleted. */
    public function remove(DentalLabPayment $payment): void
    {
        $this->existing($payment)?->delete();
    }

    private function existing(DentalLabPayment $payment): ?Expense
    {
        return Expense::where('source_type', $payment->getMorphClass())
            ->where('source_id', $payment->getKey())
            ->first();
    }

    /** Find or create the vendor that represents this order's lab. */
    private function vendorForLab(int $clinicId, $order): ?Vendor
    {
        if (! $order || ! $order->dental_lab_id) {
            return null;
        }

        return Vendor::firstOrCreate(
            ['clinic_id' => $clinicId, 'dental_lab_id' => $order->dental_lab_id],
            [
                'name' => $order->lab?->name ?? __('app.vendors.title'),
                'type' => VendorType::Lab,
                'is_active' => true,
            ],
        );
    }

    /**
     * Matches the seeded preset name (categories store a literal name, not a
     * translation key) so auto-capture reuses the existing "Dental Lab Fees"
     * category instead of creating a duplicate.
     */
    private function categoryName(): string
    {
        return 'Dental Lab Fees';
    }

    private function title($order): string
    {
        $lab = $order?->lab?->name;
        $ref = $order ? ' #'.$order->id : '';

        return trim(__('app.finance.lab_payment').' — '.($lab ?? '').$ref);
    }
}
