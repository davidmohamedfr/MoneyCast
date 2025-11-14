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

const props = defineProps<{
    totalBalance: number;
    currency?: string;
    accountCount?: number;
}>();

const { formatCurrency, getBalanceColorClass } = useFormatCurrency();

const formattedBalance = computed(() => {
    return formatCurrency(props.totalBalance, props.currency || 'EUR');
});

const balanceColor = computed(() => getBalanceColorClass(props.totalBalance));

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
    <Card
        class="flex h-[340px] flex-col border-2 shadow-sm transition-shadow hover:shadow-md dark:shadow-none"
    >
        <CardHeader class="flex-shrink-0 pb-4">
            <div class="flex items-center justify-between">
                <div>
                    <CardTitle class="text-xl">Total Balance</CardTitle>
                    <CardDescription>Across all accounts</CardDescription>
                </div>
            </div>
        </CardHeader>
        <CardContent class="flex flex-1 flex-col">
            <div class="space-y-4">
                <!-- PRIMARY: Balance Amount -->
                <div class="space-y-2">
                    <div class="flex items-baseline gap-3">
                        <Icon
                            :name="balanceStatus.icon"
                            :class="balanceColor"
                            class="h-8 w-8"
                            :aria-label="balanceStatus.label"
                        />
                        <div
                            :class="balanceColor"
                            class="text-5xl leading-none font-bold"
                        >
                            {{ formattedBalance }}
                        </div>
                    </div>
                    <p class="text-sm font-medium text-muted-foreground">
                        {{ balanceStatus.label }}
                    </p>
                </div>

                <!-- SECONDARY: Account Summary -->
                <div v-if="accountCount !== undefined" class="space-y-2">
                    <div class="flex items-center gap-2">
                        <Icon
                            name="wallet"
                            class="h-4 w-4 text-muted-foreground"
                            aria-hidden="true"
                        />
                        <p class="text-sm font-medium text-muted-foreground">
                            Active Accounts
                        </p>
                    </div>
                    <p class="text-2xl font-semibold">
                        {{ accountCount }}
                    </p>
                </div>
            </div>

            <!-- Spacer to distribute remaining space -->
            <div class="flex-1"></div>
        </CardContent>
    </Card>
</template>
