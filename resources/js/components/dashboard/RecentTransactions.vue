<script setup lang="ts">
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import type { Transaction } from '@/types/transaction'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'

const props = defineProps<{
  transactions: Transaction[]
}>()

const currencyFormatter = computed(() => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'EUR',
  })
})

const formatAmount = (amount: string, type: string) => {
  const formatted = currencyFormatter.value.format(parseFloat(amount))
  return type === 'expense' ? `-${formatted}` : `+${formatted}`
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
  })
}

const getTypeColor = (type: string) => {
  switch (type) {
    case 'income':
      return 'text-green-600 dark:text-green-400'
    case 'expense':
      return 'text-red-600 dark:text-red-400'
    case 'transfer':
      return 'text-blue-600 dark:text-blue-400'
    default:
      return ''
  }
}

const viewAllTransactions = () => {
  router.visit('/transactions')
}
</script>

<template>
  <Card>
    <CardHeader>
      <div class="flex items-center justify-between">
        <div>
          <CardTitle>Recent Transactions</CardTitle>
          <CardDescription>Your latest financial activity</CardDescription>
        </div>
        <Button variant="ghost" size="sm" @click="viewAllTransactions">
          View All
        </Button>
      </div>
    </CardHeader>
    <CardContent>
      <div v-if="transactions.length === 0" class="text-center py-8">
        <p class="text-sm text-muted-foreground">No transactions yet</p>
        <Button
          variant="outline"
          size="sm"
          class="mt-4"
          @click="router.visit('/transactions/create')"
        >
          Create Transaction
        </Button>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="transaction in transactions"
          :key="transaction.id"
          class="flex items-center justify-between border-b pb-4 last:border-b-0 last:pb-0"
        >
          <div class="flex-1">
            <div class="flex items-center gap-2">
              <p class="font-medium">{{ transaction.payee }}</p>
              <Badge
                v-if="transaction.category"
                variant="outline"
                class="text-xs"
              >
                {{ transaction.category.name }}
              </Badge>
            </div>
            <p class="text-sm text-muted-foreground">
              {{ formatDate(transaction.date) }}
              <span v-if="transaction.account" class="ml-2">
                • {{ transaction.account.name }}
              </span>
            </p>
          </div>
          <div :class="getTypeColor(transaction.type)" class="font-semibold">
            {{ formatAmount(transaction.amount, transaction.type) }}
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
