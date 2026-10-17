<?php

namespace Database\Seeders;

use App\Enums\DentalItemLevel;
use App\Enums\DentalLabOrderStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Clinic;
use App\Models\DentalItem;
use App\Models\DentalLab;
use App\Models\DentalLabOrder;
use App\Models\DentalStockItem;
use App\Services\DentalStockService;
use App\Support\ClinicalChart;
use App\Support\DentalTooth;
use Illuminate\Database\Seeder;

/**
 * Sample data for the Dental Lab module: a few labs, orders across every status
 * (with line items, teeth, patient prices and payments) and some stock. Scoped
 * to a dental clinic. Safe to re-run — it clears its own previously seeded rows.
 */
class DentalLabTestSeeder extends Seeder
{
    private const LAB_NAMES = ['معمل النخبة للأسنان', 'معمل التميز', 'Smart Dental Lab'];

    public function run(): void
    {
        $clinics = $this->dentalClinics();
        if ($clinics->isEmpty()) {
            $this->command?->warn('No clinic found — skipping dental lab test data.');

            return;
        }

        foreach ($clinics as $clinic) {
            $this->seedClinic($clinic);
        }
    }

    public function seedClinic(Clinic $clinic): void
    {
        $this->command?->info("Seeding dental lab test data into clinic #{$clinic->id} ({$clinic->name}).");

        // Clear anything a previous run of this seeder created (orders cascade).
        DentalLab::where('clinic_id', $clinic->id)->whereIn('name', self::LAB_NAMES)->get()->each->delete();

        $clients = $this->clientsFor($clinic);
        $tooth = DentalItem::where('level', 'tooth')->whereNull('clinic_id')->get();
        $patientLvl = DentalItem::where('level', 'patient')->whereNull('clinic_id')->get();
        $general = DentalItem::where('level', 'clinic_general')->whereNull('clinic_id')->get();
        $codes = DentalTooth::codes();

        if ($tooth->isEmpty()) {
            $this->command?->warn('Dental catalog is empty — run DentalItemsSeeder first.');

            return;
        }

        $labs = collect(self::LAB_NAMES)->map(fn ($name, $i) => DentalLab::create([
            'clinic_id' => $clinic->id,
            'name' => $name,
            'phone' => '0100' . str_pad((string) random_int(0, 9999999), 7, '0', STR_PAD_LEFT),
            'address' => 'عنوان تجريبي ' . ($i + 1),
            'is_active' => true,
        ]));

        $stock = app(DentalStockService::class);
        // The workflow statuses only — "used" is a doctor action, not a seed state.
        $statuses = [
            DentalLabOrderStatus::Sent,
            DentalLabOrderStatus::Preparing,
            DentalLabOrderStatus::Ready,
            DentalLabOrderStatus::Received,
        ];

        // A spread of orders across statuses.
        for ($i = 0; $i < 9; $i++) {
            $status = $statuses[$i % count($statuses)];
            $lab = $labs->random();
            // Most orders are for a patient; one or two are materials-only.
            $client = ($i % 5 === 4 || $clients->isEmpty()) ? null : $clients->random();

            $teeth = collect($codes)->shuffle()->take(random_int(1, 3))->values()->all();
            $patientPrice = $client ? random_int(5, 30) * 100 : null;

            $order = DentalLabOrder::create([
                'clinic_id' => $clinic->id,
                'dental_lab_id' => $lab->id,
                'client_id' => $client?->id,
                'status' => $status,
                'teeth_mode' => 'order',
                'teeth' => $teeth,
                'patient_price' => $patientPrice,
                'notes' => $i % 3 === 0 ? 'يرجى مراعاة اللون B1' : null,
                'created_at' => now()->subDays(9 - $i),
            ]);

            // Line items: a tooth item always; sometimes a patient-level item and
            // a clinic-general material.
            $order->items()->create([
                'dental_item_id' => $tooth->random()->id,
                'level' => DentalItemLevel::Tooth,
                'name' => $tooth->random()->name,
                'unit' => 'tooth',
                'quantity' => count($teeth),
                'unit_price' => random_int(3, 12) * 100,
            ]);
            if ($i % 3 === 0 && $patientLvl->isNotEmpty()) {
                $pi = $patientLvl->random();
                $order->items()->create([
                    'dental_item_id' => $pi->id, 'level' => DentalItemLevel::Patient,
                    'name' => $pi->name, 'unit' => $pi->default_unit,
                    'quantity' => 1, 'unit_price' => random_int(8, 20) * 100,
                ]);
            }
            if ($i % 2 === 0 && $general->isNotEmpty()) {
                $gi = $general->random();
                $order->items()->create([
                    'dental_item_id' => $gi->id, 'level' => DentalItemLevel::ClinicGeneral,
                    'name' => $gi->name, 'unit' => $gi->default_unit,
                    'quantity' => random_int(20, 120), 'unit_price' => random_int(1, 5),
                ]);
            }

            $order->load('items');
            $total = $order->total();

            // Lab payments: later statuses are more settled.
            $labPaid = match ($status) {
                DentalLabOrderStatus::Sent => 0,
                DentalLabOrderStatus::Preparing => round($total * 0.3, 2),
                DentalLabOrderStatus::Ready => round($total * 0.5, 2),
                DentalLabOrderStatus::Received => $total,
            };
            if ($labPaid > 0) {
                $order->payments()->create(['amount' => $labPaid, 'paid_at' => $order->created_at->copy()->addDay(), 'method' => 'cash']);
            }

            // Patient payments (clinic receivable) when the order is billed.
            if ($patientPrice) {
                $patientPaid = $status === DentalLabOrderStatus::Received
                    ? $patientPrice
                    : round($patientPrice * 0.5, 2);
                $order->patientPayments()->create(['amount' => $patientPaid, 'paid_at' => $order->created_at->copy()->addDay(), 'method' => 'cash']);
            }

            // Received orders add their clinic-general materials into stock.
            if ($status === DentalLabOrderStatus::Received) {
                $order->received_at = $order->created_at->copy()->addDays(2);
                $order->save();
                $stock->applyOrderReceipt($order);
            }
        }

        // A couple of stock items with an opening balance, so the stock screen
        // isn't empty even before any order is received.
        foreach ($general->take(3) as $gi) {
            if (DentalStockItem::where('clinic_id', $clinic->id)->where('dental_item_id', $gi->id)->exists()) {
                continue;
            }
            $item = DentalStockItem::create([
                'clinic_id' => $clinic->id,
                'dental_item_id' => $gi->id,
                'name' => $gi->name,
                'unit' => $gi->default_unit,
                'quantity_on_hand' => 0,
                'reorder_level' => 50,
                'is_active' => true,
            ]);
            $item->recordMovement(\App\Enums\StockMovementType::In, random_int(100, 500), [
                'reason' => 'رصيد افتتاحي (بيانات تجريبية)',
            ]);
        }

        $this->command?->info('Dental lab test data seeded: ' . $labs->count() . ' labs, 9 orders.');
    }

    /** Every dental clinic (teeth chart), or the first clinic if none match. */
    private function dentalClinics()
    {
        $dental = Clinic::with('doctor.specialization')->get()->filter(
            fn (Clinic $c) => $c->doctor
                && ClinicalChart::forSpecialization($c->doctor->specialization) === ClinicalChart::TEETH
        )->values();

        return $dental->isNotEmpty() ? $dental : Clinic::limit(1)->get();
    }

    /** Patients seen at this clinic, falling back to any clients. */
    private function clientsFor(Clinic $clinic)
    {
        $ids = Appointment::where('clinic_id', $clinic->id)->distinct()->pluck('client_id')->filter();
        $clients = Client::whereIn('id', $ids)->take(8)->get();

        return $clients->isNotEmpty() ? $clients : Client::take(8)->get();
    }
}
