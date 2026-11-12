<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SaudiOfficeResource;
use App\Models\SaudiOffice;
use App\Rules\UniqueOfficeLicense;
use Illuminate\Http\Request;

class SaudiOfficeController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);
        $query = SaudiOffice::query();

        // Generic search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('destination', 'like', "%{$search}%")
                    ->orWhere('license_number', 'like', "%{$search}%")
                    ->orWhere('responsible_employee', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Column filters
        $query->when($request->name, fn($q) => $q->where('name', 'like', "%{$request->name}%"))
            ->when($request->destination, fn($q) => $q->where('destination', 'like', "%{$request->destination}%"))
            ->when($request->city, fn($q) => $q->where('city', 'like', "%{$request->city}%"))
            ->when($request->responsible_employee, fn($q) => $q->where('responsible_employee', 'like', "%{$request->responsible_employee}%"))
            ->when($request->filled('from_date'), fn($q) => $q->whereDate('created_at', '>=', $request->date('from_date')))
            ->when($request->filled('to_date'), fn($q) => $q->whereDate('created_at', '<=', $request->date('to_date')));

        if ($request->boolean('all')) {
            return SaudiOfficeResource::collection($query->latest()->limit(100)->get());
        }

        $perPage = (int) ($validated['per_page'] ?? 15);

        return SaudiOfficeResource::collection($query->latest()->paginate($perPage));
    }

    public function store(Request $request)
    {
        $request->merge([
            'license_number' => trim((string) $request->input('license_number', '')),
        ]);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'license_number' => ['required', 'string', 'max:255', new UniqueOfficeLicense()],
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
        ], [
            'license_number.required' => 'رقم الترخيص مطلوب.',
            'license_number.string' => 'رقم الترخيص يجب أن يكون نصًا صحيحًا.',
            'license_number.max' => 'رقم الترخيص يجب ألا يتجاوز 255 حرفًا.',
        ]);

        $office = SaudiOffice::create($validated);

        return new SaudiOfficeResource($office);
    }

    public function update(Request $request, SaudiOffice $saudiOffice)
    {
        $request->merge([
            'license_number' => trim((string) $request->input('license_number', '')),
        ]);
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'license_number' => ['required', 'string', 'max:255', new UniqueOfficeLicense('saudi_offices', $saudiOffice->id)],
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
        ], [
            'license_number.required' => 'رقم الترخيص مطلوب.',
            'license_number.string' => 'رقم الترخيص يجب أن يكون نصًا صحيحًا.',
            'license_number.max' => 'رقم الترخيص يجب ألا يتجاوز 255 حرفًا.',
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
