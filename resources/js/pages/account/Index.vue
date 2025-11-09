<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import type { AccountWithBalance } from '@/types/account'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import AccountCard from '@/components/account/AccountCard.vue'

interface PageProps {
  accounts: AccountWithBalance[]
}

const props = defineProps<PageProps>()

const totalCurrentBalance = computed(() => {
  return props.accounts.reduce((sum, acc) => sum + acc.current_balance, 0)
})

const totalProjectedBalance = computed(() => {
  return props.accounts.reduce((sum, acc) => sum + acc.projected_balance, 0)
})

const currencyFormatter = computed(() => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'EUR',
  })
})

const formatCurrency = (amount: number) => {
  return currencyFormatter.value.format(amount)
}
</script>

<template>
  <AppLayout>
    <Head title="Accounts" />

    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <Heading>Accounts</Heading>
        <Link href="/accounts/create">
          <Button>Add Account</Button>
        </Link>
      </div>

      <div v-if="accounts.length > 0" class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2">
          <div class="rounded-lg border p-4">
            <p class="text-sm text-muted-foreground">Total Current Balance</p>
            <p class="text-3xl font-bold">{{ formatCurrency(totalCurrentBalance) }}</p>
          </div>
          <div class="rounded-lg border p-4">
            <p class="text-sm text-muted-foreground">Total Projected Balance</p>
            <p class="text-3xl font-bold">{{ formatCurrency(totalProjectedBalance) }}</p>
          </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          <AccountCard v-for="accountData in accounts" :key="accountData.account.id" :account-data="accountData" />
        </div>
      </div>

      <div v-else class="flex flex-col items-center justify-center py-12 text-center">
        <p class="text-lg text-muted-foreground mb-4">No accounts yet</p>
        <p class="text-sm text-muted-foreground mb-6">Create your first account to get started</p>
        <Link href="/accounts/create">
          <Button>Create Account</Button>
        </Link>
      </div>
    </div>
  </AppLayout>
</template>
