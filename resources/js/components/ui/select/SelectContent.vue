<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import {
  SelectContent as RekaSelectContent,
  SelectPortal,
  SelectViewport,
  type SelectContentEmits,
  type SelectContentProps,
} from 'reka-ui'
import { cn } from '@/lib/utils'

const props = withDefaults(
  defineProps<SelectContentProps & {
    class?: HTMLAttributes['class']
  }>(),
  {
    position: 'popper',
    sideOffset: 4,
  },
)

const emits = defineEmits<SelectContentEmits>()

const forwardedEmits = {
  closeAutoFocus: (event: any) => emits('closeAutoFocus', event),
  escapeKeyDown: (event: any) => emits('escapeKeyDown', event),
  pointerDownOutside: (event: any) => emits('pointerDownOutside', event),
}
</script>

<template>
  <SelectPortal :disabled="true">
    <RekaSelectContent
      data-slot="select-content"
      :class="cn(
        'data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2 relative z-50 max-h-96 min-w-32 overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-md',
        props.position === 'popper' &&
          'data-[side=bottom]:translate-y-1 data-[side=left]:-translate-x-1 data-[side=right]:translate-x-1 data-[side=top]:-translate-y-1',
        props.class,
      )"
      v-bind="{ ...props, ...forwardedEmits }"
    >
      <SelectViewport
        :class="cn(
          'p-1',
          props.position === 'popper' &&
            'h-[var(--reka-select-trigger-height)] w-full min-w-[var(--reka-select-trigger-width)]',
        )"
      >
        <slot />
      </SelectViewport>
    </RekaSelectContent>
  </SelectPortal>
</template>
