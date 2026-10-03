<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Support\PermissionAccess;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $hideDelegateNumbers = PermissionAccess::isHiddenFor($request->user(), PermissionAccess::HIDE_DELEGATE_NUMBERS);
        $relations = ['client', 'saudiOffice', 'externalOffice', 'employee', 'tracking', 'attachments'];
        if (! PermissionAccess::isHiddenFor($request->user(), PermissionAccess::HIDE_TRANSACTIONS)) {
            $relations[] = 'transactions';
        }
        $orders = Order::query()
            ->with($relations)
            ->when($request->filled('search'), function ($query) use ($request, $hideDelegateNumbers) {
                $search = $request->string('search');
                $query->where(function ($q) use ($search, $hideDelegateNumbers) {
                    $q->where('id', 'like', "%{$search}%")
                        ->orWhere('visa_holder_name', 'like', "%{$search}%")
                        ->orWhere('visa_holder_phone', 'like', "%{$search}%")
                        ->orWhere('visa_number', 'like', "%{$search}%")
                        ->orWhere('service_type', 'like', "%{$search}%")
                        ->orWhere('id_number', 'like', "%{$search}%")
                        ->orWhere('passport_number', 'like', "%{$search}%")
                        ->orWhere('musaned_contract_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                    if (! $hideDelegateNumbers) {
                        $q->orWhere('sponsor_number', 'like', "%{$search}%")
                            ->orWhereHas('client', function ($clientQuery) use ($search) {
                                $clientQuery
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('phone', 'like', "%{$search}%");
                            });
                    } else {
                        $q->orWhereHas('client', fn($clientQuery) => $clientQuery->where('name', 'like', "%{$search}%"));
                    }
                });
            })
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('order_status'), fn($query) => $query->where('order_status', $request->string('order_status')))
            ->when($request->filled('tracking_status'), function ($query) use ($request) {
                $trackingStatus = $request->input('tracking_status');

                if ($trackingStatus === OrderTracking::WORKFLOW_STATUS_REVIEWED) {
                    $query->whereHas('tracking', fn($trackingQuery) => $trackingQuery
                        ->where('workflow_status', $trackingStatus)
                        ->orWhere(fn($legacyQuery) => $legacyQuery
                            ->whereNull('workflow_status')
                            ->where('is_authenticated', true)));
                } elseif ($trackingStatus === OrderTracking::WORKFLOW_STATUS_CERTIFIED) {
                    $query->where(fn($completedQuery) => $completedQuery
                        ->whereHas('tracking', fn($trackingQuery) => $trackingQuery
                            ->where('workflow_status', $trackingStatus))
                        ->orWhere('order_status', 'completed')
                        ->orWhere(fn($legacyQuery) => $legacyQuery
                            ->whereNull('order_status')
                            ->whereIn('status', ['completed', 'مكتمل'])));
                } else {
                    $query->whereHas(
                        'tracking',
                        fn($trackingQuery) => $trackingQuery->where('workflow_status', $trackingStatus)
                    );
                }
            })
            ->when($request->filled('visa_number'), fn($query) => $query->where('visa_number', 'like', '%' . $request->string('visa_number') . '%'))
            ->when($request->filled('service_type'), fn($query) => $query->where('service_type', $request->input('service_type')))
            ->when($request->filled('id_number'), fn($query) => $query->where('id_number', 'like', '%' . $request->string('id_number') . '%'))
            ->when($request->filled('employee_id'), fn($query) => $query->where('employee_id', $request->integer('employee_id')))
            ->when($request->filled('client_id'), fn($query) => $query->where('client_id', $request->integer('client_id')))
            ->when($request->filled('saudi_office_id'), fn($query) => $query->where('saudi_office_id', $request->integer('saudi_office_id')))
            ->when($request->filled('external_office_id'), fn($query) => $query->where('external_office_id', $request->integer('external_office_id')))
            ->when($request->filled('is_paid_by_office'), fn($query) => $query->where('is_paid_by_office', $request->boolean('is_paid_by_office')))
            ->when($request->filled('from_date'), fn($query) => $query->whereDate('created_at', '>=', $request->date('from_date')))
            ->when($request->filled('to_date'), fn($query) => $query->whereDate('created_at', '<=', $request->date('to_date')))
            ->when($request->boolean('without_tracking'), fn($query) => $query->whereDoesntHave('tracking'))
            ->orderBy(
                in_array($request->input('sort_by'), ['id', 'visa_holder_name', 'visa_holder_phone', 'visa_number', 'service_type', 'id_number', 'musaned_contract_number', 'status', 'order_status', 'total_price', 'musaned_paid', 'created_at', 'contract_date'], true)
                    ? $request->input('sort_by')
                    : 'id',
                $request->input('sort_dir') === 'asc' ? 'asc' : 'desc'
            )
            ->paginate((int) $request->integer('per_page', 15))
            ->withQueryString();

        return OrderResource::collection($orders);
    }

    public function show(Order $order)
    {
        $relations = ['client', 'employee', 'saudiOffice', 'externalOffice', 'tracking', 'attachments'];
        if (! PermissionAccess::isHiddenFor(request()->user(), PermissionAccess::HIDE_TRANSACTIONS)) {
            $relations[] = 'transactions';
        }
        $order->load($relations);
        return new OrderResource($order);
    }

    public function store(StoreOrderRequest $request)
    {
        if ($request->filled('nationality')) {
            $nationalityExists = Setting::where('group', 'nationality')
                ->where('key', $request->nationality)
                ->where('is_active', true)
                ->exists();

            if (!$nationalityExists) {
                throw ValidationException::withMessages([
                    'nationality' => ['الجنسية المحددة غير موجودة في الإعدادات']
                ]);
            }
        }

        if ($request->filled('profession')) {
            $professionExists = Setting::where('group', 'profession')
                ->where('key', $request->profession)
                ->where('is_active', true)
                ->exists();

            if (!$professionExists) {
                throw ValidationException::withMessages([
                    'profession' => ['المهنة المحددة غير موجودة في الإعدادات']
                ]);
            }
        }

        if ($request->filled('arrival_destination') && $request->filled('nationality')) {
            $destinationMatchesNationality = Setting::where('group', 'arrival_destination')
                ->where(function ($query) use ($request) {
                    $query->where('key', $request->arrival_destination)
                        ->orWhere('label', $request->arrival_destination);
                })
                ->where('nationality_key', $request->nationality)
                ->where('is_active', true)
                ->exists();

            if (!$destinationMatchesNationality) {
                throw ValidationException::withMessages([
                    'arrival_destination' => ['جهة القدوم لا تتوافق مع الجنسية المحددة']
                ]);
            }
        }

        if (!$request->client_id && $request->new_client_name && $request->new_client_phone) {
            $client = Client::create([
                'name' => $request->new_client_name,
                'phone' => $request->new_client_phone,
                'client_type' => $request->new_client_type ?? 'individual',
            ]);
            $request->merge(['client_id' => $client->id]);
        }

        $data = $request->validated();
        unset($data['attachment_files'], $data['attachment_titles']);
        $order = Order::create($data);
        $this->storeOrderAttachments($order, $request);

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        if ($request->filled('nationality')) {
            $nationalityExists = Setting::where('group', 'nationality')
                ->where('key', $request->nationality)
                ->where('is_active', true)
                ->exists();

            if (!$nationalityExists) {
                throw ValidationException::withMessages([
                    'nationality' => ['الجنسية المحددة غير موجودة في الإعدادات']
                ]);
            }
        }

        if ($request->filled('profession')) {
            $professionExists = Setting::where('group', 'profession')
                ->where('key', $request->profession)
                ->where('is_active', true)
                ->exists();

            if (!$professionExists) {
                throw ValidationException::withMessages([
                    'profession' => ['المهنة المحددة غير موجودة في الإعدادات']
                ]);
            }
        }

        if ($request->filled('arrival_destination') && $request->filled('nationality')) {
            $destinationMatchesNationality = Setting::where('group', 'arrival_destination')
                ->where(function ($query) use ($request) {
                    $query->where('key', $request->arrival_destination)
                        ->orWhere('label', $request->arrival_destination);
                })
                ->where('nationality_key', $request->nationality)
                ->where('is_active', true)
                ->exists();

            if (!$destinationMatchesNationality) {
                throw ValidationException::withMessages([
                    'arrival_destination' => ['جهة القدوم لا تتوافق مع الجنسية المحددة']
                ]);
            }
        }

        $data = $request->validated();
        if (PermissionAccess::isHiddenFor($request->user(), PermissionAccess::HIDE_DELEGATE_NUMBERS)) {
            unset($data['sponsor_number']);
        }
        unset($data['attachment_files'], $data['attachment_titles']);
        $order->update($data);
        $this->storeOrderAttachments($order, $request);

        return new OrderResource($order);
    }

    public function destroy(Order $order)
    {
        foreach ($order->attachments as $attachment) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($attachment->file_path);
            $attachment->delete();
        }
        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully.',
        ]);
    }

    private function storeOrderAttachments(Order $order, Request $request): void
    {
        $files = $request->file('attachment_files', []);
        $titles = $request->input('attachment_titles', []);

        foreach ($files as $index => $file) {
            if (!$file) {
                continue;
            }
            $title = (is_array($titles) && isset($titles[$index])) ? $titles[$index] : ('Attachment ' . ((int)$index + 1));
            $path = $file->store('attachments/orders/' . $order->id, 'public');
            $order->attachments()->create([
                'title' => $title,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }

    public function getOrdersWithoutTracking(Request $request)
    {
        $query = Order::query()
            ->with(['client', 'saudiOffice', 'externalOffice', 'employee'])
            ->whereDoesntHave('tracking');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('visa_holder_name', 'like', "%{$search}%")
                    ->orWhere('visa_number', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('saudi_office_id')) {
            $query->where('saudi_office_id', $request->saudi_office_id);
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        $orders = $query->orderBy('id', 'desc')
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return OrderResource::collection($orders);
    }
}
