<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Board extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_cols',
        'board_rows',
        'custom',
    ];

    protected $casts = [
        'custom' => 'array', 
    ];

    public function games()
    {
        return $this->hasMany(Game::class, 'board_id');
    }
}
