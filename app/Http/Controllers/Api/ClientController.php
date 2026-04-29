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
    public function index(Request $request)
    {
        $query = Client::query()->with('employee');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('additional_phone', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->integer('employee_id'));
        }

        if ($request->filled('client_type')) {
            $query->where('client_type', $request->string('client_type'));
        }

        $allowedSortBy = ['id', 'name', 'phone', 'client_type', 'created_at'];
        $sortBy = in_array($request->input('sort_by'), $allowedSortBy, true)
            ? $request->input('sort_by')
            : 'created_at';
        $sortDir = $request->input('sort_dir') === 'asc' ? 'asc' : 'desc';

        $clients = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate((int) $request->integer('per_page', 10))
            ->withQueryString();

        return ClientResource::collection($clients);
    }

    public function store(StoreClientRequest $request)
    {
        $data = $request->validated();
        $client = Client::create($data);
        return new ClientResource($client->load('employee'));
    }

    public function show(Client $client)
    {
        return new ClientResource($client->load('employee'));
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $data = $request->validated();
        $client->update($data);
        return new ClientResource($client->load('employee'));
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return response()->json(['message' => 'Client deleted successfully']);
    }

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1',
        ]);

        $query = $request->query('query');

        $clients = Client::with('employee')
            ->where('phone', 'like', "%{$query}%")
            ->limit(10)
            ->get();

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
            'client_type' => 'nullable|string|in:individual,office',
        ]);

        $client = Client::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'client_type' => $request->client_type ?? 'individual',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Client created successfully',
            'data' => $client,
        ], 201);
    }
}