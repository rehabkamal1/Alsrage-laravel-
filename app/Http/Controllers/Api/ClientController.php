<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    public function index()
    {
        return ClientResource::collection(Client::latest()->get());
    }

    public function store(StoreClientRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('passport_image')) {
            $data['passport_image'] = $request->file('passport_image')->store('clients/passports', 'public');
        }

        if ($request->hasFile('visa_image')) {
            $data['visa_image'] = $request->file('visa_image')->store('clients/visas', 'public');
        }

        if ($request->hasFile('id_image')) {
            $data['id_image'] = $request->file('id_image')->store('clients/id_cards', 'public');
        }

        $client = Client::create($data);

        return new ClientResource($client);
    }

    public function show(Client $client)
    {
        return new ClientResource($client);
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $data = $request->validated();

        if ($request->hasFile('passport_image')) {
            if ($client->passport_image) {
                Storage::disk('public')->delete($client->passport_image);
            }
            $data['passport_image'] = $request->file('passport_image')->store('clients/passports', 'public');
        }

        if ($request->hasFile('visa_image')) {
            if ($client->visa_image) {
                Storage::disk('public')->delete($client->visa_image);
            }
            $data['visa_image'] = $request->file('visa_image')->store('clients/visas', 'public');
        }

        if ($request->hasFile('id_image')) {
            if ($client->id_image) {
                Storage::disk('public')->delete($client->id_image);
            }
            $data['id_image'] = $request->file('id_image')->store('clients/id_cards', 'public');
        }

        $client->update($data);

        return new ClientResource($client);
    }

    public function destroy(Client $client)
    {
        if ($client->passport_image) {
            Storage::disk('public')->delete($client->passport_image);
        }
        if ($client->visa_image) {
            Storage::disk('public')->delete($client->visa_image);
        }
        if ($client->id_image) {
            Storage::disk('public')->delete($client->id_image);
        }

        $client->delete();

        return response()->json(['message' => 'Client deleted successfully']);
    }

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1',
        ]);

        $query = $request->query('query');

        $clients = Client::where('name', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'name', 'phone']);

        return response()->json([
            'success' => true,
            'data' => $clients,
        ]);
    }

    public function quickStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:clients,phone',
        ]);

        $client = Client::create([
            'name' => $request->name,
            'phone' => $request->phone,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Client created successfully',
            'data' => $client,
        ], 201);
    }
}