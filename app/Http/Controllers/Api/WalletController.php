<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CraftsmanPayout;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $transactions = Transaction::where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'status' => true,
            'data' => [
                'wallet_balance' => (float) $user->wallet_balance,
                'transactions' => $transactions,
            ],
        ]);
    }

    public function requestPayout(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:50',
            'bank_name' => 'nullable|string',
            'iban' => 'nullable|string',
        ]);

        $payout = CraftsmanPayout::create([
            'payout_number' => 'PO-'.time(),
            'craftsman_user_id' => $user->id,
            'amount' => $validated['amount'],
            'currency_id' => 1,
            'status' => 'PENDING',
            'notes' => 'طلب سحب عبر التطبيق - '.$validated['bank_name'].' / '.$validated['iban'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم رفع طلب السحب للإدارة وسيتم مراجعته والتحويل قريباً',
            'data' => $payout,
        ]);
    }

    public function deposit(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'nullable|string',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'transfer_reference' => 'nullable|string',
            'receipt_image' => 'nullable|string',
        ]);

        $user = $request->user();
        $amount = (float) $validated['amount'];

        $pmId = $validated['payment_method_id'] ?? null;
        if (! $pmId && ! empty($validated['payment_method'])) {
            $pm = PaymentMethod::where('code', $validated['payment_method'])->first();
            $pmId = $pm?->id;
        }

        $savedReceipt = null;
        if (! empty($validated['receipt_image'])) {
            $raw = $validated['receipt_image'];
            if (str_starts_with($raw, 'data:image')) {
                $raw = substr($raw, strpos($raw, ',') + 1);
            }
            if (! str_starts_with($raw, 'http') && ! str_starts_with($raw, '/storage/')) {
                $filename = 'receipts/rc_'.time().'_'.rand(100, 999).'.jpg';
                Storage::disk('public')->put($filename, base64_decode($raw));
                $savedReceipt = '/storage/'.$filename;
            } else {
                $savedReceipt = $raw;
            }
        }

        $tx = Transaction::create([
            'transaction_number' => 'DEP-'.time().'-'.rand(100, 999),
            'user_id' => $user->id,
            'amount' => $amount,
            'currency_id' => 1,
            'payment_method_id' => $pmId ?? 1,
            'provider_transaction_id' => $validated['transfer_reference'] ?? null,
            'receipt_image' => $savedReceipt,
            'payment_status' => 'pending',
            'paid_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم استلام طلب الشحن بنجاح وجاري التحقق من إشعار الإيداع ✓',
            'data' => [
                'wallet_balance' => (float) $user->wallet_balance,
                'transaction' => $tx,
            ],
        ]);
    }
}
