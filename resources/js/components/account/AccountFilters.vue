<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import { Button } from '@/components/ui/button';
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
    bank?: string;
}

const props = defineProps<{
    filters: Filters;
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
        '/accounts',
        {
            search: localFilters.value.search || undefined,
            type: localFilters.value.type || undefined,
            bank: localFilters.value.bank || undefined,
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
    router.get('/accounts');
};

const hasActiveFilters = () => {
    return (
        localFilters.value.search ||
        localFilters.value.type ||
        localFilters.value.bank
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

        <div class="grid gap-4 md:grid-cols-3">
            <!-- Search by name/bank -->
            <div class="space-y-2 md:col-span-2">
                <Label for="search" class="sr-only">Search accounts</Label>
                <div class="relative">
                    <Icon
                        name="search"
                        class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        aria-hidden="true"
                    />
                    <Input
                        id="search"
                        v-model="localFilters.search"
                        placeholder="Search accounts..."
                        class="pl-9"
                        aria-label="Search accounts by name or bank"
                    />
                    <Icon
                        v-if="isSearching"
                        name="loader-circle"
                        class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 animate-spin text-muted-foreground"
                        aria-hidden="true"
                        aria-label="Searching"
                    />
                </div>
            </div>

            <!-- Filter by type -->
            <div class="space-y-2">
                <Label for="type" class="sr-only">Account type</Label>
                <Select v-model="localFilters.type" @update:model-value="applyFilters">
                    <SelectTrigger id="type" aria-label="Filter by account type">
                        <SelectValue placeholder="All types" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">All types</SelectItem>
                        <SelectItem value="checking">Checking</SelectItem>
                        <SelectItem value="savings">Savings</SelectItem>
                        <SelectItem value="credit">Credit</SelectItem>
                    </SelectContent>
                </Select>
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
