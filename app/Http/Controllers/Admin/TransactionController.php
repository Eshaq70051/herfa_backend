<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'request', 'paymentMethod', 'currency']);

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('payment_method_id')) {
            $query->where('payment_method_id', $request->payment_method_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('transaction_number', 'like', "%{$s}%")
                    ->orWhere('provider_transaction_id', 'like', "%{$s}%")
                    ->orWhereHas('user', function ($uq) use ($s) {
                        $uq->where('full_name', 'like', "%{$s}%")
                            ->orWhere('phone_encrypted', 'like', "%{$s}%");
                    });
            });
        }

        $transactions = $query->latest('id')->paginate(15);
        $paymentMethods = PaymentMethod::where('is_active', 1)->get();

        return view('admin.accounting.transactions', compact('transactions', 'paymentMethods'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,refunded',
        ]);

        $trx = Transaction::findOrFail($id);
        $trx->payment_status = $request->payment_status;
        if ($request->payment_status === 'paid' && ! $trx->paid_at) {
            $trx->paid_at = now();
        }
        $trx->save();

        return back()->with('success', 'تم تحديث حالة العملية المالية بنجاح.');
    }
}
