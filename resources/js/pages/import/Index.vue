<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItemType } from '@/types';
import type { Import } from '@/types/import';
import { Head, Link, router } from '@inertiajs/vue3';

interface PageProps {
    imports: Import[];
}

const props = defineProps<PageProps>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Imports', href: '/imports' },
];

const getStatusBadgeVariant = (status: string) => {
    switch (status) {
        case 'completed':
            return 'default';
        case 'failed':
            return 'destructive';
        case 'processing':
            return 'secondary';
        default:
            return 'outline';
    }
};

const deleteImport = (id: number) => {
    if (confirm('Are you sure you want to delete this import?')) {
        router.delete(`/imports/${id}`);
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Imports" />

        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
            <div class="flex items-center justify-between">
                <Heading title="Import History" description="View and manage your transaction imports" />
                <Link :href="`/imports/create`">
                    <Button>New Import</Button>
                </Link>
            </div>

            <div v-if="imports.length === 0">
                <EmptyState
                    title="No imports yet"
                    description="Upload a CSV file to import transactions into your accounts"
                    action-label="Start Import"
                    action-href="/imports/create"
                />
            </div>

            <div v-else class="overflow-hidden rounded-lg border bg-card">
                <table class="w-full">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-medium">File Name</th>
                            <th class="px-4 py-3 text-left text-sm font-medium">Source</th>
                            <th class="px-4 py-3 text-left text-sm font-medium">Status</th>
                            <th class="px-4 py-3 text-right text-sm font-medium">Total Rows</th>
                            <th class="px-4 py-3 text-right text-sm font-medium">Imported</th>
                            <th class="px-4 py-3 text-right text-sm font-medium">Skipped</th>
                            <th class="px-4 py-3 text-right text-sm font-medium">Failed</th>
                            <th class="px-4 py-3 text-left text-sm font-medium">Created</th>
                            <th class="px-4 py-3 text-right text-sm font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="importItem in imports"
                            :key="importItem.id"
                            class="border-b last:border-b-0 hover:bg-muted/50"
                        >
                            <td class="px-4 py-3 text-sm">{{ importItem.file_name }}</td>
                            <td class="px-4 py-3 text-sm uppercase">{{ importItem.source_type }}</td>
                            <td class="px-4 py-3 text-sm">
                                <Badge :variant="getStatusBadgeVariant(importItem.status)">
                                    {{ importItem.status }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-right text-sm">{{ importItem.total_rows }}</td>
                            <td class="px-4 py-3 text-right text-sm">{{ importItem.imported_rows }}</td>
                            <td class="px-4 py-3 text-right text-sm">{{ importItem.skipped_rows }}</td>
                            <td class="px-4 py-3 text-right text-sm">{{ importItem.failed_rows }}</td>
                            <td class="px-4 py-3 text-sm">
                                {{ new Date(importItem.created_at).toLocaleDateString() }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                <div class="flex justify-end gap-2">
                                    <Link :href="`/imports/${importItem.id}`">
                                        <Button variant="ghost" size="sm">View</Button>
                                    </Link>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="deleteImport(importItem.id)"
                                    >
                                        Delete
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
