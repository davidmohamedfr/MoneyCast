<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import type { Transaction } from '@/types/transaction'
import type { Account } from '@/types/account'
import type { Category } from '@/types/category'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import TransactionForm from '@/components/transaction/TransactionForm.vue'

interface PageProps {
  transaction: Transaction
  accounts: Account[]
  categories: Category[]
}

const props = defineProps<PageProps>()

const handleSubmit = (values: any) => {
  router.put(`/transactions/${props.transaction.id}`, values)
}
</script>

<template>
  <AppLayout title="Edit Transaction">
    <div class="mx-auto max-w-2xl space-y-6">
      <Heading>Edit Transaction</Heading>

      <Card>
        <CardHeader>
          <CardTitle>Update Transaction</CardTitle>
        </CardHeader>
        <CardContent>
          <TransactionForm
            :transaction="transaction"
            :accounts="accounts"
            :categories="categories"
            :is-edit="true"
            @submit="handleSubmit"
          />
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
