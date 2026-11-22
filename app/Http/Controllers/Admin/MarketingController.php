<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\AdvertisementPosition;
use App\Models\Offer;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketingController extends Controller
{
    public function offers()
    {
        $offers = Offer::with(['service', 'user'])->latest('id')->paginate(15);
        $services = Service::where('is_active', 1)->get();

        return view('admin.marketing.offers', compact('offers', 'services'));
    }

    public function storeOffer(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'service_id' => 'required|exists:services,id',
            'discount_percentage' => 'required|numeric|min:1|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);

        Offer::create([
            'title' => $request->title,
            'service_id' => $request->service_id,
            'discount_percentage' => $request->discount_percentage,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'description' => $request->description,
            'offer_status' => 'active',
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'تم إنشاء العرض الترويجي بنجاح.');
    }

    public function updateOffer(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'service_id' => 'required|exists:services,id',
            'discount_percentage' => 'required|numeric|min:1|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'offer_status' => 'required|in:pending,active,expired,rejected',
        ]);

        $offer = Offer::findOrFail($id);
        $offer->update($request->only('title', 'service_id', 'discount_percentage', 'start_date', 'end_date', 'description', 'offer_status'));

        return back()->with('success', 'تم تعديل العرض الترويجي بنجاح.');
    }

    public function destroyOffer($id)
    {
        $offer = Offer::findOrFail($id);
        $offer->delete();

        return back()->with('success', 'تم حذف العرض الترويجي بنجاح.');
    }

    public function advertisements()
    {
        $ads = Advertisement::with('user')->latest('id')->paginate(15);
        $positions = AdvertisementPosition::where('is_active', 1)->get();

        return view('admin.marketing.advertisements', compact('ads', 'positions'));
    }

    public function storeAdvertisement(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'advertiser_name' => 'required|string|max:150',
            'target_url' => 'nullable|url',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);

        Advertisement::create([
            'title' => $request->title,
            'advertiser_name' => $request->advertiser_name,
            'target_url' => $request->target_url,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'description' => $request->description,
            'status' => 'active',
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'تمت إضافة الإعلان بنجاح.');
    }

    public function updateAdvertisement(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'advertiser_name' => 'required|string|max:150',
            'target_url' => 'nullable|url',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,active,inactive,expired',
        ]);

        $ad = Advertisement::findOrFail($id);
        $ad->update($request->only('title', 'advertiser_name', 'target_url', 'start_date', 'end_date', 'description', 'status'));

        return back()->with('success', 'تم تعديل بيانات الإعلان بنجاح.');
    }

    public function destroyAdvertisement($id)
    {
        $ad = Advertisement::findOrFail($id);
        $ad->delete();

        return back()->with('success', 'تم حذف الإعلان بنجاح.');
    }
}
