<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useFormatCurrency } from '@/composables/useFormatCurrency';
import type { AccountStats } from '@/types/account';

defineProps<{
    stats: AccountStats;
    currency: string;
}>();

const { formatCurrency } = useFormatCurrency();
</script>

<template>
    <div class="grid gap-4 md:grid-cols-3">
        <Card>
            <CardHeader>
                <CardTitle class="text-sm font-medium">Total Income</CardTitle>
            </CardHeader>
            <CardContent>
                <p class="text-2xl font-bold text-green-600">
                    {{ formatCurrency(stats.total_income) }}
                </p>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle class="text-sm font-medium"
                    >Total Expenses</CardTitle
                >
            </CardHeader>
            <CardContent>
                <p class="text-2xl font-bold text-red-600">
                    {{ formatCurrency(stats.total_expenses) }}
                </p>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle class="text-sm font-medium"
                    >Current Balance</CardTitle
                >
            </CardHeader>
            <CardContent>
                <p
                    class="text-2xl font-bold"
                    :class="{
                        'text-red-600': stats.current_balance < 0,
                    }"
                >
                    {{ formatCurrency(stats.current_balance) }}
                </p>
            </CardContent>
        </Card>
    </div>
</template>
