<?php

namespace App\Http\Controllers\Account;

use App\Domain\Account\Actions\ArchiveAccountAction;
use App\Domain\Account\Models\Account;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class ArchiveAccountController extends Controller
{
    public function __construct(
        private ArchiveAccountAction $archiveAction
    ) {}

    public function __invoke(Account $account): RedirectResponse
    {
        $this->authorize('delete', $account);

        try {
            $this->archiveAction->execute($account);

            return redirect()->route('accounts.index')
                ->with('success', 'Account archived successfully');
        } catch (\Exception $e) {
            return redirect()->route('accounts.index')
                ->with('error', $e->getMessage());
        }
    }
}
