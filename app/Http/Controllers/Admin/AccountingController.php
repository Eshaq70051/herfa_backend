<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\CraftsmanCollection;
use App\Models\CraftsmanEarning;
use App\Models\CraftsmanPayout;
use App\Models\CraftsmanRemittance;
use App\Models\CraftsmanSettlement;
use App\Models\Currency;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\PaymentMethod;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AccountingController extends Controller
{
    public function currencies(): View
    {
        $currencies = Currency::query()->orderByDesc('is_default')->orderBy('code')->paginate(20);

        return view('admin.accounting.currencies', compact('currencies'));
    }

    public function storeCurrency(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|size:3|unique:currencies,code',
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'decimal_places' => 'required|integer|min:0|max:4',
            'is_active' => 'sometimes|boolean',
            'is_default' => 'sometimes|boolean',
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $isDefault = $request->boolean('is_default');

            if ($isDefault) {
                Currency::query()->update(['is_default' => false]);
            }

            Currency::create([
                'code' => strtoupper($validated['code']),
                'name' => $validated['name'],
                'symbol' => $validated['symbol'],
                'decimal_places' => $validated['decimal_places'],
                'is_active' => $request->boolean('is_active'),
                'is_default' => $isDefault,
            ]);
        });

        return back()->with('success', 'تمت إضافة العملة بنجاح.');
    }

    public function updateCurrency(Request $request, $id): RedirectResponse
    {
        $currency = Currency::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'decimal_places' => 'required|integer|min:0|max:4',
            'is_active' => 'required|boolean',
            'is_default' => 'sometimes|boolean',
        ]);

        DB::transaction(function () use ($currency, $request, $validated): void {
            $isDefault = $request->boolean('is_default');

            if ($isDefault) {
                Currency::where('id', '!=', $currency->id)->update(['is_default' => false]);
            }

            $currency->update([
                'name' => $validated['name'],
                'symbol' => $validated['symbol'],
                'decimal_places' => $validated['decimal_places'],
                'is_active' => (bool) $validated['is_active'],
                'is_default' => $isDefault || $currency->is_default,
            ]);
        });

        return back()->with('success', 'تم تحديث بيانات العملة بنجاح.');
    }

    public function setDefaultCurrency($id): RedirectResponse
    {
        $currency = Currency::findOrFail($id);

        DB::transaction(function () use ($currency): void {
            Currency::query()->update(['is_default' => false]);
            $currency->update(['is_default' => true, 'is_active' => true]);
        });

        return back()->with('success', 'تم تعيين '.$currency->name.' كعملة أساسية للنظام.');
    }

    public function destroyCurrency($id): RedirectResponse
    {
        $currency = Currency::findOrFail($id);

        if ($currency->is_default) {
            return back()->withErrors(['msg' => 'لا يمكن حذف العملة الافتراضية للنظام.']);
        }

        $currency->delete();

        return back()->with('success', 'تم حذف العملة بنجاح.');
    }

    // إدارة الفترات والسنوات المالية والمحاسبية
    public function periods(): View
    {
        $periods = AccountingPeriod::with(['closedByUser', 'lockedByUser'])
            ->withCount('journalEntries')
            ->orderByDesc('start_date')
            ->paginate(15);

        return view('admin.accounting.periods', compact('periods'));
    }

    public function storePeriod(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period_code' => 'required|string|max:30|unique:accounting_periods,period_code',
            'name' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
        ]);

        AccountingPeriod::create([
            ...$validated,
            'status' => 'OPEN',
        ]);

        return back()->with('success', 'تم فتح الفترة المحاسبية بنجاح.');
    }

    public function updatePeriodStatus(Request $request, $id): RedirectResponse
    {
        $period = AccountingPeriod::findOrFail($id);
        $action = $request->input('action'); // 'close', 'lock', 'reopen'

        if ($action === 'close') {
            $period->update([
                'status' => 'CLOSED',
                'closed_by_user_id' => Auth::id(),
                'closed_at' => now(),
            ]);
            $msg = 'تم إقفال الفترة المحاسبية بنجاح.';
        } elseif ($action === 'lock') {
            $period->update([
                'status' => 'LOCKED',
                'locked_by_user_id' => Auth::id(),
                'locked_at' => now(),
            ]);
            $msg = 'تم قفل وتجميد الفترة المحاسبية نهائياً.';
        } elseif ($action === 'reopen') {
            $period->update([
                'status' => 'OPEN',
                'closed_by_user_id' => null,
                'closed_at' => null,
                'locked_by_user_id' => null,
                'locked_at' => null,
            ]);
            $msg = 'تمت إعادة فتح الفترة المحاسبية.';
        } else {
            return back()->withErrors(['msg' => 'إجراء غير معروف.']);
        }

        return back()->with('success', $msg);
    }

    public function destroyPeriod($id): RedirectResponse
    {
        $period = AccountingPeriod::findOrFail($id);

        if ($period->journalEntries()->exists()) {
            return back()->withErrors(['msg' => 'لا يمكن حذف هذه الفترة لوجود قيود يومية مسجلة ضمنها.']);
        }

        $period->delete();

        return back()->with('success', 'تم حذف الفترة المحاسبية بنجاح.');
    }

    // شجرة ودليل الحسابات العامة (أصول، خصوم، حقوق ملكية، إيرادات، مصروفات)
    public function accounts()
    {
        $accounts = Account::with('parent')->orderBy('account_code')->get();

        return view('admin.accounting.accounts', compact('accounts'));
    }

    public function storeAccount(Request $request)
    {
        $validated = $request->validate([
            'account_code' => 'required|string|max:30|unique:accounts,account_code',
            'account_name' => 'required|string|max:150',
            'account_type' => 'required|in:ASSET,LIABILITY,EQUITY,REVENUE,EXPENSE',
            'normal_balance' => 'required|in:DEBIT,CREDIT',
            'parent_account_id' => 'nullable|exists:accounts,id',
            'is_control_account' => 'sometimes|boolean',
            'is_postable' => 'sometimes|boolean',
            'description' => 'nullable|string',
        ]);

        Account::create([
            ...$validated,
            'is_control_account' => $request->boolean('is_control_account'),
            'is_postable' => $request->boolean('is_postable'),
        ]);

        return back()->with('success', 'تم إنشاء الحساب في شجرة الحسابات بنجاح.');
    }

    public function updateAccount(Request $request, $id)
    {
        $validated = $request->validate([
            'account_code' => 'required|string|max:30|unique:accounts,account_code,'.$id,
            'account_name' => 'required|string|max:150',
            'account_type' => 'required|in:ASSET,LIABILITY,EQUITY,REVENUE,EXPENSE',
            'normal_balance' => 'required|in:DEBIT,CREDIT',
            'parent_account_id' => 'nullable|exists:accounts,id',
            'is_control_account' => 'sometimes|boolean',
            'is_postable' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'description' => 'nullable|string',
        ]);

        $account = Account::findOrFail($id);
        $account->update([
            ...$validated,
            'is_control_account' => $request->boolean('is_control_account'),
            'is_postable' => $request->boolean('is_postable'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'تم تحديث الحساب بنجاح.');
    }

    public function destroyAccount($id)
    {
        $account = Account::findOrFail($id);
        if ($account->journalEntryLines()->count() > 0) {
            return back()->withErrors(['msg' => 'لا يمكن حذف هذا الحساب لوجود قيود يومية مسجلة عليه.']);
        }
        $account->delete();

        return back()->with('success', 'تم حذف الحساب بنجاح.');
    }

    // قيود اليومية المحاسبية وسطورها
    public function journal(Request $request): View
    {
        $query = JournalEntry::with(['journalEntryLines.account', 'createdByUser', 'postedByUser']);

        // فلترة بالبحث
        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('entry_number', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%");
            });
        }

        // فلترة بالحالة
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // فلترة بالفترة المحاسبية
        if ($request->filled('period_id')) {
            $query->where('accounting_period_id', $request->input('period_id'));
        }

        // فلترة بنطاق التاريخ
        if ($request->filled('from_date')) {
            $query->where('entry_date', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->where('entry_date', '<=', $request->input('to_date'));
        }

        $entries = $query->latest('entry_date')->latest('id')->paginate(15);
        $accounts = Account::where('is_postable', 1)->where('is_active', 1)->orderBy('account_code')->get();
        $periods = AccountingPeriod::where('status', 'OPEN')->get();

        return view('admin.accounting.journal', compact('entries', 'accounts', 'periods'));
    }

    // حذف قيد اليومية
    public function destroyJournalEntry($id): RedirectResponse
    {
        $entry = JournalEntry::with('journalEntryLines')->findOrFail($id);

        DB::transaction(function () use ($entry) {
            $entry->journalEntryLines()->delete();
            $entry->delete();
        });

        return back()->with('success', 'تم حذف قيد اليومية وسطوره بنجاح.');
    }

    public function storeJournalEntry(Request $request)
    {
        $request->validate([
            'entry_number' => 'required|string|max:50|unique:journal_entries,entry_number',
            'entry_date' => 'required|date',
            'accounting_period_id' => 'nullable|exists:accounting_periods,id,status,OPEN',
            'description' => 'required|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounts,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
        ]);

        if ($request->filled('accounting_period_id')) {
            $period = AccountingPeriod::findOrFail($request->integer('accounting_period_id'));
            $entryDate = Carbon::parse($request->input('entry_date'))->toDateString();

            if (
                $entryDate < Carbon::parse($period->start_date)->toDateString()
                || $entryDate > Carbon::parse($period->end_date)->toDateString()
            ) {
                return back()->withErrors(['accounting_period_id' => 'تاريخ القيد خارج الفترة المحاسبية المحددة.'])->withInput();
            }
        }

        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($request->lines as $line) {
            $debit = (float) ($line['debit'] ?? 0);
            $credit = (float) ($line['credit'] ?? 0);

            if (($debit > 0) === ($credit > 0)) {
                return back()->withErrors(['msg' => 'يجب أن يحتوي كل سطر على مبلغ مدين أو دائن فقط.'])->withInput();
            }

            $totalDebit += (float) ($line['debit'] ?? 0);
            $totalCredit += (float) ($line['credit'] ?? 0);
        }

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            return back()->withErrors(['msg' => "القيد غير متزن! إجمالي المدين ({$totalDebit}) لا يساوي إجمالي الدائن ({$totalCredit})"])->withInput();
        }

        DB::transaction(function () use ($request) {
            $entry = JournalEntry::create([
                'entry_number' => $request->entry_number,
                'entry_date' => $request->entry_date,
                'description' => $request->description,
                'accounting_period_id' => $request->accounting_period_id,
                'status' => 'POSTED',
                'created_by_user_id' => Auth::id(),
                'posted_by_user_id' => Auth::id(),
                'posted_at' => now(),
            ]);

            $lineNo = 1;
            foreach ($request->lines as $l) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'line_number' => $lineNo++,
                    'account_id' => $l['account_id'],
                    'debit_amount' => $l['debit'] ?? 0,
                    'credit_amount' => $l['credit'] ?? 0,
                    'description' => $l['description'] ?? $request->description,
                ]);
            }
        });

        return back()->with('success', 'تم ترحيل قيد اليومية بنجاح.');
    }

    // مستحقات وأرباح الحرفيين
    public function earnings(Request $request)
    {
        $query = CraftsmanEarning::with(['craftsmanUser', 'serviceRequest', 'currency']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $earnings = $query->latest('id')->paginate(15);

        return view('admin.accounting.earnings', compact('earnings'));
    }

    public function approveEarning($id)
    {
        $earning = CraftsmanEarning::findOrFail($id);
        $earning->status = 'APPROVED';
        $earning->reviewed_by_user_id = Auth::id();
        $earning->approved_at = now();
        $earning->save();

        return back()->with('success', 'تم اعتماد مستحقات الحرفي بنجاح.');
    }

    // المتحصلات النقدية المحصلة عبر الحرفيين
    public function collections(Request $request)
    {
        $query = CraftsmanCollection::with(['craftsmanUser', 'customerUser', 'serviceRequest', 'currency']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $collections = $query->latest('id')->paginate(15);

        return view('admin.accounting.collections', compact('collections'));
    }

    public function verifyCollection($id)
    {
        $col = CraftsmanCollection::findOrFail($id);
        $col->status = 'CONFIRMED';
        $col->verified_by_user_id = Auth::id();
        $col->verified_at = now();
        $col->save();

        return back()->with('success', 'تم التحقق من تحصيل الحرفي وتأكيده بنجاح.');
    }

    // سندات صرف ودفع مستحقات الحرفيين
    public function payouts(Request $request)
    {
        $query = CraftsmanPayout::with(['craftsmanUser', 'currency', 'paymentMethod', 'account']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payouts = $query->latest('id')->paginate(15);
        $paymentMethods = PaymentMethod::where('is_active', 1)->get();
        $accounts = Account::where('is_postable', 1)->where('account_type', 'ASSET')->get();
        $craftsmen = User::craftsmen()->get(['id', 'full_name', 'phone_encrypted']);
        $currencies = Currency::where('is_active', 1)->get();

        return view('admin.accounting.payouts', compact('payouts', 'paymentMethods', 'accounts', 'craftsmen', 'currencies'));
    }

    public function processPayout(Request $request, $id)
    {
        $request->validate([
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $payout = CraftsmanPayout::findOrFail($id);
        $payout->status = 'CONFIRMED';
        $payout->reference_number = $request->reference_number;
        $payout->notes = $request->notes;
        $payout->paid_at = now();
        $payout->processed_by_user_id = Auth::id();
        $payout->save();

        return back()->with('success', 'تم تسجيل تنفيذ وتأكيد صرف الدفعة للحرفي بنجاح.');
    }

    public function storePayoutDirect(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'craftsman_user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'account_id' => 'required|exists:accounts,id',
            'currency_id' => 'required|exists:currencies,id',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        CraftsmanPayout::create([
            'payout_number' => 'POUT-'.date('Ymd').'-'.rand(1000, 9999),
            'craftsman_user_id' => $validated['craftsman_user_id'],
            'amount' => $validated['amount'],
            'payment_method_id' => $validated['payment_method_id'],
            'account_id' => $validated['account_id'],
            'currency_id' => $validated['currency_id'],
            'reference_number' => $validated['reference_number'],
            'notes' => $validated['notes'],
            'status' => 'CONFIRMED',
            'paid_at' => now(),
            'processed_by_user_id' => Auth::id(),
        ]);

        return back()->with('success', 'تم إنشاء وتأكيد سند صرف الدفعة للحرفي بنجاح.');
    }

    // تسويات الحرفيين وحوالات السداد والتوريد النقدي
    public function settlements(Request $request): View
    {
        $settlementsQuery = CraftsmanSettlement::with([
            'earning.craftsmanUser',
            'earning.serviceRequest',
            'collection.customerUser',
            'reviewedByUser',
        ]);

        if ($request->filled('settlement_status')) {
            $settlementsQuery->where('status', $request->settlement_status);
        }

        $settlements = $settlementsQuery->latest('id')->paginate(15, ['*'], 'settlements_page');

        $remittancesQuery = CraftsmanRemittance::with([
            'craftsmanUser',
            'paymentMethod',
            'account',
            'currency',
            'receivedByUser',
        ]);

        if ($request->filled('remittance_status')) {
            $remittancesQuery->where('status', $request->remittance_status);
        }

        $remittances = $remittancesQuery->latest('id')->paginate(15, ['*'], 'remittances_page');

        $craftsmen = User::craftsmen()->get(['id', 'full_name', 'phone_encrypted']);
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        $accounts = Account::where('account_type', 'ASSET')->where('is_postable', true)->get();
        $currencies = Currency::where('is_active', true)->get();

        // إحصائيات مالية إجمالية للتسويات والتوريد
        $totalCollected = CraftsmanCollection::where('status', 'CONFIRMED')->sum('collected_amount');
        $totalEarningsApproved = CraftsmanEarning::where('status', 'APPROVED')->sum('craftsman_share_amount');
        $totalCompanyCommission = CraftsmanEarning::where('status', 'APPROVED')->sum('company_share_amount');
        $totalRemitted = CraftsmanRemittance::where('status', 'CONFIRMED')->sum('amount');

        return view('admin.accounting.settlements', compact(
            'settlements',
            'remittances',
            'craftsmen',
            'paymentMethods',
            'accounts',
            'currencies',
            'totalCollected',
            'totalEarningsApproved',
            'totalCompanyCommission',
            'totalRemitted'
        ));
    }

    public function approveSettlement($id): RedirectResponse
    {
        $settlement = CraftsmanSettlement::findOrFail($id);
        $settlement->status = 'REMITTED';
        $settlement->reviewed_by_user_id = Auth::id();
        $settlement->reviewed_at = now();
        $settlement->save();

        return back()->with('success', 'تم اعتماد ومطابقة تسوية الحرفي بنجاح.');
    }

    public function storeRemittance(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'craftsman_user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'account_id' => 'required|exists:accounts,id',
            'currency_id' => 'required|exists:currencies,id',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        CraftsmanRemittance::create([
            'remittance_number' => 'REM-'.date('Ymd').'-'.rand(1000, 9999),
            'craftsman_user_id' => $validated['craftsman_user_id'],
            'amount' => $validated['amount'],
            'payment_method_id' => $validated['payment_method_id'],
            'account_id' => $validated['account_id'],
            'currency_id' => $validated['currency_id'],
            'reference_number' => $validated['reference_number'],
            'notes' => $validated['notes'],
            'remitted_at' => now(),
            'received_by_user_id' => Auth::id(),
            'status' => 'CONFIRMED',
        ]);

        return back()->with('success', 'تم تسجيل وتأكيد استلام حوالة التوريد من الحرفي بنجاح.');
    }

    public function confirmRemittance($id): RedirectResponse
    {
        $remittance = CraftsmanRemittance::findOrFail($id);
        $remittance->status = 'CONFIRMED';
        $remittance->received_by_user_id = Auth::id();
        $remittance->save();

        return back()->with('success', 'تم تأكيد استلام الحوالة وإيداعها في الحساب المالي.');
    }

    // إنشاء وترحيل قيد يومية عكسي لتصحيح وتصفير القيد السابق
    public function reverseJournalEntry($id): RedirectResponse
    {
        $entry = JournalEntry::with('journalEntryLines')->findOrFail($id);

        if ($entry->status === 'REVERSED') {
            return back()->withErrors(['msg' => 'هذا القيد تم عكسه مسبقاً.']);
        }

        DB::transaction(function () use ($entry): void {
            $reverseEntry = JournalEntry::create([
                'entry_number' => 'REV-'.$entry->entry_number,
                'entry_date' => now()->toDateString(),
                'accounting_period_id' => $entry->accounting_period_id,
                'description' => 'قيد عكسي للقيد رقم #'.$entry->entry_number.' - '.$entry->description,
                'status' => 'POSTED',
                'created_by_user_id' => Auth::id(),
                'posted_by_user_id' => Auth::id(),
                'posted_at' => now(),
            ]);

            $lineNo = 1;
            foreach ($entry->journalEntryLines as $origLine) {
                JournalEntryLine::create([
                    'journal_entry_id' => $reverseEntry->id,
                    'line_number' => $lineNo++,
                    'account_id' => $origLine->account_id,
                    'debit_amount' => $origLine->credit_amount,
                    'credit_amount' => $origLine->debit_amount,
                    'description' => 'عكس السطر رقم '.$origLine->line_number.' ('.($origLine->description ?? '').')',
                ]);
            }

            $entry->update([
                'status' => 'REVERSED',
            ]);
        });

        return back()->with('success', 'تم إنشاء وترحيل القيد العكسي بنجاح لتصحيح المعاملة.');
    }

    // التقارير المالية وميزان المراجعة بالمجاميع والأرصدة ودفتر الأستاذ العام
    public function reports(Request $request): View
    {
        $periodId = $request->query('period_id');
        $accountId = $request->query('account_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $periods = AccountingPeriod::orderByDesc('start_date')->get();
        $accounts = Account::orderBy('account_code')->get();

        // 1. احتساب ميزان المراجعة بالمجاميع والأرصدة
        $accountsWithBalances = Account::with(['journalEntryLines' => function ($q) use ($periodId, $startDate, $endDate) {
            $q->whereHas('journalEntry', function ($jq) use ($periodId, $startDate, $endDate) {
                $jq->where('status', 'POSTED');
                if ($periodId) {
                    $jq->where('accounting_period_id', $periodId);
                }
                if ($startDate) {
                    $jq->where('entry_date', '>=', $startDate);
                }
                if ($endDate) {
                    $jq->where('entry_date', '<=', $endDate);
                }
            });
        }])->get()->map(function ($acc) {
            $totalDebit = $acc->journalEntryLines->sum('debit_amount');
            $totalCredit = $acc->journalEntryLines->sum('credit_amount');

            if ($acc->normal_balance === 'DEBIT') {
                $closingBalance = $totalDebit - $totalCredit;
                $closingDebit = $closingBalance > 0 ? $closingBalance : 0;
                $closingCredit = $closingBalance < 0 ? abs($closingBalance) : 0;
            } else {
                $closingBalance = $totalCredit - $totalDebit;
                $closingCredit = $closingBalance > 0 ? $closingBalance : 0;
                $closingDebit = $closingBalance < 0 ? abs($closingBalance) : 0;
            }

            $acc->total_debit = $totalDebit;
            $acc->total_credit = $totalCredit;
            $acc->closing_debit = $closingDebit;
            $acc->closing_credit = $closingCredit;
            $acc->net_balance = $closingBalance;

            return $acc;
        });

        $totalTrialDebit = $accountsWithBalances->sum('total_debit');
        $totalTrialCredit = $accountsWithBalances->sum('total_credit');
        $totalClosingDebit = $accountsWithBalances->sum('closing_debit');
        $totalClosingCredit = $accountsWithBalances->sum('closing_credit');
        $isTrialBalanced = round($totalTrialDebit, 2) === round($totalTrialCredit, 2);

        // 2. كشف حساب دفتر الأستاذ العام للحساب المالي المختار
        $selectedAccount = null;
        $ledgerLines = collect();
        if ($accountId) {
            $selectedAccount = Account::find($accountId);
            if ($selectedAccount) {
                $query = JournalEntryLine::with(['journalEntry.createdByUser', 'account'])
                    ->where('account_id', $accountId)
                    ->whereHas('journalEntry', function ($q) use ($periodId, $startDate, $endDate) {
                        $q->where('status', 'POSTED');
                        if ($periodId) {
                            $q->where('accounting_period_id', $periodId);
                        }
                        if ($startDate) {
                            $q->where('entry_date', '>=', $startDate);
                        }
                        if ($endDate) {
                            $q->where('entry_date', '<=', $endDate);
                        }
                    });

                $ledgerLines = $query->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                    ->orderBy('journal_entries.entry_date')
                    ->orderBy('journal_entries.id')
                    ->select('journal_entry_lines.*')
                    ->get();

                // احتساب الرصيد التراكمي للحساب سطراً بسطر
                $running = 0;
                foreach ($ledgerLines as $line) {
                    if ($selectedAccount->normal_balance === 'DEBIT') {
                        $running += ($line->debit_amount - $line->credit_amount);
                    } else {
                        $running += ($line->credit_amount - $line->debit_amount);
                    }
                    $line->running_balance = $running;
                }
            }
        }

        return view('admin.accounting.reports', compact(
            'periods',
            'accounts',
            'accountsWithBalances',
            'totalTrialDebit',
            'totalTrialCredit',
            'totalClosingDebit',
            'totalClosingCredit',
            'isTrialBalanced',
            'selectedAccount',
            'ledgerLines'
        ));
    }
}
