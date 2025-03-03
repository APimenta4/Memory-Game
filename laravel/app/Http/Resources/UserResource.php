<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified' => $this->email_verified_at ? true : false,
            'type' => $this->type,
            'nickname' => $this->nickname,
            'blocked' => $this->blocked,
            'photo_filename' => $this->photo_filename,
            'brain_coins_balance' => $this->brain_coins_balance,
            'custom' => $this->custom,
        ];
    }
}
