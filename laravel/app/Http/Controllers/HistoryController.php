<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\GameResource;
use App\Http\Resources\GameDetailedResource;
use App\Http\Requests\HistoryRequest;

class HistoryController extends Controller
{

    public function index(HistoryRequest $request)
    {

        // TODO if 'A'

        $validated = $request->validated();
        $user = $request->user();
        $type = $validated['type'] ?? null;
        $perPage = $validated['per_page'] ?? null;
        $status = $validated['status'] ?? null;
        $sortBy = $validated['sort_by'] ?? 'began_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;
        $boardId = $validated['board_id'] ?? null;
        $won = $validated['won'] ?? false;

        if ($type === 'multiplayer') {
            $query = $user->multiplayerGames()->orderBy($sortBy, $sortOrder);
        } elseif ($type === 'singleplayer') {
            $query = $user->singleplayerGames()->orderBy($sortBy, $sortOrder);
        } else {
            $query = $user->allGames()->orderBy($sortBy, $sortOrder);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($startDate) {
            $query->whereDate('began_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('began_at', '<=', $endDate);
        }

        if ($boardId) {
            $query->where('board_id', $boardId);
        }

        if ($type === 'multiplayer' && $won) {
            $query->where('winner_user_id', $user->id);
        }


        $games = $query->paginate($perPage);


        return GameResource::collection($games);
    }
}
