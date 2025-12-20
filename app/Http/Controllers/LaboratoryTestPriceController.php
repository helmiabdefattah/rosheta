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

        // Get all medical tests
        $medicalTests = MedicalTest::orderBy('test_name_en')->get();

        // Get existing prices for this laboratory
        $existingPrices = LaboratoryTestPrice::where('laboratory_id', $laboratory->id)
            ->pluck('price', 'medical_test_id')
            ->toArray();

        return view('laboratories.test-prices.index', compact('medicalTests', 'existingPrices', 'laboratory'));
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

        $request->validate([
            'medical_test_id' => 'required|exists:medical_tests,id',
            'price' => 'required|numeric|min:0',
        ]);

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

