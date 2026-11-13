<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use App\Models\PayoutRequest;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $transactions = WalletTransaction::where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'status' => true,
            'data' => [
                'wallet_balance' => (float)$user->wallet_balance,
                'transactions' => $transactions,
            ]
        ]);
    }

    public function requestPayout(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:50',
            'bank_name' => 'required|string',
            'iban' => 'required|string',
        ]);

        if ($user->wallet_balance < $validated['amount']) {
            return response()->json([
                'status' => false,
                'message' => 'الرصيد المتاح غير كافٍ لسحب هذا المبلغ',
            ], 400);
        }

        $user->wallet_balance -= $validated['amount'];
        $user->save();

        $payout = PayoutRequest::create([
            'craftsman_id' => $user->id,
            'amount' => $validated['amount'],
            'bank_name' => $validated['bank_name'],
            'iban' => $validated['iban'],
            'status' => 'pending',
        ]);

        WalletTransaction::create([
            'user_id' => $user->id,
            'type' => 'withdrawal',
            'amount' => $validated['amount'],
            'balance_after' => $user->wallet_balance,
            'description' => "طلب سحب رصيد إلى الحساب البنكي ({$validated['bank_name']})",
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم رفع طلب السحب للإدارة وسيتم التحويل خلال 24 ساعة',
            'data' => $payout,
        ]);
    }
}
