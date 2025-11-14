<?php

namespace App\Http\Controllers\Transaction;

use App\Domain\Account\Repositories\AccountRepositoryInterface;
use App\Domain\Category\Repositories\CategoryRepositoryInterface;
use App\Domain\Transaction\Actions\CreateTransactionAction;
use App\Domain\Transaction\Actions\DeleteTransactionAction;
use App\Domain\Transaction\Actions\UpdateTransactionAction;
use App\Domain\Transaction\Data\TransactionData;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\Transaction\Services\TransactionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService,
        private AccountRepositoryInterface $accountRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private CreateTransactionAction $createAction,
        private UpdateTransactionAction $updateAction,
        private DeleteTransactionAction $deleteAction
    ) {}

    public function index(): Response
    {
        $filters = array_filter([
            'search' => request('search'),
            'type' => request('type'),
            'category_id' => request('category_id'),
            'start_date' => request('start_date'),
            'end_date' => request('end_date'),
        ]);

        $transactions = $this->transactionService->getTransactionsForUser(auth()->id(), $filters);
        $categories = $this->categoryRepository->all();

        return Inertia::render('transaction/Index', [
            'transactions' => $transactions,
            'filters' => $filters,
            'categories' => $categories,
        ]);
    }

    public function create(): Response
    {
        $accounts = $this->accountRepository->getAllForUser(auth()->id());
        $categories = $this->categoryRepository->all();

        return Inertia::render('transaction/Create', [
            'accounts' => $accounts,
            'categories' => $categories,
        ]);
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();

        $data = TransactionData::from($validated);

        $this->createAction->execute($data);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction created successfully');
    }

    public function edit(Transaction $transaction): Response
    {
        $this->authorize('update', $transaction);

        $accounts = $this->accountRepository->getAllForUser(auth()->id());
        $categories = $this->categoryRepository->all();

        return Inertia::render('transaction/Edit', [
            'transaction' => $transaction->load(['account', 'category']),
            'accounts' => $accounts,
            'categories' => $categories,
        ]);
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);

        $data = TransactionData::from($request->validated());
        $this->updateAction->execute($transaction, $data);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction updated successfully');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        try {
            $this->deleteAction->execute($transaction);

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction deleted successfully');
        } catch (\InvalidArgumentException $e) {
            Log::warning('Invalid transaction deletion attempt', [
                'user_id' => auth()->id(),
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('transactions.index')
                ->with('error', $e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Failed to delete transaction', [
                'user_id' => auth()->id(),
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return redirect()->route('transactions.index')
                ->with('error', 'Failed to delete transaction. Please try again.');
        } catch (\Exception $e) {
            Log::error('Unexpected error deleting transaction', [
                'user_id' => auth()->id(),
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('transactions.index')
                ->with('error', 'An unexpected error occurred while deleting the transaction.');
        }
    }
}
