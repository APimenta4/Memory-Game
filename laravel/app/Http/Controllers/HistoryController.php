<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\GameResource;
use App\Http\Requests\HistoryRequest;

class HistoryController extends Controller
{

    public function index(HistoryRequest $request)
    {
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
        } elseif($type === 'singleplayer') {
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

        if ($perPage) {
            $games = $query->paginate($perPage);
        } else {
            $games = $query->get();
        }

        return GameResource::collection($games);
    }

    public function multiplayer(HistoryRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();
        $perPage = $validated['per_page'] ?? 10;
        $status = $validated['status'] ?? null;
        $sortBy = $validated['sort_by'] ?? 'began_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;
        $boardId = $validated['board_id'] ?? null;
        $won = $validated['won'] ?? false;
        $query = $user->multiplayerGames()
            ->orderBy($sortBy, $sortOrder)
            ->where(function($query) {
                $query->where('status', 'E')
                    ->orWhere('status', 'I');
            });

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

        if ($won) {
            $query->where('winner_user_id', $user->id);
        }

        $multiplayerGames = $query->paginate($perPage);

        return GameResource::collection($multiplayerGames);
    }

    public function singleplayer(HistoryRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();
        $perPage = $validated['per_page'] ?? 10;
        $status = $validated['status'] ?? null;
        $sortBy = $validated['sort_by'] ?? 'began_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;
        $boardId = $validated['board_id'] ?? null;

        $query = $user->singleplayerGames()
            ->orderBy($sortBy, $sortOrder)
            ->where(function($query) {
                $query->where('status', 'E')
                    ->orWhere('status', 'I');
            });


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

        $singleplayerGames = $query->paginate($perPage);

        return GameResource::collection($singleplayerGames);
    }
}
