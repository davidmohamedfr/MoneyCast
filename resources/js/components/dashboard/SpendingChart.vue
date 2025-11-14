<script setup lang="ts">
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { computed } from 'vue';

interface CategorySpending {
    category: string;
    amount: number;
    transaction_count: number;
    color: string;
}

const props = defineProps<{
    categoryData: CategorySpending[];
}>();

// DataScout-inspired color palette
const chartColors = [
    'hsl(250, 95%, 65%)', // Purple (primary)
    'hsl(190, 95%, 55%)', // Cyan
    'hsl(160, 85%, 50%)', // Green
    'hsl(280, 90%, 65%)', // Violet
    'hsl(340, 85%, 60%)', // Pink
    'hsl(30, 90%, 60%)', // Orange
    'hsl(210, 90%, 60%)', // Blue
    'hsl(50, 90%, 55%)', // Yellow
];

// Limit to top 5 categories + aggregate "Other"
const displayCategories = computed(() => {
    if (props.categoryData.length <= 5) {
        return props.categoryData;
    }

    // Sort by amount (highest first)
    const sorted = [...props.categoryData].sort(
        (a, b) => Math.abs(b.amount) - Math.abs(a.amount),
    );

    // Take top 5
    const top5 = sorted.slice(0, 5);

    // Aggregate remaining into "Other"
    const remaining = sorted.slice(5);
    const otherAmount = remaining.reduce(
        (sum, item) => sum + Math.abs(item.amount),
        0,
    );
    const otherCount = remaining.reduce(
        (sum, item) => sum + item.transaction_count,
        0,
    );

    if (otherAmount > 0) {
        return [
            ...top5,
            {
                category: 'Other',
                amount: otherAmount,
                transaction_count: otherCount,
                color: chartColors[5],
            },
        ];
    }

    return top5;
});

// Sort categories by amount (highest first)
const sortedCategories = computed(() => {
    return [...displayCategories.value].sort(
        (a, b) => Math.abs(b.amount) - Math.abs(a.amount),
    );
});

const totalSpending = computed(() => {
    return displayCategories.value.reduce(
        (sum, item) => sum + Math.abs(item.amount),
        0,
    );
});

const getCategoryPercentage = (amount: number) => {
    if (totalSpending.value === 0) return 0;
    const percentage = (Math.abs(amount) / totalSpending.value) * 100;
    return percentage.toFixed(1);
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'EUR',
    }).format(amount);
};
</script>

<template>
    <Card class="flex h-[340px] flex-col">
        <CardHeader class="flex-shrink-0">
            <CardTitle>Top Spending Breakdown</CardTitle>
            <CardDescription
                >Your expenses by category this month</CardDescription
            >
        </CardHeader>
        <CardContent class="flex flex-1 flex-col overflow-hidden">
            <div
                v-if="categoryData.length > 0"
                class="flex flex-1 flex-col space-y-4"
            >
                <!-- Category List with Progress Bars (scrollable if needed) -->
                <div class="flex-1 space-y-4 overflow-y-auto pr-1">
                    <div
                        v-for="(category, index) in sortedCategories"
                        :key="category.category"
                        class="space-y-2"
                    >
                        <!-- Category name, amount, and percentage -->
                        <div class="flex items-baseline justify-between gap-3">
                            <span class="text-sm font-medium text-foreground">
                                {{ category.category }}
                            </span>
                            <div class="flex items-baseline gap-3">
                                <!-- Amount (primary) -->
                                <span
                                    class="text-sm font-semibold text-foreground"
                                >
                                    {{
                                        formatCurrency(
                                            Math.abs(category.amount),
                                        )
                                    }}
                                </span>
                                <!-- Details (percentage + count) stacked -->
                                <div class="flex flex-col items-end gap-0.5">
                                    <!-- Percentage (secondary primary) -->
                                    <span
                                        class="text-sm font-medium text-muted-foreground"
                                    >
                                        {{
                                            getCategoryPercentage(
                                                category.amount,
                                            )
                                        }}%
                                    </span>
                                    <!-- Transaction count (tertiary) -->
                                    <span
                                        class="text-xs text-muted-foreground/70"
                                    >
                                        {{ category.transaction_count }}
                                        {{
                                            category.transaction_count === 1
                                                ? 'transaction'
                                                : 'transactions'
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Progress bar -->
                        <div
                            class="h-2.5 w-full overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full transition-all duration-300"
                                :style="{
                                    width: `${getCategoryPercentage(category.amount)}%`,
                                    backgroundColor:
                                        chartColors[index % chartColors.length],
                                }"
                                role="progressbar"
                                :aria-valuenow="
                                    Number(
                                        getCategoryPercentage(category.amount),
                                    )
                                "
                                aria-valuemin="0"
                                aria-valuemax="100"
                                :aria-label="`${category.category} spending: ${getCategoryPercentage(category.amount)}%`"
                            />
                        </div>
                    </div>
                </div>

                <!-- Footer: Total Spending + Category Count (fixed at bottom) -->
                <div class="flex-shrink-0 space-y-3 border-t pt-4">
                    <!-- Total Spending Summary -->
                    <div class="flex items-baseline justify-between">
                        <span class="text-base font-medium text-foreground"
                            >Total Spending</span
                        >
                        <span class="text-lg font-bold text-foreground">
                            {{ formatCurrency(totalSpending) }}
                        </span>
                    </div>

                    <!-- Category Count -->
                    <div
                        class="flex items-center justify-between text-xs text-muted-foreground"
                    >
                        <span
                            >{{ displayCategories.length }} categories
                            shown</span
                        >
                        <span v-if="categoryData.length > 5">
                            ({{ categoryData.length - 5 }} grouped as "Other")
                        </span>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="flex flex-1 flex-col items-center justify-center text-center"
            >
                <div
                    class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-muted"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="text-muted-foreground"
                    >
                        <path d="M3 3v18h18" />
                        <path d="m19 9-5 5-4-4-3 3" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-foreground">
                    No spending data yet
                </p>
                <p class="text-xs text-muted-foreground">
                    Start adding expenses to see your breakdown
                </p>
            </div>
        </CardContent>
    </Card>
</template>
