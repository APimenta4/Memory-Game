<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GameUpdateRequest extends FormRequest
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
            'winner_user_id' => 'nullable|integer|exists:users,id',
            'status' => 'required|string|in:PE,PL,E,I',
            'total_time' => 'decimal:2|min:0|required_if:status,E',
            'total_turns_winner' => 'integer|min:0|required_if:status,E',
            'custom' => 'nullable|json',
        ];
    }
    public function messages(): array
    {
        return [
            'winner_user_id.required_if' => 'The winner must be specified if the game has ended.',
            'total_time.required_if' => 'Total time is required when the game has ended.',
            'status.in' => 'The status must be one of PE (Pending), PL (Playing), E (Ended), or I (Invalid).',
        ];
    }
}