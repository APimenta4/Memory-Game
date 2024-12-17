<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserListRequest extends FormRequest
{
  

    public function rules(): array
    {
        return [
            'user_name_like' => 'sometimes|string|nullable',
        ];
    }
}
