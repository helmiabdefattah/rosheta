<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use App\Models\LaboratoryTestPrice;
use App\Models\MedicalTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaboratoryTestPriceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $laboratory = Laboratory::find($user->laboratory_id);

        if (!$laboratory) {
            return redirect()->route('admin.dashboard')
                ->with('error', app()->getLocale() === 'ar' ? 'أنت غير مرتبط بأي معمل.' : 'You are not associated with any laboratory.');
        }

        $labType = $this->laboratoryTestType($laboratory);
        $orderColumn = app()->getLocale() === 'ar' ? 'test_name_ar' : 'test_name_en';

        $medicalTests = MedicalTest::query()
            ->where('type', $labType)
            ->orderBy($orderColumn)
            ->get();

        // Get existing prices for this laboratory
        $existingPrices = LaboratoryTestPrice::where('laboratory_id', $laboratory->id)
            ->pluck('price', 'medical_test_id')
            ->toArray();

        return view('laboratories.test-prices.index', compact('medicalTests', 'existingPrices', 'laboratory', 'labType'));
    }

    /**
     * Medical test catalog type for this laboratory (test vs radiology).
     */
    private function laboratoryTestType(Laboratory $laboratory): string
    {
        return in_array($laboratory->type, ['test', 'radiology'], true)
            ? $laboratory->type
            : 'test';
    }

    public function storeOrUpdate(Request $request)
    {
        $user = Auth::user();
        $laboratory = Laboratory::find($user->laboratory_id);

        if (!$laboratory) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'ar' ? 'أنت غير مرتبط بأي معمل.' : 'You are not associated with any laboratory.'
            ], 403);
        }

        $labType = $this->laboratoryTestType($laboratory);

        $request->validate([
            'medical_test_id' => 'required|exists:medical_tests,id',
            'price' => 'required|numeric|min:0',
        ]);

        $medicalTest = MedicalTest::findOrFail($request->medical_test_id);
        if ($medicalTest->type !== $labType) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'ar'
                    ? 'هذا الفحص لا يتبع نوع معملك.'
                    : 'This test does not match your laboratory type.',
            ], 422);
        }

        LaboratoryTestPrice::updateOrCreate(
            [
                'laboratory_id' => $laboratory->id,
                'medical_test_id' => $request->medical_test_id,
            ],
            [
                'price' => $request->price,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => app()->getLocale() === 'ar' ? 'تم حفظ السعر بنجاح' : 'Price saved successfully'
        ]);
    }
}

