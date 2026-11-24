<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductModelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $model = $this->route('product_model');

        return [
            'reference' => ['required', 'string', 'max:50', Rule::unique('product_models', 'reference')->ignore($model)],
            'designation' => ['required', 'string', 'max:255'],
            'dimensions' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'suggested_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
