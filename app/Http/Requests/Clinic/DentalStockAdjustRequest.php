<?php

namespace App\Http\Requests\Clinic;

use App\Enums\StockMovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for a manual stock adjustment (add / remove / correct).
 */
class DentalStockAdjustRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(StockMovementType::values())],
            'quantity' => ['required', 'numeric', 'min:0.01', 'max:100000000'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
