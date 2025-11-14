<script setup lang="ts">
import type { Transaction } from '@/types/transaction';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import TransactionCard from './TransactionCard.vue';

interface PaginatedTransactions {
    data: Transaction[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    transactions: PaginatedTransactions;
}>();

// Undo functionality - stores pending deletion
const pendingDeletion = ref<{
    id: number;
    timeout: ReturnType<typeof setTimeout>;
} | null>(null);

const handleDelete = (id: number) => {
    // Find transaction details for toast message
    const transaction = props.transactions.data.find((t) => t.id === id);
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
</script>

<template>
    <div class="space-y-4">
        <TransactionCard
            v-for="transaction in transactions.data"
            :key="transaction.id"
            :transaction="transaction"
            @delete="handleDelete"
        />

        <div
            v-if="transactions.data.length === 0"
            class="rounded-lg border p-8 text-center"
        >
            <p class="text-muted-foreground">No transactions found</p>
        </div>
    </div>
</template>
