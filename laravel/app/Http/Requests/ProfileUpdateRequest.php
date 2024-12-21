<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string',
            'email' => 'sometimes|email',
            'nickname' => 'sometimes|string',
            'photo_filename' => 'sometimes|string',
            'password' => 'sometimes|string|min:3',
        ];
        
    }
}
