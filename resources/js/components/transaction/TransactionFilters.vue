<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import { Button } from '@/components/ui/button';
import { DatePicker } from '@/components/ui/date-picker';
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
const isApplyingFilters = ref(false);
const isClearingFilters = ref(false);

const applyFilters = () => {
    isApplyingFilters.value = true;
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
            onFinish: () => {
                isApplyingFilters.value = false;
            },
        },
    );
};

const clearFilters = () => {
    isClearingFilters.value = true;
    localFilters.value = {};
    router.get(
        `/accounts/${props.accountId}`,
        {},
        {
            preserveState: true,
            onFinish: () => {
                isClearingFilters.value = false;
            },
        },
    );
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
                <DatePicker
                    id="start_date"
                    v-model="localFilters.start_date"
                    placeholder="Select start date"
                />
            </div>

            <div class="space-y-2">
                <Label for="end_date">To Date</Label>
                <DatePicker
                    id="end_date"
                    v-model="localFilters.end_date"
                    :min-date="
                        localFilters.start_date
                            ? new Date(localFilters.start_date)
                            : null
                    "
                    placeholder="Select end date"
                />
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <Button
                variant="outline"
                @click="clearFilters"
                :disabled="isClearingFilters || isApplyingFilters"
            >
                <Icon
                    v-if="isClearingFilters"
                    name="loader-circle"
                    class="mr-2 h-4 w-4 animate-spin"
                    aria-hidden="true"
                />
                Clear
            </Button>
            <Button
                @click="applyFilters"
                :disabled="isApplyingFilters || isClearingFilters"
            >
                <Icon
                    v-if="isApplyingFilters"
                    name="loader-circle"
                    class="mr-2 h-4 w-4 animate-spin"
                    aria-hidden="true"
                />
                Apply Filters
            </Button>
        </div>
    </div>
</template>
