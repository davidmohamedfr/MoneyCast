<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { ref } from 'vue';

const props = defineProps<{
    modelValue: File | null;
    error?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: File | null];
}>();

const isDragging = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);

const handleDrop = (e: DragEvent) => {
    isDragging.value = false;
    const files = e.dataTransfer?.files;
    if (files && files.length > 0) {
        emit('update:modelValue', files[0]);
    }
};

const handleFileSelect = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const files = target.files;
    if (files && files.length > 0) {
        emit('update:modelValue', files[0]);
    }
};

const removeFile = () => {
    emit('update:modelValue', null);
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};
</script>

<template>
    <div class="w-full">
        <div
            v-if="!modelValue"
            class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed p-8"
            :class="{
                'border-primary bg-primary/5': isDragging,
                'border-destructive': error,
            }"
            @dragover.prevent="isDragging = true"
            @dragleave="isDragging = false"
            @drop.prevent="handleDrop"
        >
            <svg
                class="mb-4 h-12 w-12 text-muted-foreground"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                />
            </svg>
            <p class="mb-2 text-sm text-muted-foreground">
                <span class="font-semibold">Click to upload</span> or drag and drop
            </p>
            <p class="text-xs text-muted-foreground">CSV files only (MAX. 10MB)</p>
            <input
                ref="fileInput"
                type="file"
                class="hidden"
                accept=".csv,text/csv"
                @change="handleFileSelect"
            />
            <Button type="button" class="mt-4" @click="fileInput?.click()">Choose File</Button>
        </div>

        <div v-else class="flex items-center justify-between rounded-lg border p-4">
            <div class="flex items-center gap-3">
                <svg class="h-10 w-10 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                    />
                </svg>
                <div>
                    <p class="text-sm font-medium">{{ modelValue.name }}</p>
                    <p class="text-xs text-muted-foreground">{{ formatFileSize(modelValue.size) }}</p>
                </div>
            </div>
            <Button type="button" variant="ghost" size="sm" @click="removeFile">Remove</Button>
        </div>

        <p v-if="error" class="mt-2 text-sm text-destructive">{{ error }}</p>
    </div>
</template>
