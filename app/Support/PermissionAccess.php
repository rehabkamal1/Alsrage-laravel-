<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;

class PermissionAccess
{
    public const HIDE_TRANSACTIONS = 'hide_transactions';

    public const HIDE_DELEGATE_NUMBERS = 'hide_delegate_numbers';

    public static function isHiddenFor(?Authenticatable $user, string $restriction): bool
    {
        if (! $user) {
            return true;
        }

        if (data_get($user, 'role') === 'admin') {
            return false;
        }

        $restrictions = data_get($user, 'permissions', []);

        return is_array($restrictions) && in_array($restriction, $restrictions, true);
    }
}
