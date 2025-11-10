<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { computed } from 'vue';

const props = defineProps<{
    totalBalance: number;
    currency?: string;
}>();

const currencyFormatter = computed(() => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: props.currency || 'EUR',
    });
});

const formattedBalance = computed(() => {
    return currencyFormatter.value.format(props.totalBalance);
});

const balanceColor = computed(() => {
    if (props.totalBalance > 0) {
        return 'text-green-600 dark:text-green-400';
    } else if (props.totalBalance < 0) {
        return 'text-red-600 dark:text-red-400';
    }
    return 'text-muted-foreground';
});

const balanceStatus = computed(() => {
    if (props.totalBalance > 0) {
        return {
            icon: 'trending-up',
            label: 'Positive balance',
        };
    } else if (props.totalBalance < 0) {
        return {
            icon: 'trending-down',
            label: 'Negative balance - attention needed',
        };
    }
    return {
        icon: 'minus',
        label: 'Neutral balance',
    };
});
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Total Balance</CardTitle>
            <CardDescription>Across all accounts</CardDescription>
        </CardHeader>
        <CardContent class="space-y-2">
            <div class="flex items-center gap-2">
                <Icon
                    :name="balanceStatus.icon"
                    :class="balanceColor"
                    class="h-6 w-6"
                    aria-hidden="true"
                />
                <div :class="balanceColor" class="text-4xl font-bold">
                    {{ formattedBalance }}
                </div>
            </div>
            <p class="text-sm text-muted-foreground">
                {{ balanceStatus.label }}
            </p>
        </CardContent>
    </Card>
</template>
