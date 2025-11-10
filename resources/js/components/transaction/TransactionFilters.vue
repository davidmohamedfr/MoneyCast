<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { TransactionFilters } from '@/types/account';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    filters: TransactionFilters;
    accountId: number;
}>();

const localFilters = ref<TransactionFilters>({ ...props.filters });

const applyFilters = () => {
    router.get(
        `/accounts/${props.accountId}`,
        {
            payee: localFilters.value.payee || undefined,
            amount_min: localFilters.value.amount_min || undefined,
            amount_max: localFilters.value.amount_max || undefined,
            category_id: localFilters.value.category_id || undefined,
            start_date: localFilters.value.start_date || undefined,
            end_date: localFilters.value.end_date || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['transactions', 'filters'],
        },
    );
};

const clearFilters = () => {
    localFilters.value = {};
    router.get(`/accounts/${props.accountId}`, {}, { preserveState: true });
};
</script>

<template>
    <div class="space-y-4 rounded-lg border p-4">
        <h3 class="font-semibold">Filters</h3>
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div class="space-y-2">
                <Label for="payee">Payee</Label>
                <Input
                    id="payee"
                    v-model="localFilters.payee"
                    placeholder="Search by payee"
                />
            </div>

            <div class="space-y-2">
                <Label for="amount_min">Min Amount</Label>
                <Input
                    id="amount_min"
                    v-model.number="localFilters.amount_min"
                    type="number"
                    step="0.01"
                    placeholder="0.00"
                />
            </div>

            <div class="space-y-2">
                <Label for="amount_max">Max Amount</Label>
                <Input
                    id="amount_max"
                    v-model.number="localFilters.amount_max"
                    type="number"
                    step="0.01"
                    placeholder="0.00"
                />
            </div>

            <div class="space-y-2">
                <Label for="start_date">From Date</Label>
                <Input
                    id="start_date"
                    v-model="localFilters.start_date"
                    type="date"
                />
            </div>

            <div class="space-y-2">
                <Label for="end_date">To Date</Label>
                <Input
                    id="end_date"
                    v-model="localFilters.end_date"
                    type="date"
                />
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <Button variant="outline" @click="clearFilters">Clear</Button>
            <Button @click="applyFilters">Apply Filters</Button>
        </div>
    </div>
</template>
