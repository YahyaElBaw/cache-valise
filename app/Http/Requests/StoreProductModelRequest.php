<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductModelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reference' => ['required', 'string', 'max:50', Rule::unique('product_models', 'reference')],
            'designation' => ['required', 'string', 'max:255'],
            'dimensions' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'suggested_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
