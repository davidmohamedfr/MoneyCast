<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import BalanceCard from '@/components/dashboard/BalanceCard.vue';
import MonthlyStats from '@/components/dashboard/MonthlyStats.vue';
import RecentTransactions from '@/components/dashboard/RecentTransactions.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import type { Transaction } from '@/types/transaction';
import { Head, Link } from '@inertiajs/vue3';

interface MonthlyStatsData {
    income: number;
    expenses: number;
    net: number;
    transaction_count: number;
}

interface AccountWithBalance {
    account: {
        id: number;
        name: string;
        type: string;
        bank: string;
        currency: string;
        archived_at: string | null;
    };
    current_balance: number;
    projected_balance: number;
}

interface PageProps {
    accounts: AccountWithBalance[];
    total_balance: number;
    recent_transactions: Transaction[];
    monthly_stats: MonthlyStatsData;
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
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
            <div class="grid gap-4 md:grid-cols-3">
                <BalanceCard :total-balance="total_balance" />
                <MonthlyStats :stats="monthly_stats" />
                <div class="md:col-span-1">
                    <div class="space-y-2">
                        <h3 class="text-sm font-semibold text-foreground">
                            Quick Actions
                        </h3>
                        <div class="grid gap-2">
                            <Link
                                href="/transactions/create"
                                class="group focus-standard flex items-start gap-3 rounded-lg border border-sidebar-border/70 p-4 transition-all hover:border-primary/50 hover:bg-accent hover:shadow-sm dark:border-sidebar-border dark:hover:bg-accent/50"
                            >
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-primary-foreground"
                                >
                                    <Icon name="plus" class="h-5 w-5" />
                                </div>
                                <div class="flex-1">
                                    <p
                                        class="font-medium text-foreground group-hover:text-primary"
                                    >
                                        New Transaction
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        Add income or expense
                                    </p>
                                </div>
                                <Icon
                                    name="chevron-right"
                                    class="h-4 w-4 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100"
                                />
                            </Link>

                            <Link
                                href="/accounts/create"
                                class="group focus-standard flex items-start gap-3 rounded-lg border border-sidebar-border/70 p-4 transition-all hover:border-primary/50 hover:bg-accent hover:shadow-sm dark:border-sidebar-border dark:hover:bg-accent/50"
                            >
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-primary-foreground"
                                >
                                    <Icon name="wallet" class="h-5 w-5" />
                                </div>
                                <div class="flex-1">
                                    <p
                                        class="font-medium text-foreground group-hover:text-primary"
                                    >
                                        New Account
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        Add checking or savings
                                    </p>
                                </div>
                                <Icon
                                    name="chevron-right"
                                    class="h-4 w-4 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100"
                                />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex-1">
                <RecentTransactions :transactions="recent_transactions" />
            </div>
        </div>
    </AppLayout>
</template>
