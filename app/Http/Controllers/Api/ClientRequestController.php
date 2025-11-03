<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClientRequestController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
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
            'lines.*.unit' => ['required', Rule::in(['box', 'strips'])],
        ]);

        // Ensure the address belongs to the client
        $address = ClientAddress::where('id', $validated['client_address_id'])
            ->where('client_id', $validated['client_id'])
            ->first();

        if (!$address) {
            return response()->json([
                'message' => 'The selected address does not belong to the client.',
            ], 422);
        }

        $created = DB::transaction(function () use ($validated) {
            $requestModel = ClientRequest::create([
                'client_id' => $validated['client_id'],
                'client_address_id' => $validated['client_address_id'],
                'pregnant' => $validated['pregnant'] ?? false,
                'diabetic' => $validated['diabetic'] ?? false,
                'heart_patient' => $validated['heart_patient'] ?? false,
                'high_blood_pressure' => $validated['high_blood_pressure'] ?? false,
                'note' => $validated['note'] ?? null,
                'status' => $validated['status'] ?? 'pending',
            ]);

            $linesPayload = collect($validated['lines'])
                ->map(fn ($line) => [
                    'medicine_id' => $line['medicine_id'],
                    'quantity' => $line['quantity'],
                    'unit' => $line['unit'],
                ])->toArray();

            $requestModel->lines()->createMany($linesPayload);

            return $requestModel->load(['address.city', 'address.area', 'lines.medicine']);
        });

        return response()->json([
            'message' => 'Request created successfully.',
            'data' => $created,
        ], 201);
    }
}



