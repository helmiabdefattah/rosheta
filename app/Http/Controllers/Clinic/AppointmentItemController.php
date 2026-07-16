<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentItem;
use App\Models\BillableItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Chargeable extras on a visit (dressing, injection…). The doctor picks from
 * their price list or names a new item inline, which joins the list for reuse.
 */
class AppointmentItemController extends Controller
{
    use ClinicContext;

    public function store(Request $request, Appointment $appointment): RedirectResponse
    {
        $doctor = $this->authorizeAppointment($request, $appointment);

        $data = $request->validate([
            'billable_item_id' => ['nullable', 'integer'],
            'new_name' => ['nullable', 'string', 'max:255'],
            'new_price' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        // Either an existing catalog entry, or a new one named inline.
        if (! empty($data['billable_item_id'])) {
            // Resolved through the doctor's own list, so a foreign id can't be
            // charged onto this visit.
            $item = $doctor->billableItems()->whereKey($data['billable_item_id'])->firstOrFail();
            $name = $item->name;
            $unitPrice = (float) $item->price;
            $itemId = $item->id;
        } elseif (filled($data['new_name'] ?? null)) {
            $name = trim($data['new_name']);
            $unitPrice = (float) ($data['new_price'] ?? 0);

            // Same name twice just re-prices the existing entry instead of
            // creating a duplicate in the dropdown.
            $item = BillableItem::updateOrCreate(
                ['doctor_id' => $doctor->id, 'name' => $name],
                ['price' => $unitPrice, 'is_active' => true]
            );
            $itemId = $item->id;
        } else {
            throw ValidationException::withMessages([
                'billable_item_id' => __('app.items.pick_or_name'),
            ]);
        }

        $appointment->items()->create([
            'billable_item_id' => $itemId,
            'name' => $name,           // snapshot: catalog edits must not rewrite past bills
            'quantity' => $data['quantity'],
            'unit_price' => $unitPrice,
        ]);

        return back()->with('status', __('app.items.added', ['name' => $name]));
    }

    public function destroy(Request $request, AppointmentItem $item): RedirectResponse
    {
        $this->authorizeAppointment($request, $item->appointment);
        $item->delete();

        return back()->with('status', __('app.items.removed'));
    }

    /** The visit must belong to the acting doctor; returns that doctor. */
    protected function authorizeAppointment(Request $request, Appointment $appointment)
    {
        $doctor = $this->clinicDoctor($request);
        abort_unless($appointment->doctor_id === $doctor->id, 403);

        return $doctor;
    }
}
