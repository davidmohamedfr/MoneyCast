<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import type { Transaction } from '@/types/transaction';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    transactions: Transaction[];
}>();

const currencyFormatter = computed(() => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'EUR',
    });
});

const formatAmount = (amount: string, type: string) => {
    const formatted = currencyFormatter.value.format(parseFloat(amount));
    return type === 'expense' ? `-${formatted}` : `+${formatted}`;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
    });
};

const getTypeColor = (type: string) => {
    switch (type) {
        case 'income':
            return 'text-green-600 dark:text-green-400';
        case 'expense':
            return 'text-red-600 dark:text-red-400';
        case 'transfer':
            return 'text-blue-600 dark:text-blue-400';
        default:
            return '';
    }
};

const getTypeIcon = (type: string) => {
    switch (type) {
        case 'income':
            return 'arrow-down-left';
        case 'expense':
            return 'arrow-up-right';
        case 'transfer':
            return 'arrow-right-left';
        default:
            return 'circle';
    }
};

const getTypeLabel = (type: string) => {
    switch (type) {
        case 'income':
            return 'Income';
        case 'expense':
            return 'Expense';
        case 'transfer':
            return 'Transfer';
        default:
            return type;
    }
};

const viewAllTransactions = () => {
    router.visit('/transactions');
};
</script>

<template>
    <Card>
        <CardHeader>
            <div class="flex items-center justify-between">
                <div>
                    <CardTitle>Recent Transactions</CardTitle>
                    <CardDescription
                        >Your latest financial activity</CardDescription
                    >
                </div>
                <Button variant="ghost" size="sm" @click="viewAllTransactions">
                    View All
                </Button>
            </div>
        </CardHeader>
        <CardContent>
            <div v-if="transactions.length === 0" class="py-8 text-center">
                <Icon
                    name="receipt"
                    class="mx-auto mb-3 h-12 w-12 text-muted-foreground"
                />
                <p class="text-sm font-medium text-foreground">
                    No transactions yet
                </p>
                <p class="mb-4 text-xs text-muted-foreground">
                    Start by adding your first transaction
                </p>
                <Button
                    variant="outline"
                    size="sm"
                    @click="router.visit('/transactions/create')"
                >
                    <Icon name="plus" class="mr-2 h-4 w-4" />
                    Create Transaction
                </Button>
            </div>

            <div v-else class="space-y-2">
                <div
                    v-for="transaction in transactions"
                    :key="transaction.id"
                    class="flex items-center justify-between rounded-lg p-3 transition-colors hover:bg-accent"
                >
                    <div class="flex min-w-0 flex-1 items-center gap-3">
                        <div
                            :class="[
                                'flex h-10 w-10 shrink-0 items-center justify-center rounded-full',
                                transaction.type === 'income'
                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                    : transaction.type === 'expense'
                                      ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                      : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                            ]"
                        >
                            <Icon
                                :name="getTypeIcon(transaction.type)"
                                class="h-5 w-5"
                                aria-hidden="true"
                            />
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="truncate font-medium text-foreground">
                                    {{ transaction.payee }}
                                </p>
                                <Badge
                                    v-if="transaction.category"
                                    variant="outline"
                                    class="shrink-0 text-xs"
                                >
                                    {{ transaction.category.name }}
                                </Badge>
                            </div>
                            <p class="text-sm text-muted-foreground">
                                {{ formatDate(transaction.date) }}
                                <span v-if="transaction.account" class="ml-2">
                                    • {{ transaction.account.name }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="flex shrink-0 flex-col items-end gap-1">
                        <div
                            :class="getTypeColor(transaction.type)"
                            class="text-lg font-semibold tabular-nums"
                        >
                            {{
                                formatAmount(
                                    transaction.amount,
                                    transaction.type,
                                )
                            }}
                        </div>
                        <span class="text-xs text-muted-foreground">
                            {{ getTypeLabel(transaction.type) }}
                        </span>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
