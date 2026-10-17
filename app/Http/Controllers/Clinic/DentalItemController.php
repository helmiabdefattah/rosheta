<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clinic\DentalItemRequest;
use App\Models\DentalItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The clinic's own custom catalog items, shown alongside the shared seeded
 * catalog. A clinic may only edit/remove its own rows, never the shared ones.
 */
class DentalItemController extends Controller
{
    use ClinicContext;

    public function index(Request $request): View
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));

        $shared = DentalItem::whereNull('clinic_id')->where('is_active', true)
            ->orderBy('level')->orderBy('sort')->get()->groupBy('level');

        $custom = DentalItem::where('clinic_id', $clinic->id)
            ->orderBy('level')->orderBy('name')->get()->groupBy('level');

        return view('clinic.dental-lab.items.index', compact('clinic', 'shared', 'custom'));
    }

    public function store(DentalItemRequest $request): RedirectResponse
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));

        DentalItem::create([
            'clinic_id' => $clinic->id,
            'is_active' => $request->boolean('is_active', true),
            ...$request->safe()->except('is_active'),
        ]);

        return back()->with('status', __('app.dental.catalog.created'));
    }

    public function update(DentalItemRequest $request, DentalItem $item): RedirectResponse
    {
        $this->guardOwnItem($request, $item);

        $item->update([
            'is_active' => $request->boolean('is_active', true),
            ...$request->safe()->except('is_active'),
        ]);

        return back()->with('status', __('app.dental.catalog.updated'));
    }

    public function destroy(Request $request, DentalItem $item): RedirectResponse
    {
        $this->guardOwnItem($request, $item);

        $item->delete();

        return back()->with('status', __('app.dental.catalog.deleted'));
    }

    /** A clinic may only touch its own custom items. */
    private function guardOwnItem(Request $request, DentalItem $item): void
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));
        abort_unless($item->clinic_id === $clinic->id, 403);
    }
}
