<script setup lang="ts">
import { Spinner } from '@/components/ui/spinner';
import type { Import } from '@/types/import';
import { router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps<{
    importData: Import;
}>();

const localImportData = ref<Import>(props.importData);
const debugLogs = ref<Array<{ timestamp: string; message: string }>>([]);

const progress = computed(() => {
    if (localImportData.value.total_rows > 0) {
        return Math.round((localImportData.value.imported_rows / localImportData.value.total_rows) * 100);
    }
    return 0;
});

let eventSource: EventSource | null = null;

onMounted(() => {
    // Only connect SSE if still processing
    if (localImportData.value.status === 'processing') {
        eventSource = new EventSource(`/imports/${localImportData.value.id}/progress`);

        eventSource.addEventListener('progress', (event) => {
            const data = JSON.parse(event.data);
            localImportData.value = {
                ...localImportData.value,
                status: data.status,
                imported_rows: data.imported_rows,
                total_rows: data.total_rows,
                skipped_rows: data.skipped_rows,
                failed_rows: data.failed_rows,
            };
            if (data.debug_logs && Array.isArray(data.debug_logs)) {
                debugLogs.value = data.debug_logs;
            }
        });

        eventSource.addEventListener('close', () => {
            eventSource?.close();
            eventSource = null;
            // Reload page to show final state
            router.reload({ only: ['importData'] });
        });

        eventSource.onerror = () => {
            eventSource?.close();
            eventSource = null;
            // Reload on error to see current state
            router.reload({ only: ['importData'] });
        };
    }
});

onUnmounted(() => {
    if (eventSource) {
        eventSource.close();
        eventSource = null;
    }
});
</script>

<template>
    <div class="flex flex-col space-y-4 rounded-lg border p-8">
        <div class="flex flex-col items-center justify-center space-y-4">
            <Spinner class="h-12 w-12" />
            <div class="text-center">
                <h3 class="text-lg font-semibold">Processing Import...</h3>
                <p class="text-sm text-muted-foreground">
                    {{ localImportData.imported_rows }} of {{ localImportData.total_rows }} transactions
                    processed
                </p>
            </div>
            <div class="w-full max-w-md">
                <div class="h-2 w-full overflow-hidden rounded-full bg-muted">
                    <div
                        class="h-full bg-primary transition-all duration-300"
                        :style="{ width: `${progress}%` }"
                    />
                </div>
                <p class="mt-2 text-center text-sm text-muted-foreground">{{ progress }}%</p>
            </div>
        </div>

        <div v-if="debugLogs.length > 0" class="mt-6 w-full">
            <h4 class="mb-2 text-sm font-semibold">Debug Logs</h4>
            <div class="max-h-60 overflow-y-auto rounded border bg-muted/30 p-3 font-mono text-xs">
                <div
                    v-for="(log, index) in debugLogs"
                    :key="index"
                    class="mb-1 text-muted-foreground"
                >
                    <span class="text-muted-foreground/70">[{{ log.timestamp }}]</span>
                    {{ log.message }}
                </div>
            </div>
        </div>
    </div>
</template>
