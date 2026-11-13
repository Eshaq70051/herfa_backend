<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Address;
use App\Models\Governorate;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = Address::with('governorate')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $addresses,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'address_line' => 'required|string',
            'city' => 'nullable|string|max:100',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'neighborhood' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);

        $userId = $request->user()->id;

        if (!empty($validated['is_default'])) {
            Address::where('user_id', $userId)->update(['is_default' => false]);
        }

        $cityName = $validated['city'] ?? 'صنعاء';
        $gov = Governorate::where('name', 'like', "%{$cityName}%")->first()
            ?? Governorate::first();

        $address = Address::create([
            'user_id' => $userId,
            'governorate_id' => $gov?->id ?? 1,
            'district_id' => null,
            'label' => $validated['title'],
            'address_details' => $validated['address_line'] . (!empty($validated['notes']) ? ' (' . $validated['notes'] . ')' : ''),
            'neighborhood' => $validated['neighborhood'] ?? null,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'is_default' => !empty($validated['is_default']),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تمت إضافة العنوان بنجاح',
            'data' => $address->load('governorate'),
        ], 201);
    }

    public function setDefault(Request $request, $id)
    {
        $userId = $request->user()->id;
        Address::where('user_id', $userId)->update(['is_default' => false]);
        $address = Address::where('user_id', $userId)->findOrFail($id);
        $address->is_default = true;
        $address->save();

        return response()->json([
            'status' => true,
            'message' => 'تم تعيين العنوان كافتراضي بنجاح',
            'data' => $address,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $address = Address::where('user_id', $request->user()->id)->findOrFail($id);
        $address->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم حذف العنوان بنجاح',
        ]);
    }
}
