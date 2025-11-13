<script setup lang="ts">
import AccountFilters from '@/components/account/AccountFilters.vue';
import AccountList from '@/components/account/AccountList.vue';
import ArchivedAccountsSection from '@/components/account/ArchivedAccountsSection.vue';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { useFormatCurrency } from '@/composables/useFormatCurrency';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItemType } from '@/types';
import type { AccountWithBalance } from '@/types/account';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Filters {
    search?: string;
    type?: string;
    bank?: string;
}

interface PageProps {
    accounts: AccountWithBalance[];
    archivedAccounts: AccountWithBalance[];
    filters?: Filters;
}

const props = defineProps<PageProps>();

const { formatCurrency } = useFormatCurrency();

const totalCurrentBalance = computed(() => {
    return props.accounts.reduce((sum, acc) => sum + acc.current_balance, 0);
});

const totalProjectedBalance = computed(() => {
    return props.accounts.reduce((sum, acc) => sum + acc.projected_balance, 0);
});

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Accounts', href: '/accounts' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Accounts" />

        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
            <div class="flex items-center justify-between">
                <Heading>Accounts</Heading>
                <Link href="/accounts/create">
                    <Button variant="gradient">Add Account</Button>
                </Link>
            </div>

            <div v-if="accounts.length > 0 || filters" class="space-y-6">
                <!-- Search and filters for ADHD/Dyslexia users -->
                <AccountFilters :filters="filters || {}" />

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-lg border bg-card p-6 shadow-sm">
                        <p class="text-sm font-medium text-muted-foreground">
                            Total Current Balance
                        </p>
                        <p class="mt-2 text-3xl font-bold tracking-tight">
                            {{ formatCurrency(totalCurrentBalance) }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Across all accounts
                        </p>
                    </div>
                    <div class="rounded-lg border bg-card p-6 shadow-sm">
                        <p class="text-sm font-medium text-muted-foreground">
                            Total Projected Balance
                        </p>
                        <p class="mt-2 text-3xl font-bold tracking-tight">
                            {{ formatCurrency(totalProjectedBalance) }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Including pending transactions
                        </p>
                    </div>
                </div>

                <AccountList v-if="accounts.length > 0" :accounts="accounts" />

                <!-- Empty search results state -->
                <EmptyState
                    v-else
                    title="No accounts found"
                    description="Try adjusting your search or filters"
                    action-label="Clear filters"
                    @action="() => {}"
                />

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
