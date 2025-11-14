<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { useFormatCurrency } from '@/composables/useFormatCurrency';
import type { Transaction } from '@/types/transaction';
import { Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    transaction: Transaction;
}>();

const emit = defineEmits<{
    delete: [id: number];
}>();

const { formatCurrency, getTransactionColorClass } = useFormatCurrency();

const showDeleteDialog = ref(false);

const formattedAmount = computed(() => {
    const currency = props.transaction.account?.currency || 'EUR';
    return formatCurrency(props.transaction.amount, currency);
});

const formattedDate = computed(() => {
    return new Date(props.transaction.date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
});

const typeColor = computed(() => {
    switch (props.transaction.type) {
        case 'income':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'expense':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        case 'transfer':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        default:
            return '';
    }
});

const amountColorClass = computed(() =>
    getTransactionColorClass(props.transaction.type),
);

const handleEdit = () => {
    router.visit(`/transactions/${props.transaction.id}/edit`);
};

const confirmDelete = () => {
    emit('delete', props.transaction.id);
    showDeleteDialog.value = false;
};
</script>

<template>
    <Card>
        <CardHeader>
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <CardTitle class="text-lg">{{
                        transaction.payee
                    }}</CardTitle>
                    <CardDescription>
                        {{ formattedDate }}
                        <span v-if="transaction.account" class="ml-2">
                            •
                            <Link
                                :href="`/accounts/${transaction.account.id}`"
                                class="hover:underline focus:underline"
                            >
                                {{ transaction.account.name }}
                            </Link>
                        </span>
                    </CardDescription>
                </div>
                <div class="flex flex-col items-end gap-2">
                    <Badge :class="typeColor">
                        {{ transaction.type }}
                    </Badge>
                    <span
                        :class="amountColorClass"
                        class="text-xl font-semibold"
                    >
                        {{ transaction.type === 'expense' ? '-' : '+'
                        }}{{ formattedAmount }}
                    </span>
                </div>
            </div>
        </CardHeader>

        <CardContent v-if="transaction.description || transaction.category">
            <div class="space-y-2 text-sm">
                <div
                    v-if="transaction.category"
                    class="flex items-center gap-2"
                >
                    <span class="text-muted-foreground">Category:</span>
                    <Badge variant="outline">{{
                        transaction.category.name
                    }}</Badge>
                </div>
                <div
                    v-if="transaction.description"
                    class="text-muted-foreground"
                >
                    {{ transaction.description }}
                </div>
            </div>
        </CardContent>

        <CardFooter class="flex justify-end gap-2">
            <Button variant="outline" size="sm" @click="handleEdit"
                >Edit</Button
            >
            <Dialog v-model:open="showDeleteDialog">
                <DialogTrigger as-child>
                    <Button variant="destructive" size="sm">Delete</Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete Transaction?</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete this transaction?
                            This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>

                    <!-- Transaction details for context - ADHD/Autism users -->
                    <div
                        class="space-y-3 rounded-lg border border-destructive/30 bg-destructive/5 p-4"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="font-semibold text-foreground">
                                    {{ transaction.payee }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    {{ formattedDate }}
                                </p>
                            </div>
                            <Badge :class="typeColor">
                                {{ transaction.type }}
                            </Badge>
                        </div>

                        <div
                            class="flex items-center justify-between border-t pt-3"
                        >
                            <span class="text-sm text-muted-foreground"
                                >Amount:</span
                            >
                            <span
                                :class="amountColorClass"
                                class="text-lg font-semibold"
                            >
                                {{ transaction.type === 'expense' ? '-' : '+'
                                }}{{ formattedAmount }}
                            </span>
                        </div>

                        <div
                            v-if="transaction.account"
                            class="flex items-center justify-between"
                        >
                            <span class="text-sm text-muted-foreground"
                                >Account:</span
                            >
                            <span class="text-sm font-medium">{{
                                transaction.account.name
                            }}</span>
                        </div>

                        <div
                            v-if="transaction.category"
                            class="flex items-center justify-between"
                        >
                            <span class="text-sm text-muted-foreground"
                                >Category:</span
                            >
                            <Badge variant="outline">{{
                                transaction.category.name
                            }}</Badge>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button
                            variant="outline"
                            @click="showDeleteDialog = false"
                        >
                            Cancel
                        </Button>
                        <Button variant="destructive" @click="confirmDelete">
                            Delete Transaction
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </CardFooter>
    </Card>
</template>
