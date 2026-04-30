<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SaudiOfficeResource;
use App\Models\SaudiOffice;
use Illuminate\Http\Request;

class SaudiOfficeController extends Controller
{
    public function index(Request $request)
    {
        $query = SaudiOffice::query();

        // Generic search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('destination', 'like', "%{$search}%")
                  ->orWhere('responsible_employee', 'like', "%{$search}%");
            });
        }

        // Column filters
        $query->when($request->name, fn($q) => $q->where('name', 'like', "%{$request->name}%"))
              ->when($request->destination, fn($q) => $q->where('destination', 'like', "%{$request->destination}%"))
              ->when($request->city, fn($q) => $q->where('city', 'like', "%{$request->city}%"))
              ->when($request->responsible_employee, fn($q) => $q->where('responsible_employee', 'like', "%{$request->responsible_employee}%"));

        $perPage = $request->integer('per_page', 10);
        
        return SaudiOfficeResource::collection($query->latest()->paginate($perPage));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'destination' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'responsible_employee' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'total_authorization' => 'nullable|numeric',
            'musaned_price' => 'nullable|numeric',
            'whatsapp_link' => 'nullable|string|url|max:255',
            'is_supplier' => 'boolean',
        ]);

        $office = SaudiOffice::create($validated);

        return new SaudiOfficeResource($office);
    }

    public function update(Request $request, SaudiOffice $saudiOffice)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'destination' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'responsible_employee' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'total_authorization' => 'nullable|numeric',
            'musaned_price' => 'nullable|numeric',
            'whatsapp_link' => 'nullable|string|url|max:255',
            'is_supplier' => 'boolean',
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
