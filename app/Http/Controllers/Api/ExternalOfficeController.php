<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExternalOfficeResource;
use App\Models\ExternalOffice;
use Illuminate\Http\Request;

class ExternalOfficeController extends Controller
{
    public function index()
    {
        return ExternalOfficeResource::collection(ExternalOffice::latest()->get());
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
