<?php

namespace App\Http\Requests\Clinic;

use App\Enums\DentalItemLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for a clinic's own (custom) catalog item.
 */
class DentalItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'level' => ['required', Rule::in(DentalItemLevel::values())],
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'default_unit' => ['required', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
