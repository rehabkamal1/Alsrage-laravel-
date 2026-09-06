<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExternalOfficeResource;
use App\Models\ExternalOffice;
use Illuminate\Http\Request;

class ExternalOfficeController extends Controller
{
    public function index(Request $request)
    {
        $query = ExternalOffice::query();

        // Generic search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
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
            return ExternalOfficeResource::collection($query->get());
        }

        $perPage = $request->integer('per_page', 15);

        return ExternalOfficeResource::collection($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'nullable|string|max:255',
            'contacts' => 'nullable|array',
            'contacts.*.name' => 'nullable|string|max:255',
            'contacts.*.phone' => 'nullable|string|max:255',
            'contacts.*.commission' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'whatsapp_link' => 'nullable|string|url|max:255',
        ]);

        $office = ExternalOffice::create($validated);

        return new ExternalOfficeResource($office);
    }

    public function update(Request $request, ExternalOffice $externalOffice)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'country' => 'nullable|string|max:255',
            'contacts' => 'nullable|array',
            'contacts.*.name' => 'nullable|string|max:255',
            'contacts.*.phone' => 'nullable|string|max:255',
            'contacts.*.commission' => 'nullable|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'notes' => 'nullable|string',
            'whatsapp_link' => 'nullable|string|url|max:255',
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