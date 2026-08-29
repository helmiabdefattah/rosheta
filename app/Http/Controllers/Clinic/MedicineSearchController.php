<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Support\MedicineDoseOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Type-ahead over the medicines catalogue for the prescription form.
 *
 * The table holds ~25k rows, so the list is never shipped to the browser:
 * the form asks for matches as the doctor types. Prefix matches come first
 * because `medicines.name` is indexed and that is what a doctor types.
 */
class MedicineSearchController extends Controller
{
    private const LIMIT = 15;

    public function __invoke(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json(['data' => []]);
        }

        $escaped = addcslashes($term, '%_\\');

        $medicines = Medicine::query()
            ->where(function ($q) use ($escaped) {
                $q->where('name', 'like', $escaped.'%')
                    ->orWhere('arabic', 'like', $escaped.'%');
            })
            ->orderByRaw('CHAR_LENGTH(name)')
            ->limit(self::LIMIT)
            ->get(['id', 'name', 'arabic', 'dosage_form', 'active_ingredient', 'company']);

        // Only widen to "contains" when the cheap indexed prefix search came up
        // short — otherwise every keystroke pays for a full scan.
        if ($medicines->count() < self::LIMIT) {
            $more = Medicine::query()
                ->whereNotIn('id', $medicines->pluck('id'))
                ->where(function ($q) use ($escaped) {
                    $q->where('name', 'like', '%'.$escaped.'%')
                        ->orWhere('arabic', 'like', '%'.$escaped.'%');
                })
                ->orderByRaw('CHAR_LENGTH(name)')
                ->limit(self::LIMIT - $medicines->count())
                ->get(['id', 'name', 'arabic', 'dosage_form', 'active_ingredient', 'company']);

            $medicines = $medicines->concat($more);
        }

        return response()->json([
            'data' => $medicines->map(fn (Medicine $m) => [
                'id' => $m->id,
                'name' => $m->name,
                'arabic' => $m->arabic,
                'form' => $m->dosage_form,
                'ingredient' => $m->active_ingredient,
                'company' => $m->company,
                'dose_options' => MedicineDoseOptions::for($m->dosage_form),
            ])->values(),
        ]);
    }
}
