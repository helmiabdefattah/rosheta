<?php

namespace App\Http\Controllers;

use App\Models\ClientRequest;
use App\Models\Laboratory;
use App\Models\MedicalTest;
use App\Models\Medicine;
use App\Models\Offer;
use App\Models\OfferLine;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OfferController extends Controller
{
    public function create(Request $request, $id)
    {
        $clientRequest = ClientRequest::with([
            'client',
            'address'
        ])->findOrFail($id);

        $clientRequest->load(['lines.medicine', 'testLines.medicalTest']);

        $tests = MedicalTest::get()->mapWithKeys(function ($test) {
            return [$test->id => [
                'test_name_en' => $test->test_name_en,
                'test_name_ar' => $test->test_name_ar,
                'test_description' => $test->test_description,
                'conditions' => $test->conditions
            ]];
        })->toArray();

        $medicines = Medicine::get()->mapWithKeys(function ($medicine) {
            return [$medicine->id => [
                'name' => $medicine->name,
                'dosage_form' => $medicine->dosage_form,
                'units' => $medicine->units,
                'old_price' => $medicine->price
            ]];
        })->toArray();

        $pharmacies = [];
        $laboratories = [];

        if ($clientRequest->type == 'test') {
            if (auth()->user()->laboratory_id) {
                $laboratories = Laboratory::where('id', auth()->user()->laboratory_id)
                    ->pluck('name', 'id');
            } else {
                $laboratories = Laboratory::pluck('name', 'id');
            }
        } else {
            if (auth()->user()->pharmacy_id) {
                $pharmacies = Pharmacy::where('id', auth()->user()->pharmacy_id)
                    ->pluck('name', 'id');
            } else {
                $pharmacies = Pharmacy::pluck('name', 'id');
            }
        }

        $users = [];
        $clientRequests = [];

        // Get laboratory if user is a laboratory owner
        $laboratory = null;
        $testPrices = [];
        if (auth()->user()->laboratory_id) {
            $laboratory = Laboratory::find(auth()->user()->laboratory_id);

            // Get test prices for this laboratory
            $testPrices = \App\Models\LaboratoryTestPrice::where('laboratory_id', $laboratory->id)
                ->pluck('price', 'medical_test_id')
                ->toArray();
        }

        // Get existing offers for this request
        $existingOffers = Offer::where('client_request_id', $clientRequest->id)
            ->with([
                'pharmacy:id,name',
                'laboratory:id,name',
                'user:id,name',
                'medicineLines' => function($q) {
                    $q->with('medicine:id,name');
                },
                'testLines' => function($q) {
                    $q->with('medicalTest:id,test_name_en,test_name_ar');
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.offers.create', compact(
            'medicines',
            'tests',
            'pharmacies',
            'laboratories',
            'users',
            'clientRequest',
            'clientRequests',
            'laboratory',
            'existingOffers',
            'testPrices'
        ));
    }    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_request_id' => 'required|exists:client_requests,id',
            'total_price' => 'required|numeric|min:0',
            'visit_price' => 'nullable|numeric|min:0',
            'offer_lines' => 'required|array|min:1',
        ]);

        // جلب الطلب لمعرفة نوعه
        $clientRequest = ClientRequest::findOrFail($validated['client_request_id']);

        // التحقق من النوع وتحديد الحقول المطلوبة
        if ($clientRequest->type == 'test') {
            $request->validate([
                'laboratory_id' => 'required|exists:laboratories,id',
            ]);

            // تحقق من أن كل سطر يحتوي على medical_test_id
            foreach ($request->offer_lines as $index => $line) {
                if (!isset($line['medical_test_id']) || empty($line['medical_test_id'])) {
                    return back()->withErrors([
                        'offer_lines.' . $index . '.medical_test_id' => 'Test is required for test offer lines'
                    ])->withInput();
                }
            }
        } else {
            $request->validate([
                'pharmacy_id' => 'required|exists:pharmacies,id',
            ]);

            // تحقق من أن كل سطر يحتوي على medicine_id
            foreach ($request->offer_lines as $index => $line) {
                if (!isset($line['medicine_id']) || empty($line['medicine_id'])) {
                    return back()->withErrors([
                        'offer_lines.' . $index . '.medicine_id' => 'Medicine is required for medicine offer lines'
                    ])->withInput();
                }
            }
        }

        $homeVisitType = $request->input('home_visit_type');

        $visitPrice = null;

        if ($homeVisitType === 'free_visit') {
            $visitPrice = 0;
        } elseif ($homeVisitType === 'price') {
            $visitPrice = $request->input('visit_price', 0);
        }

// Create Offer
    $offer = new Offer();
    $offer->client_request_id = $validated['client_request_id'];
    $offer->request_type = $clientRequest->type;
    $offer->total_price = $validated['total_price'];
    $offer->visit_price = $visitPrice;
    $offer->user_id = auth()->id();
    $offer->status = 'pending';
    $offer->save();

        // تحديد الصيدلية أو المعمل بناءً على النوع
        if ($clientRequest->type == 'test') {
            $offer->laboratory_id = $request->laboratory_id;
            $offer->pharmacy_id = null;
        } else {
            $offer->pharmacy_id = $request->pharmacy_id;
            $offer->laboratory_id = null;
        }

        $offer->save();

        foreach ($request->offer_lines as $line) {
            $offerLineData = [
                'offer_id' => $offer->id,
                'item_type' => $clientRequest->type == 'test' ? 'test' : 'medicine',
                'price' => $line['price'],
            ];

            if ($clientRequest->type == 'test') {
                $offerLineData['medical_test_id'] = $line['medical_test_id'];
                $offerLineData['medicine_id'] = null;
                $offerLineData['quantity'] = 1; // الفحوصات عادة تكون كمية = 1
                $offerLineData['unit'] = 'test';
            } else {
                $offerLineData['medicine_id'] = $line['medicine_id'];
                $offerLineData['medical_test_id'] = null;
                $offerLineData['quantity'] = $line['quantity'] ?? 1;
                $offerLineData['unit'] = $line['unit'] ?? 'box';
            }

            OfferLine::create($offerLineData);
        }

        // Redirect based on user type
        $user = Auth::user();
        if ($user->laboratory_id) {
            // Laboratory owner - redirect to laboratory dashboard
            return redirect()->route('laboratories.dashboard')
                ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء العرض بنجاح' : 'Offer created successfully.');
        } else {
            // Admin - redirect to client requests
            return redirect()->route('admin.client-requests.index')
                ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء العرض بنجاح' : 'Offer created successfully.');
        }
    }
    public function show(Offer $offer)
    {
        $offer->load(['request.client', 'request.lines.medicine', 'pharmacy', 'user', 'lines.medicine']);
        return view('offers.show', compact('offer'));
    }
}
