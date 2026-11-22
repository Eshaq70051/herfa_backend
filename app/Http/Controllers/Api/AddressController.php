<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\District;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = Address::with(['district.governorate', 'neighborhood'])
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
            'title' => 'required|string|max:50',
            'address_line' => 'required|string|max:255',
            'district_id' => 'nullable|exists:districts,id',
            'neighborhood_id' => 'nullable|exists:neighborhoods,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_default' => 'nullable|boolean',
        ]);

        $userId = $request->user()->id;

        if (! empty($validated['is_default'])) {
            Address::where('user_id', $userId)->update(['is_default' => false]);
        }

        $districtId = $validated['district_id'] ?? District::first()?->id ?? 1;

        $address = Address::create([
            'user_id' => $userId,
            'district_id' => $districtId,
            'neighborhood_id' => $validated['neighborhood_id'] ?? null,
            'label' => $validated['title'],
            'address_details' => $validated['address_line'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'is_default' => ! empty($validated['is_default']),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم حفظ العنوان الجديد بنجاح',
            'data' => $address->load(['district.governorate', 'neighborhood']),
        ], 201);
    }

    public function setDefault(Request $request, $id)
    {
        $userId = $request->user()->id;
        $address = Address::where('user_id', $userId)->findOrFail($id);

        Address::where('user_id', $userId)->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json([
            'status' => true,
            'message' => 'تم تعيين العنوان كعنوان افتراضي',
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
