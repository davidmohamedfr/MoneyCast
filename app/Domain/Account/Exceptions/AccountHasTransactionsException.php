<?php

namespace App\Domain\Account\Exceptions;

use App\Domain\Account\Models\Account;
use Exception;

class AccountHasTransactionsException extends Exception
{
    public function __construct(Account $account)
    {
        parent::__construct(
            "Cannot delete account '{$account->name}' because it has existing transactions. Archive it instead.",
            422
        );
    }
}
