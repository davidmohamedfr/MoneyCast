<?php

namespace App\Http\Controllers\Account;

use App\Domain\Account\Actions\CreateAccountAction;
use App\Domain\Account\Actions\DeleteAccountAction;
use App\Domain\Account\Actions\UpdateAccountAction;
use App\Domain\Account\Data\AccountData;
use App\Domain\Account\Models\Account;
use App\Domain\Account\Services\AccountService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\StoreAccountRequest;
use App\Http\Requests\Account\UpdateAccountRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function __construct(
        private AccountService $accountService,
        private CreateAccountAction $createAction,
        private UpdateAccountAction $updateAction,
        private DeleteAccountAction $deleteAction
    ) {}

    public function index(): Response
    {
        $accounts = $this->accountService->getAccountsWithBalances(auth()->id());

        return Inertia::render('account/Index', [
            'accounts' => $accounts,
        ]);
    }

    public function show(Account $account): Response
    {
        $this->authorize('view', $account);

        return Inertia::render('account/Show', [
            'account' => $account,
            'current_balance' => $this->accountService->calculateCurrentBalance($account),
            'projected_balance' => $this->accountService->calculateProjectedBalance($account),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('account/Create');
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();

        $data = AccountData::from($validated);

        $this->createAction->execute($data);

        return redirect()->route('accounts.index')
            ->with('success', 'Account created successfully');
    }

    public function edit(Account $account): Response
    {
        $this->authorize('update', $account);

        return Inertia::render('account/Edit', [
            'account' => $account,
        ]);
    }

    public function update(UpdateAccountRequest $request, Account $account): RedirectResponse
    {
        $this->authorize('update', $account);

        $data = AccountData::from($request->validated());
        $this->updateAction->execute($account, $data);

        return redirect()->route('accounts.index')
            ->with('success', 'Account updated successfully');
    }

    public function destroy(Account $account): RedirectResponse
    {
        $this->authorize('delete', $account);

        try {
            $this->deleteAction->execute($account);
            return redirect()->route('accounts.index')
                ->with('success', 'Account deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
