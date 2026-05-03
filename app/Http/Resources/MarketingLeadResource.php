<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\SaudiOffice;
use App\Models\ExternalOffice;
use App\Models\Client;

class MarketingLeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $sourceName = null;

        if ($this->source_type === 'saudi_office') {
            $office = SaudiOffice::find($this->source_id);
            $sourceName = $office?->name;
        } elseif ($this->source_type === 'external_office') {
            $office = ExternalOffice::find($this->source_id);
            $sourceName = $office?->name;
        } elseif ($this->source_type === 'client') {
            $client = Client::find($this->source_id);
            $sourceName = $client?->office_name ?? $client?->name;
        }

        return [
            'id' => $this->id,
            'source_id' => $this->source_id,
            'source_type' => $this->source_type,
            'source_name' => $sourceName,
            'source_phone' => $this->source_phone,
            'name' => $this->name,
            'phone' => $this->phone,
            'type' => $this->type,
            'type_text' => $this->getTypeText(),
            'status' => $this->status,
            'priority_level' => $this->priority_level,
            'notes' => $this->notes,
            'contact_date' => $this->contact_date,
            'next_followup_date' => $this->next_followup_date,
            'assigned_to' => $this->assigned_to,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function getTypeText(): string
    {
        return match ($this->type) {
            'saudi_office' => 'مكتب سعودي',
            'external_office' => 'مكتب خارجي',
            'service_office' => 'مكتب خدمات',
            default => $this->type,
        };
    }
}