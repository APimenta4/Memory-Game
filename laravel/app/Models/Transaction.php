<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'transaction_datetime',
        'user_id',
        'game_id',
        'type',
        'euros',
        'brain_coins',
        'payment_type',
        'payment_reference',
        'custom',
    ];

    protected $casts = [
        'transaction_datetime' => 'datetime',
        'custom' => 'array',
        'type' => TransactionType::class
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the game associated with this transaction (only for type 'I').
     */
    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
