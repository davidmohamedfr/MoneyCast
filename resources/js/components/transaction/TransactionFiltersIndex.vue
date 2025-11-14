<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import { Button } from '@/components/ui/button';
import { DatePicker } from '@/components/ui/date-picker';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { router } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';

interface Filters {
    search?: string;
    type?: string;
    category_id?: number;
    start_date?: string;
    end_date?: string;
}

const props = defineProps<{
    filters: Filters;
    categories?: Array<{ id: number; name: string }>;
}>();

const localFilters = ref<Filters>({ ...props.filters });
const isSearching = ref(false);

// Auto-apply search with debounce for ADHD users (immediate feedback)
let searchTimeout: ReturnType<typeof setTimeout>;
watch(
    () => localFilters.value.search,
    () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyFilters();
        }, 300);
    },
);

const applyFilters = () => {
    isSearching.value = true;
    router.get(
        '/transactions',
        {
            search: localFilters.value.search || undefined,
            type: localFilters.value.type || undefined,
            category_id: localFilters.value.category_id || undefined,
            start_date: localFilters.value.start_date || undefined,
            end_date: localFilters.value.end_date || undefined,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                isSearching.value = false;
            },
        },
    );
};

const clearFilters = () => {
    localFilters.value = {};
    router.get('/transactions');
};

const hasActiveFilters = () => {
    return (
        localFilters.value.search ||
        localFilters.value.type ||
        localFilters.value.category_id ||
        localFilters.value.start_date ||
        localFilters.value.end_date
    );
};

onBeforeUnmount(() => {
    clearTimeout(searchTimeout);
});
</script>

<template>
    <div class="space-y-4 rounded-lg border border-muted bg-muted/30 p-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-foreground">
                Search & Filter
            </h3>
            <Button
                v-if="hasActiveFilters()"
                variant="ghost"
                size="sm"
                @click="clearFilters"
            >
                <Icon name="x" class="mr-1 h-3 w-3" aria-hidden="true" />
                Clear
            </Button>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <!-- Search by payee -->
            <div class="space-y-2 md:col-span-2">
                <Label for="search" class="sr-only">Search transactions</Label>
                <div class="relative">
                    <Icon
                        name="search"
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        aria-hidden="true"
                    />
                    <Input
                        id="search"
                        v-model="localFilters.search"
                        placeholder="Search by payee or description..."
                        class="pl-9"
                        aria-label="Search transactions by payee or description"
                    />
                    <Icon
                        v-if="isSearching"
                        name="loader-circle"
                        class="absolute top-1/2 right-3 h-4 w-4 -translate-y-1/2 animate-spin text-muted-foreground"
                        aria-hidden="true"
                        aria-label="Searching"
                    />
                </div>
            </div>

            <!-- Filter by type -->
            <div class="space-y-2">
                <Label for="type" class="sr-only">Transaction type</Label>
                <Select
                    v-model="localFilters.type"
                    @update:model-value="applyFilters"
                >
                    <SelectTrigger
                        id="type"
                        aria-label="Filter by transaction type"
                    >
                        <SelectValue placeholder="All types" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">All types</SelectItem>
                        <SelectItem value="income">Income</SelectItem>
                        <SelectItem value="expense">Expense</SelectItem>
                        <SelectItem value="transfer">Transfer</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Filter by category (if categories provided) -->
            <div v-if="categories && categories.length > 0" class="space-y-2">
                <Label for="category" class="sr-only">Category</Label>
                <Select
                    v-model="localFilters.category_id"
                    @update:model-value="applyFilters"
                >
                    <SelectTrigger
                        id="category"
                        aria-label="Filter by category"
                    >
                        <SelectValue placeholder="All categories" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem :value="undefined"
                            >All categories</SelectItem
                        >
                        <SelectItem
                            v-for="category in categories"
                            :key="category.id"
                            :value="category.id"
                        >
                            {{ category.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <!-- Date range filters -->
        <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
                <Label for="start_date">From Date</Label>
                <DatePicker
                    id="start_date"
                    v-model="localFilters.start_date"
                    placeholder="Select start date"
                    @change="applyFilters"
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
                    @change="applyFilters"
                />
            </div>
        </div>

        <!-- Results count for ADHD/Dyslexia users (clear feedback) -->
        <p
            v-if="hasActiveFilters()"
            class="text-xs text-muted-foreground"
            role="status"
            aria-live="polite"
        >
            Filters active - results are filtered
        </p>
    </div>
</template>
