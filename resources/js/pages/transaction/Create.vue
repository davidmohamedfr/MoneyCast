<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import TransactionForm from '@/components/transaction/TransactionForm.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItemType } from '@/types';
import type { Account } from '@/types/account';
import type { Category } from '@/types/category';
import { router } from '@inertiajs/vue3';

interface PageProps {
    accounts: Account[];
    categories: Category[];
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const props = defineProps<PageProps>();

const handleSubmit = (values: any) => {
    router.post('/transactions', values);
};

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Transactions', href: '/transactions' },
    { title: 'Create', href: '/transactions/create' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs" title="Create Transaction">
        <div class="mx-auto max-w-2xl space-y-6">
            <Heading>Create Transaction</Heading>

            <Card>
                <CardHeader>
                    <CardTitle>New Transaction</CardTitle>
                </CardHeader>
                <CardContent>
                    <TransactionForm
                        :accounts="accounts"
                        :categories="categories"
                        @submit="handleSubmit"
                    />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
