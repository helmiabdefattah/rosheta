<?php

namespace App\Http\Requests\Clinic;

use App\Enums\DentalItemLevel;
use App\Support\DentalTooth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating/updating a lab order with its line items. The lab and
 * patient are additionally checked against the active clinic in the controller.
 */
class DentalLabOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $codes = DentalTooth::codes();

        return [
            'dental_lab_id' => ['required', 'integer', 'exists:dental_labs,id'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'teeth_mode' => ['required', Rule::in(['order', 'item'])],
            'teeth' => ['nullable', 'array'],
            'teeth.*' => [Rule::in($codes)],
            'notes' => ['nullable', 'string', 'max:2000'],
            'patient_price' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'operating_fee' => ['nullable', 'numeric', 'min:0', 'max:100000000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.dental_item_id' => ['nullable', 'integer', 'exists:dental_items,id'],
            'items.*.level' => ['required', Rule::in(DentalItemLevel::values())],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.unit' => ['required', 'string', 'max:30'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:100000'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:100000000'],
            'items.*.operating_fee' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'items.*.teeth' => ['nullable', 'array'],
            'items.*.teeth.*' => [Rule::in($codes)],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => __('app.dental.order.items_required'),
            'items.min' => __('app.dental.order.items_required'),
        ];
    }
}
