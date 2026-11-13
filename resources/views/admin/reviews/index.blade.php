@extends('admin.layout')

@section('title', 'التقييمات ومراقبة الجودة')
@section('page_title', 'تقييمات وآراء العملاء وضمان الجودة (Customer Reviews)')

@section('content')
<div class="space-y-6" x-data="{
    showStatusModal: false,
    showDeleteModal: false,
    selectedReview: {}
}">

    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <div>
            <h3 class="text-lg font-bold text-gray-800">مراقبة جودة الخدمات وتقييمات العملاء</h3>
            <p class="text-xs text-gray-500 mt-1">متابعة رضا العملاء، تقييمات الحرفيين، وإدارة التقييمات المخالفة</p>
        </div>
        <div class="text-xs font-bold text-gray-600 bg-gray-100 px-3 py-1.5 rounded-xl">
            إجمالي التقييمات: {{ $reviews->total() }}
        </div>
    </div>

    <!-- Reviews Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($reviews as $rev)
            <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm space-y-3 flex flex-col justify-between hover:border-emerald-300 transition">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <span class="text-base font-black text-amber-500 font-mono">{{ $rev->rating }}</span>
                            <div class="text-amber-400 text-xs">
                                @for($i=1; $i<=5; $i++)
                                    <i class="fa-solid fa-star {{ $i <= $rev->rating ? '' : 'text-gray-200' }}"></i>
                                @endfor
                            </div>
                        </div>
                        <div>
                            @if($rev->review_status === 'visible')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">ظاهر</span>
                            @elseif($rev->review_status === 'hidden')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600 border border-gray-200">مخفي</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-600 border border-red-200">مبلغ عنه</span>
                            @endif
                        </div>
                    </div>

                    @if($rev->comment)
                        <p class="text-xs text-gray-700 bg-gray-50 p-3 rounded-xl leading-relaxed italic">
                            "{{ $rev->comment }}"
                        </p>
                    @endif

                    <div class="pt-2 text-xs space-y-1">
                        <div class="flex justify-between">
                            <span class="text-gray-400 text-[11px]">العميل:</span>
                            <span class="font-bold text-gray-800">{{ $rev->customer->full_name ?? ($rev->client->name ?? 'عميل') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400 text-[11px]">الحرفي:</span>
                            <span class="font-bold text-emerald-700">{{ $rev->craftsman->full_name ?? ($rev->craftsman->name ?? 'حرفي') }}</span>
                        </div>
                        @if($rev->request)
                            <div class="flex justify-between">
                                <span class="text-gray-400 text-[11px]">الطلب:</span>
                                <a href="{{ route('admin.orders.show', $rev->request_id) }}" class="text-blue-600 font-mono font-bold hover:underline">
                                    {{ $rev->request->request_number ?? ('#' . $rev->request_id) }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Review Actions -->
                <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-[10px] text-gray-400 font-mono">{{ $rev->created_at->format('Y-m-d') }}</span>
                    <div class="flex items-center gap-1.5">
                        <button @click="selectedReview = {
                            id: {{ $rev->id }},
                            status: '{{ $rev->review_status }}'
                        }; showStatusModal = true" class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-emerald-50 hover:text-emerald-700 text-gray-700 text-xs font-semibold transition" title="تعديل حالة الظهور">
                            الحالة
                        </button>
                        <button @click="selectedReview = { id: {{ $rev->id }} }; showDeleteModal = true" class="p-1 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 text-xs transition" title="حذف التقييم">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 py-16 text-center text-gray-400 bg-white rounded-2xl border border-gray-200">
                <i class="fa-solid fa-star-half-stroke text-4xl text-gray-300 mb-2"></i>
                <p>لا توجد تقييمات مسجلة حالياً</p>
            </div>
        @endforelse
    </div>

    @if($reviews->hasPages())
        <div class="p-4 bg-white rounded-2xl border border-gray-200">
            {{ $reviews->links() }}
        </div>
    @endif

    <!-- MODAL: Update Status -->
    <div x-show="showStatusModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showStatusModal = false" class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl border border-gray-100">
            <h3 class="text-base font-bold text-gray-800 mb-3">تحديث حالة ظهور التقييم</h3>
            <form :action="'{{ url('/admin/reviews') }}/' + selectedReview.id + '/status'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <select name="review_status" x-model="selectedReview.status" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="visible">ظاهر للجميع في التطبيق (Visible)</option>
                        <option value="hidden">إخفاء التقييم (Hidden)</option>
                        <option value="reported">مبلغ عنه وقيد المراجعة (Reported)</option>
                    </select>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="showStatusModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-md shadow-emerald-600/20">تحديث</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Delete Review -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showDeleteModal = false" class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl border border-gray-100 text-center">
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-trash-can text-xl"></i>
            </div>
            <h3 class="text-base font-bold text-gray-800">حذف التقييم</h3>
            <p class="text-xs text-gray-500 mt-1">هل أنت متأكد من حذف هذا التقييم نهائياً من المنظومة؟</p>
            <form :action="'{{ url('/admin/reviews') }}/' + selectedReview.id" method="POST" class="mt-5 flex items-center justify-center gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDeleteModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50">إلغاء</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 text-white text-xs font-bold hover:bg-red-700 shadow-md shadow-red-600/20">تأكيد الحذف</button>
            </form>
        </div>
    </div>

</div>
@endsection
