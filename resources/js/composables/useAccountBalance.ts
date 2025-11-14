import type { Account } from '@/types/account';
import { computed, type Ref } from 'vue';
import { useFormatCurrency } from './useFormatCurrency';

/**
 * Account balance composable
 *
 * Provides utilities for working with account balances including
 * formatting, color coding, and balance calculations.
 */

interface AccountWithBalance {
    account: Account;
    current_balance: number;
    projected_balance: number;
}

export function useAccountBalance(
    accountData: Ref<AccountWithBalance> | AccountWithBalance,
) {
    const { formatCurrency, getBalanceColorClass } = useFormatCurrency();

    // Extract reactive account data
    const account = computed(() =>
        'value' in accountData
            ? accountData.value.account
            : accountData.account,
    );

    const currentBalance = computed(() =>
        'value' in accountData
            ? accountData.value.current_balance
            : accountData.current_balance,
    );

    const projectedBalance = computed(() =>
        'value' in accountData
            ? accountData.value.projected_balance
            : accountData.projected_balance,
    );

    // Formatted balances
    const formattedCurrentBalance = computed(() =>
        formatCurrency(currentBalance.value, account.value.currency),
    );

    const formattedProjectedBalance = computed(() =>
        formatCurrency(projectedBalance.value, account.value.currency),
    );

    const formattedInitialBalance = computed(() =>
        formatCurrency(account.value.initial_balance, account.value.currency),
    );

    // Balance color classes
    const currentBalanceColorClass = computed(() =>
        getBalanceColorClass(currentBalance.value),
    );

    const projectedBalanceColorClass = computed(() =>
        getBalanceColorClass(projectedBalance.value),
    );

    // Balance status
    const isNegative = computed(() => currentBalance.value < 0);
    const isPositive = computed(() => currentBalance.value > 0);
    const isZero = computed(() => currentBalance.value === 0);

    // Projected balance comparison
    const projectedDifference = computed(
        () => projectedBalance.value - currentBalance.value,
    );

    const formattedProjectedDifference = computed(() =>
        formatCurrency(
            Math.abs(projectedDifference.value),
            account.value.currency,
        ),
    );

    const willIncrease = computed(() => projectedDifference.value > 0);
    const willDecrease = computed(() => projectedDifference.value < 0);
    const willStaySame = computed(() => projectedDifference.value === 0);

    return {
        // Account data
        account,
        currentBalance,
        projectedBalance,

        // Formatted values
        formattedCurrentBalance,
        formattedProjectedBalance,
        formattedInitialBalance,
        formattedProjectedDifference,

        // Color classes
        currentBalanceColorClass,
        projectedBalanceColorClass,

        // Status flags
        isNegative,
        isPositive,
        isZero,

        // Projection comparison
        projectedDifference,
        willIncrease,
        willDecrease,
        willStaySame,
    };
}
