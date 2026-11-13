<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Advertisement;
use App\Models\AdvertisementImage;
use App\Models\Offer;
use App\Models\OfferImage;
use App\Models\Service;
use App\Models\User;

class AdminAdvertisementController extends Controller
{
    public function index()
    {
        $advertisements = Advertisement::with(['creator', 'images'])->latest()->paginate(10);
        $offers = Offer::with(['craftsman', 'service', 'images'])->latest()->paginate(10);
        $services = Service::where('is_active', true)->get();
        $craftsmen = User::where('role', 'craftsman')->get();

        return view('admin.advertisements.index', compact('advertisements', 'offers', 'services', 'craftsmen'));
    }

    public function storeAd(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'advertiser_name' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'target_url' => 'nullable|url',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive,expired',
            'image' => 'nullable|image|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('advertisements', 'public');
            $imagePath = '/storage/' . $path;
        }

        $ad = Advertisement::create([
            'title' => $validated['title'],
            'advertiser_name' => $validated['advertiser_name'] ?? 'منصة حِرفة',
            'description' => $validated['description'] ?? null,
            'target_url' => $validated['target_url'] ?? null,
            'image' => $imagePath,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'],
            'created_by' => auth()->id() ?? 1,
        ]);

        if ($imagePath) {
            AdvertisementImage::create([
                'advertisement_id' => $ad->id,
                'image_path' => $imagePath,
            ]);
        }

        return back()->with('success', 'تمت إضافة الإعلان الترويجي بنجاح');
    }

    public function updateAd(Request $request, $id)
    {
        $ad = Advertisement::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'advertiser_name' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'target_url' => 'nullable|url',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive,expired',
            'image' => 'nullable|image|max:5120',
        ]);

        $data = [
            'title' => $validated['title'],
            'advertiser_name' => $validated['advertiser_name'] ?? $ad->advertiser_name,
            'description' => $validated['description'] ?? $ad->description,
            'target_url' => $validated['target_url'] ?? $ad->target_url,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'],
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('advertisements', 'public');
            $data['image'] = '/storage/' . $path;

            AdvertisementImage::create([
                'advertisement_id' => $ad->id,
                'image_path' => $data['image'],
            ]);
        }

        $ad->update($data);

        return back()->with('success', 'تم تحديث بيانات الإعلان بنجاح');
    }

    public function destroyAd($id)
    {
        $ad = Advertisement::findOrFail($id);
        $title = $ad->title;
        $ad->delete();

        return back()->with('success', "تم حذف الإعلان ($title) بنجاح");
    }

    public function storeOffer(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'service_id' => 'required|exists:services,id',
            'craftsman_id' => 'nullable|exists:users,id',
            'discount_percentage' => 'required|numeric|min:1|max:100',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'offer_status' => 'required|in:active,inactive,expired',
            'image' => 'nullable|image|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('offers', 'public');
            $imagePath = '/storage/' . $path;
        }

        $offer = Offer::create([
            'title' => $validated['title'],
            'service_id' => $validated['service_id'],
            'craftsman_id' => $validated['craftsman_id'] ?? null,
            'discount_percentage' => $validated['discount_percentage'],
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'offer_status' => $validated['offer_status'],
        ]);

        if ($imagePath) {
            OfferImage::create([
                'offer_id' => $offer->id,
                'image_path' => $imagePath,
            ]);
        }

        return back()->with('success', 'تم إنشاء وتفعيل العرض الترويجي بنجاح');
    }

    public function updateOffer(Request $request, $id)
    {
        $offer = Offer::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'service_id' => 'required|exists:services,id',
            'craftsman_id' => 'nullable|exists:users,id',
            'discount_percentage' => 'required|numeric|min:1|max:100',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'offer_status' => 'required|in:active,inactive,expired',
            'image' => 'nullable|image|max:5120',
        ]);

        $data = [
            'title' => $validated['title'],
            'service_id' => $validated['service_id'],
            'craftsman_id' => $validated['craftsman_id'] ?? null,
            'discount_percentage' => $validated['discount_percentage'],
            'description' => $validated['description'] ?? $offer->description,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'offer_status' => $validated['offer_status'],
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('offers', 'public');
            $data['image'] = '/storage/' . $path;

            OfferImage::create([
                'offer_id' => $offer->id,
                'image_path' => $data['image'],
            ]);
        }

        $offer->update($data);

        return back()->with('success', 'تم تحديث بيانات العرض بنجاح');
    }

    public function destroyOffer($id)
    {
        $offer = Offer::findOrFail($id);
        $title = $offer->title;
        $offer->delete();

        return back()->with('success', "تم حذف العرض الترويجي ($title) بنجاح");
    }
}
