<?php

namespace App\Http\Controllers;

use App\Models\ClientAddress;
use App\Models\ClientRequest;
use App\Models\MedicalTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClientTestRequestController extends Controller
{
    public function create()
    {
        $client = Auth::guard('client')->user();
        
        // Load medical tests
        $medicalTests = MedicalTest::select('id', 'test_name_en', 'test_name_ar', 'test_description', 'conditions')
            ->orderBy('test_name_en')
            ->get();
        
        // Load client addresses
        $addresses = ClientAddress::where('client_id', $client->id)
            ->with(['city', 'area'])
            ->get();
        
        return view('client.test-requests.create', compact('medicalTests', 'addresses'));
    }

    public function store(Request $request)
    {
        $client = Auth::guard('client')->user();
        
        $validated = $request->validate([
            'client_address_id' => ['nullable', 'exists:client_addresses,id'],
            'requires_home_visit' => ['nullable', 'boolean'],
            'pregnant' => ['nullable', 'boolean'],
            'diabetic' => ['nullable', 'boolean'],
            'heart_patient' => ['nullable', 'boolean'],
            'high_blood_pressure' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'tests' => ['nullable', 'array'],
            'tests.*.test_id' => ['required_with:tests', 'exists:medical_tests,id'],
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'], // 5MB max
        ]);

        // If home visit is required, validate address
        $requiresHomeVisit = $request->has('requires_home_visit') && $request->requires_home_visit;
        if ($requiresHomeVisit && empty($validated['client_address_id'])) {
            return back()->withErrors(['client_address_id' => 'Address is required when home visit is requested.'])->withInput();
        }

        // Validate address belongs to client if provided
        if (!empty($validated['client_address_id'])) {
            $address = ClientAddress::where('id', $validated['client_address_id'])
                ->where('client_id', $client->id)
                ->first();

            if (!$address) {
                return back()->withErrors(['client_address_id' => 'The selected address does not belong to you.'])->withInput();
            }
        }

        // Validate that either tests or images are provided
        if (empty($validated['tests']) && empty($validated['images'])) {
            return back()->withErrors(['tests' => 'Either medical tests or prescription images must be provided.'])->withInput();
        }

        try {
            $created = DB::transaction(function () use ($validated, $client, $request) {
                // Handle image uploads
                $imageNames = [];
                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $file) {
                        $filename = time() . '_' . $file->getClientOriginalName();
                        $file->storeAs('requests', $filename, 'public');
                        $imageNames[] = $filename;
                    }
                }

                $requestModel = ClientRequest::create([
                    'client_id' => $client->id,
                    'client_address_id' => $validated['client_address_id'] ?? null,
                    'pregnant' => $validated['pregnant'] ?? false,
                    'diabetic' => $validated['diabetic'] ?? false,
                    'heart_patient' => $validated['heart_patient'] ?? false,
                    'high_blood_pressure' => $validated['high_blood_pressure'] ?? false,
                    'note' => $validated['note'] ?? null,
                    'status' => $validated['status'] ?? 'pending',
                    'images' => $imageNames,
                    'type' => 'test',
                ]);

                // Save test lines if provided
                if (!empty($validated['tests'])) {
                    $linesPayload = collect($validated['tests'])->map(function ($test) use ($requestModel) {
                        return [
                            'client_request_id' => $requestModel->id,
                            'medical_test_id' => $test['test_id'],
                            'quantity' => 1,
                            'item_type' => 'test',
                        ];
                    })->toArray();

                    \App\Models\ClientRequestLine::insert($linesPayload);
                }

                return $requestModel->load(['address.city', 'address.area', 'testLines.medicalTest', 'client']);
            });

            return redirect()->route('client.dashboard')
                ->with('success', app()->getLocale() === 'ar' 
                    ? 'تم إنشاء طلب التحاليل بنجاح' 
                    : 'Medical test request created successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create request: ' . $e->getMessage()])->withInput();
        }
    }
}

