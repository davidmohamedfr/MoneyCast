<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useFormatCurrency } from '@/composables/useFormatCurrency';
import { computed } from 'vue';

interface MonthlyStats {
    income: number;
    expenses: number;
    net: number;
    transaction_count: number;
}

const props = defineProps<{
    stats: MonthlyStats;
    currency?: string;
}>();

const { formatCurrency, getBalanceColorClass } = useFormatCurrency();

const formattedIncome = computed(() => {
    return formatCurrency(props.stats.income, props.currency || 'EUR');
});

const formattedExpenses = computed(() => {
    return formatCurrency(props.stats.expenses, props.currency || 'EUR');
});

const formattedNet = computed(() => {
    return formatCurrency(props.stats.net, props.currency || 'EUR');
});

const netColor = computed(() => getBalanceColorClass(props.stats.net));

const netStatus = computed(() => {
    if (props.stats.net > 0) {
        return {
            icon: 'trending-up',
            label: 'Positive net income',
        };
    } else if (props.stats.net < 0) {
        return {
            icon: 'trending-down',
            label: 'Negative net income',
        };
    }
    return {
        icon: 'minus',
        label: 'Break even',
    };
});

const currentMonth = computed(() => {
    return new Date().toLocaleDateString('en-US', {
        month: 'long',
        year: 'numeric',
    });
});
</script>

<template>
    <Card class="flex h-[340px] flex-col">
        <CardHeader class="flex-shrink-0">
            <CardTitle>This Month</CardTitle>
            <CardDescription>{{ currentMonth }}</CardDescription>
        </CardHeader>
        <CardContent class="flex flex-1 flex-col">
            <div class="space-y-4">
                <!-- SECTION 1: Net Amount (PRIMARY) -->
                <div class="space-y-1.5">
                    <p class="text-sm font-medium text-muted-foreground">Net</p>
                    <div class="flex items-baseline gap-2">
                        <Icon
                            :name="netStatus.icon"
                            :class="netColor"
                            class="h-6 w-6"
                            :aria-label="netStatus.label"
                        />
                        <p :class="netColor" class="text-3xl font-bold">
                            {{ formattedNet }}
                        </p>
                    </div>
                    <p class="text-xs font-medium text-muted-foreground">
                        {{ netStatus.label }}
                    </p>
                </div>

                <!-- SECTION 2: Income/Expense (SECONDARY - ALWAYS VISIBLE) -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-1.5">
                            <Icon
                                name="arrow-down-left"
                                class="h-4 w-4 text-green-600 dark:text-green-400"
                                aria-label="Income"
                            />
                            <p
                                class="text-xs font-medium text-muted-foreground"
                            >
                                Income
                            </p>
                        </div>
                        <p
                            class="text-lg font-semibold text-green-600 dark:text-green-400"
                        >
                            {{ formattedIncome }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center gap-1.5">
                            <Icon
                                name="arrow-up-right"
                                class="h-4 w-4 text-red-600 dark:text-red-400"
                                aria-label="Expenses"
                            />
                            <p
                                class="text-xs font-medium text-muted-foreground"
                            >
                                Expenses
                            </p>
                        </div>
                        <p
                            class="text-lg font-semibold text-red-600 dark:text-red-400"
                        >
                            {{ formattedExpenses }}
                        </p>
                    </div>
                </div>

                <!-- Transaction Count -->
                <div class="space-y-1">
                    <p class="text-xs font-medium text-muted-foreground">
                        Transactions this month
                    </p>
                    <p class="text-lg font-semibold">
                        {{ stats.transaction_count }}
                    </p>
                </div>
            </div>

            <!-- Spacer to distribute remaining space -->
            <div class="flex-1"></div>
        </CardContent>
    </Card>
</template>
