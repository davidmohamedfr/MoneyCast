<script setup lang="ts">
import type { Transaction } from '@/types/transaction'
import type { BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import BalanceCard from '@/components/dashboard/BalanceCard.vue'
import MonthlyStats from '@/components/dashboard/MonthlyStats.vue'
import RecentTransactions from '@/components/dashboard/RecentTransactions.vue'
import { dashboard } from '@/routes'

interface MonthlyStatsData {
  income: number
  expenses: number
  net: number
  transaction_count: number
}

interface AccountWithBalance {
  account: {
    id: number
    name: string
    type: string
    currency: string
  }
  current_balance: number
  projected_balance: number
}

interface PageProps {
  accounts: AccountWithBalance[]
  total_balance: number
  recent_transactions: Transaction[]
  monthly_stats: MonthlyStatsData
}

defineProps<PageProps>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
]
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
      <div class="grid gap-4 md:grid-cols-3">
        <BalanceCard :total-balance="total_balance" />
        <MonthlyStats :stats="monthly_stats" />
        <div class="md:col-span-1">
          <div class="space-y-2">
            <p class="text-sm font-medium">Quick Actions</p>
            <div class="grid gap-2">
              <a
                href="/transactions/create"
                class="rounded-lg border border-sidebar-border/70 p-4 hover:bg-accent dark:border-sidebar-border"
              >
                <p class="font-medium">New Transaction</p>
                <p class="text-sm text-muted-foreground">
                  Add income or expense
                </p>
              </a>
              <a
                href="/accounts/create"
                class="rounded-lg border border-sidebar-border/70 p-4 hover:bg-accent dark:border-sidebar-border"
              >
                <p class="font-medium">New Account</p>
                <p class="text-sm text-muted-foreground">
                  Add checking or savings
                </p>
              </a>
            </div>
          </div>
        </div>
      </div>
      <div class="flex-1">
        <RecentTransactions :transactions="recent_transactions" />
      </div>
    </div>
  </AppLayout>
</template>
