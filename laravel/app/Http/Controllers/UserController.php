<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Transaction;
use App\Http\Requests\UserListRequest;
use App\Http\Requests\RegisterRequest;
use App\Notifications\TransactionNotification;

class UserController extends Controller
{

    public function index(UserListRequest $request)
    {
        $users = User::orderBy('type', 'asc')->get();
        return UserResource::collection($users);
    }

    public function store(RegisterRequest $request)
    {
        $newUserData = $request->validated();

        // If a logged in admin is creating an account, he is creating an administrator account
        if ($request->user('sanctum')?->type == 'A') {
            $newUserData['type'] = 'A';
        } else {
            $newUserData['type'] = 'P';
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 'public');
            $newUserData['photo_filename'] = basename($path);
        }

        $user = User::create($newUserData);

        if ($user->type === 'P'){
            $this->registeredBonus($user);
        }
        
        return new UserResource($user);
    }


    public function update(Request $request, string $id)
    {

    }

    public function destroy(Request $request, User $user)
    {
        if($request->user()->type!=='A'){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
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

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }


    /**
     * Make the transaction with bonus.
     *
     * @param  User  $user
     * @return void
     */
    public function registeredBonus(User $user){
        $bonus = 10;
        $transaction = Transaction::create([
            'transaction_datetime' => now(),
            'user_id' => $user->id,
            'type' => 'B',
            'brain_coins' => $bonus,
        ]);
        $transaction->save();

        $user->brain_coins_balance += $bonus;
        $user->save();
        $user->notify(new TransactionNotification($transaction));
    }
}
