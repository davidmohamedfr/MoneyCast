<script setup lang="ts">
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import type { Transaction } from '@/types/transaction'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'

const props = defineProps<{
  transaction: Transaction
}>()

const emit = defineEmits<{
  delete: [id: number]
}>()

const formattedAmount = computed(() => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'EUR', // TODO: Get from account when available
  }).format(parseFloat(props.transaction.amount))
})

const formattedDate = computed(() => {
  return new Date(props.transaction.date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
})

const typeColor = computed(() => {
  switch (props.transaction.type) {
    case 'income':
      return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
    case 'expense':
      return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
    case 'transfer':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'
    default:
      return ''
  }
})

const handleEdit = () => {
  router.visit(`/transactions/${props.transaction.id}/edit`)
}

const handleDelete = () => {
  if (confirm('Are you sure you want to delete this transaction?')) {
    emit('delete', props.transaction.id)
  }
}
</script>

<template>
  <Card>
    <CardHeader>
      <div class="flex items-start justify-between">
        <div class="flex-1">
          <CardTitle class="text-lg">{{ transaction.payee }}</CardTitle>
          <CardDescription>
            {{ formattedDate }}
            <span v-if="transaction.account" class="ml-2">
              • {{ transaction.account.name }}
            </span>
          </CardDescription>
        </div>
        <div class="flex flex-col items-end gap-2">
          <Badge :class="typeColor">
            {{ transaction.type }}
          </Badge>
          <span
            :class="{
              'text-green-600 dark:text-green-400':
                transaction.type === 'income',
              'text-red-600 dark:text-red-400': transaction.type === 'expense',
              'text-blue-600 dark:text-blue-400':
                transaction.type === 'transfer',
            }"
            class="text-xl font-semibold"
          >
            {{ transaction.type === 'expense' ? '-' : '+' }}{{ formattedAmount }}
          </span>
        </div>
      </div>
    </CardHeader>

    <CardContent v-if="transaction.description || transaction.category">
      <div class="space-y-2 text-sm">
        <div v-if="transaction.category" class="flex items-center gap-2">
          <span class="text-muted-foreground">Category:</span>
          <Badge variant="outline">{{ transaction.category.name }}</Badge>
        </div>
        <div v-if="transaction.description" class="text-muted-foreground">
          {{ transaction.description }}
        </div>
      </div>
    </CardContent>

    <CardFooter class="flex justify-end gap-2">
      <Button variant="outline" size="sm" @click="handleEdit">Edit</Button>
      <Button variant="destructive" size="sm" @click="handleDelete">
        Delete
      </Button>
    </CardFooter>
  </Card>
</template>
