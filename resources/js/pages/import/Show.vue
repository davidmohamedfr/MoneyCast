<script setup lang="ts">
import FieldMapper from '@/components/import/FieldMapper.vue';
import ImportPreview from '@/components/import/ImportPreview.vue';
import ImportProgress from '@/components/import/ImportProgress.vue';
import ImportSummary from '@/components/import/ImportSummary.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItemType } from '@/types';
import type { Import, ParsedImportData } from '@/types/import';
import { Head } from '@inertiajs/vue3';

interface PageProps {
    importData: Import;
    parsedData?: ParsedImportData;
}

const props = defineProps<PageProps>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Imports', href: '/imports' },
    { title: props.importData.file_name, href: `/imports/${props.importData.id}` },
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
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Import: ${importData.file_name}`" />

        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
            <div class="flex items-center justify-between">
                <div>
                    <Heading>{{ importData.file_name }}</Heading>
                    <p class="text-sm text-muted-foreground">
                        Import ID: #{{ importData.id }}
                    </p>
                </div>
                <Badge :variant="getStatusBadgeVariant(importData.status)">
                    {{ importData.status }}
                </Badge>
            </div>

            <div class="mx-auto w-full max-w-4xl">
                <div v-if="importData.status === 'mapping'">
                    <div v-if="parsedData">
                        <FieldMapper :parsed-data="parsedData" :import-id="importData.id" />
                    </div>
                    <div v-else class="rounded-lg border border-yellow-200 bg-yellow-50 p-6 dark:bg-yellow-950">
                        <h3 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200">
                            Parsing Data...
                        </h3>
                        <p class="mt-2 text-sm text-yellow-600 dark:text-yellow-300">
                            Please refresh the page. The import data is being processed.
                        </p>
                    </div>
                </div>

                <div v-else-if="importData.status === 'validating'">
                    <div v-if="parsedData">
                        <ImportPreview :parsed-data="parsedData" />
                    </div>
                    <div v-else class="text-center text-muted-foreground">
                        Validating import data...
                    </div>
                </div>

                <div v-else-if="importData.status === 'processing'">
                    <ImportProgress :import-data="importData" />
                </div>

                <div v-else-if="importData.status === 'completed'">
                    <ImportSummary :import-data="importData" />
                </div>

                <div v-else-if="importData.status === 'failed'" class="text-center">
                    <div class="rounded-lg border border-red-200 bg-red-50 p-6 dark:bg-red-950">
                        <h3 class="text-lg font-semibold text-red-800 dark:text-red-200">
                            Import Failed
                        </h3>
                        <p class="mt-2 text-sm text-red-600 dark:text-red-300">
                            {{ importData.error_message || 'An error occurred during import' }}
                        </p>
                    </div>
                </div>

                <div v-else class="text-center text-muted-foreground">
                    Processing import...
                </div>
            </div>
        </div>
    </AppLayout>
</template>
