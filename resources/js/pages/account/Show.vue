<script setup lang="ts">
import AccountStats from '@/components/account/AccountStats.vue';
import Heading from '@/components/Heading.vue';
import TransactionFilters from '@/components/transaction/TransactionFilters.vue';
import TransactionList from '@/components/transaction/TransactionList.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type {
    Account,
    TransactionFilters as Filters,
    AccountStats as Stats,
} from '@/types/account';
import type { Transaction } from '@/types/transaction';
import { Head, Link, router } from '@inertiajs/vue3';

interface PaginatedTransactions {
    data: Transaction[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface PageProps {
    account: Account;
    transactions: PaginatedTransactions;
    stats: Stats;
    filters: Filters;
}

const props = defineProps<PageProps>();

const archiveAccount = () => {
    if (confirm('Are you sure you want to archive this account?')) {
        router.post(`/accounts/${props.account.id}/archive`);
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="account.name" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div class="space-y-2">
                    <Heading>{{ account.name }}</Heading>
                    <div class="flex items-center gap-2">
                        <Badge variant="outline">{{ account.type }}</Badge>
                        <span class="text-sm text-muted-foreground">{{
                            account.bank
                        }}</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Link :href="`/accounts/${account.id}/edit`">
                        <Button variant="outline">Edit</Button>
                    </Link>
                    <Button variant="destructive" @click="archiveAccount">
                        Archive
                    </Button>
                </div>
            </div>

            <AccountStats :stats="stats" :currency="account.currency" />

            <TransactionFilters :filters="filters" :account-id="account.id" />

            <div class="space-y-4">
                <h2 class="text-xl font-semibold">Transactions</h2>
                <TransactionList :transactions="transactions" />
            </div>
        </div>
    </AppLayout>
</template>
