<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Http\Requests\UserListRequest;

class UserController extends Controller
{

    public function index(UserListRequest $request) 
    {   
        $users = User::orderBy('type', 'asc')->get();
        return UserResource::collection($users);    
    }

    public function store(Request $request)
    {
       
    }


    public function update(Request $request, string $id)
    {
        
    }

    public function destroy(User $user)
    {
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->delete();
        return response()->json(null, 204);    
    }

    public function block(User $user)
    {
        if ($user->blocked) {
            return response()->json(['message' => 'User is already blocked'], 400);
        }
        $user->blocked = true;
        $user->save();
        return response()->json(['message' => 'User blocked'], 200); 
    }

    public function unblock(User $user)
    {
        if (!$user->blocked) {
            return response()->json(['message' => 'User is not blocked'], 400);
        }
        $user->blocked = false;
        $user->save();
        return response()->json(['message' => 'User unblocked'], 200); 
    }

    public function me(Request $request) {
        return new UserResource($request->user());
    }
}
