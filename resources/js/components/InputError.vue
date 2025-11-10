<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

defineProps<{
    message?: string;
    helpText?: string;
}>();
</script>

<template>
    <div v-show="message" class="space-y-1">
        <div class="flex items-start gap-2">
            <Icon
                name="alert-circle"
                class="mt-0.5 h-4 w-4 shrink-0 text-destructive"
                aria-hidden="true"
            />
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-destructive">
                    {{ message }}
                </p>
                <p v-if="helpText" class="mt-1 text-xs text-muted-foreground">
                    {{ helpText }}
                </p>
            </div>
            <TooltipProvider v-if="helpText">
                <Tooltip>
                    <TooltipTrigger as-child>
                        <button
                            type="button"
                            class="focus-standard shrink-0 rounded-full p-1 hover:bg-muted"
                            aria-label="Additional help for this field"
                        >
                            <Icon
                                name="info"
                                class="h-3.5 w-3.5 text-muted-foreground"
                            />
                        </button>
                    </TooltipTrigger>
                    <TooltipContent
                        :side="'top'"
                        :side-offset="4"
                        class="max-w-xs"
                    >
                        <p class="text-xs">{{ helpText }}</p>
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>
        </div>
    </div>
</template>
