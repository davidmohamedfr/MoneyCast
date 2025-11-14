<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import AccountForm from '@/components/account/AccountForm.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Account } from '@/types/account';
import { Head, router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

const props = defineProps<{
    account: Account;
}>();

const handleSubmit = (values: any) => {
    router.put(`/accounts/${props.account.id}`, values, {
        onSuccess: () => {
            toast.success('Account updated successfully');
        },
        onError: () => {
            toast.error(
                'Failed to update account. Please check the form and try again.',
            );
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head :title="`Edit ${account.name}`" />

        <div class="max-w-2xl space-y-6">
            <Heading>Edit Account</Heading>

            <Card>
                <CardHeader>
                    <CardTitle>Account Details</CardTitle>
                </CardHeader>
                <CardContent>
                    <AccountForm
                        :account="account"
                        :is-edit="true"
                        @submit="handleSubmit"
                    />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
