<script setup lang="ts">
import { cn } from '@/lib/utils';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<{
  date: Date;
  isSelected?: boolean;
  isToday?: boolean;
  isOutsideMonth?: boolean;
  isDisabled?: boolean;
  isFocused?: boolean;
  class?: HTMLAttributes['class'];
}>();

const emit = defineEmits<{
  select: [date: Date];
}>();

const handleClick = () => {
  if (!props.isDisabled) {
    emit('select', props.date);
  }
};

const dayNumber = computed(() => props.date.getDate());

const ariaLabel = computed(() => {
  const dateStr = new Intl.DateTimeFormat(navigator.language, {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  }).format(props.date);

  const labels: string[] = [dateStr];
  if (props.isToday) labels.push('Today');
  if (props.isSelected) labels.push('Selected');
  if (props.isDisabled) labels.push('Not available');

  return labels.join(', ');
});
</script>

<template>
  <button
    type="button"
    role="gridcell"
    :aria-label="ariaLabel"
    :aria-selected="isSelected"
    :aria-disabled="isDisabled"
    :disabled="isDisabled"
    :tabindex="isFocused ? 0 : -1"
    :class="cn(
      'relative h-9 w-9 rounded-md p-0 text-center text-sm font-normal transition-colors',
      'hover:bg-accent hover:text-accent-foreground',
      'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
      'disabled:pointer-events-none disabled:opacity-50',
      // Outside current month
      isOutsideMonth && 'text-muted-foreground/40',
      // Today indicator
      isToday && !isSelected && 'border border-primary text-primary font-semibold',
      // Selected state
      isSelected && 'bg-primary text-primary-foreground font-semibold hover:bg-primary hover:text-primary-foreground',
      // Disabled state
      isDisabled && 'cursor-not-allowed opacity-40',
      props.class,
    )"
    @click="handleClick"
  >
    <span class="relative z-10">{{ dayNumber }}</span>
  </button>
</template>
