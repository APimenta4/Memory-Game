<?php

namespace App\Http\Resources;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Enums\GameType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {    
        $response = [
            'id' => $this->id,
            'creator' => $this->creator,
            'type' => $this->type,
            'status' => $this->status,
            'began_at' => $this->began_at,         
            'total_time' => $this->total_time,
            'board_size' => $this->board->board_cols . $this->board->board_rows,         
        ]; 
               
        if ($this->status == GameStatus::ENDED) {
            $response['total_time'] = $this->score;
            $response['ended_at'] = $this->ended_at;
        }
        
        if ($this->type == GameType::MULTIPLAYER) {
            $response['players'] = $this->players;
        
            if ($this->status == GameStatus::ENDED) {
                $response['winner'] = $this->winner;
            }
        }     

        return $response;
    }
}
