<?php

namespace App\Http\Resources;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Enums\GameType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\BoardResource;

class GameDetailedResource extends JsonResource
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
            'creator' => new UserResource($this->creator),
            'type' => $this->type,
            'status' => $this->status,
            'began_at' => $this->began_at,
            'board' => new BoardResource($this->board),
            'custom' => $this->custom,
        ];
             
        if ($this->status == GameStatus::ENDED) {
            $response['total_time'] = $this->total_time;
            $response['ended_at'] = $this->ended_at;
            $response['total_turns_winner'] = $this->total_turns_winner;
        }
        
        if ($this->type == GameType::MULTIPLAYER) {
            foreach ($this->players as $player) {
                $player = new UserResource($player);
                $response['players'][] = $player;
            }
       
            if ($this->status == GameStatus::ENDED) {
                $response['winner'] = $this->winner;
                $response['total_turns_winner'] = $this->total_turns_winner; 
            }
        }     

        return $response;
    }
}
