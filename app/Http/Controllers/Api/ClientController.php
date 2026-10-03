<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Support\PermissionAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $hideDelegateNumbers = PermissionAccess::isHiddenFor($request->user(), PermissionAccess::HIDE_DELEGATE_NUMBERS);
        $hideTransactions = PermissionAccess::isHiddenFor($request->user(), PermissionAccess::HIDE_TRANSACTIONS);
        $relations = ['employee', 'orders', 'orders.tracking'];
        if (! $hideTransactions) {
            $relations = [...$relations, 'orders.transactions', 'transactions'];
        }
        $query = Client::query()->with($relations);

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search, $hideDelegateNumbers) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
                if (! $hideDelegateNumbers) {
                    $q->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('additional_phone', 'like', "%{$search}%");
                }
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->integer('employee_id'));
        }

        if ($request->filled('client_type')) {
            $query->where('client_type', $request->string('client_type'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->date('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->date('to_date'));
        }

        $allowedSortBy = ['id', 'name', 'client_type', 'created_at'];
        if (! $hideDelegateNumbers) {
            $allowedSortBy[] = 'phone';
        }
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
        return new ClientResource($client->load(['employee', 'orders']));
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $data = $request->validated();
        if (PermissionAccess::isHiddenFor($request->user(), PermissionAccess::HIDE_DELEGATE_NUMBERS)) {
            unset($data['phone'], $data['additional_phone']);
        }
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
            ->where(function ($builder) use ($query, $request) {
                $builder->where('name', 'like', "%{$query}%");
                if (! PermissionAccess::isHiddenFor($request->user(), PermissionAccess::HIDE_DELEGATE_NUMBERS)) {
                    $builder->orWhere('phone', 'like', "%{$query}%");
                }
            })
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => ClientResource::collection($clients)->resolve($request),
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
            'data' => (new ClientResource($client))->resolve($request),
        ], 201);
    }
}
