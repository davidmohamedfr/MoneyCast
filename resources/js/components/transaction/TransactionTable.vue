<script setup lang="ts">
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useFormatCurrency } from '@/composables/useFormatCurrency';
import { useInitials } from '@/composables/useInitials';
import type { Transaction } from '@/types/transaction';
import { router } from '@inertiajs/vue3';
import Icon from '@/components/Icon.vue';
import { computed, ref } from 'vue';

const props = defineProps<{
    transactions: Transaction[];
}>();

const emit = defineEmits<{
    delete: [id: number];
}>();

const { formatCurrency, getTransactionColorClass } = useFormatCurrency();
const { getInitials } = useInitials();

// Sorting state
type SortField = 'date' | 'payee' | 'amount' | 'type' | 'category';
type SortDirection = 'asc' | 'desc';

const sortField = ref<SortField>('date');
const sortDirection = ref<SortDirection>('desc');

const sortedTransactions = computed(() => {
    const sorted = [...props.transactions].sort((a, b) => {
        let aValue: string | number;
        let bValue: string | number;

        switch (sortField.value) {
            case 'date':
                aValue = new Date(a.date).getTime();
                bValue = new Date(b.date).getTime();
                break;
            case 'payee':
                aValue = a.payee.toLowerCase();
                bValue = b.payee.toLowerCase();
                break;
            case 'amount':
                aValue = parseFloat(a.amount);
                bValue = parseFloat(b.amount);
                break;
            case 'type':
                aValue = a.type.toLowerCase();
                bValue = b.type.toLowerCase();
                break;
            case 'category':
                aValue = a.category?.name.toLowerCase() || '';
                bValue = b.category?.name.toLowerCase() || '';
                break;
            default:
                return 0;
        }

        if (aValue < bValue) return sortDirection.value === 'asc' ? -1 : 1;
        if (aValue > bValue) return sortDirection.value === 'asc' ? 1 : -1;
        return 0;
    });

    return sorted;
});

const toggleSort = (field: SortField) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'desc';
    }
};

const getSortIcon = (field: SortField) => {
    if (sortField.value !== field) return 'chevrons-up-down';
    return sortDirection.value === 'asc' ? 'chevron-up' : 'chevron-down';
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getTypeColor = (type: string) => {
    switch (type) {
        case 'income':
            return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        case 'expense':
            return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        case 'transfer':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
        default:
            return '';
    }
};

const getPayeeInitials = (payee: string) => {
    return getInitials(payee);
};

const getPayeeAvatarColor = (payee: string) => {
    // Generate consistent color based on payee name
    const colors = [
        'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
        'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
        'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-400',
        'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
    ];

    const hash = payee.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
    return colors[hash % colors.length];
};

const handleRowClick = (transaction: Transaction) => {
    router.visit(`/transactions/${transaction.id}/edit`);
};

// Delete dialog state
const deleteDialogOpen = ref(false);
const transactionToDelete = ref<Transaction | null>(null);

const openDeleteDialog = (transaction: Transaction, event: Event) => {
    event.stopPropagation();
    transactionToDelete.value = transaction;
    deleteDialogOpen.value = true;
};

const confirmDelete = () => {
    if (transactionToDelete.value) {
        emit('delete', transactionToDelete.value.id);
        deleteDialogOpen.value = false;
        transactionToDelete.value = null;
    }
};
</script>

<template>
    <div class="rounded-xl border bg-card shadow-lg">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b bg-muted/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold">
                            <button
                                class="flex items-center gap-2 font-semibold text-foreground transition-colors hover:text-primary focus-standard"
                                @click="toggleSort('payee')"
                            >
                                Payee
                                <Icon
                                    :name="getSortIcon('payee')"
                                    class="h-4 w-4"
                                    :class="
                                        sortField === 'payee'
                                            ? 'text-primary'
                                            : 'text-muted-foreground'
                                    "
                                />
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">
                            <button
                                class="flex items-center gap-2 font-semibold text-foreground transition-colors hover:text-primary focus-standard"
                                @click="toggleSort('category')"
                            >
                                Category
                                <Icon
                                    :name="getSortIcon('category')"
                                    class="h-4 w-4"
                                    :class="
                                        sortField === 'category'
                                            ? 'text-primary'
                                            : 'text-muted-foreground'
                                    "
                                />
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">
                            <button
                                class="flex items-center gap-2 font-semibold text-foreground transition-colors hover:text-primary focus-standard"
                                @click="toggleSort('date')"
                            >
                                Date
                                <Icon
                                    :name="getSortIcon('date')"
                                    class="h-4 w-4"
                                    :class="
                                        sortField === 'date'
                                            ? 'text-primary'
                                            : 'text-muted-foreground'
                                    "
                                />
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">
                            <button
                                class="flex items-center gap-2 font-semibold text-foreground transition-colors hover:text-primary focus-standard"
                                @click="toggleSort('type')"
                            >
                                Type
                                <Icon
                                    :name="getSortIcon('type')"
                                    class="h-4 w-4"
                                    :class="
                                        sortField === 'type'
                                            ? 'text-primary'
                                            : 'text-muted-foreground'
                                    "
                                />
                            </button>
                        </th>
                        <th class="px-4 py-3 text-right text-sm font-semibold">
                            <button
                                class="flex items-center justify-end gap-2 font-semibold text-foreground transition-colors hover:text-primary focus-standard ml-auto"
                                @click="toggleSort('amount')"
                            >
                                Amount
                                <Icon
                                    :name="getSortIcon('amount')"
                                    class="h-4 w-4"
                                    :class="
                                        sortField === 'amount'
                                            ? 'text-primary'
                                            : 'text-muted-foreground'
                                    "
                                />
                            </button>
                        </th>
                        <th class="px-4 py-3 text-right text-sm font-semibold">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr
                        v-for="transaction in sortedTransactions"
                        :key="transaction.id"
                        class="cursor-pointer transition-colors hover:bg-accent/50 group"
                        @click="handleRowClick(transaction)"
                    >
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <Avatar
                                    :class="getPayeeAvatarColor(transaction.payee)"
                                    class="h-10 w-10 shrink-0"
                                >
                                    <AvatarFallback
                                        :class="getPayeeAvatarColor(transaction.payee)"
                                        class="font-semibold"
                                    >
                                        {{ getPayeeInitials(transaction.payee) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="min-w-0">
                                    <p class="font-medium text-foreground truncate">
                                        {{ transaction.payee }}
                                    </p>
                                    <p
                                        v-if="transaction.account"
                                        class="text-sm text-muted-foreground truncate"
                                    >
                                        {{ transaction.account.name }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <Badge
                                v-if="transaction.category"
                                variant="outline"
                                class="text-xs"
                            >
                                {{ transaction.category.name }}
                            </Badge>
                            <span v-else class="text-sm text-muted-foreground">—</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-muted-foreground">
                            {{ formatDate(transaction.date) }}
                        </td>
                        <td class="px-4 py-3">
                            <Badge :class="getTypeColor(transaction.type)" class="capitalize">
                                {{ transaction.type }}
                            </Badge>
                        </td>
                        <td
                            class="px-4 py-3 text-right font-semibold tabular-nums"
                            :class="getTransactionColorClass(transaction.type)"
                        >
                            {{ transaction.type === 'expense' ? '-' : '+'
                            }}{{ formatCurrency(parseFloat(transaction.amount), transaction.account?.currency || 'EUR') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click.stop="handleRowClick(transaction)"
                                >
                                    Edit
                                </Button>
                                <Button
                                    variant="destructive"
                                    size="sm"
                                    @click.stop="openDeleteDialog(transaction, $event)"
                                >
                                    Delete
                                </Button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Delete Dialog -->
        <Dialog v-model:open="deleteDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Transaction?</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete this transaction? This action
                        cannot be undone.
                    </DialogDescription>
                </DialogHeader>

                <div
                    v-if="transactionToDelete"
                    class="space-y-3 rounded-lg border border-destructive/30 bg-destructive/5 p-4"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <Avatar
                                :class="getPayeeAvatarColor(transactionToDelete.payee)"
                                class="h-10 w-10 shrink-0"
                            >
                                <AvatarFallback
                                    :class="getPayeeAvatarColor(transactionToDelete.payee)"
                                    class="font-semibold"
                                >
                                    {{ getPayeeInitials(transactionToDelete.payee) }}
                                </AvatarFallback>
                            </Avatar>
                            <div>
                                <p class="font-semibold text-foreground">
                                    {{ transactionToDelete.payee }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    {{ formatDate(transactionToDelete.date) }}
                                </p>
                            </div>
                        </div>
                        <Badge :class="getTypeColor(transactionToDelete.type)">
                            {{ transactionToDelete.type }}
                        </Badge>
                    </div>

                    <div class="flex items-center justify-between border-t pt-3">
                        <span class="text-sm text-muted-foreground">Amount:</span>
                        <span
                            :class="getTransactionColorClass(transactionToDelete.type)"
                            class="text-lg font-semibold"
                        >
                            {{ transactionToDelete.type === 'expense' ? '-' : '+'
                            }}{{ formatCurrency(parseFloat(transactionToDelete.amount), transactionToDelete.account?.currency || 'EUR') }}
                        </span>
                    </div>

                    <div
                        v-if="transactionToDelete.category"
                        class="flex items-center justify-between"
                    >
                        <span class="text-sm text-muted-foreground">Category:</span>
                        <Badge variant="outline">{{
                            transactionToDelete.category.name
                        }}</Badge>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="deleteDialogOpen = false">
                        Cancel
                    </Button>
                    <Button variant="destructive" @click="confirmDelete">
                        Delete Transaction
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
