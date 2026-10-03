<?php

namespace App\Support;

use App\Models\Setting;

class PaymentMethodClassifier
{
    public static function requiresBeneficiaryBank(?string $value): bool
    {
        if (! $value) {
            return false;
        }

        $method = Setting::query()
            ->where('group', 'payment_method')
            ->where(function ($query) use ($value) {
                $query->where('key', $value)
                    ->orWhere('label', $value);
            })
            ->first(['key', 'label']);

        $searchableValues = array_filter([
            $value,
            $method?->key,
            $method?->label,
        ]);

        foreach ($searchableValues as $searchableValue) {
            $normalized = mb_strtolower(trim($searchableValue));
            if (str_contains($normalized, 'بنك') || str_contains($normalized, 'bank')) {
                return true;
            }
        }

        return false;
    }
}
