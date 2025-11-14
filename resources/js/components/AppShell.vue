<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import { useFlashMessages } from '@/composables/useFlashMessages';
import { usePage } from '@inertiajs/vue3';
import { Toaster } from 'vue-sonner';

interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

const isOpen = usePage().props.sidebarOpen;

// Initialize flash message handling for toast notifications
useFlashMessages();
</script>

<template>
    <!-- Toast Notifications - ADHD/Autism friendly feedback system -->
    <Toaster
        position="bottom-right"
        :duration="5000"
        :close-button="true"
        :visible-toasts="3"
        rich-colors
    />

    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
        <!-- Skip Navigation Links - WCAG 2.1 AA - 2.4.1 Bypass Blocks -->
        <a href="#main-content" class="skip-link"> Skip to main content </a>
        <slot />
    </div>
    <SidebarProvider v-else :default-open="isOpen">
        <!-- Skip Navigation Links - WCAG 2.1 AA - 2.4.1 Bypass Blocks -->
        <a href="#main-content" class="skip-link"> Skip to main content </a>
        <a href="#sidebar-nav" class="skip-link"> Skip to navigation </a>
        <slot />
    </SidebarProvider>
</template>

<style scoped>
/*
  Skip links for keyboard navigation
  Critical for ADHD, Autism, and keyboard-only users
  Hidden until focused, positioned at top-left when visible
*/
.skip-link {
    position: absolute;
    top: -40px;
    left: 0;
    background: hsl(var(--primary));
    color: hsl(var(--primary-foreground));
    padding: 8px 16px;
    text-decoration: none;
    border-radius: 0 0 4px 0;
    z-index: 100;
    font-weight: 600;
    transition: top 0.2s ease-in-out;
}

.skip-link:focus {
    top: 0;
    outline: 3px solid hsl(var(--ring));
    outline-offset: 2px;
}

/* Respect reduced motion preference */
@media (prefers-reduced-motion: reduce) {
    .skip-link {
        transition: none;
    }
}
</style>
