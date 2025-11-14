<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const isOpen = ref(false);

// Detect platform for modifier key display
const isMac = computed(() => {
    return (
        /Mac|iPhone|iPod|iPad/.test(navigator.platform) ||
        navigator.userAgent.includes('Mac')
    );
});

const modifierKey = computed(() => (isMac.value ? '⌘' : 'Ctrl'));

const shortcuts = [
    {
        category: 'Navigation',
        items: [
            {
                keys: [
                    { key: modifierKey.value, isModifier: true },
                    { key: '⇧', isModifier: true },
                    'D',
                ],
                description: 'Go to Dashboard',
            },
            {
                keys: [
                    { key: modifierKey.value, isModifier: true },
                    { key: '⇧', isModifier: true },
                    'A',
                ],
                description: 'Go to Accounts',
            },
            {
                keys: [
                    { key: modifierKey.value, isModifier: true },
                    { key: '⇧', isModifier: true },
                    'T',
                ],
                description: 'Go to Transactions',
            },
        ],
    },
    {
        category: 'Actions',
        items: [
            {
                keys: [
                    { key: modifierKey.value, isModifier: true },
                    { key: '⇧', isModifier: true },
                    'N',
                ],
                description: 'New Transaction',
            },
        ],
    },
    {
        category: 'Help',
        items: [
            {
                keys: [{ key: '?', isModifier: false }],
                description: 'Show Shortcuts',
            },
        ],
    },
];

const handleShowShortcuts = () => {
    isOpen.value = true;
};

onMounted(() => {
    window.addEventListener('show-keyboard-shortcuts', handleShowShortcuts);
});

onUnmounted(() => {
    window.removeEventListener('show-keyboard-shortcuts', handleShowShortcuts);
});
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="max-w-xl">
            <DialogHeader>
                <DialogTitle>Keyboard Shortcuts</DialogTitle>
                <DialogDescription>
                    Speed up your workflow with these shortcuts
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-6">
                <div
                    v-for="section in shortcuts"
                    :key="section.category"
                    class="space-y-3"
                >
                    <h4 class="text-sm font-semibold text-foreground">
                        {{ section.category }}
                    </h4>
                    <div class="grid gap-2">
                        <div
                            v-for="(shortcut, index) in section.items"
                            :key="index"
                            class="flex items-center justify-between rounded-lg border border-border p-3 hover:bg-muted/50"
                        >
                            <span class="text-sm text-foreground">{{
                                shortcut.description
                            }}</span>
                            <div class="flex items-center gap-1">
                                <template
                                    v-for="(keyItem, keyIndex) in shortcut.keys"
                                    :key="keyIndex"
                                >
                                    <kbd
                                        class="inline-flex h-6 min-w-6 items-center justify-center rounded border border-border bg-muted px-2 text-xs font-semibold text-foreground"
                                    >
                                        {{
                                            typeof keyItem === 'string'
                                                ? keyItem
                                                : keyItem.key
                                        }}
                                    </kbd>
                                    <span
                                        v-if="
                                            keyIndex < shortcut.keys.length - 1
                                        "
                                        class="mx-1 text-xs text-muted-foreground"
                                    >
                                        +
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="mt-4 rounded-lg border border-border bg-muted/30 p-4 text-sm text-muted-foreground"
            >
                <p class="font-medium text-foreground">Pro tip:</p>
                <p class="mt-1">
                    Use {{ modifierKey }} + Shift combinations for safe
                    shortcuts that won't conflict with browser defaults.
                    Shortcuts are automatically disabled in input fields.
                </p>
            </div>
        </DialogContent>
    </Dialog>
</template>
