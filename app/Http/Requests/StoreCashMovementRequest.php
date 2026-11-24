<?php

namespace App\Http\Requests;

use App\Enums\CashMovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCashMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'moved_at' => ['nullable', 'date'],
            'type' => ['required', Rule::in(array_map(fn ($c) => $c->value, CashMovementType::manualCases()))],
            'direction' => ['nullable', 'required_if:type,correction', Rule::in(['in', 'out'])],
            'amount' => ['required', 'numeric', 'gt:0'],
            'label' => ['nullable', 'required_if:type,correction', 'string', 'max:255'],
        ];
    }
}
