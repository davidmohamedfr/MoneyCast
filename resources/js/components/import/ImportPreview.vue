<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import type { ParsedImportData } from '@/types/import';

defineProps<{
    parsedData: ParsedImportData;
}>();
</script>

<template>
    <div class="space-y-4">
        <div>
            <h3 class="text-lg font-semibold">Import Preview</h3>
            <p class="text-sm text-muted-foreground">
                Review the first {{ Math.min(10, parsedData.transactions.length) }} transactions before importing
            </p>
        </div>

        <div class="flex gap-4 rounded-lg border p-4">
            <div class="text-center">
                <div class="text-2xl font-bold">{{ parsedData.total_rows }}</div>
                <div class="text-sm text-muted-foreground">Total</div>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg border">
            <table class="w-full">
                <thead class="border-b bg-muted/50">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium">Row</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Date</th>
                        <th class="px-4 py-2 text-right text-sm font-medium">Amount</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Payee</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="transaction in parsedData.transactions.slice(0, 10)"
                        :key="transaction.row_number"
                        class="border-b last:border-b-0"
                    >
                        <td class="px-4 py-2 text-sm">{{ transaction.row_number }}</td>
                        <td class="px-4 py-2 text-sm">{{ transaction.date }}</td>
                        <td class="px-4 py-2 text-right text-sm font-mono">
                            {{
                                transaction.amount ??
                                (transaction.debit ? `-${transaction.debit}` : `+${transaction.credit}`)
                            }}
                        </td>
                        <td class="px-4 py-2 text-sm">{{ transaction.payee }}</td>
                        <td class="px-4 py-2 text-sm text-muted-foreground">
                            {{ transaction.description || '-' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
