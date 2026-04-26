<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SaudiOfficeResource;
use App\Models\SaudiOffice;
use Illuminate\Http\Request;

class SaudiOfficeController extends Controller
{
    public function index()
    {
        return SaudiOfficeResource::collection(SaudiOffice::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'destination' => 'nullable|string|max:255',
            'responsible_employee' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $office = SaudiOffice::create($validated);

        return new SaudiOfficeResource($office);
    }

    public function update(Request $request, SaudiOffice $saudiOffice)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'destination' => 'nullable|string|max:255',
            'responsible_employee' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $saudiOffice->update($validated);

        return new SaudiOfficeResource($saudiOffice);
    }

    public function destroy(SaudiOffice $saudiOffice)
    {
        $saudiOffice->delete();

        return response()->json(['message' => 'Saudi office deleted successfully']);
    }
}
