<?php

namespace App\Http\Requests;

use App\Enums\BatchStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $batch = $this->route('batch');

        return [
            'number' => ['required', 'string', 'max:50', Rule::unique('batches', 'number')->ignore($batch)],
            'product_model_id' => ['required', 'exists:product_models,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'production_date' => ['required', 'date'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::enum(BatchStatus::class)],
            'notes' => ['nullable', 'string'],
        ];
    }
}
