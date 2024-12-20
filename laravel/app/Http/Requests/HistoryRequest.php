<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HistoryRequest extends FormRequest
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
            'status' => 'sometimes|string|in:PE,PL,E,I|nullable',
            'sort_by' => 'sometimes|string|in:began_at,total_time,id|nullable',
            'sort_order' => 'sometimes|string|in:asc,desc|nullable',
            'start_date' => 'sometimes|date|nullable',
            'end_date' => 'sometimes|date|nullable',
            'type' => 'sometimes|string|in:multiplayer,singleplayer|nullable',
            'board_id' => 'sometimes|int|nullable',
        ];
    }


}