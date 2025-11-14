<?php

namespace App\Domain\Dashboard\Services;

use App\Domain\Account\Repositories\AccountRepositoryInterface;
use App\Domain\Account\Services\AccountService;
use App\Domain\Transaction\Enums\TransactionType;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;

class DashboardService
{
    private const TOP_CATEGORIES_LIMIT = 8;

    private const RECENT_TRANSACTIONS_LIMIT = 10;

    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private AccountService $accountService,
        private TransactionRepositoryInterface $transactionRepository
    ) {}

    public function getDashboardData(int $userId): array
    {
        $accounts = $this->accountService->getAccountsWithBalances($userId);
        $recentTransactions = $this->getRecentTransactions($userId, self::RECENT_TRANSACTIONS_LIMIT);
        $monthlyStats = $this->getMonthlyStats($userId);
        $categorySpending = $this->getCategorySpending($userId);
        $totalBalance = $this->calculateTotalBalance($accounts);

        return [
            'accounts' => $accounts,
            'total_balance' => (float) round($totalBalance, 2),
            'recent_transactions' => $recentTransactions,
            'monthly_stats' => [
                'income' => (float) round($monthlyStats['income'], 2),
                'expenses' => (float) round($monthlyStats['expenses'], 2),
                'net' => (float) round($monthlyStats['net'], 2),
                'transaction_count' => $monthlyStats['transaction_count'],
            ],
            'category_spending' => $categorySpending,
        ];
    }

    private function calculateTotalBalance(array $accounts): float
    {
        $total = 0.0;

        foreach ($accounts as $accountData) {
            $total += $accountData['current_balance'];
        }

        return $total;
    }

    private function getRecentTransactions(int $userId, int $limit = 10): array
    {
        $transactions = $this->transactionRepository->getAllForUser($userId, [
            'exclude_opening_balance' => true,
        ]);

        return $transactions->take($limit)->toArray();
    }

    private function getMonthlyStats(int $userId): array
    {
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');

        $transactions = $this->transactionRepository->getAllForUser($userId, [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'exclude_opening_balance' => true,
        ]);

        $income = 0.0;
        $expenses = 0.0;

        foreach ($transactions as $transaction) {
            match ($transaction->type) {
                TransactionType::Income => $income += (float) $transaction->amount,
                TransactionType::Expense => $expenses += (float) $transaction->amount,
                default => null,
            };
        }

        return [
            'income' => (float) $income,
            'expenses' => (float) $expenses,
            'net' => (float) ($income - $expenses),
            'transaction_count' => $transactions->count(),
        ];
    }

    private function getCategorySpending(int $userId): array
    {
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');

        // Use database-level aggregation for performance
        $categorySpending = \App\Domain\Transaction\Models\Transaction::query()
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $userId)
            ->where('transactions.type', TransactionType::Expense->value)
            ->where('transactions.is_opening_balance', false)
            ->whereDate('transactions.date', '>=', $startDate)
            ->whereDate('transactions.date', '<=', $endDate)
            ->whereNotNull('transactions.category_id')
            ->selectRaw('categories.name as category, SUM(transactions.amount) as amount, COUNT(*) as transaction_count')
            ->groupBy('categories.name')
            ->orderByDesc('amount')
            ->limit(self::TOP_CATEGORIES_LIMIT)
            ->get();

        // Format for chart
        return $categorySpending->map(function ($item) {
            return [
                'category' => $item->category,
                'amount' => (float) round($item->amount, 2),
                'transaction_count' => $item->transaction_count,
                'color' => '',  // Will be assigned in frontend
            ];
        })->toArray();
    }
}
