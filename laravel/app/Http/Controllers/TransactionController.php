<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Notifications\TransactionNotification;
use App\Http\Requests\TransactionHistoryRequest;



class TransactionController extends Controller
{

    public function store(TransactionRequest $request)
    {
        $newTransaction = new Transaction();
        $newTransaction->fill($request->validated());
        $newTransaction->transaction_datetime = now();
        $newTransaction->user_id = $request->user()->id;
        $newTransaction->save();
        return new TransactionResource($newTransaction);
    }

    public function buyBrainCoins(Request $request)
    {
        $validated = $request->validate([
            'payment_type' => 'required|in:MBWAY,PAYPAL,IBAN,MB,VISA',
            'payment_reference' => 'required|string',
            'value' => 'required|integer|min:1|max:99',
        ]);

        DB::beginTransaction();
        try {

            $response = Http::post('https://dad-202425-payments-api.vercel.app/api/debit', [
                'type' => $validated['payment_type'],
                'reference' => $validated['payment_reference'],
                'value' => $validated['value'],
            ]);

            if ($response->status() !== 201) {
                return response()->json(['error' => 'Payment failed'], 422);
            }

            $brainCoins = $validated['value'] * 10;
            $user = $request->user();


            $user->brain_coins_balance += $brainCoins;
            $user->save();


            $transaction = Transaction::create([
                'transaction_datetime' => now(),
                'user_id' => $user->id,
                'type' => 'P',
                'euros' => $validated['value'],
                'brain_coins' => $brainCoins,
                'payment_type' => $validated['payment_type'],
                'payment_reference' => $validated['payment_reference'],
            ]);

            DB::commit();

            $user->notify(new TransactionNotification($transaction));

            return response()->json(['message' => 'Purchase successful', 'brain_coins' => $brainCoins]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Transaction failed'], 500);
        }
    }


    public function transactionHistory(TransactionHistoryRequest $request)
    {
        $user = $request->user();

        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 15;
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;
        $type = $validated['type'] ?? null;
        $userNameLike = null;

        // Check if the user is an Administrator, and if he is, show all games
        if ($user->type === 'A') {
            $query = Transaction::orderBy('transaction_datetime', 'desc');
            $userNameLike = $validated['user_name_like'] ?? null;
        } else {
            // if they aren't, show only the user's games
            $query = Transaction::where('user_id', $user->id)->orderBy('transaction_datetime', 'desc');
        }

        if ($startDate) {
            $query->whereDate('transaction_datetime', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('transaction_datetime', '<=', $endDate);
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($userNameLike) {
            $query->whereHas('user', function ($q) use ($userNameLike) {
                $q->where('name', 'like', '%' . $userNameLike . '%');
            });
        }


        $transactions = $query->paginate($perPage);
        return TransactionResource::collection($transactions);
    }




}