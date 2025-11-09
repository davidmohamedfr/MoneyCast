<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import type { Account } from '@/types/account'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'

interface PageProps {
  account: Account
  current_balance: number
  projected_balance: number
}

const props = defineProps<PageProps>()

const currencyFormatter = computed(() => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: props.account.currency,
  })
})

const formatCurrency = (amount: number) => {
  return currencyFormatter.value.format(amount)
}

const deleteAccount = () => {
  if (confirm('Are you sure you want to delete this account?')) {
    router.delete(`/accounts/${props.account.id}`)
  }
}
</script>

<template>
  <AppLayout>
    <Head :title="account.name" />

    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <Heading>{{ account.name }}</Heading>
          <Badge variant="outline" class="mt-2">{{ account.type }}</Badge>
        </div>
        <div class="flex gap-2">
          <Link :href="`/accounts/${account.id}/edit`">
            <Button variant="outline">Edit</Button>
          </Link>
          <Button variant="destructive" @click="deleteAccount">Delete</Button>
        </div>
      </div>

      <div class="grid gap-4 md:grid-cols-3">
        <Card>
          <CardHeader>
            <CardTitle class="text-sm font-medium text-muted-foreground">Initial Balance</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-2xl font-bold">{{ formatCurrency(account.initial_balance) }}</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle class="text-sm font-medium text-muted-foreground">Current Balance</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-2xl font-bold">{{ formatCurrency(current_balance) }}</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle class="text-sm font-medium text-muted-foreground">Projected Balance</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-2xl font-bold">{{ formatCurrency(projected_balance) }}</p>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Transaction History</CardTitle>
        </CardHeader>
        <CardContent>
          <p class="text-muted-foreground">No transactions yet</p>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
