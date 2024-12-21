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
        if ($request->user()->type!=='A' && $user->id !== $request->user()->id){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        if ($request->user()->type==='A' && $user->id === $request->user()->id){
            return response()->json(['message' => 'Admins cannot delete them selfs'], 401);
        }
        $user->delete();
        return response()->json(null, 204);
    }

    public function block(Request $request, User $user)
    {
        if ($request->user()->type==='A' && $user->id === $request->user()->id){
            return response()->json(['message' => 'Admins cannot block them selfs'], 401);
        }
        if ($user->blocked) {
            return response()->json(['message' => 'User is already blocked'], 400);
        }
        $user->blocked = true;
        $user->save();
        return response()->json(['message' => 'User blocked'], 200);
    }

    public function unblock(Request $request, User $user)
    {
        if ($request->user()->type==='A' && $user->id === $request->user()->id){
            return response()->json(['message' => 'Admins cannot unblock them selfs'], 401);
        }
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

    public function showBalance(Request $request)
    {
        return response()->json(['brain_coins_balance' => $request->user()->brain_coins_balance], 200);
    }

}
