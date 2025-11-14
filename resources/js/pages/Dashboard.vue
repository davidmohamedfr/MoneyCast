<script setup lang="ts">
import AccountsOverview from '@/components/dashboard/AccountsOverview.vue';
import BalanceCard from '@/components/dashboard/BalanceCard.vue';
import MonthlyStats from '@/components/dashboard/MonthlyStats.vue';
import RecentTransactions from '@/components/dashboard/RecentTransactions.vue';
import SpendingChart from '@/components/dashboard/SpendingChart.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import type { AccountWithBalance } from '@/types/account';
import type { Transaction } from '@/types/transaction';
import { Head } from '@inertiajs/vue3';

interface MonthlyStatsData {
    income: number;
    expenses: number;
    net: number;
    transaction_count: number;
}

interface CategorySpending {
    category: string;
    amount: number;
    transaction_count: number;
    color: string;
}

interface PageProps {
    accounts: AccountWithBalance[];
    total_balance: number;
    recent_transactions: Transaction[];
    monthly_stats: MonthlyStatsData;
    category_spending: CategorySpending[];
}

defineProps<PageProps>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 overflow-x-auto p-4">
            <!-- PRIMARY: Total Balance, This Month & Spending Breakdown -->
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-1">
                    <BalanceCard
                        :total-balance="total_balance"
                        :account-count="accounts.length"
                    />
                </div>
                <div class="lg:col-span-1">
                    <MonthlyStats :stats="monthly_stats" />
                </div>
                <div class="lg:col-span-1">
                    <SpendingChart :category-data="category_spending" />
                </div>
            </div>

            <!-- SECONDARY: Your Accounts & Recent Transactions -->
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="lg:col-span-1">
                    <AccountsOverview :accounts="accounts" />
                </div>
                <div class="lg:col-span-1">
                    <RecentTransactions :transactions="recent_transactions" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
