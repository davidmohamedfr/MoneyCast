<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useFormatCurrency } from '@/composables/useFormatCurrency';
import type { AccountWithBalance } from '@/types/account';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    accountData: AccountWithBalance;
}>();

const { formatCurrency } = useFormatCurrency();

const accountTypeLabel = computed(() => {
    const types = {
        checking: 'Checking',
        savings: 'Savings',
        credit: 'Credit',
    };
    return (
        types[props.accountData.account.type] || props.accountData.account.type
    );
});

const balanceDifference = computed(() => {
    return (
        props.accountData.projected_balance - props.accountData.current_balance
    );
});
</script>

<template>
    <Card>
        <CardHeader>
            <div class="flex items-start justify-between">
                <div class="space-y-2">
                    <CardTitle>{{ accountData.account.name }}</CardTitle>
                    <div class="flex items-center gap-2">
                        <Badge variant="default">{{ accountTypeLabel }}</Badge>
                        <span class="text-sm text-muted-foreground">{{
                            accountData.account.bank
                        }}</span>
                    </div>
                </div>
                <Link :href="`/accounts/${accountData.account.id}`">
                    <Button variant="gradient" size="sm">View Details</Button>
                </Link>
            </div>
        </CardHeader>
        <CardContent>
            <div class="space-y-3">
                <div class="rounded-lg bg-muted/50 p-3">
                    <p class="text-sm font-medium text-muted-foreground">
                        Current Balance
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold"
                        :class="{
                            'text-red-600 dark:text-red-500':
                                accountData.current_balance < 0,
                        }"
                    >
                        {{ formatCurrency(accountData.current_balance) }}
                    </p>
                </div>
                <div class="rounded-lg bg-muted/30 p-3">
                    <p class="text-sm font-medium text-muted-foreground">
                        Projected Balance
                    </p>
                    <p class="mt-1 text-lg font-semibold">
                        {{ formatCurrency(accountData.projected_balance) }}
                    </p>
                </div>
                <div v-if="balanceDifference !== 0" class="border-t pt-2">
                    <p
                        class="text-sm"
                        :class="
                            balanceDifference > 0
                                ? 'text-green-600'
                                : 'text-red-600'
                        "
                    >
                        {{ balanceDifference > 0 ? '+' : ''
                        }}{{ formatCurrency(balanceDifference) }} projected
                    </p>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
