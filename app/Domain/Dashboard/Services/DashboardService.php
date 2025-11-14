<?php

namespace App\Domain\Dashboard\Services;

use App\Domain\Account\Repositories\AccountRepositoryInterface;
use App\Domain\Account\Services\AccountService;
use App\Domain\Transaction\Enums\TransactionType;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;

class DashboardService
{
    private const TOP_CATEGORIES_LIMIT = 8;

    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private AccountService $accountService,
        private TransactionRepositoryInterface $transactionRepository
    ) {}

    public function getDashboardData(int $userId): array
    {
        $accounts = $this->accountService->getAccountsWithBalances($userId);
        $recentTransactions = $this->getRecentTransactions($userId, 10);
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

        $transactions = $this->transactionRepository->getAllForUser($userId, [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'type' => TransactionType::Expense->value,
            'exclude_opening_balance' => true,
        ]);

        $categorySpending = [];

        foreach ($transactions as $transaction) {
            if (! $transaction->category) {
                continue;
            }

            $categoryName = $transaction->category->name;

            if (! isset($categorySpending[$categoryName])) {
                $categorySpending[$categoryName] = [
                    'amount' => 0.0,
                    'count' => 0,
                ];
            }

            $categorySpending[$categoryName]['amount'] += (float) $transaction->amount;
            $categorySpending[$categoryName]['count']++;
        }

        // Sort by amount descending and format for chart
        uasort($categorySpending, fn ($a, $b) => $b['amount'] <=> $a['amount']);

        $formattedData = [];
        foreach ($categorySpending as $category => $data) {
            $formattedData[] = [
                'category' => $category,
                'amount' => (float) round($data['amount'], 2),
                'transaction_count' => $data['count'],
                'color' => '',  // Will be assigned in frontend
            ];
        }

        // Limit to top categories
        return array_slice($formattedData, 0, self::TOP_CATEGORIES_LIMIT);
    }
}
