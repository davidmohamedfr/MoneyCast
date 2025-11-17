<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import type { ParsedImportData, FieldMapping } from '@/types/import';
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps<{
    parsedData: ParsedImportData;
    importId: number;
}>();

// Use individual refs instead of a single ref object for better reactivity
const dateColumn = ref<string>(props.parsedData.detected_columns.date || '');
const payeeColumn = ref<string>(props.parsedData.detected_columns.payee || '');
const amountColumn = ref<string>(props.parsedData.detected_columns.amount || '');
const debitColumn = ref<string>(props.parsedData.detected_columns.debit || '');
const creditColumn = ref<string>(props.parsedData.detected_columns.credit || '');
const descriptionColumn = ref<string>(props.parsedData.detected_columns.description || '');

const availableColumns = Object.keys(props.parsedData.sample_rows[0] || {});

// Computed property to build the mapping object from individual refs
const mapping = computed<FieldMapping>(() => ({
    date: dateColumn.value,
    payee: payeeColumn.value,
    amount: amountColumn.value || undefined,
    debit: debitColumn.value || undefined,
    credit: creditColumn.value || undefined,
    description: descriptionColumn.value || undefined,
}));

const handleSubmit = () => {
    router.put(`/imports/${props.importId}`, {
        mapping: mapping.value,
    });
};
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-lg font-semibold">Map CSV Columns</h3>
            <p class="text-sm text-muted-foreground">
                Match your CSV columns to the required transaction fields
            </p>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <Label for="date-column">Date Column *</Label>
                    <select
                        id="date-column"
                        v-model="dateColumn"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        required
                    >
                        <option value="" disabled>Select date column</option>
                        <option v-for="col in availableColumns" :key="col" :value="col">
                            {{ col }}
                        </option>
                    </select>
                </div>

                <div class="space-y-2">
                    <Label for="payee-column">Payee Column *</Label>
                    <select
                        id="payee-column"
                        v-model="payeeColumn"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        required
                    >
                        <option value="" disabled>Select payee column</option>
                        <option v-for="col in availableColumns" :key="col" :value="col">
                            {{ col }}
                        </option>
                    </select>
                </div>

                <div class="space-y-2">
                    <Label for="amount-column">Amount Column</Label>
                    <select
                        id="amount-column"
                        v-model="amountColumn"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option value="">None</option>
                        <option v-for="col in availableColumns" :key="col" :value="col">
                            {{ col }}
                        </option>
                    </select>
                </div>

                <div class="space-y-2">
                    <Label for="debit-column">Debit Column</Label>
                    <select
                        id="debit-column"
                        v-model="debitColumn"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option value="">None</option>
                        <option v-for="col in availableColumns" :key="col" :value="col">
                            {{ col }}
                        </option>
                    </select>
                </div>

                <div class="space-y-2">
                    <Label for="credit-column">Credit Column</Label>
                    <select
                        id="credit-column"
                        v-model="creditColumn"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option value="">None</option>
                        <option v-for="col in availableColumns" :key="col" :value="col">
                            {{ col }}
                        </option>
                    </select>
                </div>

                <div class="space-y-2">
                    <Label for="description-column">Description Column</Label>
                    <select
                        id="description-column"
                        v-model="descriptionColumn"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option value="">None</option>
                        <option v-for="col in availableColumns" :key="col" :value="col">
                            {{ col }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="rounded-lg border p-4">
                <h4 class="mb-2 font-medium">Sample Data</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th
                                    v-for="col in availableColumns"
                                    :key="col"
                                    class="px-2 py-1 text-left font-medium"
                                >
                                    {{ col }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, idx) in parsedData.sample_rows"
                                :key="idx"
                                class="border-b last:border-b-0"
                            >
                                <td
                                    v-for="col in availableColumns"
                                    :key="col"
                                    class="px-2 py-1"
                                >
                                    {{ row[col] }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end">
                <Button type="submit">Continue to Preview</Button>
            </div>
        </form>
    </div>
</template>
