<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;



class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
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

          
            Transaction::create([
                'transaction_datetime' => now(),
                'user_id' => $user->id,
                'type' => 'P',
                'euros' => $validated['value'],
                'brain_coins' => $brainCoins,
                'payment_type' => $validated['payment_type'],
                'payment_reference' => $validated['payment_reference'],
            ]);

            DB::commit();

            return response()->json(['message' => 'Purchase successful', 'brain_coins' => $brainCoins]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Transaction failed'], 500);
        }
    }
    

    public function transactionHistory(Request $request)
    {
        $transactions = Transaction::where('user_id', $request->user()->id)
            ->orderBy('transaction_datetime', 'desc')
            ->get();

        return response()->json($transactions);
    }


    public function internalTransaction(TransactionRequest $request){
        $newTransaction = new Transaction();
        $newTransaction->fill($request->validated());
        $newTransaction->transaction_datetime = now();
        $newTransaction->user_id = $request->user()->id;
        $newTransaction->save();

        $user = $request->user();

        $user->brain_coins_balance += $newTransaction->brain_Coins;

                
        return new TransactionResource($newTransaction);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TransactionRequest $request, Transaction $transaction)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}