<script setup lang="ts">
import { computed } from 'vue'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'

const props = defineProps<{
  totalBalance: number
  currency?: string
}>()

const currencyFormatter = computed(() => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: props.currency || 'EUR',
  })
})

const formattedBalance = computed(() => {
  return currencyFormatter.value.format(props.totalBalance)
})

const balanceColor = computed(() => {
  if (props.totalBalance > 0) {
    return 'text-green-600 dark:text-green-400'
  } else if (props.totalBalance < 0) {
    return 'text-red-600 dark:text-red-400'
  }
  return 'text-muted-foreground'
})
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Total Balance</CardTitle>
      <CardDescription>Across all accounts</CardDescription>
    </CardHeader>
    <CardContent>
      <div :class="balanceColor" class="text-4xl font-bold">
        {{ formattedBalance }}
      </div>
    </CardContent>
  </Card>
</template>
