<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Currency;
use App\Models\PaymentMethod;
use App\Models\PurchaseInvoice;
use App\Models\ServiceRequest;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount('purchaseInvoices')->latest('id')->paginate(15);
        $accounts = Account::query()->where('is_active', true)->where('is_postable', true)->orderBy('account_code')->get();
        $currencies = Currency::query()->where('is_active', true)->orderBy('code')->get();

        return view('admin.suppliers.index', compact('suppliers', 'accounts', 'currencies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_code' => 'required|string|max:30|unique:suppliers,supplier_code',
            'name' => 'required|string|max:150',
            'contact_person' => 'nullable|string|max:150',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'account_id' => 'required|exists:accounts,id',
            'default_currency_id' => 'required|exists:currencies,id',
            'opening_balance' => 'nullable|numeric',
            'opening_balance_date' => 'nullable|date',
            'tax_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        Supplier::create($validated);

        return back()->with('success', 'تمت إضافة المورد بنجاح.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'contact_person' => 'nullable|string|max:150',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'is_active' => 'required|boolean',
        ]);

        $sup = Supplier::findOrFail($id);
        $sup->update($validated);

        return back()->with('success', 'تم تحديث بيانات المورد بنجاح.');
    }

    public function destroy($id)
    {
        $sup = Supplier::findOrFail($id);
        if ($sup->purchaseInvoices()->count() > 0) {
            return back()->withErrors(['msg' => 'لا يمكن حذف المورد لوجود فواتير شراء مرتبطة به.']);
        }
        $sup->delete();

        return back()->with('success', 'تم حذف المورد بنجاح.');
    }

    public function invoices()
    {
        $invoices = PurchaseInvoice::with(['supplier', 'currency'])
            ->latest('id')
            ->paginate(15);
        $suppliers = Supplier::where('is_active', 1)->get();
        $currencies = Currency::where('is_active', 1)->get();
        $serviceRequests = ServiceRequest::query()
            ->select(['id', 'request_number'])
            ->latest('id')
            ->limit(100)
            ->get();

        return view('admin.suppliers.invoices', compact('invoices', 'suppliers', 'currencies', 'serviceRequests'));
    }

    public function storeInvoice(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required|string|max:50|unique:purchase_invoices,invoice_number',
            'supplier_id' => 'required|exists:suppliers,id',
            'service_request_id' => 'required|exists:service_requests,id',
            'currency_id' => 'required|exists:currencies,id',
            'invoice_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
        ]);

        PurchaseInvoice::create([
            'invoice_number' => $request->invoice_number,
            'supplier_id' => $request->supplier_id,
            'service_request_id' => $request->service_request_id,
            'currency_id' => $request->currency_id,
            'invoice_date' => $request->invoice_date,
            'subtotal' => $request->total_amount,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => $request->total_amount,
            'status' => 'DRAFT',
            'created_by_user_id' => Auth::id(),
        ]);

        return back()->with('success', 'تم تسجيل فاتورة الشراء بنجاح.');
    }

    public function updateInvoiceStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:DRAFT,POSTED,CANCELLED',
        ]);

        $invoice = PurchaseInvoice::findOrFail($id);
        $invoice->update([
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'تم تحديث حالة فاتورة الشراء بنجاح.');
    }

    public function destroyInvoice($id)
    {
        $invoice = PurchaseInvoice::findOrFail($id);

        if (in_array($invoice->status, ['PAID', 'POSTED'])) {
            return back()->withErrors(['msg' => 'لا يمكن حذف فاتورة مدفوعة أو مرحلة محاسبياً.']);
        }

        $invoice->delete();

        return back()->with('success', 'تم حذف فاتورة الشراء بنجاح.');
    }

    // سندات صرف ودفع مستحقات الموردين
    public function payments(Request $request)
    {
        $query = SupplierPayment::with(['supplier', 'currency', 'paymentMethod', 'account', 'createdByUser']);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->latest('id')->paginate(15);
        $suppliers = Supplier::where('is_active', true)->get();
        $currencies = Currency::where('is_active', true)->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        $accounts = Account::where('account_type', 'ASSET')->where('is_postable', true)->get();

        return view('admin.suppliers.payments', compact('payments', 'suppliers', 'currencies', 'paymentMethods', 'accounts'));
    }

    public function storePayment(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'account_id' => 'required|exists:accounts,id',
            'currency_id' => 'required|exists:currencies,id',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        SupplierPayment::create([
            'payment_number' => 'SPAY-'.date('Ymd').'-'.rand(1000, 9999),
            'supplier_id' => $validated['supplier_id'],
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'payment_method_id' => $validated['payment_method_id'],
            'account_id' => $validated['account_id'],
            'currency_id' => $validated['currency_id'],
            'reference_number' => $validated['reference_number'],
            'notes' => $validated['notes'],
            'status' => 'CONFIRMED',
            'created_by_user_id' => Auth::id(),
            'confirmed_by_user_id' => Auth::id(),
            'confirmed_at' => now(),
        ]);

        return back()->with('success', 'تم تسجيل سند الصرف وسداد المورد بنجاح.');
    }

    public function confirmPayment($id)
    {
        $payment = SupplierPayment::findOrFail($id);
        $payment->status = 'CONFIRMED';
        $payment->confirmed_by_user_id = Auth::id();
        $payment->confirmed_at = now();
        $payment->save();

        return back()->with('success', 'تم تأكيد صرف الدفعة بنجاح.');
    }

    public function destroyPayment($id)
    {
        $payment = SupplierPayment::findOrFail($id);
        $payment->delete();

        return back()->with('success', 'تم حذف سند الصرف بنجاح.');
    }

    // مردودات المشتريات وإشعارات الخصم
    public function returns(Request $request)
    {
        $query = SupplierReturn::with(['supplier', 'purchaseInvoice', 'currency', 'createdByUser']);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $returns = $query->latest('id')->paginate(15);
        $suppliers = Supplier::where('is_active', true)->get();
        $invoices = PurchaseInvoice::where('status', '!=', 'CANCELLED')->latest('id')->get();
        $currencies = Currency::where('is_active', true)->get();

        return view('admin.suppliers.returns', compact('returns', 'suppliers', 'invoices', 'currencies'));
    }

    public function storeReturn(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_invoice_id' => 'required|exists:purchase_invoices,id',
            'return_date' => 'required|date',
            'total_amount' => 'required|numeric|min:1',
            'currency_id' => 'required|exists:currencies,id',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        SupplierReturn::create([
            'return_number' => 'SRET-'.date('Ymd').'-'.rand(1000, 9999),
            'supplier_id' => $validated['supplier_id'],
            'purchase_invoice_id' => $validated['purchase_invoice_id'],
            'return_date' => $validated['return_date'],
            'total_amount' => $validated['total_amount'],
            'currency_id' => $validated['currency_id'],
            'status' => 'POSTED',
            'reason' => $validated['reason'],
            'notes' => $validated['notes'],
            'created_by_user_id' => Auth::id(),
            'posted_by_user_id' => Auth::id(),
            'posted_at' => now(),
        ]);

        return back()->with('success', 'تم تسجيل سند مردودات المشتريات وترحيله بنجاح.');
    }

    public function destroyReturn($id)
    {
        $ret = SupplierReturn::findOrFail($id);
        $ret->delete();

        return back()->with('success', 'تم حذف سند المردودات بنجاح.');
    }
}
