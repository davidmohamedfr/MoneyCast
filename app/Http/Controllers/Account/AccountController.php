<?php

namespace App\Http\Controllers\Account;

use App\Domain\Account\Actions\CreateAccountAction;
use App\Domain\Account\Actions\DeleteAccountAction;
use App\Domain\Account\Actions\UpdateAccountAction;
use App\Domain\Account\Data\AccountData;
use App\Domain\Account\Models\Account;
use App\Domain\Account\Services\AccountService;
use App\Domain\Transaction\Services\TransactionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\StoreAccountRequest;
use App\Http\Requests\Account\UpdateAccountRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function __construct(
        private AccountService $accountService,
        private TransactionService $transactionService,
        private CreateAccountAction $createAction,
        private UpdateAccountAction $updateAction,
        private DeleteAccountAction $deleteAction
    ) {}

    public function index(): Response
    {
        $filters = array_filter([
            'search' => request('search'),
            'type' => request('type'),
            'bank' => request('bank'),
        ]);

        $accounts = $this->accountService->getAccountsWithBalances(auth()->id(), $filters);
        $archivedAccounts = $this->accountService->getArchivedAccountsWithBalances(auth()->id());

        return Inertia::render('account/Index', [
            'accounts' => $accounts,
            'archivedAccounts' => $archivedAccounts,
            'filters' => $filters,
        ]);
    }

    public function show(Account $account): Response
    {
        $this->authorize('view', $account);

        $filters = array_filter([
            'payee' => request('payee'),
            'amount_min' => request('amount_min'),
            'amount_max' => request('amount_max'),
            'category_id' => request('category_id'),
            'start_date' => request('start_date'),
            'end_date' => request('end_date'),
        ]);

        $transactions = $this->transactionService->getPaginatedTransactionsForAccount(
            $account->id,
            $filters,
            20
        );

        $stats = $this->transactionService->calculateAccountStats($account->id);

        return Inertia::render('account/Show', [
            'account' => $account,
            'transactions' => $transactions,
            'stats' => [
                'total_income' => $stats['total_income'],
                'total_expenses' => $stats['total_expenses'],
                'current_balance' => $this->accountService->calculateCurrentBalance($account),
            ],
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('account/Create');
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $validated['user_id'] = auth()->id();

            $data = AccountData::from($validated);

            $this->createAction->execute($data);

            return redirect()->route('accounts.index')
                ->with('success', 'Account created successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Failed to create account', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create account. Please try again.');
        } catch (\Exception $e) {
            Log::error('Unexpected error creating account', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'An unexpected error occurred. Please try again.');
        }
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
        } catch (\App\Domain\Account\Exceptions\AccountHasTransactionsException $e) {
            Log::warning('Attempted to delete account with transactions', [
                'user_id' => auth()->id(),
                'account_id' => $account->id,
            ]);

            return redirect()->route('accounts.index')
                ->with('error', 'Cannot delete account with existing transactions. Please delete all transactions first.');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Failed to delete account', [
                'user_id' => auth()->id(),
                'account_id' => $account->id,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return redirect()->route('accounts.index')
                ->with('error', 'Failed to delete account. Please try again.');
        } catch (\Exception $e) {
            Log::error('Unexpected error deleting account', [
                'user_id' => auth()->id(),
                'account_id' => $account->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('accounts.index')
                ->with('error', 'An unexpected error occurred while deleting the account.');
        }
    }
}
