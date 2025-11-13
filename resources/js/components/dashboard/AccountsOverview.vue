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
import { useFormatCurrency } from '@/composables/useFormatCurrency';
import type { AccountWithBalance } from '@/types/account';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    accounts: AccountWithBalance[];
}

const props = defineProps<Props>();

const { formatCurrency, getBalanceColorClass } = useFormatCurrency();

/**
 * Get icon name based on account type
 */
const getAccountIcon = (type: 'checking' | 'savings' | 'credit'): string => {
    const icons = {
        checking: 'wallet',
        savings: 'piggy-bank',
        credit: 'credit-card',
    };
    return icons[type] || 'wallet';
};

/**
 * Get human-readable label for account type
 */
const getAccountTypeLabel = (
    type: 'checking' | 'savings' | 'credit',
): string => {
    const labels = {
        checking: 'Checking',
        savings: 'Savings',
        credit: 'Credit',
    };
    return labels[type] || type;
};

/**
 * Sort accounts: active first, then by name
 */
const sortedAccounts = computed(() => {
    return [...props.accounts].sort((a, b) => {
        // Sort by name
        return a.account.name.localeCompare(b.account.name);
    });
});

const viewAllAccounts = () => {
    router.visit('/accounts');
};
</script>

<template>
    <Card>
        <CardHeader>
            <div class="flex items-center justify-between">
                <div>
                    <CardTitle>Your Accounts</CardTitle>
                    <CardDescription>Overview of all your accounts</CardDescription>
                </div>
                <Button
                    v-if="accounts.length > 0"
                    variant="gradient"
                    size="sm"
                    @click="viewAllAccounts"
                >
                    View All
                </Button>
            </div>
        </CardHeader>
        <CardContent>
            <div v-if="accounts.length === 0" class="py-8 text-center">
                <Icon
                    name="wallet"
                    class="mx-auto mb-3 h-12 w-12 text-muted-foreground"
                />
                <p class="text-sm font-medium text-foreground">
                    No accounts yet
                </p>
                <p class="mb-4 text-xs text-muted-foreground">
                    Create your first account to start tracking your finances
                </p>
                <Button
                    variant="outline"
                    size="sm"
                    @click="router.visit('/accounts/create')"
                >
                    <Icon name="plus" class="mr-2 h-4 w-4" aria-hidden="true" />
                    Create Account
                </Button>
            </div>

            <div v-else class="space-y-2">
                <div
                    v-for="accountData in sortedAccounts"
                    :key="accountData.account.id"
                    class="flex cursor-pointer items-center justify-between rounded-lg p-3 transition-colors hover:bg-accent"
                    @click="router.visit(`/accounts/${accountData.account.id}`)"
                >
                    <div class="flex min-w-0 flex-1 items-center gap-3">
                        <div
                            :class="[
                                'flex h-10 w-10 shrink-0 items-center justify-center rounded-full',
                                accountData.account.type === 'checking'
                                    ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                    : accountData.account.type === 'savings'
                                      ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                      : 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                            ]"
                            :aria-label="getAccountTypeLabel(accountData.account.type)"
                        >
                            <Icon
                                :name="getAccountIcon(accountData.account.type)"
                                class="h-5 w-5"
                            />
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="truncate font-medium text-foreground">
                                    {{ accountData.account.name }}
                                </p>
                                <Badge variant="outline" class="shrink-0 text-xs">
                                    {{ getAccountTypeLabel(accountData.account.type) }}
                                </Badge>
                            </div>
                            <p class="text-sm text-muted-foreground">
                                {{ accountData.account.bank }}
                            </p>
                        </div>
                    </div>

                    <div class="flex shrink-0 flex-col items-end gap-1">
                        <div
                            :class="getBalanceColorClass(accountData.current_balance)"
                            class="text-lg font-semibold tabular-nums"
                        >
                            {{
                                formatCurrency(
                                    accountData.current_balance,
                                    accountData.account.currency,
                                )
                            }}
                        </div>
                        <span class="text-xs text-muted-foreground">
                            Current Balance
                        </span>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
