<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExternalOfficeResource;
use App\Models\ExternalOffice;
use App\Models\Setting;
use App\Rules\UniqueOfficeLicense;
use Illuminate\Http\Request;

class ExternalOfficeController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);
        $query = ExternalOffice::query();

        // Generic search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%")
                    ->orWhere('license_number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($request->filled('nationality')) {
            $nationalityValues = Setting::getNationalityCountryValues($request->string('nationality')->toString());

            if (! empty($nationalityValues)) {
                $query->whereIn('country', $nationalityValues);
            }
        }

        // Date filters
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->date('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->date('to_date'));
        }

        // Sorting
        $sortField = $request->input('sort_field', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');
        $allowedSortFields = ['id', 'name', 'country', 'created_at'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'id';
        }
        $query->orderBy($sortField, $sortDirection);

        if ($request->boolean('all')) {
            return ExternalOfficeResource::collection($query->limit(100)->get());
        }

        $perPage = (int) ($validated['per_page'] ?? 15);

        return ExternalOfficeResource::collection($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $request->merge([
            'license_number' => trim((string) $request->input('license_number', '')),
        ]);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'license_number' => ['required', 'string', 'max:255', new UniqueOfficeLicense()],
            'country' => 'nullable|string|max:255',
            'contacts' => 'nullable|array',
            'contacts.*.name' => 'nullable|string|max:255',
            'contacts.*.phone' => 'nullable|string|max:255',
            'contacts.*.commission' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'whatsapp_link' => 'nullable|string|url|max:255',
        ], [
            'license_number.required' => 'رقم الترخيص مطلوب.',
            'license_number.string' => 'رقم الترخيص يجب أن يكون نصًا صحيحًا.',
            'license_number.max' => 'رقم الترخيص يجب ألا يتجاوز 255 حرفًا.',
        ]);

        $office = ExternalOffice::create($validated);

        return new ExternalOfficeResource($office);
    }

    public function update(Request $request, ExternalOffice $externalOffice)
    {
        $request->merge([
            'license_number' => trim((string) $request->input('license_number', '')),
        ]);
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'license_number' => ['required', 'string', 'max:255', new UniqueOfficeLicense('external_offices', $externalOffice->id)],
            'country' => 'nullable|string|max:255',
            'contacts' => 'nullable|array',
            'contacts.*.name' => 'nullable|string|max:255',
            'contacts.*.phone' => 'nullable|string|max:255',
            'contacts.*.commission' => 'nullable|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'notes' => 'nullable|string',
            'whatsapp_link' => 'nullable|string|url|max:255',
        ], [
            'license_number.required' => 'رقم الترخيص مطلوب.',
            'license_number.string' => 'رقم الترخيص يجب أن يكون نصًا صحيحًا.',
            'license_number.max' => 'رقم الترخيص يجب ألا يتجاوز 255 حرفًا.',
        ]);

        $externalOffice->update($validated);

        return new ExternalOfficeResource($externalOffice);
    }

    public function destroy(ExternalOffice $externalOffice)
    {
        $externalOffice->delete();

        return response()->json(['message' => 'External office deleted successfully']);
    }
}