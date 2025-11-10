<?php

namespace App\Domain\Dashboard\Services;

use App\Domain\Account\Repositories\AccountRepositoryInterface;
use App\Domain\Account\Services\AccountService;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;

class DashboardService
{
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
        $transactions = $this->transactionRepository->getAllForUser($userId);

        return $transactions->take($limit)->toArray();
    }

    private function getMonthlyStats(int $userId): array
    {
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');

        $transactions = $this->transactionRepository->getAllForUser($userId, [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $income = 0.0;
        $expenses = 0.0;

        foreach ($transactions as $transaction) {
            match ($transaction->type) {
                'income' => $income += (float) $transaction->amount,
                'expense' => $expenses += (float) $transaction->amount,
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
}
