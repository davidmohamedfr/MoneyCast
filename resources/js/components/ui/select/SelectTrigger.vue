<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { SelectTrigger as RekaSelectTrigger, type SelectTriggerProps } from 'reka-ui'
import { ChevronDown } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

const props = defineProps<SelectTriggerProps & {
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <RekaSelectTrigger
    data-slot="select-trigger"
    :class="cn(
      'border-input focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive dark:bg-input/30 flex h-9 w-full items-center justify-between gap-2 whitespace-nowrap rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none transition-[color,box-shadow] placeholder:text-muted-foreground focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 [&>span]:line-clamp-1 [&>span]:text-ellipsis [&>span]:overflow-hidden',
      props.class,
    )"
    v-bind="props"
  >
    <slot />
    <ChevronDown class="size-4 shrink-0 opacity-50" />
  </RekaSelectTrigger>
</template>
