<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Category, CategoryType } from '@/types/category';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    open: boolean;
    type: CategoryType;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    created: [category: Category];
}>();

const name = ref('');
const isSubmitting = ref(false);
const error = ref('');

const handleSubmit = async () => {
    if (!name.value.trim()) {
        error.value = 'Category name is required';
        return;
    }

    isSubmitting.value = true;
    error.value = '';

    try {
        // Make POST request to create category
        const response = await fetch('/categories', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                name: name.value.trim(),
                type: props.type,
            }),
        });

        if (!response.ok) {
            const data = await response.json();
            throw new Error(data.message || 'Failed to create category');
        }

        const data = await response.json();

        // Reset form
        name.value = '';

        // Emit success event with new category
        emit('created', data.category);

        // Close dialog
        emit('update:open', false);

        // Reload page to refresh categories list
        router.reload({ only: ['categories'] });
    } catch (err) {
        error.value =
            err instanceof Error
                ? err.message
                : 'Failed to create category. Please try again.';
    } finally {
        isSubmitting.value = false;
    }
};

const handleClose = () => {
    if (!isSubmitting.value) {
        name.value = '';
        error.value = '';
        emit('update:open', false);
    }
};
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Create New Category</DialogTitle>
                <DialogDescription>
                    Add a new {{ type }} category to organize your transactions
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div class="space-y-2">
                    <Label for="category-name">
                        Category Name
                        <span class="text-destructive" aria-label="required"
                            >*</span
                        >
                    </Label>
                    <Input
                        id="category-name"
                        v-model="name"
                        placeholder="e.g., Groceries, Salary"
                        :disabled="isSubmitting"
                        required
                        aria-required="true"
                        autofocus
                    />
                    <InputError
                        :message="error"
                        help-text="Choose a descriptive name for this category"
                    />
                </div>

                <div
                    class="rounded-md border border-muted bg-muted/30 px-3 py-2"
                >
                    <p class="text-sm text-muted-foreground">
                        <strong>Type:</strong>
                        <span class="ml-1 capitalize">{{ type }}</span>
                    </p>
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="handleClose"
                        :disabled="isSubmitting"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="isSubmitting">
                        <Icon
                            v-if="isSubmitting"
                            name="loader-circle"
                            class="mr-2 h-4 w-4 animate-spin"
                            aria-hidden="true"
                        />
                        Create Category
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
