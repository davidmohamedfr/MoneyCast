<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
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

const currencyFormatter = computed(() => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: props.currency || 'EUR',
    });
});

const formattedIncome = computed(() => {
    return currencyFormatter.value.format(props.stats.income);
});

const formattedExpenses = computed(() => {
    return currencyFormatter.value.format(props.stats.expenses);
});

const formattedNet = computed(() => {
    return currencyFormatter.value.format(props.stats.net);
});

const netColor = computed(() => {
    if (props.stats.net > 0) {
        return 'text-green-600 dark:text-green-400';
    } else if (props.stats.net < 0) {
        return 'text-red-600 dark:text-red-400';
    }
    return 'text-muted-foreground';
});

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
    <Card>
        <CardHeader>
            <CardTitle>Monthly Overview</CardTitle>
            <CardDescription>{{ currentMonth }}</CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-1.5">
                        <Icon
                            name="arrow-down-left"
                            class="h-4 w-4 text-green-600 dark:text-green-400"
                            aria-hidden="true"
                        />
                        <p class="text-sm text-muted-foreground">Income</p>
                    </div>
                    <p
                        class="text-2xl font-semibold text-green-600 dark:text-green-400"
                    >
                        {{ formattedIncome }}
                    </p>
                </div>

                <div class="space-y-1">
                    <div class="flex items-center gap-1.5">
                        <Icon
                            name="arrow-up-right"
                            class="h-4 w-4 text-red-600 dark:text-red-400"
                            aria-hidden="true"
                        />
                        <p class="text-sm text-muted-foreground">Expenses</p>
                    </div>
                    <p
                        class="text-2xl font-semibold text-red-600 dark:text-red-400"
                    >
                        {{ formattedExpenses }}
                    </p>
                </div>
            </div>

            <Separator />

            <div class="space-y-1">
                <p class="text-sm text-muted-foreground">Net</p>
                <div class="flex items-center gap-2">
                    <Icon
                        :name="netStatus.icon"
                        :class="netColor"
                        class="h-5 w-5"
                        aria-hidden="true"
                    />
                    <p :class="netColor" class="text-3xl font-bold">
                        {{ formattedNet }}
                    </p>
                </div>
                <p class="text-xs text-muted-foreground">
                    {{ netStatus.label }}
                </p>
            </div>

            <div class="space-y-1">
                <p class="text-sm text-muted-foreground">Transactions</p>
                <p class="text-xl font-semibold">
                    {{ stats.transaction_count }}
                </p>
            </div>
        </CardContent>
    </Card>
</template>
