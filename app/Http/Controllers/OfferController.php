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
        $clientRequest = ClientRequest::with(['client', 'address'])->findOrFail($id);

        $clientRequest->load(['lines.medicine', 'lines.medicalTest']);

        // Prepare tests array
        $tests = MedicalTest::get()->mapWithKeys(function ($test) {
            return [$test->id => [
                'test_name_en' => $test->test_name_en,
                'test_name_ar' => $test->test_name_ar,
                'test_description' => $test->test_description,
                'conditions' => $test->conditions
            ]];
        })->toArray();

        // Prepare medicines array
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

        if ($clientRequest->type === 'test' || $clientRequest->type === 'radiology') {
            // User-specific or all laboratories
            if (auth()->user()->laboratory_id) {
                $laboratories = Laboratory::where('id', auth()->user()->laboratory_id)
                    ->pluck('name', 'id');
            } else {
                $laboratories = Laboratory::pluck('name', 'id');
            }
        } elseif ($clientRequest->type === 'medicine') {
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
                'lines.medicine:id,name',
                'lines.medicalTest:id,test_name_en,test_name_ar',
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
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_request_id' => 'required|exists:client_requests,id',
            'total_price' => 'required|numeric|min:0',
            'visit_price' => 'nullable|numeric|min:0',
            'offer_lines' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();
            $clientRequest = ClientRequest::findOrFail($validated['client_request_id']);

            // Validate type-specific fields
            if ($clientRequest->type === 'test' || $clientRequest->type === 'radiology') {
                $request->validate(['laboratory_id' => 'required|exists:laboratories,id']);
                foreach ($request->offer_lines as $index => $line) {
                    if (!isset($line['medical_test_id']) || empty($line['medical_test_id'])) {
                        return back()->withErrors([
                            'offer_lines.' . $index . '.medical_test_id' => 'Test is required for offer lines'
                        ])->withInput();
                    }
                }
            } elseif ($clientRequest->type === 'medicine') {
                $request->validate(['pharmacy_id' => 'required|exists:pharmacies,id']);
                foreach ($request->offer_lines as $index => $line) {
                    if (!isset($line['medicine_id']) || empty($line['medicine_id'])) {
                        return back()->withErrors([
                            'offer_lines.' . $index . '.medicine_id' => 'Medicine is required for offer lines'
                        ])->withInput();
                    }
                }
            }

            // Determine visit price
            $homeVisitType = $request->input('home_visit_type');
            $visitPrice = match ($homeVisitType) {
                'free_visit' => 0,
                'price' => $request->input('visit_price', 0),
                default => null,
            };

            // Create Offer
            $offer = Offer::create([
                'client_request_id' => $validated['client_request_id'],
                'request_type' => $clientRequest->type,
                'total_price' => $validated['total_price'],
                'visit_price' => $visitPrice,
                'user_id' => auth()->id(),
                'status' => 'pending',
                'laboratory_id' => in_array($clientRequest->type, ['test', 'radiology']) ? $request->laboratory_id : null,
                'pharmacy_id' => $clientRequest->type === 'medicine' ? $request->pharmacy_id : null,
            ]);

            foreach ($request->offer_lines as $line) {
                OfferLine::create([
                    'offer_id' => $offer->id,
                    'item_type' => $clientRequest->type === 'medicine' ? 'medicine' : 'test',
                    'price' => $line['price'],
                    'medical_test_id' => $clientRequest->type === 'medicine' ? null : $line['medical_test_id'] ?? null,
                    'medicine_id' => $clientRequest->type === 'medicine' ? $line['medicine_id'] : null,
                    'quantity' => $clientRequest->type === 'medicine' ? ($line['quantity'] ?? 1) : 1,
                    'unit' => $clientRequest->type === 'medicine' ? ($line['unit'] ?? 'box') : 'test',
                ]);
            }
            // Redirect
            $user = Auth::user();
            DB::commit();

            $successMessage = app()->getLocale() === 'ar' ? 'تم إنشاء العرض بنجاح' : 'Offer created successfully.';

            if ($user->laboratory_id) {
                return redirect()->route('laboratories.dashboard')
                    ->with('success', $successMessage);
            }

            return redirect()->route('admin.client-requests.index')
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            return back()->withErrors([
                'error' => $e->getMessage()
            ])->withInput();
        }
        
    }
    public function show(Offer $offer)
    {
        $offer->load([
            'request.client',
            'request.lines.medicine',
            'request.lines.test', // unified relation for test & radiology
            'pharmacy',
            'laboratory',
            'user'
        ]);

        return view('offers.show', compact('offer'));
    }

}
