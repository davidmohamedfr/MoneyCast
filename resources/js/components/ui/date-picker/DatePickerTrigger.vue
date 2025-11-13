<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import { cn } from '@/lib/utils';
import { DialogTrigger } from 'reka-ui';
import { computed, type HTMLAttributes } from 'vue';

const props = withDefaults(defineProps<{
  formattedDate?: string;
  placeholder?: string;
  disabled?: boolean;
  required?: boolean;
  id?: string;
  name?: string;
  class?: HTMLAttributes['class'];
}>(), {
  formattedDate: '',
  placeholder: 'Select date',
  disabled: false,
  required: false,
});

const displayValue = computed(() => props.formattedDate || props.placeholder);
</script>

<template>
  <DialogTrigger as-child>
    <button
      type="button"
      role="combobox"
      aria-haspopup="dialog"
      :aria-label="formattedDate ? `Selected date: ${formattedDate}` : 'Choose date'"
      :aria-required="required"
      :disabled="disabled"
      :class="cn(
        'file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input flex h-9 w-full items-center justify-between rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
        'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
        !formattedDate && 'text-muted-foreground',
        props.class,
      )"
    >
      <span>{{ displayValue }}</span>
      <Icon name="calendar" class="ml-2 h-4 w-4 shrink-0 opacity-50" aria-hidden="true" />
    </button>
  </DialogTrigger>
</template>
