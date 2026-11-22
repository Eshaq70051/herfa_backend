@extends('admin.layout')

@section('title', 'تفاصيل الطلب #' . $request->request_number)
@section('page_title', 'طلب خدمة رقم: #' . $request->request_number)

@section('content')
<div class="space-y-8">
    
    <!-- Top Action Bar -->
    <div class="flex flex-wrap items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <div class="flex items-center gap-3">
                <h3 class="text-xl font-extrabold text-slate-800">الطلب #{{ $request->request_number }}</h3>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                    {{ $request->requestStatus->name ?? 'غير محدد' }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">تاريخ الإنشاء: {{ $request->created_at ? $request->created_at->format('Y-m-d H:i') : '-' }}</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.requests.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-right"></i>
                <span>العودة للقائمة</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left 2 Cols: Main Info -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Request Core Details -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-6">
                <h4 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-3">بيانات الطلب والخدمة</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60">
                        <span class="text-slate-400 block mb-1">الخدمة المطلوبة:</span>
                        <span class="font-bold text-slate-800 text-sm">{{ $request->service->name ?? '-' }}</span>
                        <span class="text-brand-600 block text-[11px] mt-1">{{ $request->service->category->name ?? '' }}</span>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60">
                        <span class="text-slate-400 block mb-1">نوع التسعير:</span>
                        <span class="font-bold text-slate-800">{{ $request->service->pricing_type ?? '-' }}</span>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60">
                        <span class="text-slate-400 block mb-1">الموعد المحدد:</span>
                        <span class="font-bold text-slate-800 font-mono">{{ $request->preferred_date ?? 'غير محدد' }} {{ $request->preferred_time ?? '' }}</span>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60">
                        <span class="text-slate-400 block mb-1">العنوان والموقع:</span>
                        <span class="font-bold text-slate-800">{{ $request->address_details ?? ($request->userAddress->address_details ?? 'غير محدد') }}</span>
                    </div>
                </div>

                <div>
                    <span class="text-slate-400 text-xs block mb-1.5">وصف العميل للمشكلة / الطلب:</span>
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60 text-xs text-slate-700 leading-relaxed font-medium">
                        {{ $request->description ?? 'لا يوجد وصف مدخل' }}
                    </div>
                </div>
            </div>

            <!-- Assignments (Craftsmen) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h4 class="font-bold text-slate-800 text-sm">الحرفيين المسندين للطلب (request_assignments)</h4>
                </div>

                <div class="space-y-3">
                    @forelse($request->requestAssignments as $assign)
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold">
                                    <i class="fa-solid fa-wrench"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800 text-xs">{{ $assign->craftsmanProfile->user->full_name ?? 'حرفي' }}</p>
                                    <p class="text-[11px] text-slate-400">تاريخ الإسناد: {{ $assign->assigned_at }}</p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $assign->assignment_status }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-4">لم يتم إسناد أي حرفي لهذا الطلب حتى الآن.</p>
                    @endforelse
                </div>

                <!-- Assign Craftsman Form -->
                <form action="{{ route('admin.requests.assign', $request->id) }}" method="POST" class="pt-4 border-t border-slate-100">
                    @csrf
                    <label class="block text-xs font-semibold text-slate-700 mb-2">إسناد الطلب لحرفي معتمد:</label>
                    <div class="flex gap-2">
                        <select name="craftsman_id" required class="flex-1 px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                            <option value="">اختر الحرفي المعتمد...</option>
                            @foreach($availableCraftsmen as $craft)
                                <option value="{{ $craft->id }}">{{ $craft->user->full_name ?? 'حرفي' }} ({{ $craft->user->phone_encrypted ?? '' }})</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs transition">
                            إسناد الآن
                        </button>
                    </div>
                </form>
            </div>

            <!-- Fees & Financials -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-6">
                <h4 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-3">رسوم ومصروفات الطلب (request_fees)</h4>
                
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-50 text-slate-400">
                        <tr>
                            <th class="py-2.5 px-3">المبلغ</th>
                            <th class="py-2.5 px-3">العملة</th>
                            <th class="py-2.5 px-3">حالة الرسوم</th>
                            <th class="py-2.5 px-3">تاريخ الإضافة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($request->requestFees as $fee)
                            <tr>
                                <td class="py-2.5 px-3 font-bold font-mono text-slate-800">{{ number_format($fee->fee_amount, 2) }}</td>
                                <td class="py-2.5 px-3 font-semibold">{{ $fee->currency->code ?? '-' }}</td>
                                <td class="py-2.5 px-3 font-bold text-brand-700">{{ $fee->fee_status }}</td>
                                <td class="py-2.5 px-3 text-slate-400 font-mono">{{ $fee->created_at }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-slate-400">لا توجد رسوم مسجلة</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <form action="{{ route('admin.requests.fee', $request->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-3 pt-4 border-t border-slate-100">
                    @csrf
                    <div>
                        <input type="number" step="0.01" name="fee_amount" placeholder="مبلغ الرسوم" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    </div>
                    <div>
                        <select name="currency_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            @foreach($currencies as $curr)
                                <option value="{{ $curr->id }}">{{ $curr->name }} ({{ $curr->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <select name="fee_status" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <option value="PENDING">PENDING</option>
                            <option value="PAID">PAID</option>
                            <option value="CANCELLED">CANCELLED</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl text-xs transition">
                            إضافة رسوم
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- Right 1 Col: Customer info & Status Update & History -->
        <div class="space-y-8">
            
            <!-- Update Status Box -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
                <h4 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-3">تحديث حالة الطلب</h4>
                <form action="{{ route('admin.requests.status', $request->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">الحالة الجديدة</label>
                        <select name="new_status_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                            @foreach($statuses as $st)
                                <option value="{{ $st->id }}" {{ $request->status_id == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">ملاحظات التغيير</label>
                        <textarea name="notes" rows="2" placeholder="أدخل سبب أو ملاحظة التغيير..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500"></textarea>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs transition shadow-md shadow-brand-500/20">
                        حفظ وتوثيق الحركة
                    </button>
                </form>
            </div>

            <!-- Customer Card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
                <h4 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-3">بيانات العميل</h4>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-lg">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div>
                        <h5 class="font-bold text-slate-800 text-sm">{{ $request->user->full_name ?? 'غير محدد' }}</h5>
                        <p class="text-xs text-slate-400 font-mono">{{ $request->user->phone_encrypted ?? '-' }}</p>
                    </div>
                </div>
                <div class="text-xs text-slate-500 space-y-1 pt-2 border-t border-slate-100">
                    <p>البريد: <span class="font-mono">{{ $request->user->email_encrypted ?? 'غير مسجل' }}</span></p>
                    <p>حالة الحساب: <span class="font-bold text-emerald-600">{{ $request->user->account_status ?? 'active' }}</span></p>
                </div>
            </div>

            <!-- Status History Timeline -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
                <h4 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-3">سجل حركات الطلب (Status History)</h4>
                <div class="space-y-4 relative pr-4 border-r-2 border-slate-200 text-xs">
                    @forelse($request->requestStatusHistories as $hist)
                        <div class="relative">
                            <span class="absolute -right-[23px] top-1 w-3 h-3 rounded-full bg-brand-500 ring-4 ring-white"></span>
                            <p class="font-bold text-slate-800">{{ $hist->requestStatus->name ?? 'حالة' }}</p>
                            <p class="text-[11px] text-slate-400 font-mono">{{ $hist->created_at }}</p>
                            @if($hist->notes)
                                <p class="text-[11px] text-slate-600 mt-1 bg-slate-50 p-2 rounded-lg border border-slate-100">{{ $hist->notes }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-slate-400">لا يوجد سجل حركات سابق</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
