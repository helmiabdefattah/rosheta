<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientRequestResource;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\ClientRequest;
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
        $client = $request->user();

        $query = ClientRequest::where('client_id', $client->id)
            ->with(['address.city', 'address.area', 'lines.medicine', 'offers.pharmacy']);

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
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

            'pregnant' => ['sometimes', 'boolean'],
            'diabetic' => ['sometimes', 'boolean'],
            'heart_patient' => ['sometimes', 'boolean'],
            'high_blood_pressure' => ['sometimes', 'boolean'],
            'note' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],

            'lines' => ['required', 'array', 'min:1'],
            'lines.*.medicine_id' => ['required', 'exists:medicines,id'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
            'lines.*.unit' => ['required', Rule::in(['box', 'strips', 'bottle', 'pack', 'piece', 'tablet', 'capsule', 'vial', 'ampoule'])],

            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'string'],
        ]);

        // Get authenticated client
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

        $created = DB::transaction(function () use ($validated, $client) {
            $requestModel = ClientRequest::create([
                'client_id' => $client->id,
                'client_address_id' => $validated['client_address_id'],
                'pregnant' => $validated['pregnant'] ?? false,
                'diabetic' => $validated['diabetic'] ?? false,
                'heart_patient' => $validated['heart_patient'] ?? false,
                'high_blood_pressure' => $validated['high_blood_pressure'] ?? false,
                'note' => $validated['note'] ?? null,
                'status' => $validated['status'] ?? 'pending',
                'images' => $validated['images'] ?? [],
            ]);

            $linesPayload = collect($validated['lines'])
                ->map(fn ($line) => [
                    'medicine_id' => $line['medicine_id'],
                    'quantity' => $line['quantity'],
                    'unit' => $line['unit'],
                ])->toArray();

            $requestModel->lines()->createMany($linesPayload);

            return $requestModel->load(['address.city', 'address.area', 'lines.medicine', 'client']);
        });

        return (new ClientRequestResource($created))
            ->additional([
                'message' => 'Request created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }
}



