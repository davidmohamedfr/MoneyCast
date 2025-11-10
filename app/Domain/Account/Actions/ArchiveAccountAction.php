<?php

namespace App\Domain\Account\Actions;

use App\Domain\Account\Models\Account;

class ArchiveAccountAction
{
    public function execute(Account $account): Account
    {
        // Note: Forecast validation will be added when Forecast domain is implemented
        // For now, allow archiving all accounts

        $account->delete();

        return $account;
    }
}
