<script setup lang="ts">
import AccountList from '@/components/account/AccountList.vue';
import ArchivedAccountsSection from '@/components/account/ArchivedAccountsSection.vue';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { useFormatCurrency } from '@/composables/useFormatCurrency';
import AppLayout from '@/layouts/AppLayout.vue';
import type { AccountWithBalance } from '@/types/account';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface PageProps {
    accounts: AccountWithBalance[];
    archivedAccounts: AccountWithBalance[];
}

const props = defineProps<PageProps>();

const { formatCurrency } = useFormatCurrency();

const totalCurrentBalance = computed(() => {
    return props.accounts.reduce((sum, acc) => sum + acc.current_balance, 0);
});

const totalProjectedBalance = computed(() => {
    return props.accounts.reduce((sum, acc) => sum + acc.projected_balance, 0);
});
</script>

<template>
    <AppLayout>
        <Head title="Accounts" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <Heading>Accounts</Heading>
                <Link href="/accounts/create">
                    <Button>Add Account</Button>
                </Link>
            </div>

            <div v-if="accounts.length > 0" class="space-y-8">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-lg border p-4">
                        <p class="text-sm text-muted-foreground">
                            Total Current Balance
                        </p>
                        <p class="text-3xl font-bold">
                            {{ formatCurrency(totalCurrentBalance) }}
                        </p>
                    </div>
                    <div class="rounded-lg border p-4">
                        <p class="text-sm text-muted-foreground">
                            Total Projected Balance
                        </p>
                        <p class="text-3xl font-bold">
                            {{ formatCurrency(totalProjectedBalance) }}
                        </p>
                    </div>
                </div>

                <AccountList :accounts="accounts" />

                <ArchivedAccountsSection
                    :archived-accounts="archivedAccounts"
                />
            </div>

            <EmptyState
                v-else
                title="No accounts yet"
                description="Create your first account to get started"
                action-label="Create Account"
                action-href="/accounts/create"
            />
        </div>
    </AppLayout>
</template>
