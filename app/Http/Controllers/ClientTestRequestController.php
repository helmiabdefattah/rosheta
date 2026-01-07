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
//        dd($request);
        abort_unless(in_array($type, ['test', 'radiology', 'medicine']), 404);

        $client = Auth::guard('client')->user();
        abort_unless($client, 403);

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
            $rules['medicines.*.medicine_id'] = ['required_with:medicines', 'integer'];
            $rules['medicines.*.quantity'] = ['required_with:medicines', 'integer', 'min:1'];
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

        DB::transaction(function () use ($request, $validated, $client, $type) {

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
        });

        return redirect()
            ->route('client.dashboard')
            ->with('success', __('Request submitted successfully'));
    }

}

