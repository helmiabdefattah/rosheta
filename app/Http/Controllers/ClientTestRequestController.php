<?php

namespace App\Http\Controllers;

use App\Models\ClientAddress;
use App\Models\ClientRequest;
use App\Models\ClientRequestLine;
use App\Models\MedicalTest;
use App\Models\InsuranceCompany;
use App\Models\User;
use App\Notifications\NewClientRequestNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClientTestRequestController extends Controller
{
    public function create(Request $request, string $type)
    {
        if (!in_array($type, ['test', 'radiology'])) {
            abort(404);
        }

        abort_unless(in_array($type, ['test', 'radiology']), 404);

        $client = Auth::guard('client')->user()->load('insuranceCompany');

        // Load items based on type
        if ($type === 'test') {
            $items = MedicalTest::select(
                'id',
                'test_name_en',
                'test_name_ar',
                'test_description',
                'conditions'
            )->where('type','test')->orderBy('test_name_en')->get();
        } else {
            $items = MedicalTest::select(
                'id',
                'test_name_en',
                'test_name_ar',
                'test_description',
                'conditions'
            )->where('type','radiology')->orderBy('test_name_en')->get();
        }

        $addresses = ClientAddress::where('client_id', $client->id)
            ->with(['city', 'area'])
            ->get();

        $insuranceCompanies = InsuranceCompany::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Check if a specific laboratory is selected
        $laboratory = null;
        if ($request->has('laboratory_id')) {
            $laboratory = \App\Models\Laboratory::where('id', $request->laboratory_id)
                ->where('is_active', true)
                ->with(['area.city.governorate'])
                ->first();
        }

        return view('client.test-requests.create', [
            'items' => $items,
            'addresses' => $addresses,
            'insuranceCompanies' => $insuranceCompanies,
            'type' => $type,
            'laboratory' => $laboratory,
        ]);
    }
    public function store(Request $request, string $type)
    {
//        dd($request);
        abort_unless(in_array($type, ['test', 'radiology', 'medicine']), 404);

        $client = Auth::guard('client')->user();
        abort_unless($client, 403);

        if ($type === 'medicine' && $request->has('medicines')) {
            $filteredMedicines = collect($request->input('medicines', []))
                ->filter(fn ($row) => filled($row['medicine_id'] ?? null))
                ->values()
                ->all();
            $request->merge(['medicines' => $filteredMedicines]);
        }

        $rules = [
            'client_address_id' => ['nullable', 'exists:client_addresses,id'],
            'pregnant' => ['nullable', 'boolean'],
            'diabetic' => ['nullable', 'boolean'],
            'heart_patient' => ['nullable', 'boolean'],
            'high_blood_pressure' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:1000'],
            'insurance_company_id' => ['nullable', 'exists:insurance_companies,id'],
            'insurance_company_name' => ['nullable', 'string', 'max:255'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:5120'],
        ];

        if (in_array($type, ['test', 'radiology'])) {
            $rules['tests'] = ['nullable', 'array'];
            $rules['tests.*.test_id'] = ['required_with:tests', 'integer'];
        }

        if ($type === 'medicine') {
            $rules['medicines'] = ['nullable', 'array'];
            $rules['medicines.*.medicine_id'] = ['required', 'integer'];
            $rules['medicines.*.quantity'] = ['required', 'integer', 'min:1'];
        }

        $validated = $request->validate($rules);

        // Require at least images or items
        if (
            empty($validated['tests'] ?? []) &&
            empty($validated['medicines'] ?? []) &&
            !$request->hasFile('images')
        ) {
            return back()->withErrors([
                'items' => __('Please add items or upload images')
            ])->withInput();
        }

        $clientRequest = DB::transaction(function () use ($request, $validated, $client, $type) {

            $imageNames = [];

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $name = uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('requests', $name, 'public');
                    $imageNames[] = $name;
                }
            }

            $insuranceCompanyId = $client->insurance_company_id;

            if ($request->filled('insurance_company_name')) {
                $insuranceCompanyId = InsuranceCompany::firstOrCreate(
                    ['name' => $request->insurance_company_name],
                    ['is_active' => true]
                )->id;
            } elseif ($request->filled('insurance_company_id')) {
                $insuranceCompanyId = $request->insurance_company_id;
            }

            // Get model_type and model_id if provided
            $modelType = null;
            $modelId = null;
            if ($request->filled('laboratory_id')) {
                $laboratory = \App\Models\Laboratory::find($request->laboratory_id);
                if ($laboratory && $laboratory->is_active) {
                    $modelType = 'App\Models\Laboratory';
                    $modelId = $laboratory->id;
                }
            } elseif ($request->filled('pharmacy_id')) {
                $pharmacy = \App\Models\Pharmacy::find($request->pharmacy_id);
                if ($pharmacy && $pharmacy->is_active) {
                    $modelType = 'App\Models\Pharmacy';
                    $modelId = $pharmacy->id;
                }
            }

            $clientRequest = ClientRequest::create([
                'client_id' => $client->id,
                'client_address_id' => $validated['client_address_id'] ?? null,
                'insurance_company_id' => $insuranceCompanyId,
                'pregnant' => $request->boolean('pregnant'),
                'diabetic' => $request->boolean('diabetic'),
                'heart_patient' => $request->boolean('heart_patient'),
                'high_blood_pressure' => $request->boolean('high_blood_pressure'),
                'note' => $validated['note'] ?? null,
                'status' => 'pending',
                'images' => $imageNames,
                'type' => $type,
                'model_type' => $modelType,
                'model_id' => $modelId,
            ]);

            if (!empty($validated['tests'])) {
                ClientRequestLine::insert(
                    collect($validated['tests'])->map(fn ($t) => [
                        'client_request_id' => $clientRequest->id,
                        'medical_test_id' => $t['test_id'],
                        'quantity' => 1,
                        'item_type' => $type,
                    ])->toArray()
                );
            }

            if (!empty($validated['medicines'])) {
                ClientRequestLine::insert(
                    collect($validated['medicines'])->map(fn ($m) => [
                        'client_request_id' => $clientRequest->id,
                        'medicine_id' => $m['medicine_id'],
                        'quantity' => $m['quantity'],
                        'item_type' => 'medicine',
                    ])->toArray()
                );
            }

            return $clientRequest;
        });

        // Notify related providers
        try {
            // If request is for a specific provider, only notify that provider
            if ($clientRequest->model_type && $clientRequest->model_id) {
                if ($clientRequest->model_type === 'App\Models\Laboratory') {
                    $laboratory = \App\Models\Laboratory::find($clientRequest->model_id);
                    if ($laboratory && $laboratory->user) {
                        $laboratory->user->notify(new NewClientRequestNotification($clientRequest));
                    }
                } elseif ($clientRequest->model_type === 'App\Models\Pharmacy') {
                    $pharmacy = \App\Models\Pharmacy::find($clientRequest->model_id);
                    if ($pharmacy && $pharmacy->user) {
                        $pharmacy->user->notify(new NewClientRequestNotification($clientRequest));
                    }
                }
            } else {
                // Notify all active providers (original behavior)
                if ($clientRequest->type === 'medicine') {
                    $pharmacyUsers = User::whereNotNull('pharmacy_id')
                        ->whereHas('pharmacy', function($q) {
                            $q->where('is_active', true);
                        })->get();
                    
                    if ($pharmacyUsers->count() > 0) {
                        Notification::send($pharmacyUsers, new NewClientRequestNotification($clientRequest));
                    }
                } else {
                    $type = $clientRequest->type; // 'test' or 'radiology'
                    $labUsers = User::whereNotNull('laboratory_id')
                        ->whereHas('laboratory', function($q) use($type) {
                            $q->where(function($qt) use ($type) {
                                $qt->where('type', 'both')
                                  ->orWhere('type', $type);
                            })->where('is_active', true);
                        })
                        ->get();
                    
                    if ($labUsers->count() > 0) {
                        Notification::send($labUsers, new NewClientRequestNotification($clientRequest));
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Notification failed for client test/medicine request: ' . $e->getMessage());
        }

        return redirect()
            ->route('client.dashboard')
            ->with('success', __('Request submitted successfully'));
    }

}

