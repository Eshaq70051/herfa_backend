@extends('admin.layout')

@section('title', 'تفاصيل الطلب #' . $order->clean_number)
@section('page_title', 'تفاصيل طلب الصيانة #' . $order->clean_number)

@section('content')
@php
    $receiptImg = $order->receipt_image ?? ($order->transactions->first()?->receipt_image ?? null);
    if (!$receiptImg) {
        $receiptAtt = $order->attachments->first(fn($a) => in_array(strtolower($a->file_type ?? ''), ['receipt', 'sund', 'transfer']));
        $receiptImg = $receiptAtt?->file_path;
    }
    $transferRef = $order->transfer_reference ?? ($order->transactions->first()?->provider_transaction_id ?? null);
    $activeAssignment = $order->assignments->first();
    $craftsmanUser = $activeAssignment?->craftsman ?? ($order->craftsman_id ? \App\Models\User::find($order->craftsman_id) : null);

    $isPaid = $order->payment_status === 'paid';
    $isPendingVerification = $order->payment_status === 'pending_verification';
    $isUnpaid = !$isPaid && !$isPendingVerification;
    
    $isBankOrWallet = in_array($order->payment_method, ['kuraimi', 'jawwali', 'jeeb', 'onecash', 'wallet']);
@endphp

<div class="space-y-6" x-data="{ 
    editModalOpen: false, 
    receiptModalOpen: false, 
    confirmPaymentModalOpen: false, 
    updatePaymentModalOpen: false,
    selectedImage: '{{ $receiptImg ? asset($receiptImg) : '' }}'
}">

    <!-- Top Action Nav & Quick Header Bar -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <!-- Order Number & Creation Date -->
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.orders.index') }}" class="p-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 transition" title="العودة لقائمة الطلبات">
                <i class="fa-solid fa-arrow-right text-sm"></i>
            </a>

            <div>
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-black font-mono text-emerald-700 bg-emerald-50 px-3 py-1 rounded-xl border border-emerald-200 shadow-sm">
                        #{{ $order->clean_number }}
                    </span>
                    <span class="text-xs text-gray-400 font-mono hidden sm:inline" title="الرمز البرمجي الكامل">
                        ({{ $order->request_number }})
                    </span>
                    <button onclick="navigator.clipboard.writeText('{{ $order->clean_number }}'); alert('تم نسخ رقم الطلب: {{ $order->clean_number }}');" class="text-gray-400 hover:text-emerald-600 transition text-xs p-1" title="نسخ رقم الطلب">
                        <i class="fa-regular fa-copy"></i>
                    </button>
                </div>
                <div class="text-xs text-gray-500 mt-1 flex items-center gap-2 flex-wrap">
                    <span class="flex items-center gap-1 font-medium">
                        <i class="fa-regular fa-calendar-plus text-emerald-600"></i>
                        <span>تاريخ وتوقيت الطلب:</span>
                        <strong class="font-mono text-gray-800">{{ $order->created_at ? $order->created_at->format('Y-m-d H:i') : '-' }}</strong>
                    </span>
                    <span class="text-gray-300">|</span>
                    <span class="text-gray-500">
                        منذ {{ $order->created_at ? $order->created_at->diffForHumans() : '-' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Status Badges & Quick Action Buttons -->
        <div class="flex items-center gap-2.5 flex-wrap">
            <!-- Payment Badge -->
            @if($isPaid)
                <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                    <span>مسدد ومؤكد</span>
                </span>
            @elseif($isPendingVerification)
                <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1.5 animate-pulse">
                    <i class="fa-solid fa-clock text-amber-600"></i>
                    <span>بانتظار مراجعة وتأكيد السند</span>
                </span>
            @else
                <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-exclamation text-gray-400"></i>
                    <span>غير مدفوع</span>
                </span>
            @endif

            <!-- Print Order / Invoice Voucher Button -->
            <button type="button" onclick="window.print()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-print text-primary-600"></i>
                <span>طباعة أمر العمل / الفاتورة</span>
            </button>

            <!-- Edit Button -->
            <button @click="editModalOpen = true" class="px-3.5 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>تعديل الطلب</span>
            </button>

            <!-- Delete Form -->
            <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا الطلب نهائياً؟');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold transition flex items-center gap-1.5">
                    <i class="fa-solid fa-trash"></i>
                    <span>حذف</span>
                </button>
            </form>
        </div>

    </div>

    <!-- Main Order Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Service Details, Location & Maps, Payment & Receipt, Timeline -->
        <div class="lg:col-span-2 space-y-6">

            <!-- 1. Primary Service & Malfunction Info -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-5">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200">
                                قسم: {{ $order->service->category->name ?? 'صيانة عامة' }}
                            </span>
                            @if($order->is_urgent)
                                <span class="text-xs font-bold px-2 py-0.5 rounded-lg bg-red-50 text-red-700 border border-red-200 flex items-center gap-1">
                                    <i class="fa-solid fa-bolt text-red-600"></i>
                                    <span>طلب طارئ / عاجل</span>
                                </span>
                            @endif
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mt-2">{{ $order->service->name ?? 'خدمة صيانة منزلية' }}</h3>
                    </div>
                    <div class="text-left bg-gray-50 px-4 py-2.5 rounded-xl border border-gray-100">
                        <span class="text-[11px] text-gray-400 block font-medium">إجمالي التكلفة</span>
                        <span class="text-2xl font-mono font-black text-gray-900">{{ number_format($order->total_amount ?? $order->service->base_price ?? 80, 2) }} ر.ي</span>
                        <span class="text-[11px] text-amber-600 font-semibold block mt-0.5">ضمان {{ $order->service->warranty_days ?? 30 }} يوم</span>
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                        <i class="fa-solid fa-clipboard-question text-emerald-600"></i>
                        <span>وصف العطل / ملاحظات العميل:</span>
                    </h4>
                    <p class="text-sm text-gray-800 bg-gray-50 p-4 rounded-xl leading-relaxed border border-gray-100 font-medium">
                        {{ $order->description ?: 'لا يوجد وصف إضافي مكتوب من العميل' }}
                    </p>
                </div>

                <!-- Preferred Timing & OTP -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-1">
                    <div class="bg-gray-50/80 p-3 rounded-xl border border-gray-100">
                        <span class="text-gray-400 block mb-1">تاريخ وتوقيت الزيارة المفضل من العميل:</span>
                        <span class="font-bold text-gray-800 font-mono text-sm flex items-center gap-1.5">
                            <i class="fa-regular fa-clock text-emerald-600"></i>
                            {{ $order->preferred_date ? \Carbon\Carbon::parse($order->preferred_date)->format('Y-m-d') : ($order->created_at ? $order->created_at->format('Y-m-d') : '-') }}
                            @if($order->preferred_time)
                                (الساعة: {{ $order->preferred_time }})
                            @endif
                        </span>
                    </div>

                    <div class="bg-gray-50/80 p-3 rounded-xl border border-gray-100">
                        <span class="text-gray-400 block mb-1">رمز تأكيد إنجاز الخدمة (OTP):</span>
                        <span class="font-mono font-bold text-emerald-700 text-sm tracking-widest">
                            {{ $order->completion_otp ?: 'غير محدد' }}
                        </span>
                    </div>
                </div>

                <!-- Order Media & Attachments -->
                @php
                    $voiceUrl = $order->voice_note_url ?? ($order->voice_note ? asset($order->voice_note) : null);
                @endphp
                @if($order->image || $order->completion_image || $voiceUrl || $order->attachments->count() > 0)
                <div class="pt-4 border-t border-gray-100 space-y-3">
                    <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-images text-emerald-600"></i>
                        <span>صور ومرفقات المشكلة</span>
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @if($order->image)
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-200/70 space-y-2">
                            <span class="text-[11px] font-bold text-gray-700 flex items-center gap-1">
                                <i class="fa-solid fa-camera text-blue-500"></i>
                                صورة المشكلة / العطل المرفقة
                            </span>
                            <a href="{{ asset($order->image) }}" target="_blank" class="block aspect-video rounded-lg overflow-hidden border border-gray-200 bg-white hover:opacity-90 transition">
                                <img src="{{ asset($order->image) }}" alt="صورة المشكلة" class="w-full h-full object-cover">
                            </a>
                        </div>
                        @endif

                        @if($order->completion_image)
                        <div class="p-3 bg-emerald-50/50 rounded-xl border border-emerald-200/70 space-y-2">
                            <span class="text-[11px] font-bold text-emerald-800 flex items-center gap-1">
                                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                صورة إنجاز العمل (الفني)
                            </span>
                            <a href="{{ asset($order->completion_image) }}" target="_blank" class="block aspect-video rounded-lg overflow-hidden border border-emerald-200 bg-white hover:opacity-90 transition">
                                <img src="{{ asset($order->completion_image) }}" alt="صورة إنجاز العمل" class="w-full h-full object-cover">
                            </a>
                        </div>
                        @endif
                    </div>

                    @if($voiceUrl)
                    <div class="p-3 bg-amber-50/60 rounded-xl border border-amber-200/70 space-y-2">
                        <span class="text-[11px] font-bold text-amber-800 flex items-center gap-1">
                            <i class="fa-solid fa-microphone text-amber-600"></i>
                            التسجيل الصوتي لوصف العطل
                        </span>
                        <audio controls class="w-full h-9">
                            <source src="{{ $voiceUrl }}" type="audio/mp4">
                            <source src="{{ $voiceUrl }}" type="audio/m4a">
                            <source src="{{ $voiceUrl }}" type="audio/aac">
                            المتصفح لا يدعم مشغل الصوتيات.
                        </audio>
                    </div>
                    @endif
                </div>
                @endif
            </div>

            <!-- 2. Customer Location & Interactive Map Section -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-gray-100 gap-3">
                    <div>
                        <h4 class="font-bold text-gray-900 text-base flex items-center gap-2">
                            <i class="fa-solid fa-location-dot text-red-500 text-lg"></i>
                            <span>موقع العميل وخريطة العنوان بدقة</span>
                        </h4>
                        <p class="text-xs text-gray-400 mt-0.5">يمكنك الاطلاع على الموقع الجغرافي للعميل والضغط لفتحه مباشرة في خرائط Google</p>
                    </div>

                    <a href="{{ $order->map_url }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/20 transition flex-shrink-0">
                        <i class="fa-solid fa-map-location-dot"></i>
                        <span>عرض الموقع على Google Maps ↗</span>
                    </a>
                </div>

                <!-- Address Details & Coordinates -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-100 space-y-1">
                        <span class="text-gray-400 block font-medium">العنوان والوصف المسجل:</span>
                        <span class="font-bold text-gray-900 text-sm block">{{ $order->address_details ?: 'صنعاء - العنوان الافتراضي' }}</span>
                    </div>

                    <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-100 space-y-1">
                        <span class="text-gray-400 block font-medium">الإحداثيات الجغرافية (Coordinates):</span>
                        @if($order->latitude && $order->longitude)
                            <div class="font-mono font-bold text-blue-700 text-sm flex items-center justify-between">
                                <span>خط العرض: {{ $order->latitude }} | خط الطول: {{ $order->longitude }}</span>
                                <button onclick="navigator.clipboard.writeText('{{ $order->latitude }}, {{ $order->longitude }}'); alert('تم نسخ الإحداثيات: {{ $order->latitude }}, {{ $order->longitude }}');" class="text-gray-400 hover:text-blue-600 p-1" title="نسخ الإحداثيات">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                        @else
                            <span class="text-gray-500 font-semibold">غير محددة رقمياً (يتم الاعتماد على نص العنوان)</span>
                        @endif
                    </div>
                </div>

                <!-- Embedded Map Preview -->
                @if($order->latitude && $order->longitude)
                <div class="rounded-xl overflow-hidden border border-gray-200 relative shadow-sm aspect-video sm:aspect-[21/9] bg-gray-100">
                    <iframe 
                        width="100%" 
                        height="100%" 
                        frameborder="0" 
                        scrolling="no" 
                        marginheight="0" 
                        marginwidth="0" 
                        src="https://www.openstreetmap.org/export/embed.html?bbox={{ $order->longitude - 0.008 }}%2C{{ $order->latitude - 0.006 }}%2C{{ $order->longitude + 0.008 }}%2C{{ $order->latitude + 0.006 }}&amp;layer=mapnik&amp;marker={{ $order->latitude }}%2C{{ $order->longitude }}"
                        class="w-full h-full border-0">
                    </iframe>
                    <div class="absolute bottom-3 left-3 bg-white/95 backdrop-blur-sm px-3 py-1.5 rounded-lg border border-gray-200 text-[11px] font-bold text-gray-700 shadow flex items-center gap-1.5">
                        <i class="fa-solid fa-crosshairs text-red-500"></i>
                        <span>موقع طلب الصيانة ({{ $order->latitude }}, {{ $order->longitude }})</span>
                    </div>
                </div>
                @else
                <div class="p-4 bg-blue-50/50 rounded-xl border border-blue-100 text-xs text-blue-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-blue-600 text-sm"></i>
                        <span>تم تسجيل العنوان نصياً. يمكنك النقر على زر خرائط Google أعلاه للبحث عن الموقع المكتوب.</span>
                    </div>
                    <a href="{{ $order->map_url }}" target="_blank" class="font-bold underline hover:text-blue-900">فتح البحث ↗</a>
                </div>
                @endif
            </div>

            <!-- 3. Payment Method & Receipt Verification Section -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-gray-100 gap-3">
                    <div>
                        <h4 class="font-bold text-gray-900 text-base flex items-center gap-2">
                            <i class="fa-solid fa-money-bill-transfer text-emerald-600 text-lg"></i>
                            <span>وسيلة الدفع وسندات السداد (Payments & Receipts)</span>
                        </h4>
                        <p class="text-xs text-gray-400 mt-0.5">مراجعة طريقة السداد والتأكد من إشعار السند البنكي أو المحفظة وتأكيد العملية</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button @click="updatePaymentModalOpen = true" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                            <i class="fa-solid fa-sliders"></i>
                            <span>تعديل وسيلة الدفع</span>
                        </button>

                        @if(!$isPaid)
                            <button @click="confirmPaymentModalOpen = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>تأكيد واعتماد السداد ✓</span>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Payment Status & Method Info Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-100 space-y-1">
                        <span class="text-gray-400 block font-medium">وسيلة الدفع المحددة:</span>
                        <span class="font-bold text-gray-900 text-sm block">
                            @if(in_array($order->payment_method, ['kuraimi']))
                                <i class="fa-solid fa-building-columns text-blue-600 ml-1"></i>
                            @elseif(in_array($order->payment_method, ['jawwali', 'jeeb', 'onecash', 'wallet']))
                                <i class="fa-solid fa-wallet text-emerald-600 ml-1"></i>
                            @else
                                <i class="fa-solid fa-hand-holding-dollar text-amber-600 ml-1"></i>
                            @endif
                            {{ $order->payment_method_name }}
                        </span>
                    </div>

                    <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-100 space-y-1">
                        <span class="text-gray-400 block font-medium">حالة السداد الحالية:</span>
                        <div>
                            @if($isPaid)
                                <span class="inline-flex items-center gap-1 font-bold text-emerald-700 bg-emerald-100/70 px-2.5 py-1 rounded-lg">
                                    <i class="fa-solid fa-check-double text-emerald-600"></i>
                                    <span>تم السداد والتأكيد بنجاح ✓</span>
                                </span>
                                @php
                                    $paymentHistory = $order->history->first(fn($h) => str_contains($h->notes ?? '', 'السداد') || str_contains($h->notes ?? '', 'السند'));
                                @endphp
                                @if($paymentHistory && $paymentHistory->changedBy)
                                    <div class="text-[11px] text-emerald-800 font-bold mt-1.5 flex items-center gap-1">
                                        <i class="fa-solid fa-user-check text-emerald-600"></i>
                                        <span>معتمد السند: {{ $paymentHistory->changedBy->full_name }}</span>
                                        <span class="text-[10px] text-gray-400 font-mono">({{ $paymentHistory->created_at->format('Y-m-d H:i') }})</span>
                                    </div>
                                @endif
                            @elseif($isPendingVerification)
                                <span class="inline-flex items-center gap-1 font-bold text-amber-800 bg-amber-100/80 px-2.5 py-1 rounded-lg">
                                    <i class="fa-solid fa-hourglass-half text-amber-600 animate-spin"></i>
                                    <span>بانتظار مراجعة السند من الإدارة</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 font-bold text-gray-600 bg-gray-200 px-2.5 py-1 rounded-lg">
                                    <i class="fa-solid fa-xmark text-gray-500"></i>
                                    <span>غير مدفوع بعد</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-100 space-y-1">
                        <span class="text-gray-400 block font-medium">رقم الحوالة / السند المرجعي:</span>
                        @if($transferRef)
                            <div class="font-mono font-bold text-gray-900 text-sm flex items-center justify-between">
                                <span>{{ $transferRef }}</span>
                                <button onclick="navigator.clipboard.writeText('{{ $transferRef }}'); alert('تم نسخ رقم الحوالة: {{ $transferRef }}');" class="text-gray-400 hover:text-emerald-600 p-1" title="نسخ رقم الحوالة">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                        @else
                            <span class="text-gray-400 font-medium">لا يوجد رقم مرجعي مسجل</span>
                        @endif
                    </div>
                </div>

                <!-- Financial Breakdown -->
                <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                    <h5 class="text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">تفصيل الحساب المالي للطلب:</h5>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                        <div>
                            <span class="text-gray-400 block">رسوم الفحص والكشف:</span>
                            <span class="font-mono font-bold text-gray-800">{{ number_format($order->inspection_fee ?? 0, 2) }} ر.ي</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block">أجور اليد والعمل:</span>
                            <span class="font-mono font-bold text-gray-800">{{ number_format($order->labor_fee ?? 0, 2) }} ر.ي</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block">قطع الغيار والمواد:</span>
                            <span class="font-mono font-bold text-gray-800">{{ number_format($order->materials_fee ?? 0, 2) }} ر.ي</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block">الخصم / الكوبون:</span>
                            <span class="font-mono font-bold text-red-600">-{{ number_format($order->discount ?? 0, 2) }} ر.ي</span>
                        </div>
                    </div>
                </div>

                <!-- Bank / Wallet Transfer Receipt Section -->
                <div class="pt-3 border-t border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-gray-700 flex items-center gap-1.5">
                            <i class="fa-solid fa-receipt text-emerald-600"></i>
                            <span>إشعار / سند التحويل البنكي أو المحفظة:</span>
                        </span>

                        @if(!$receiptImg)
                            <button @click="updatePaymentModalOpen = true" class="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                                <i class="fa-solid fa-upload"></i>
                                <span>إرفاق سند سداد يدوياً</span>
                            </button>
                        @endif
                    </div>

                    @if($receiptImg)
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 flex flex-col sm:flex-row items-center gap-4">
                            <div class="w-full sm:w-44 h-32 rounded-lg overflow-hidden border border-gray-300 bg-white shadow-inner flex-shrink-0 cursor-pointer group relative" @click="selectedImage = '{{ asset($receiptImg) }}'; receiptModalOpen = true">
                                <img src="{{ asset($receiptImg) }}" alt="سند السداد" class="w-full h-full object-cover group-hover:scale-105 transition duration-200">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1">
                                    <i class="fa-solid fa-magnifying-glass-plus"></i>
                                    <span>تكبير السند</span>
                                </div>
                            </div>

                            <div class="flex-1 space-y-2 text-xs w-full">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-gray-800 text-sm">مستند إشعار التحويل البنكي / المحفظة</span>
                                    <a href="{{ asset($receiptImg) }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold transition flex items-center gap-1">
                                        <i class="fa-solid fa-up-right-from-square"></i>
                                        <span>فتح بحجم كامل</span>
                                    </a>
                                </div>
                                <p class="text-gray-500 leading-relaxed">
                                    يرجى مراجعة إشعار السند للتأكد من مطابقة المبلغ الإجمالي (<strong class="text-gray-900 font-mono">{{ number_format($order->total_amount, 2) }} ر.ي</strong>) مع رقم الحوالة والتاريخ الموضح قبل الضغط على تأكيد السداد.
                                </p>

                                @if(!$isPaid)
                                    <div class="pt-2">
                                        <button @click="confirmPaymentModalOpen = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
                                            <i class="fa-solid fa-check"></i>
                                            <span>السند سليم - اعتماد وتأكيد السداد الآن ✓</span>
                                        </button>
                                    </div>
                                @else
                                    <div class="pt-1 text-emerald-700 font-bold flex items-center gap-1">
                                        <i class="fa-solid fa-circle-check"></i>
                                        <span>تمت مراجعة هذا السند واعتماده بنجاح من قبل الإدارة.</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="p-4 rounded-xl border border-dashed border-gray-300 bg-gray-50/50 text-center space-y-2">
                            <i class="fa-solid fa-file-invoice text-gray-300 text-2xl"></i>
                            <p class="text-xs text-gray-500">لم يتم إرفاق صورة لسند السداد حتى الآن.</p>
                            @if($isBankOrWallet)
                                <p class="text-[11px] text-amber-700 font-semibold">
                                    وسيلة الدفع المختارة هي ({{ $order->payment_method_name }}). إذا قام العميل بالسداد، يمكنك إرفاق صورة السند أو إدخال رقم الإشعار لتأكيد العملية.
                                </p>
                            @endif
                            <button @click="updatePaymentModalOpen = true" class="px-3.5 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-xl text-xs font-bold transition">
                                إرفاق صورة السند أو تحديث السداد
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 4. Status History Timeline -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                <h4 class="font-bold text-gray-800 text-base flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-emerald-600"></i>
                    <span>سجل تتبع مراحل الطلب (Timeline)</span>
                </h4>

                <div class="space-y-4 pr-2">
                    @forelse($order->history->sortByDesc('created_at') as $h)
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs flex-shrink-0 mt-0.5">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-gray-800">{{ $h->notes ?? 'تحديث حالة الطلب' }}</span>
                                    <span class="text-gray-400 font-mono">{{ $h->created_at ? $h->created_at->format('Y-m-d H:i') : '-' }}</span>
                                </div>
                                <span class="text-[11px] text-gray-500">بواسطة: {{ $h->changedBy->full_name ?? 'النظام الآلي' }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-xs text-gray-400">لا يوجد سجل تاريخي حتى الآن</div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right Col: Customer Info, Craftsman Tracking, Status Management -->
        <div class="space-y-6">

            <!-- 1. Customer Details Card (بيانات العميل) -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <h4 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-user text-emerald-600 text-base"></i>
                        <span>بيانات العميل</span>
                    </h4>
                    <span class="text-[11px] font-mono text-gray-400">المعرف: #{{ $order->customer_id ?? '-' }}</span>
                </div>

                <div class="text-xs space-y-3">
                    <div class="flex justify-between items-center py-1">
                        <span class="text-gray-400">اسم العميل:</span>
                        <span class="font-bold text-gray-900 text-sm">{{ $order->customer->full_name ?? 'عميل غير مسجل' }}</span>
                    </div>

                    <div class="flex justify-between items-center py-1">
                        <span class="text-gray-400">رقم الهاتف:</span>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-gray-900 font-mono text-sm">{{ $order->customer->phone ?? '-' }}</span>
                            @if(!empty($order->customer->phone))
                                <a href="tel:{{ $order->customer->phone }}" class="p-1 text-emerald-600 hover:bg-emerald-50 rounded" title="اتصال">
                                    <i class="fa-solid fa-phone"></i>
                                </a>
                                <a href="https://wa.me/967{{ ltrim($order->customer->phone, '0') }}" target="_blank" class="p-1 text-green-600 hover:bg-green-50 rounded" title="واتساب">
                                    <i class="fa-brands fa-whatsapp text-sm"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-between items-center py-1">
                        <span class="text-gray-400">البريد الإلكتروني:</span>
                        <span class="text-gray-700 font-medium">{{ $order->customer->email ?? 'لا يوجد بريد' }}</span>
                    </div>

                    <div class="flex justify-between items-center py-1">
                        <span class="text-gray-400">رصيد محفظة العميل:</span>
                        <span class="font-mono font-bold text-emerald-700">{{ number_format($order->customer->wallet_balance ?? 0, 2) }} ر.ي</span>
                    </div>

                    <div class="flex justify-between items-center py-1">
                        <span class="text-gray-400">تاريخ تسجيل الحساب:</span>
                        <span class="font-mono text-gray-600">{{ $order->customer->created_at ? $order->customer->created_at->format('Y-m-d') : '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- 2. Craftsman Assignment & Tracking Card (متى أُسند ومتى فتح الحرفي الطلب) -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <h4 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-user-gear text-blue-600 text-base"></i>
                        <span>متابعة الحرفي وتوقيتات التفاعل</span>
                    </h4>
                </div>

                @if($craftsmanUser)
                    <div class="p-3.5 bg-blue-50/50 rounded-xl border border-blue-100 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-blue-950 text-sm">{{ $craftsmanUser->full_name }}</span>
                            <span class="text-[11px] px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-800 font-bold">
                                {{ $craftsmanUser->account_status === 'active' ? 'حساب نشط' : 'معلق' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-600">
                            <span class="font-mono">{{ $craftsmanUser->phone }}</span>
                            <div class="flex items-center gap-1.5">
                                <a href="tel:{{ $craftsmanUser->phone }}" class="p-1 text-blue-600 hover:bg-blue-100 rounded" title="اتصال">
                                    <i class="fa-solid fa-phone"></i>
                                </a>
                                <a href="https://wa.me/967{{ ltrim($craftsmanUser->phone, '0') }}" target="_blank" class="p-1 text-green-600 hover:bg-green-100 rounded" title="واتساب">
                                    <i class="fa-brands fa-whatsapp text-sm"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Timestamps: When assigned & When opened -->
                    <div class="space-y-3 text-xs pt-1">
                        <!-- متى تم إسناد الطلب -->
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <span class="text-gray-400 block mb-1 font-medium">متى تم إسناد الطلب:</span>
                            @if($activeAssignment?->assigned_at)
                                <div class="font-mono font-bold text-gray-900 flex items-center gap-1.5">
                                    <i class="fa-regular fa-clock text-blue-600"></i>
                                    <span>{{ $activeAssignment->assigned_at->format('Y-m-d H:i') }}</span>
                                    <span class="text-[11px] text-gray-400 font-normal">({{ $activeAssignment->assigned_at->diffForHumans() }})</span>
                                </div>
                                @if($activeAssignment->assignedBy)
                                    <div class="mt-1.5 p-1.5 bg-blue-50/80 rounded-lg text-[11px] text-blue-900 font-bold flex items-center gap-1.5 border border-blue-200">
                                        <i class="fa-solid fa-user-check text-blue-600"></i>
                                        <span>تم الإسناد بواسطة الموظف: {{ $activeAssignment->assignedBy->full_name }}</span>
                                    </div>
                                @endif
                            @else
                                <span class="font-bold text-gray-600">تم التعيين بدون توثيق وقت محدد</span>
                            @endif
                        </div>

                        <!-- متى الحرفي فتح الطلب -->
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <span class="text-gray-400 block mb-1 font-medium">متى فتح الحرفي الطلب:</span>
                            @if($activeAssignment?->opened_at)
                                <div class="font-mono font-bold text-emerald-700 flex items-center gap-1.5">
                                    <i class="fa-solid fa-envelope-open-text text-emerald-600"></i>
                                    <span>{{ $activeAssignment->opened_at->format('Y-m-d H:i') }}</span>
                                    <span class="text-[11px] text-emerald-600 font-normal">({{ $activeAssignment->opened_at->diffForHumans() }})</span>
                                </div>
                                <span class="text-[11px] text-emerald-700 font-semibold block mt-0.5">✓ تم الاطلاع والمعاينة من الفني</span>
                            @elseif($activeAssignment?->responded_at || $activeAssignment?->accepted_at)
                                @php
                                    $openTime = $activeAssignment->responded_at ?? $activeAssignment->accepted_at;
                                @endphp
                                <div class="font-mono font-bold text-emerald-700 flex items-center gap-1.5">
                                    <i class="fa-solid fa-envelope-open-text text-emerald-600"></i>
                                    <span>{{ $openTime->format('Y-m-d H:i') }}</span>
                                    <span class="text-[11px] text-emerald-600 font-normal">({{ $openTime->diffForHumans() }})</span>
                                </div>
                                <span class="text-[11px] text-emerald-700 font-semibold block mt-0.5">✓ تم قبول ومعاينة الطلب من الفني</span>
                            @else
                                <div class="flex items-center gap-1.5 text-amber-700 font-bold">
                                    <i class="fa-regular fa-envelope text-amber-500"></i>
                                    <span>لم يقم الحرفي بفتح الطلب حتى الآن ⏳</span>
                                </div>
                                <span class="text-[11px] text-gray-400 block mt-0.5">بانتظار تسجيل دخول الحرفي للاطلاع على تفاصيل الطلب</span>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="p-4 rounded-xl border border-dashed border-gray-300 text-center space-y-2">
                        <i class="fa-solid fa-user-xmark text-gray-300 text-2xl"></i>
                        <p class="text-xs text-gray-500 font-medium">لم يتم إسناد هذا الطلب لأي حرفي حتى الآن.</p>
                        <p class="text-[11px] text-gray-400">يمكنك إسناد الطلب مباشرة من النموذج أدناه.</p>
                    </div>
                @endif
            </div>

            <!-- 3. Status Management & Assign Form -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                <h4 class="font-bold text-gray-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-emerald-600"></i>
                    <span>إدارة وتحديث حالة الطلب</span>
                </h4>

                <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">حالة الطلب الحالية</label>
                        <select name="status_id" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                            @foreach($statuses as $st)
                                <option value="{{ $st->id }}" {{ $order->status_id == $st->id ? 'selected' : '' }}>
                                    {{ $st->description ?? $st->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">الحرفي المسند للطلب</label>
                        <select name="craftsman_id" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="">بدون حرفي (بانتظار قبول)</option>
                            @php
                                $curCraftsmanId = $craftsmanUser?->id ?? null;
                            @endphp
                            @foreach($craftsmen as $cm)
                                @php
                                    $isOpen = $cm->account_status === 'active'
                                        && ($cm->craftsmanProfile->approval_status ?? '') === 'approved'
                                        && (($cm->craftsmanProfile->availability_status ?? false) || $cm->is_online);
                                @endphp
                                <option value="{{ $cm->id }}" {{ $curCraftsmanId == $cm->id ? 'selected' : '' }} {{ !$isOpen && $curCraftsmanId != $cm->id ? 'disabled class=text-gray-400 bg-gray-100' : '' }}>
                                    {{ $cm->full_name }} ({{ $cm->phone }}) - {{ $isOpen ? '🟢 متاح' : '🔴 مغلق الحالة' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-gray-400 mt-1">لا يمكن تعيين حرفي حسابه غير متاح.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">ملاحظات الإدارة للتحديث</label>
                        <input type="text" name="notes" placeholder="سبب التغيير..." class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition">
                        حفظ التعديل وإشعار الأطراف
                    </button>
                </form>
            </div>

        </div>

    </div>

    <!-- Modal 1: Receipt Full Size Preview Modal -->
    <div x-show="receiptModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4 backdrop-blur-sm">
        <div @click.away="receiptModalOpen = false" class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 flex-shrink-0">
                <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-emerald-600"></i>
                    <span>معاينة سند التحويل البنكي / المحفظة بدقة كاملة</span>
                </h3>
                <button @click="receiptModalOpen = false" class="text-gray-400 hover:text-gray-600 text-lg p-1">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="flex-1 overflow-auto rounded-xl border border-gray-200 bg-gray-900/5 p-2 flex items-center justify-center">
                <img :src="selectedImage" alt="سند السداد" class="max-w-full max-h-[65vh] object-contain rounded-lg shadow">
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-gray-100 flex-shrink-0 text-xs">
                <a :href="selectedImage" target="_blank" download class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition flex items-center gap-1.5">
                    <i class="fa-solid fa-download"></i>
                    <span>تحميل السند</span>
                </a>

                <div class="flex items-center gap-2">
                    <button type="button" @click="receiptModalOpen = false" class="px-4 py-2 rounded-xl text-gray-500 hover:bg-gray-100 font-bold">
                        إغلاق
                    </button>
                    @if(!$isPaid)
                        <button type="button" @click="receiptModalOpen = false; confirmPaymentModalOpen = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow">
                            اعتماد وتأكيد السداد الآن ✓
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 2: Confirm Payment Modal (تأكيد واعتماد السداد) -->
    <div x-show="confirmPaymentModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
        <div @click.away="confirmPaymentModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                    <span>تأكيد واعتماد عملية السداد</span>
                </h3>
                <button @click="confirmPaymentModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('admin.orders.confirm-payment', $order->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-xs text-emerald-800 space-y-1">
                    <div class="flex justify-between">
                        <span>إجمالي المبلغ المطلوب اعتماده:</span>
                        <strong class="font-mono text-sm">{{ number_format($order->total_amount, 2) }} ر.ي</strong>
                    </div>
                    <div class="flex justify-between">
                        <span>وسيلة الدفع:</span>
                        <strong>{{ $order->payment_method_name }}</strong>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">رقم الحوالة أو الإشعار البنكي المرجعي</label>
                    <input type="text" name="transfer_reference" value="{{ $transferRef }}" placeholder="مثال: TXN-9281048 أو رقم السند..." class="w-full p-2.5 rounded-xl border border-gray-200 text-xs font-mono outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                @if(!$receiptImg)
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">إرفاق صورة السند (اختياري)</label>
                    <input type="file" name="receipt_image" accept="image/*" class="w-full p-1.5 rounded-xl border border-gray-200 text-xs bg-gray-50">
                </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">ملاحظات الإدارة حول الاعتماد (اختياري)</label>
                    <input type="text" name="notes" placeholder="تمت مطابقة السند مع كشف الحساب البنكي..." class="w-full p-2.5 rounded-xl border border-gray-200 text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100 text-xs">
                    <button type="button" @click="confirmPaymentModalOpen = false" class="px-4 py-2 rounded-xl text-gray-500 hover:bg-gray-100 font-bold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-md shadow-emerald-600/20">
                        تأكيد السداد والاعتماد النهائي ✓
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: Update Payment Details Modal (تعديل وسيلة الدفع أو رفع سند) -->
    <div x-show="updatePaymentModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
        <div @click.away="updatePaymentModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-blue-600"></i>
                    <span>تعديل وسيلة وبيانات السداد</span>
                </h3>
                <button @click="updatePaymentModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('admin.orders.update-payment', $order->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-gray-700 mb-1">وسيلة الدفع</label>
                    <select name="payment_method" class="w-full p-2.5 rounded-xl border border-gray-200 font-semibold outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($paymentMethods as $pm)
                            <option value="{{ $pm->code }}" {{ $order->payment_method === $pm->code ? 'selected' : '' }}>
                                {{ $pm->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">حالة السداد</label>
                    <select name="payment_status" class="w-full p-2.5 rounded-xl border border-gray-200 font-semibold outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="unpaid" {{ $order->payment_status === 'unpaid' ? 'selected' : '' }}>غير مدفوع (Unpaid)</option>
                        <option value="pending_verification" {{ $order->payment_status === 'pending_verification' ? 'selected' : '' }}>بانتظار مراجعة وتأكيد السند (Pending Verification)</option>
                        <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>مدفوع ومؤكد (Paid)</option>
                        <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>مسترجع (Refunded)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">رقم الحوالة / المرجع</label>
                    <input type="text" name="transfer_reference" value="{{ $transferRef }}" placeholder="رقم إشعار البنك أو المحفظة..." class="w-full p-2.5 rounded-xl border border-gray-200 font-mono outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">صورة السند / الإشعار البنكي</label>
                    <input type="file" name="receipt_image" accept="image/*" class="w-full p-1.5 rounded-xl border border-gray-200 bg-gray-50">
                    @if($receiptImg)
                        <span class="text-[10px] text-gray-400 block mt-1">يوجد سند مرفق حالياً. رفع صورة جديدة سيستبدل السند السابق.</span>
                    @endif
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">ملاحظات التحديث</label>
                    <input type="text" name="notes" placeholder="سبب التعديل..." class="w-full p-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="updatePaymentModalOpen = false" class="px-4 py-2 rounded-xl text-gray-500 hover:bg-gray-100 font-bold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-md shadow-blue-600/20">
                        حفظ بيانات السداد
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 4: Edit Order Modal -->
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div @click.away="editModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-blue-600"></i>
                    <span>تعديل تفاصيل الطلب #{{ $order->clean_number }}</span>
                </h3>
                <button @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('admin.orders.update', $order->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">وصف العطل والملاحظات *</label>
                    <textarea name="description" rows="3" required class="w-full p-2.5 rounded-xl border border-gray-200 text-xs outline-none focus:ring-2 focus:ring-blue-500">{{ $order->description }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">العنوان والتفاصيل</label>
                    <input type="text" name="address_details" value="{{ $order->address_details }}" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">تاريخ الزيارة المفضل</label>
                    <input type="date" name="preferred_date" value="{{ $order->preferred_date ? date('Y-m-d', strtotime($order->preferred_date)) : date('Y-m-d') }}" class="w-full p-2.5 rounded-xl border border-gray-200 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">صورة المشكلة</label>
                        <input type="file" name="image" accept="image/*" class="w-full p-1.5 rounded-xl border border-gray-200 text-[11px] bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">صورة إنجاز العمل</label>
                        <input type="file" name="completion_image" accept="image/*" class="w-full p-1.5 rounded-xl border border-gray-200 text-[11px] bg-gray-50">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl text-gray-500 hover:bg-gray-100 text-xs font-bold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/20">تحديث البيانات</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
