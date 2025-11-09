<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import AccountForm from '@/components/account/AccountForm.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

const handleSubmit = (values: any) => {
  router.post('/accounts', values)
}
</script>

<template>
  <AppLayout>
    <Head title="Create Account" />

    <div class="max-w-2xl space-y-6">
      <Heading>Create Account</Heading>

      <Card>
        <CardHeader>
          <CardTitle>Account Details</CardTitle>
        </CardHeader>
        <CardContent>
          <AccountForm @submit="handleSubmit" />
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
