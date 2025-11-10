<script setup lang="ts">
import { SidebarInset } from '@/components/ui/sidebar';
import { computed } from 'vue';

interface Props {
    variant?: 'header' | 'sidebar';
    class?: string;
}

const props = defineProps<Props>();
const className = computed(() => props.class);
</script>

<template>
    <SidebarInset v-if="props.variant === 'sidebar'" :class="className">
        <main id="main-content" class="focus:outline-none" tabindex="-1">
            <slot />
        </main>
    </SidebarInset>
    <main
        v-else
        id="main-content"
        class="mx-auto flex h-full w-full max-w-7xl flex-1 flex-col gap-4 rounded-xl focus:outline-none"
        :class="className"
        tabindex="-1"
    >
        <slot />
    </main>
</template>
