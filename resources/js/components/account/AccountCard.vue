<script setup lang="ts">
import { computed } from 'vue'
import type { AccountWithBalance } from '@/types/account'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Link } from '@inertiajs/vue3'

const props = defineProps<{
  accountData: AccountWithBalance
}>()

const accountTypeLabel = computed(() => {
  const types = {
    checking: 'Checking',
    savings: 'Savings',
    credit: 'Credit',
  }
  return types[props.accountData.account.type] || props.accountData.account.type
})

const currencyFormatter = computed(() => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: props.accountData.account.currency,
  })
})

const formatCurrency = (amount: number) => {
  return currencyFormatter.value.format(amount)
}

const balanceDifference = computed(() => {
  return props.accountData.projected_balance - props.accountData.current_balance
})
</script>

<template>
  <Card>
    <CardHeader>
      <div class="flex items-start justify-between">
        <div>
          <CardTitle>{{ accountData.account.name }}</CardTitle>
          <Badge variant="outline" class="mt-2">{{ accountTypeLabel }}</Badge>
        </div>
        <Link :href="`/accounts/${accountData.account.id}`">
          <Button variant="ghost" size="sm">View Details</Button>
        </Link>
      </div>
    </CardHeader>
    <CardContent>
      <div class="space-y-3">
        <div>
          <p class="text-sm text-muted-foreground">Current Balance</p>
          <p class="text-2xl font-bold">{{ formatCurrency(accountData.current_balance) }}</p>
        </div>
        <div>
          <p class="text-sm text-muted-foreground">Projected Balance</p>
          <p class="text-lg font-semibold">{{ formatCurrency(accountData.projected_balance) }}</p>
        </div>
        <div v-if="balanceDifference !== 0" class="pt-2 border-t">
          <p class="text-sm" :class="balanceDifference > 0 ? 'text-green-600' : 'text-red-600'">
            {{ balanceDifference > 0 ? '+' : '' }}{{ formatCurrency(balanceDifference) }} projected
          </p>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
