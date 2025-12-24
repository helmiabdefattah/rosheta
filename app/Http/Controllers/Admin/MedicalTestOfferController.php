<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicalTestOffer;
use App\Models\MedicalTest;
use App\Models\Laboratory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MedicalTestOfferController extends Controller
{
    public function index()
    {
        return view('admin.medical-test-offers.index');
    }

    public function create()
    {
        $user = Auth::user();
        $hasLaboratory = (bool) $user?->laboratory_id;

        $laboratory = null;
        $laboratories = [];
        if ($hasLaboratory) {
            $laboratory = Laboratory::find($user->laboratory_id);
        } else {
            $laboratories = Laboratory::orderBy('name')->get(['id', 'name']);
        }

        $medicalTests = MedicalTest::orderBy('test_name_en')->get(['id', 'test_name_en', 'test_name_ar']);

        return view('admin.medical-test-offers.create', compact('medicalTests', 'laboratory', 'laboratories', 'hasLaboratory'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $hasLaboratory = (bool) $user?->laboratory_id;

        $validated = $request->validate([
            'medical_test_id' => ['required', 'exists:medical_tests,id'],
            'laboratory_id' => [$hasLaboratory ? 'nullable' : 'required', 'exists:laboratories,id'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'offer_price' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);



        $price = $validated['price'];

        if (!empty($validated['offer_price'])) {
            $offerPrice = $validated['offer_price'];
            $discount = round((($price - $offerPrice) / $price) * 100, 2);
        } else {
            $discount = $validated['discount'];
            $offerPrice = round($price - ($price * $discount / 100), 2);
        }

        $laboratoryId = $hasLaboratory
            ? $user->laboratory_id
            : $validated['laboratory_id'];

        $exists = MedicalTestOffer::where('medical_test_id', $validated['medical_test_id'])
            ->where('laboratory_id', $laboratoryId)
            ->exists();

        if ($exists) {
            return back()
                ->withErrors([
                    'medical_test_id' => app()->getLocale() === 'ar'
                        ? 'تم إنشاء عرض لهذا الفحص في هذا المعمل بالفعل.'
                        : 'An offer for this test already exists for the selected laboratory.'
                ])
                ->withInput();
        }

        MedicalTestOffer::create([
            'medical_test_id' => $validated['medical_test_id'],
            'laboratory_id' => $laboratoryId,
            'price' => $price,
            'offer_price' => $offerPrice,
            'discount' => $discount,
        ]);

        return redirect()->route('admin.medical-test-offers.index')
            ->with(
                'success',
                app()->getLocale() === 'ar'
                    ? 'تم إنشاء العرض بنجاح'
                    : 'Test offer created successfully'
            );
    }

    public function show(MedicalTestOffer $medicalTestOffer)
    {
        $medicalTestOffer->load(['laboratory', 'medicalTest']);
        return view('admin.medical-test-offers.show', compact('medicalTestOffer'));
    }

    public function edit(MedicalTestOffer $medicalTestOffer)
    {
        $user = Auth::user();
        $hasLaboratory = (bool) $user?->laboratory_id;
        if ($hasLaboratory && $medicalTestOffer->laboratory_id !== $user->laboratory_id) {
            abort(403);
        }

        $medicalTests = MedicalTest::orderBy('test_name_en')->get(['id', 'test_name_en', 'test_name_ar']);
        $laboratories = $hasLaboratory ? [] : Laboratory::orderBy('name')->get(['id', 'name']);
        $laboratory = $hasLaboratory ? Laboratory::find($user->laboratory_id) : null;

        return view('admin.medical-test-offers.edit', compact('medicalTestOffer', 'medicalTests', 'laboratories', 'laboratory', 'hasLaboratory'));
    }

    public function update(Request $request, MedicalTestOffer $medicalTestOffer)
    {
        $user = Auth::user();
        $hasLaboratory = (bool) $user?->laboratory_id;
        if ($hasLaboratory && $medicalTestOffer->laboratory_id !== $user->laboratory_id) {
            abort(403);
        }

        $validated = $request->validate([
            'medical_test_id' => ['required', 'exists:medical_tests,id'],
            'laboratory_id' => [$hasLaboratory ? 'nullable' : 'required', 'exists:laboratories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'offer_price' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $laboratoryId = $hasLaboratory ? $user->laboratory_id : $validated['laboratory_id'];

        // Ensure unique for other rows (ignore current)
        $exists = MedicalTestOffer::where('medical_test_id', $validated['medical_test_id'])
            ->where('laboratory_id', $laboratoryId)
            ->where('id', '!=', $medicalTestOffer->id)
            ->exists();
        if ($exists) {
            return back()
                ->withErrors(['medical_test_id' => app()->getLocale() === 'ar'
                    ? 'يوجد بالفعل عرض لهذا الفحص في هذا المعمل.'
                    : 'Another offer for this test already exists for the selected laboratory.'])
                ->withInput();
        }

        $medicalTestOffer->update([
            'medical_test_id' => $validated['medical_test_id'],
            'laboratory_id' => $laboratoryId,
            'price' => $validated['price'],
            'offer_price' => $validated['offer_price'] ?? null,
            'discount' => $validated['discount'] ?? 0,
        ]);

        return redirect()->route('admin.medical-test-offers.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث العرض بنجاح' : 'Test offer updated successfully');
    }

    public function destroy(MedicalTestOffer $medicalTestOffer)
    {
        $medicalTestOffer->delete();

        return redirect()->route('admin.medical-test-offers.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف العرض بنجاح' : 'Test offer deleted successfully');
    }

    public function data()
    {
        $offers = MedicalTestOffer::with(['laboratory', 'medicalTest'])->select('medical_test_offers.*');
        $user = Auth::user();
        if ($user?->laboratory_id) {
            $offers->where('laboratory_id', $user->laboratory_id);
        }

        return DataTables::of($offers)
            ->addColumn('laboratory_name', function ($offer) {
                return $offer->laboratory->name ?? '-';
            })
            ->addColumn('test_name', function ($offer) {
                return $offer->medicalTest->test_name_en ?? '-';
            })
            ->addColumn('price_formatted', function ($offer) {
                return $offer->price ? 'EGP ' . number_format($offer->price, 2) : '-';
            })
            ->addColumn('offer_price_formatted', function ($offer) {
                return $offer->offer_price !== null ? 'EGP ' . number_format($offer->offer_price, 2) : '-';
            })
            ->addColumn('actions', function ($offer) {
                return view('admin.medical-test-offers.actions', compact('offer'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
}

