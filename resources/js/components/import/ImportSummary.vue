<script setup lang="ts">
import { Button } from '@/components/ui/button';
import type { Import } from '@/types/import';
import { Link } from '@inertiajs/vue3';

defineProps<{
    importData: Import;
}>();
</script>

<template>
    <div class="space-y-6">
        <div class="text-center">
            <h3 class="text-2xl font-bold text-green-600">Import Completed!</h3>
            <p class="text-muted-foreground">Your transactions have been imported successfully</p>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <div class="rounded-lg border p-4 text-center">
                <div class="text-2xl font-bold">{{ importData.total_rows }}</div>
                <div class="text-sm text-muted-foreground">Total Rows</div>
            </div>
            <div class="rounded-lg border bg-green-50 p-4 text-center dark:bg-green-950">
                <div class="text-2xl font-bold text-green-600">{{ importData.imported_rows }}</div>
                <div class="text-sm text-green-600">Imported</div>
            </div>
            <div class="rounded-lg border bg-yellow-50 p-4 text-center dark:bg-yellow-950">
                <div class="text-2xl font-bold text-yellow-600">{{ importData.skipped_rows }}</div>
                <div class="text-sm text-yellow-600">Skipped (Duplicates)</div>
            </div>
            <div class="rounded-lg border bg-red-50 p-4 text-center dark:bg-red-950">
                <div class="text-2xl font-bold text-red-600">{{ importData.failed_rows }}</div>
                <div class="text-sm text-red-600">Failed</div>
            </div>
        </div>

        <div v-if="importData.error_message" class="rounded-lg border border-red-200 bg-red-50 p-4 dark:bg-red-950">
            <h4 class="font-semibold text-red-800 dark:text-red-200">Errors</h4>
            <p class="text-sm text-red-600 dark:text-red-300">{{ importData.error_message }}</p>
        </div>

        <div class="flex justify-center gap-3">
            <Link href="/transactions">
                <Button>View Transactions</Button>
            </Link>
            <Link href="/imports">
                <Button variant="outline">Back to Imports</Button>
            </Link>
        </div>
    </div>
</template>
