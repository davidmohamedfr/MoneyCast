<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <div
    data-slot="card"
    :class="
      cn(
        'bg-card text-card-foreground flex flex-col gap-6 rounded-xl border py-6 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 border-border/50 hover:border-primary/20',
        props.class,
      )
    "
  >
    <slot />
  </div>
</template>
