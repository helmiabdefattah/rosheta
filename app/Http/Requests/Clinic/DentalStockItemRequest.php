<?php

namespace App\Http\Requests\Clinic;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for creating/updating a stock item. The opening quantity is only
 * read on create (updates change quantity through adjustments, not this form).
 */
class DentalStockItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dental_item_id' => ['nullable', 'integer', 'exists:dental_items,id'],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:30'],
            'opening_quantity' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'reorder_level' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
