<script setup lang="ts">
import FileUpload from '@/components/import/FileUpload.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { importSchema } from '@/lib/validation/import';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItemType } from '@/types';
import type { Account } from '@/types/account';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface PageProps {
    accounts: Account[];
}

const props = defineProps<PageProps>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Imports', href: '/imports' },
    { title: 'New Import', href: '/imports/create' },
];

const file = ref<File | null>(null);
const sourceType = ref<string>('csv');
const accountId = ref<number | null>(null);
const errors = ref<Record<string, string>>({});
const isSubmitting = ref(false);

const handleSubmit = async () => {
    errors.value = {};

    try {
        const formData = {
            file: file.value,
            source_type: sourceType.value,
            account_id: accountId.value,
        };

        importSchema.parse(formData);

        const data = new FormData();
        if (file.value) {
            data.append('file', file.value);
        }
        data.append('source_type', sourceType.value);
        if (accountId.value) {
            data.append('account_id', accountId.value.toString());
        }

        isSubmitting.value = true;

        router.post('/imports', data, {
            onError: (err) => {
                errors.value = err as Record<string, string>;
                isSubmitting.value = false;
            },
        });
    } catch (error: any) {
        if (error.errors) {
            error.errors.forEach((err: any) => {
                errors.value[err.path[0]] = err.message;
            });
        }
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="New Import" />

        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
            <Heading title="Import Transactions" description="Upload a CSV file to import transactions" />

            <div class="mx-auto w-full max-w-2xl">
                <form @submit.prevent="handleSubmit" class="space-y-6 rounded-lg border bg-card p-6">
                    <div class="space-y-2">
                        <Label>Upload File</Label>
                        <FileUpload v-model="file" :error="errors.file" />
                    </div>

                    <div class="space-y-2">
                        <Label for="source_type">Source Type</Label>
                        <select
                            id="source_type"
                            v-model="sourceType"
                            :disabled="isSubmitting"
                            required
                            class="flex h-9 w-full items-center rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50"
                        >
                            <option value="csv">CSV</option>
                        </select>
                        <p v-if="errors.source_type" class="text-sm text-destructive">
                            {{ errors.source_type }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="account_id">Account *</Label>
                        <select
                            id="account_id"
                            v-model="accountId"
                            :disabled="isSubmitting"
                            required
                            class="flex h-9 w-full items-center rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50"
                        >
                            <option :value="null" disabled>Select an account</option>
                            <option
                                v-for="account in accounts"
                                :key="account.id"
                                :value="account.id"
                            >
                                {{ account.name }}
                            </option>
                        </select>
                        <p v-if="errors.account_id" class="text-sm text-destructive">
                            {{ errors.account_id }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            Select the account where transactions will be imported.
                        </p>
                    </div>

                    <div class="flex justify-end gap-3">
                        <Button type="button" variant="outline" @click="router.visit('/imports')">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="isSubmitting">
                            {{ isSubmitting ? 'Uploading...' : 'Upload and Continue' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
