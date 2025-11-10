<script setup lang="ts">
import type { Transaction } from '@/types/transaction';
import TransactionCard from './TransactionCard.vue';

interface PaginatedTransactions {
    data: Transaction[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

defineProps<{
    transactions: PaginatedTransactions;
}>();
</script>

<template>
    <div class="space-y-4">
        <TransactionCard
            v-for="transaction in transactions.data"
            :key="transaction.id"
            :transaction="transaction"
        />

        <div
            v-if="transactions.data.length === 0"
            class="rounded-lg border p-8 text-center"
        >
            <p class="text-muted-foreground">No transactions found</p>
        </div>
    </div>
</template>
