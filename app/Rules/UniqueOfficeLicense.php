<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueOfficeLicense implements ValidationRule
{
    public function __construct(
        private readonly ?string $currentTable = null,
        private readonly ?int $currentId = null,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        foreach (['saudi_offices', 'external_offices'] as $table) {
            $query = DB::table($table)->where('license_number', $value);

            if ($table === $this->currentTable && $this->currentId !== null) {
                $query->where('id', '!=', $this->currentId);
            }

            if ($query->exists()) {
                $fail('رقم الترخيص مستخدم بالفعل لمكتب سعودي أو خارجي.');
                return;
            }
        }
    }
}
