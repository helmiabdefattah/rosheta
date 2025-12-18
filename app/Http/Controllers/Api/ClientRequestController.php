<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientRequestResource;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\ClientRequest;
use App\Models\MedicalTest;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClientRequestController extends Controller
{
    /**
     * List all client requests for authenticated client
     */
    public function index(Request $request)
    {
        // Validate that client_id is provided
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'type' => 'sometimes|in:medicine,test' // Add type validation
        ]);

        $clientId = $request->get('client_id');

        $query = ClientRequest::where('client_id', $clientId)
            ->with([
                'lines.medicine', // Load medicine for medicine lines
                'lines.medicalTest', // Load medicalTest for test lines
                'address.city',
                'address.area',
                'offers.pharmacy',
                'offers.laboratory',
            ]);
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type if provided
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Sort by created_at descending (newest first)
        $query->orderBy('created_at', 'desc');

        // Paginate results
        $perPage = $request->get('per_page', 15);
        $requests = $query->paginate($perPage);
        return ClientRequestResource::collection($requests)
            ->additional([
                'message' => 'Client requests retrieved successfully',
            ]);
    }
    /**
     * Create a new client request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_address_id' => ['required', 'exists:client_addresses,id'],

            'pregnant' => 'required',
            'diabetic' => 'required',
            'heart_patient' => 'required',
            'high_blood_pressure' => 'required',
            'note' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],

            'lines' => 'nullable',
            'lines.*.medicine_id' => ['required_with:lines', 'exists:medicines,id'],
            'lines.*.quantity' => ['required_with:lines', 'integer', 'min:1'],
            'lines.*.unit' => ['required_with:lines', Rule::in(['box', 'strips', 'bottle', 'pack', 'piece', 'tablet', 'capsule', 'vial', 'ampoule'])],

            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'], // 5MB max
        ]);

        $client = $request->user();

        // Ensure the address belongs to the authenticated client
        $address = ClientAddress::where('id', $validated['client_address_id'])
            ->where('client_id', $client->id)
            ->first();

        if (!$address) {
            return response()->json([
                'message' => 'The selected address does not belong to you.',
            ], 422);
        }

        // Validate that either lines or images are provided
        if (empty($validated['lines']) && empty($validated['images'])) {
            return response()->json([
                'message' => 'Either medicine lines or prescription images must be provided.',
            ], 422);
        }

        $created = DB::transaction(function () use ($validated, $client, $request) {

            // Handle image uploads
            $imageNames = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $filename = $file->getClientOriginalName(); // just the file name with extension
                    $file->storeAs('requests', $filename, 'public'); // store in storage/app/public/requests
                    $imageNames[] = $filename;
                }
            }

            $requestModel = ClientRequest::create([
                'client_id' => $client->id,
                'client_address_id' => $validated['client_address_id'],
                'pregnant' => $validated['pregnant'] ?? false,
                'diabetic' => $validated['diabetic'] ?? false,
                'heart_patient' => $validated['heart_patient'] ?? false,
                'high_blood_pressure' => $validated['high_blood_pressure'] ?? false,
                'note' => $validated['note'] ?? null,
                'status' => $validated['status'] ?? 'pending',
                'images' => $imageNames, // store array of file names
                'type' => 'medicine'
            ]);

            // Save request lines if provided
            if (!empty($validated['lines'])) {
                // Decode the JSON string to array
                $linesArray = json_decode($validated['lines'], true);

                // Check if decoding was successful
                if (json_last_error() === JSON_ERROR_NONE && is_array($linesArray)) {
                    $linesPayload = collect($linesArray)->map(fn($line) => [
                        'medicine_id' => $line['medicine_id'],
                        'quantity' => $line['quantity'],
                        'unit' => $line['unit'],
                    ])->toArray();

                    $requestModel->lines()->createMany($linesPayload);
                } else {
                    // Handle JSON decode error
                    throw new \Exception('Invalid lines format. Expected valid JSON array.');
                }
            }

            return $requestModel->load(['address.city', 'address.area', 'lines.medicine', 'client']);
        });

        return (new ClientRequestResource($created))
            ->additional([
                'message' => 'Request created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }
    public function test_requests(Request $request)
    {
        $validated = $request->validate([
            'client_address_id' => ['nullable', 'exists:client_addresses,id'],

            'pregnant' => 'required',
            'diabetic' => 'required',
            'heart_patient' => 'required',
            'high_blood_pressure' => 'required',
            'note' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],

            'tests' => 'nullable',
            'tests.*.medical_test_id' => ['required_with:testLines', 'exists:medicalTests,id'],

            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'], // 5MB max
        ]);

        $client = $request->user();
        if ($request->client_address_id != null) {
            $address = ClientAddress::where('id', $validated['client_address_id'])
                ->where('client_id', $client->id)
                ->first();

            if (!$address) {
                return response()->json([
                    'message' => 'The selected address does not belong to you.',
                ], 422);
            }
        }
        // Validate that either lines or images are provided
        if (empty($validated['tests']) && empty($validated['images'])) {
            return response()->json([
                'message' => 'Either tests lines or prescription images must be provided.',
            ], 422);
        }

        $created = DB::transaction(function () use ($validated, $client, $request) {

            // Handle image uploads
            $imageNames = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $filename = $file->getClientOriginalName(); // just the file name with extension
                    $file->storeAs('requests', $filename, 'public'); // store in storage/app/public/requests
                    $imageNames[] = $filename;
                }
            }

            $requestModel = ClientRequest::create([
                'client_id' => $client->id,
                'client_address_id' => $validated['client_address_id']?? null,
                'pregnant' => $validated['pregnant'] ?? false,
                'diabetic' => $validated['diabetic'] ?? false,
                'heart_patient' => $validated['heart_patient'] ?? false,
                'high_blood_pressure' => $validated['high_blood_pressure'] ?? false,
                'note' => $validated['note'] ?? null,
                'status' => $validated['status'] ?? 'pending',
                'images' => $imageNames, // store array of file names
                'type' => 'test',
            ]);

            // Save request lines if provided
            if (!empty($validated['tests'])) {
                // Decode the JSON string to array
                $linesArray = json_decode($validated['tests'], true);

                // Check if decoding was successful
                if (json_last_error() === JSON_ERROR_NONE && is_array($linesArray)) {
                    $linesPayload = collect($linesArray)->map(fn($line) => [
                        'medical_test_id' => $line['test_id'],
                        'quantity' => 1,

                    ])->toArray();

                    $requestModel->testLines()->createMany($linesPayload);
                } else {
                    // Handle JSON decode error
                    throw new \Exception('Invalid lines format. Expected valid JSON array.');
                }
            }

            return $requestModel->load(['address.city', 'address.area', 'testLines.medicalTest', 'client']);
        });

        return (new ClientRequestResource($created))
            ->additional([
                'message' => 'Request created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }
    public function medicineList()
    {
        // Return only selected fields
        $medicines = Medicine::select('name', 'arabic', 'units','dosage_form', 'img','id')->get();

        return response()->json([
            'status' => 'success',
            'data' => $medicines
        ]);
    }

    public function testsList()
    {
        $tests = MedicalTest::select('id','test_name_en', 'test_name_ar', 'test_description','conditions')->get();

        return response()->json([
            'status' => 'success',
            'data' => $tests
        ]);
    }

}



