<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GameRequest extends FormRequest
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
            'type' => 'required|string|in:S,M',
            'created_user_id' => 'required|integer|exists:users,id',
            'winner_user_id' => 'nullable|integer|exists:users,id',
            'status' => 'required|string|in:PE,PL,E,I',
            'total_time' => 'nullable|integer|min:0',
            'board_id' => 'required|integer|exists:boards,id',
            'total_turns_winner' => 'nullable|integer|min:0',
            'custom' => 'nullable|json',
        ];
    }
}