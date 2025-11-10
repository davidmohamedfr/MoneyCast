<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import type { Account } from '@/types/account'
import type { Category } from '@/types/category'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import TransactionForm from '@/components/transaction/TransactionForm.vue'

interface PageProps {
  accounts: Account[]
  categories: Category[]
}

const props = defineProps<PageProps>()

const handleSubmit = (values: any) => {
  router.post('/transactions', values)
}
</script>

<template>
  <AppLayout title="Create Transaction">
    <div class="mx-auto max-w-2xl space-y-6">
      <Heading>Create Transaction</Heading>

      <Card>
        <CardHeader>
          <CardTitle>New Transaction</CardTitle>
        </CardHeader>
        <CardContent>
          <TransactionForm
            :accounts="accounts"
            :categories="categories"
            @submit="handleSubmit"
          />
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
