<?php

namespace App\Http\Resources;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Enums\GameType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\BoardResource;

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
            'creator_id' => $this->creator->id,
            'creator_nickname' => $this->creator->nickname,
            'type' => $this->type,
            'status' => $this->status,
            'began_at' => $this->began_at,
            'board_id' => $this->board->id,
            'board_size' => $this->board->board_cols . 'x' . $this->board->board_rows,
            'custom' => $this->custom,
        ];

        if ($this->status == GameStatus::ENDED) {
            $response['total_time'] = $this->total_time;
            $response['ended_at'] = $this->ended_at;
            $response['total_turns_winner'] = $this->total_turns_winner;
        }

        if ($this->type == GameType::MULTIPLAYER) {
            foreach ($this->players as $player) {
                $player = ['player_id' => $player->id, 'player_nickname' => $player->nickname, 'pairs_discovered' => $player->pivot->pairs_discovered];
                $response['players'][] = $player;
            }

            if ($this->status == GameStatus::ENDED) {
                $response['winner_id'] = $this->winner->id;
                $response['winner_nickname'] = $this->winner->nickname;
                $response['total_turns_winner'] = $this->total_turns_winner;
            }
        }

        return $response;
    }
}
