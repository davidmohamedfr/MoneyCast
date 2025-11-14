<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import KeyboardShortcutsModal from '@/components/KeyboardShortcutsModal.vue';
import { useKeyboardShortcuts } from '@/composables/useKeyboardShortcuts';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

// Enable global keyboard shortcuts
useKeyboardShortcuts();

const page = usePage();
const pageKey = computed(() => page.url);
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden" :key="pageKey">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
        <KeyboardShortcutsModal />
    </AppShell>
</template>
