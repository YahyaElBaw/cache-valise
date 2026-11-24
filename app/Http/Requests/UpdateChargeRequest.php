<?php

namespace App\Http\Requests;

use App\Enums\ChargeCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $charge = $this->route('charge');

        return [
            'number' => ['required', 'string', 'max:50', Rule::unique('charges', 'number')->ignore($charge)],
            'charge_date' => ['required', 'date'],
            'batch_id' => ['required', 'exists:batches,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'category' => ['required', Rule::enum(ChargeCategory::class)],
            'label' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit' => ['required', 'string', 'max:50'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
