<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import TransactionCard from '@/components/transaction/TransactionCard.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Transaction } from '@/types/transaction';
import { router } from '@inertiajs/vue3';

interface PageProps {
    transactions: Transaction[];
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const props = defineProps<PageProps>();

const handleDelete = (id: number) => {
    router.delete(`/transactions/${id}`);
};

const handleCreate = () => {
    router.visit('/transactions/create');
};
</script>

<template>
    <AppLayout title="Transactions">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <Heading>Transactions</Heading>
                <Button @click="handleCreate">Create Transaction</Button>
            </div>

            <EmptyState
                v-if="transactions.length === 0"
                title="No transactions yet"
                description="Create your first transaction to get started"
                action-label="Create Transaction"
                @action="handleCreate"
            />

            <div v-else class="grid gap-4">
                <TransactionCard
                    v-for="transaction in transactions"
                    :key="transaction.id"
                    :transaction="transaction"
                    @delete="handleDelete"
                />
            </div>
        </div>
    </AppLayout>
</template>
