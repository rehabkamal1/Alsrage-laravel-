<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMarketingLeadRequest;
use App\Http\Requests\UpdateMarketingLeadRequest;
use App\Http\Resources\MarketingLeadResource;
use App\Models\Client;
use App\Models\ExternalOffice;
use App\Models\MarketingLead;
use App\Models\SaudiOffice;
use App\Models\Setting;
use App\Rules\UniqueOfficeLicense;
use Illuminate\Http\Request;

class MarketingLeadController extends Controller
{
    public function index(Request $request)
    {
        $query = MarketingLead::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $sortField = $request->input('sort_field', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $leads = $query->paginate((int) $request->input('per_page', 15))->withQueryString();

        return MarketingLeadResource::collection($leads);
    }

    public function store(StoreMarketingLeadRequest $request)
    {
        $data = $request->validated();

        if ($request->source_type === 'saudi_office') {
            $source = SaudiOffice::find($request->source_id);
            if (!$source) {
                return response()->json(['message' => 'المكتب السعودي غير موجود'], 404);
            }
            $data['name'] = $source->name;
            $data['phone'] = $source->mobile ?? $source->phone;
        } elseif ($request->source_type === 'external_office') {
            $source = ExternalOffice::find($request->source_id);
            if (!$source) {
                return response()->json(['message' => 'المكتب الخارجي غير موجود'], 404);
            }
            $data['name'] = $source->name;
            if ($source->contacts && is_array($source->contacts) && count($source->contacts) > 0) {
                $data['phone'] = $source->contacts[0]['phone'] ?? null;
            } else {
                $data['phone'] = null;
            }
        } else {
            $source = Client::where('client_type', 'office')->find($request->source_id);
            if (!$source) {
                return response()->json(['message' => 'العميل غير موجود أو ليس مكتب خدمات'], 404);
            }
            $data['name'] = $source->name;
            $data['phone'] = $source->phone;
        }

        $lead = MarketingLead::create($data);

        return (new MarketingLeadResource($lead))
            ->response()
            ->setStatusCode(201);
    }

    public function show(MarketingLead $marketingLead)
    {
        return new MarketingLeadResource($marketingLead);
    }

    public function update(UpdateMarketingLeadRequest $request, MarketingLead $marketingLead)
    {
        $marketingLead->update($request->validated());

        return new MarketingLeadResource($marketingLead);
    }

    public function destroy(MarketingLead $marketingLead)
    {
        $marketingLead->delete();

        return response()->json(['message' => 'تم حذف العميل التسويقي بنجاح']);
    }

    public function getSaudiOffices()
    {
        $offices = SaudiOffice::select('id', 'name', 'mobile', 'phone')->get();
        return response()->json(['data' => $offices]);
    }

    public function getExternalOffices()
    {
        $offices = ExternalOffice::select('id', 'name', 'contacts')->get();
        return response()->json(['data' => $offices]);
    }

    public function getServiceOffices()
    {
        $clients = Client::where('client_type', 'office')
            ->select('id', 'name', 'office_name', 'phone')
            ->get();
        return response()->json(['data' => $clients]);
    }

    public function getStatuses()
    {
        $labels = [
            'new' => 'جديد',
            'contacted' => 'تم التواصل',
            'interested' => 'مهتم',
            'not_interested' => 'غير مهتم',
            'converted' => 'تم التحويل',
        ];
        $colors = [
            'new' => '#6366f1',
            'contacted' => '#06b6d4',
            'interested' => '#10b981',
            'not_interested' => '#ef4444',
            'converted' => '#8b5cf6',
        ];
        $statusKeys = MarketingLead::query()
            ->whereNotNull('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status')
            ->all();
        $statusKeys = array_values(array_unique(array_merge(['new'], $statusKeys)));

        $statuses = array_map(fn ($key) => [
            'key' => $key,
            'label' => $labels[$key] ?? $key,
            'color' => $colors[$key] ?? '#6c757d',
        ], $statusKeys);

        return response()->json(['data' => $statuses]);
    }

    public function getPriorityLevels()
    {
        $priorityLevels = Setting::query()
            ->where('group', 'priority_level')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['key', 'label', 'color']);

        if ($priorityLevels->isEmpty()) {
            $priorityLevels = collect([
                ['key' => 'medium', 'label' => 'متوسط', 'color' => '#f59e0b'],
            ]);
        }

        return response()->json(['data' => $priorityLevels]);
    }


    public function storeSaudiOffice(Request $request)
    {
        $request->merge([
            'license_number' => trim((string) $request->input('license_number', '')),
        ]);
        $request->validate([
            'name' => 'required|string|max:255',
            'license_number' => ['required', 'string', 'max:255', new UniqueOfficeLicense()],
            'mobile' => 'required|string|max:20',
            'responsible_employee' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
        ], [
            'license_number.required' => 'رقم الترخيص مطلوب.',
            'license_number.string' => 'رقم الترخيص يجب أن يكون نصًا صحيحًا.',
            'license_number.max' => 'رقم الترخيص يجب ألا يتجاوز 255 حرفًا.',
        ]);

        $office = SaudiOffice::create([
            'name' => $request->name,
            'license_number' => $request->license_number,
            'mobile' => $request->mobile,
            'responsible_employee' => $request->responsible_employee,
            'destination' => $request->destination,
            'phone' => $request->phone ?? null,
            'address' => $request->address ?? null,
        ]);

        return response()->json([
            'message' => 'تم إضافة المكتب السعودي بنجاح',
            'data' => $office
        ], 201);
    }

    public function storeExternalOfficeNew(Request $request)
    {
        $request->merge([
            'license_number' => trim((string) $request->input('license_number', '')),
        ]);
        $request->validate([
            'name' => 'required|string|max:255',
            'license_number' => ['required', 'string', 'max:255', new UniqueOfficeLicense()],
            'country' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ], [
            'license_number.required' => 'رقم الترخيص مطلوب.',
            'license_number.string' => 'رقم الترخيص يجب أن يكون نصًا صحيحًا.',
            'license_number.max' => 'رقم الترخيص يجب ألا يتجاوز 255 حرفًا.',
        ]);

        $office = ExternalOffice::create([
            'name' => $request->name,
            'license_number' => $request->license_number,
            'country' => $request->country,
            'contacts' => [
                ['name' => 'الرئيسي', 'phone' => $request->phone]
            ],
            'notes' => $request->notes ?? null,
        ]);

        return response()->json([
            'message' => 'تم إضافة المكتب الخارجي بنجاح',
            'data' => $office
        ], 201);
    }

    public function storeServiceOfficeNew(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:clients,phone',
        ]);

        $client = Client::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'client_type' => 'office',
            'category' => 'Service Office',
            'additional_phone' => $request->additional_phone ?? null,
            'address' => $request->address ?? null,
        ]);

        return response()->json([
            'message' => 'تم إضافة مكتب الخدمات بنجاح',
            'data' => $client
        ], 201);
    }
}