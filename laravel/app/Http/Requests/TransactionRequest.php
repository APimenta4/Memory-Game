<?php

namespace App\Http\Requests;

use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'game_id' => 'nullable|exists:games,id|required_if:type,I',
            'type' => 'required|string|in:B,P,I',
            'euros' => 'nullable|numeric|min:0|required_if:type,P',
            'brain_coins' => 'required|integer',
            'payment_type' => 'nullable|string|in:MBWAY,IBAN,MB,VISA|required_if:type,P',
            'payment_reference' => 'nullable|string|required_if:type,P',
            'custom' => 'nullable|array',
        ];
    }
}