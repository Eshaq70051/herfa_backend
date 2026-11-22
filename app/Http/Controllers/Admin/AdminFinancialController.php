<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Currency;
use App\Models\Notification;
use App\Models\PaymentMethod;
use App\Models\Review;
use App\Models\ServiceRequest;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class AdminFinancialController extends Controller
{
    // ==================== العملاء (Customers / Clients CRUD) ====================
    public function clients(Request $request)
    {
        $query = User::clientAccounts()->withCount('serviceRequests');

        if ($request->has('search') && ! empty($request->search)) {
            $s = $request->search;
            $sHash = hash('sha256', $s);
            $query->where(function ($q) use ($s, $sHash) {
                $q->where('full_name', 'like', "%{$s}%")
                    ->orWhere('phone_encrypted', 'like', "%{$s}%")
                    ->orWhere('phone_hash', $sHash)
                    ->orWhere('email_encrypted', 'like', "%{$s}%");
            });
        }

        $clients = $query->latest()->paginate(15);

        return view('admin.clients.index', compact('clients'));
    }

    public function storeClient(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:150',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:150',
            'password' => 'required|min:6',
            'profile_image' => 'nullable|image|max:5120',
        ]);

        $phoneHash = hash('sha256', $validated['phone']);
        if (User::where('phone_hash', $phoneHash)->exists()) {
            return back()->withErrors(['phone' => 'رقم الهاتف مسجل مسبقاً لمستخدم آخر'])->withInput();
        }

        $profileImagePath = null;
        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profiles', 'public');
            $profileImagePath = '/storage/'.$path;
        }

        $user = User::create([
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'password' => $validated['password'],
            'role' => 'customer',
            'account_status' => 'active',
            'profile_image' => $profileImagePath,
            'phone_verified_at' => now(),
        ]);

        return back()->with('success', 'تمت إضافة العميل الجديد بنجاح');
    }

    public function updateClient(Request $request, $id)
    {
        $client = User::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:150',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:150',
            'account_status' => 'required|in:active,inactive,suspended',
            'profile_image' => 'nullable|image|max:5120',
        ]);

        $phoneHash = hash('sha256', $validated['phone']);
        if (User::where('phone_hash', $phoneHash)->where('id', '!=', $client->id)->exists()) {
            return back()->withErrors(['phone' => 'رقم الهاتف مسجل مسبقاً لمستخدم آخر'])->withInput();
        }

        $data = [
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'account_status' => $validated['account_status'],
        ];

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profiles', 'public');
            $data['profile_image'] = '/storage/'.$path;
        }

        $client->update($data);

        return back()->with('success', 'تم تحديث بيانات العميل بنجاح');
    }

    public function showClient($id)
    {
        $client = User::with([
            'addresses.district.governorate',
            'serviceRequests' => function ($q) {
                $q->with([
                    'service.category',
                    'status',
                    'assignments.craftsman',
                ])->latest();
            },
            'reviewsGiven.craftsman',
            'transactions' => function ($q) {
                $q->with(['paymentMethod', 'request.service'])->latest();
            },
            'supportTickets' => function ($q) {
                $q->latest();
            },
        ])
            ->withCount([
                'serviceRequests',
                'addresses',
                'reviewsGiven',
                'transactions',
            ])
            ->findOrFail($id);

        $totalOrders = $client->service_requests_count;
        $completedOrders = $client->serviceRequests->where('status.code', 'completed')->count();
        $inProgressOrders = $client->serviceRequests->filter(function ($req) {
            return in_array($req->status?->code, ['in_progress', 'assigned', 'accepted', 'on_the_way', 'arrived']);
        })->count();
        $cancelledOrders = $client->serviceRequests->where('status.code', 'cancelled')->count();
        $totalSpent = $client->serviceRequests->whereIn('status.code', ['completed', 'paid'])->sum('total_amount');

        return view('admin.clients.show', compact(
            'client',
            'totalOrders',
            'completedOrders',
            'inProgressOrders',
            'cancelledOrders',
            'totalSpent'
        ));
    }

    public function toggleBlockClient($id)
    {
        $client = User::findOrFail($id);
        $client->account_status = ($client->account_status === 'active') ? 'suspended' : 'active';
        $client->save();

        $msg = ($client->account_status === 'active') ? 'تم تفعيل حساب العميل بنجاح' : 'تم تعطيل/حظر حساب العميل';

        return back()->with('success', $msg);
    }

    public function destroyClient($id)
    {
        $client = User::findOrFail($id);
        $name = $client->full_name;
        $client->delete();

        return redirect()->route('admin.clients.index')->with('success', "تم حذف العميل ($name) وكافة سجلاته");
    }

    // ==================== المالية ووسائل الدفع (Financials & Payment Methods) ====================
    public function payouts(Request $request)
    {
        $transactions = Transaction::with(['user', 'request.service', 'paymentMethod'])
            ->latest()
            ->paginate(15);

        $paymentMethods = PaymentMethod::all();
        $serviceRequests = ServiceRequest::with(['customer', 'service'])->latest()->take(50)->get();
        $users = User::all();

        return view('admin.financials.payouts', compact('transactions', 'paymentMethods', 'serviceRequests', 'users'));
    }

    public function storePaymentMethod(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|unique:payment_methods,code|max:50',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('payment_methods', 'public');
            $imagePath = '/storage/'.$path;
        }

        PaymentMethod::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
            'is_active' => true,
        ]);

        return back()->with('success', 'تمت إضافة وسيلة الدفع الجديدة بنجاح');
    }

    public function updatePaymentMethod(Request $request, $id)
    {
        $method = PaymentMethod::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? $method->description,
            'is_active' => $request->has('is_active') ? true : false,
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('payment_methods', 'public');
            $data['image'] = '/storage/'.$path;
        }

        $method->update($data);

        return back()->with('success', 'تم تعديل وسيلة الدفع بنجاح');
    }

    public function destroyPaymentMethod($id)
    {
        $method = PaymentMethod::findOrFail($id);
        $name = $method->name;
        $method->delete();

        return back()->with('success', "تم حذف وسيلة الدفع ($name)");
    }

    public function storeTransaction(Request $request)
    {
        $validated = $request->validate([
            'request_id' => 'required|exists:service_requests,id',
            'user_id' => 'required|exists:users,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount' => 'required|numeric|min:1',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
        ]);

        $defaultCurrency = Currency::where('is_default', true)->first() ?? Currency::first();

        Transaction::create([
            'transaction_number' => 'TRX-'.strtoupper(uniqid()),
            'request_id' => $validated['request_id'],
            'user_id' => $validated['user_id'],
            'payment_method_id' => $validated['payment_method_id'],
            'amount' => $validated['amount'],
            'currency_id' => $defaultCurrency?->id ?? 1,
            'payment_status' => $validated['payment_status'],
            'paid_at' => $validated['payment_status'] === 'paid' ? now() : null,
        ]);

        return back()->with('success', 'تم تسجيل المعاملة المالية بنجاح');
    }

    public function processPayout(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        $validated = $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,refunded',
        ]);

        $transaction->payment_status = $validated['payment_status'];
        if ($validated['payment_status'] === 'paid' && ! $transaction->paid_at) {
            $transaction->paid_at = now();
        }

        $transaction->save();

        return back()->with('success', 'تم تحديث حالة المعاملة المالية بنجاح');
    }

    public function destroyTransaction($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();

        return back()->with('success', 'تم حذف سجل المعاملة المالية بنجاح');
    }

    // ==================== التقييمات (Reviews CRUD) ====================
    public function reviews()
    {
        $reviews = Review::with(['customer', 'craftsman', 'request.service'])
            ->latest()
            ->paginate(15);

        return view('admin.reviews.index', compact('reviews'));
    }

    public function updateReviewStatus(Request $request, $id)
    {
        $review = Review::findOrFail($id);
        $validated = $request->validate([
            'review_status' => 'required|in:visible,hidden,reported',
        ]);

        $review->update($validated);

        return back()->with('success', 'تم تحديث حالة ظهور التقييم بنجاح');
    }

    public function destroyReview($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        return back()->with('success', 'تم حذف التقييم بنجاح');
    }

    // ==================== الدعم الفني والشكاوى (Support Tickets CRUD) ====================
    public function tickets()
    {
        $tickets = SupportTicket::with(['user', 'assignedAdmin', 'messages'])
            ->latest()
            ->paginate(15);

        $users = User::all();

        return view('admin.tickets.index', compact('tickets', 'users'));
    }

    public function storeTicket(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:150',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high',
        ]);

        $ticket = SupportTicket::create([
            'ticket_number' => 'TCK-'.date('Ymd').'-'.rand(100, 999),
            'user_id' => $validated['user_id'],
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'ticket_status' => 'open',
            'assigned_admin_id' => auth()->id(),
        ]);

        return back()->with('success', 'تم فتح تذكرة الدعم بنجاح برقم: '.$ticket->ticket_number);
    }

    public function showTicket($id)
    {
        $ticket = SupportTicket::with(['user', 'assignedAdmin', 'messages.sender'])
            ->findOrFail($id);

        return view('admin.tickets.show', compact('ticket'));
    }

    public function replyTicket(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $validated = $request->validate([
            'message' => 'required|string',
            'ticket_status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => auth()->id(),
            'message' => $validated['message'],
        ]);

        $ticket->update([
            'ticket_status' => $validated['ticket_status'],
            'assigned_admin_id' => auth()->id(),
            'last_message_at' => now(),
        ]);

        try {
            Notification::create([
                'user_id' => $ticket->user_id,
                'title' => 'رد جديد على تذكرة الدعم: '.$ticket->ticket_number,
                'body' => $validated['message'],
                'related_id' => $ticket->id,
                'is_read' => false,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
        }

        return back()->with('success', 'تم إرسال الرد وتحديث حالة التذكرة بنجاح');
    }

    public function destroyTicket($id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $ticket->delete();

        return redirect()->route('admin.tickets.index')->with('success', 'تم حذف تذكرة الدعم الفني بنجاح');
    }

    // ==================== إعدادات المنظومة (App Settings CRUD) ====================
    public function settings()
    {
        $settingsList = AppSetting::all();
        $settings = $settingsList->pluck('setting_value', 'setting_key');

        return view('admin.settings.index', compact('settings', 'settingsList'));
    }

    public function updateSettings(Request $request)
    {
        $fileKeys = [
            'site_logo',
            'site_logo_dark',
            'site_favicon',
            'login_background',
            'hero_background',
            'app_splash_background',
        ];

        foreach ($fileKeys as $fileKey) {
            if ($request->hasFile($fileKey)) {
                $path = $request->file($fileKey)->store('settings', 'public');
                AppSetting::updateOrCreate(
                    ['setting_key' => $fileKey],
                    [
                        'setting_value' => '/storage/'.$path,
                        'setting_group' => 'branding',
                        'description' => 'ملف صورة أو وسائط للمنصة',
                        'updated_by' => auth()->id(),
                    ]
                );
            }
        }

        $input = $request->except(array_merge(['_token'], $fileKeys));

        if ($request->has('is_settings_form') && ! $request->has('maintenance_mode')) {
            $input['maintenance_mode'] = '0';
        }

        foreach ($input as $key => $val) {
            $group = 'general';
            if (str_contains($key, 'color') || in_array($key, ['primary_color', 'secondary_color', 'accent_color', 'sidebar_color', 'bg_color'])) {
                $group = 'appearance';
            } elseif (str_contains($key, 'social_') || in_array($key, ['support_phone', 'support_whatsapp', 'support_email'])) {
                $group = 'social';
            } elseif (in_array($key, ['platform_commission_percent', 'vat_percent', 'emergency_fee', 'currency_symbol', 'default_warranty_days', 'min_payout_amount'])) {
                $group = 'financial';
            } elseif (str_contains($key, 'store_url') || in_array($key, ['maintenance_mode', 'maintenance_message'])) {
                $group = 'system';
            }

            AppSetting::updateOrCreate(
                ['setting_key' => $key],
                [
                    'setting_value' => (string) $val,
                    'setting_group' => $group,
                    'updated_by' => auth()->id(),
                ]
            );
        }

        return back()->with('success', 'تم حفظ وتحديث إعدادات المنظومة والهوية البصرية بنجاح');
    }

    public function storeSetting(Request $request)
    {
        $validated = $request->validate([
            'setting_key' => 'required|string|unique:app_settings,setting_key|max:100',
            'setting_value' => 'required|string',
            'setting_group' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        AppSetting::create([
            'setting_key' => $validated['setting_key'],
            'setting_value' => $validated['setting_value'],
            'setting_group' => $validated['setting_group'],
            'description' => $validated['description'] ?? null,
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', 'تمت إضافة الإعداد الجديد بنجاح');
    }

    public function destroySetting($id)
    {
        $setting = AppSetting::findOrFail($id);
        $setting->delete();

        return back()->with('success', 'تم حذف الإعداد بنجاح');
    }

    // ==================== عمليات مدمجة لتوافق الروابط ====================
    public function disputes(Request $request)
    {
        return redirect()->route('admin.tickets.index')->with('info', 'تم دمج النزاعات ضمن تذاكر الدعم الفني والشكاوى.');
    }

    public function resolveDispute(Request $request, $id)
    {
        return redirect()->route('admin.tickets.index');
    }

    public function payoutRequests(Request $request)
    {
        return redirect()->route('admin.accounting.payouts')->with('info', 'تم نقل طلبات السحب إلى الإدارة المالية ومستحقات الحرفيين.');
    }

    public function approvePayout(Request $request, $id)
    {
        return redirect()->route('admin.accounting.payouts');
    }

    public function rejectPayout(Request $request, $id)
    {
        return redirect()->route('admin.accounting.payouts');
    }

    public function coupons(Request $request)
    {
        return redirect()->route('admin.advertisements.index')->with('info', 'تم استبدال الكوبونات بنظام العروض والخصومات التسويقية.');
    }

    public function storeCoupon(Request $request)
    {
        return redirect()->route('admin.advertisements.index');
    }

    public function updateCoupon(Request $request, $id)
    {
        return redirect()->route('admin.advertisements.index');
    }

    public function toggleCoupon($id)
    {
        return redirect()->route('admin.advertisements.index');
    }

    public function destroyCoupon($id)
    {
        return redirect()->route('admin.advertisements.index');
    }
}
