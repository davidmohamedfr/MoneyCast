<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import type { Transaction } from '@/types/transaction'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import TransactionCard from '@/components/transaction/TransactionCard.vue'

interface PageProps {
  transactions: Transaction[]
}

const props = defineProps<PageProps>()

const handleDelete = (id: number) => {
  router.delete(`/transactions/${id}`)
}

const handleCreate = () => {
  router.visit('/transactions/create')
}
</script>

<template>
  <AppLayout title="Transactions">
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <Heading>Transactions</Heading>
        <Button @click="handleCreate">Create Transaction</Button>
      </div>

      <div
        v-if="transactions.length === 0"
        class="flex flex-col items-center justify-center rounded-lg border border-dashed p-8 text-center"
      >
        <p class="text-muted-foreground">No transactions yet</p>
        <p class="text-sm text-muted-foreground">
          Create your first transaction to get started
        </p>
        <Button class="mt-4" @click="handleCreate">Create Transaction</Button>
      </div>

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
