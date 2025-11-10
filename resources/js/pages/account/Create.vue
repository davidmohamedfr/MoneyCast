<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import AccountForm from '@/components/account/AccountForm.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

const handleSubmit = (values: any) => {
    router.post('/accounts', values, {
        onSuccess: () => {
            toast.success('Account created successfully');
        },
        onError: () => {
            toast.error('Failed to create account. Please check the form and try again.');
        },
    });
};
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
