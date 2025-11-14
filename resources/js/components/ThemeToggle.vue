<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useAppearance } from '@/composables/useAppearance';
import { Monitor, Moon, Sun } from 'lucide-vue-next';

const { appearance, updateAppearance } = useAppearance();

const themes = [
    { value: 'light', label: 'Light', icon: Sun },
    { value: 'dark', label: 'Dark', icon: Moon },
    { value: 'system', label: 'System', icon: Monitor },
] as const;
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button
                variant="ghost"
                size="icon"
                class="relative h-9 w-9 rounded-lg transition-colors hover:bg-accent hover:text-accent-foreground"
            >
                <Sun
                    class="h-5 w-5 scale-100 rotate-0 transition-all dark:scale-0 dark:-rotate-90"
                />
                <Moon
                    class="absolute h-5 w-5 scale-0 rotate-90 transition-all dark:scale-100 dark:rotate-0"
                />
                <span class="sr-only">Toggle theme</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-40">
            <DropdownMenuItem
                v-for="theme in themes"
                :key="theme.value"
                @click="updateAppearance(theme.value)"
                class="flex cursor-pointer items-center gap-3 px-3 py-2"
                :class="{
                    'bg-accent text-accent-foreground':
                        appearance === theme.value,
                }"
            >
                <component
                    :is="theme.icon"
                    class="h-4 w-4"
                    :class="{
                        'text-primary': appearance === theme.value,
                    }"
                />
                <span>{{ theme.label }}</span>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
