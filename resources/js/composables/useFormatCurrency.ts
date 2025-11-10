/**
 * Currency formatting composable
 *
 * Provides utilities for formatting monetary values with proper currency symbols,
 * decimal places, and locale-specific formatting.
 */

export function useFormatCurrency() {
    /**
     * Format a number as currency
     *
     * @param amount - The amount to format
     * @param currency - Currency code (default: EUR)
     * @param locale - Locale for formatting (default: en-US)
     * @returns Formatted currency string
     */
    const formatCurrency = (
        amount: number | string,
        currency: string = 'EUR',
        locale: string = 'en-US',
    ): string => {
        const numericAmount =
            typeof amount === 'string' ? parseFloat(amount) : amount;

        return new Intl.NumberFormat(locale, {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(numericAmount);
    };

    /**
     * Format amount with sign prefix for transactions
     *
     * @param amount - The amount to format
     * @param type - Transaction type ('income', 'expense', 'transfer')
     * @param currency - Currency code (default: EUR)
     * @param locale - Locale for formatting (default: en-US)
     * @returns Formatted currency with sign prefix
     */
    const formatTransactionAmount = (
        amount: number | string,
        type: 'income' | 'expense' | 'transfer',
        currency: string = 'EUR',
        locale: string = 'en-US',
    ): string => {
        const formatted = formatCurrency(amount, currency, locale);
        const sign = type === 'expense' || type === 'transfer' ? '-' : '+';
        return `${sign}${formatted}`;
    };

    /**
     * Get color class based on balance value
     *
     * @param balance - The balance value
     * @returns Tailwind color classes
     */
    const getBalanceColorClass = (balance: number): string => {
        if (balance < 0) {
            return 'text-red-600 dark:text-red-400';
        }
        if (balance > 0) {
            return 'text-green-600 dark:text-green-400';
        }
        return 'text-muted-foreground';
    };

    /**
     * Get color class based on transaction type
     *
     * @param type - Transaction type
     * @returns Tailwind color classes
     */
    const getTransactionColorClass = (
        type: 'income' | 'expense' | 'transfer',
    ): string => {
        switch (type) {
            case 'income':
                return 'text-green-600 dark:text-green-400';
            case 'expense':
                return 'text-red-600 dark:text-red-400';
            case 'transfer':
                return 'text-blue-600 dark:text-blue-400';
            default:
                return 'text-muted-foreground';
        }
    };

    return {
        formatCurrency,
        formatTransactionAmount,
        getBalanceColorClass,
        getTransactionColorClass,
    };
}
