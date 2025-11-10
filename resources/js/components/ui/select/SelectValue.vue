<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { SelectValue as RekaSelectValue, type SelectValueProps } from 'reka-ui'
import { cn } from '@/lib/utils'

const props = defineProps<SelectValueProps & {
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <RekaSelectValue
    data-slot="select-value"
    :class="cn('text-muted-foreground', props.class)"
    v-bind="props"
  >
    <slot />
  </RekaSelectValue>
</template>
