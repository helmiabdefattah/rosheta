<?php

namespace App\Http\Controllers;

use App\Models\ClientAddress;
use App\Models\ClientRequest;
use App\Models\ClientRequestLine;
use App\Models\MedicalTest;
use App\Models\InsuranceCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClientTestRequestController extends Controller
{
    public function create(string $type)
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

        return view('client.test-requests.create', [
            'items' => $items,
            'addresses' => $addresses,
            'insuranceCompanies' => $insuranceCompanies,
            'type' => $type,
        ]);
    }
    public function store(Request $request, string $type)
    {
        abort_unless(in_array($type, ['test', 'radiology']), 404);

        $client = Auth::guard('client')->user();

        $validated = $request->validate([
            'client_address_id' => ['nullable', 'exists:client_addresses,id'],
            'requires_home_visit' => ['nullable', 'boolean'],
            'pregnant' => ['nullable', 'boolean'],
            'diabetic' => ['nullable', 'boolean'],
            'heart_patient' => ['nullable', 'boolean'],
            'high_blood_pressure' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:1000'],
            'insurance_company_id' => ['nullable', 'exists:insurance_companies,id'],
            'insurance_company_name' => ['nullable', 'string', 'max:255'],
            'tests' => ['nullable', 'array'],
            'tests.*.test_id' => ['required', 'integer'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:5120'],
        ]);

        if (empty($validated['tests']) && empty($validated['images'])) {
            return back()->withErrors([
                'items' => __('You must select items or upload images')
            ])->withInput();
        }

        DB::transaction(function () use ($validated, $client, $request, $type) {

            $imageNames = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('requests', $filename, 'public');
                    $imageNames[] = $filename;
                }
            }

            // Handle insurance company - create new if name provided, otherwise use selected ID or client's current
            $insuranceCompanyId = $client->insurance_company_id; // Default to client's current insurance
            if ($request->filled('insurance_company_name')) {
                $insuranceCompany = InsuranceCompany::firstOrCreate(
                    ['name' => $request->insurance_company_name],
                    ['is_active' => true]
                );
                $insuranceCompanyId = $insuranceCompany->id;
            } elseif ($request->filled('insurance_company_id')) {
                $insuranceCompanyId = $request->insurance_company_id;
            }

            $clientRequest = ClientRequest::create([
                'client_id' => $client->id,
                'client_address_id' => $validated['client_address_id'] ?? null,
                'insurance_company_id' => $insuranceCompanyId, // Store insurance company at request time
                'pregnant' => $validated['pregnant'] ?? false,
                'diabetic' => $validated['diabetic'] ?? false,
                'heart_patient' => $validated['heart_patient'] ?? false,
                'high_blood_pressure' => $validated['high_blood_pressure'] ?? false,
                'note' => $validated['note'] ?? null,
                'status' => 'pending',
                'images' => $imageNames,
                'type' => $type,
            ]);

            if (!empty($validated['tests'])) {
                $lines = collect($validated['tests'])->map(function ($item) use ($clientRequest, $type) {
                    return [
                        'client_request_id' => $clientRequest->id,
                        'medical_test_id' => $item['test_id'],
                        'quantity' => 1,
                        'item_type' => $type,
                    ];
                })->toArray();

                ClientRequestLine::insert($lines);

            }
        });

        return redirect()->route('client.dashboard')->with(
            'success',
            $type === 'test'
                ? __('Medical test request created successfully')
                : __('Radiology request created successfully')
        );
    }

}

