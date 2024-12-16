<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionHistoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'per_page' => 'integer|min:1',
            'start_date' => 'sometimes|date|nullable',
            'end_date' => 'sometimes|date|nullable',
            'type' => 'sometimes|string|in:I,B,P|nullable',
        ];
    }


}