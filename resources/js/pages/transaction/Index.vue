<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import TransactionTable from '@/components/transaction/TransactionTable.vue';
import TransactionFiltersIndex from '@/components/transaction/TransactionFiltersIndex.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItemType } from '@/types';
import type { Transaction } from '@/types/transaction';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

interface Filters {
    search?: string;
    type?: string;
    category_id?: number;
    start_date?: string;
    end_date?: string;
}

interface PageProps {
    transactions: Transaction[];
    filters?: Filters;
    categories?: Array<{ id: number; name: string }>;
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const props = defineProps<PageProps>();

// Undo functionality - stores pending deletion
const pendingDeletion = ref<{ id: number; timeout: ReturnType<typeof setTimeout> } | null>(
    null,
);

const handleDelete = (id: number) => {
    // Find transaction details for toast message
    const transaction = props.transactions.find((t) => t.id === id);
    if (!transaction) return;

    // Cancel any pending deletion
    if (pendingDeletion.value) {
        clearTimeout(pendingDeletion.value.timeout);
        pendingDeletion.value = null;
    }

    // Set timeout for actual deletion (5 seconds for ADHD users to react)
    const timeout = setTimeout(() => {
        router.delete(`/transactions/${id}`, {
            onSuccess: () => {
                toast.success('Transaction deleted');
                pendingDeletion.value = null;
            },
            onError: () => {
                toast.error('Failed to delete transaction');
                pendingDeletion.value = null;
            },
        });
    }, 5000);

    pendingDeletion.value = { id, timeout };

    // Show undo toast
    toast.success(`Deleting "${transaction.payee}"`, {
        description: 'Click undo to cancel',
        duration: 5000,
        action: {
            label: 'Undo',
            onClick: () => {
                if (pendingDeletion.value?.id === id) {
                    clearTimeout(pendingDeletion.value.timeout);
                    pendingDeletion.value = null;
                    toast.info('Deletion cancelled');
                }
            },
        },
    });
};

const handleCreate = () => {
    router.visit('/transactions/create');
};

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Transactions', href: '/transactions' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs" title="Transactions">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
            <div class="flex items-center justify-between">
                <Heading>Transactions</Heading>
                <Button variant="gradient" @click="handleCreate">Create Transaction</Button>
            </div>

            <div v-if="transactions.length > 0 || filters" class="space-y-6">
                <!-- Search and filters for ADHD/Dyslexia users -->
                <TransactionFiltersIndex
                    :filters="filters || {}"
                    :categories="categories"
                />

                <TransactionTable
                    v-if="transactions.length > 0"
                    :transactions="transactions"
                    @delete="handleDelete"
                />

                <!-- Empty search results state -->
                <EmptyState
                    v-else
                    title="No transactions found"
                    description="Try adjusting your search or filters"
                    action-label="Clear filters"
                    @action="() => router.get('/transactions')"
                />
            </div>

            <EmptyState
                v-else
                title="No transactions yet"
                description="Create your first transaction to get started"
                action-label="Create Transaction"
                @action="handleCreate"
            />
        </div>
    </AppLayout>
</template>
