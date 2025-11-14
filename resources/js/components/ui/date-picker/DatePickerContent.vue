<script setup lang="ts">
import { Calendar } from '@/components/ui/calendar';
import DialogOverlay from '@/components/ui/dialog/DialogOverlay.vue';
import { cn } from '@/lib/utils';
import { useBreakpoints } from '@vueuse/core';
import { DialogContent, DialogPortal } from 'reka-ui';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<{
  modelValue?: Date | string | null;
  defaultValue?: Date | string;
  minDate?: Date | null;
  maxDate?: Date | null;
  class?: HTMLAttributes['class'];
}>();

const emit = defineEmits<{
  'update:modelValue': [value: Date | null];
  select: [value: Date | null];
}>();

const breakpoints = useBreakpoints({
  mobile: 0,
  tablet: 768,
  desktop: 1024,
});

const isMobile = breakpoints.smaller('tablet');

const handleUpdateModelValue = (value: Date | null) => {
  emit('update:modelValue', value);
};

const handleSelect = (value: Date | null) => {
  emit('select', value);
};
</script>

<template>
  <DialogPortal>
    <DialogOverlay />
    <DialogContent
      :class="cn(
        'bg-background data-[state=open]:animate-in data-[state=closed]:animate-out fixed z-50 shadow-lg outline-none',
        // Mobile: bottom sheet
        isMobile && 'data-[state=closed]:slide-out-to-bottom data-[state=open]:slide-in-from-bottom inset-x-0 bottom-0 max-h-[85vh] rounded-t-lg border-t',
        // Desktop: centered dialog
        !isMobile && 'data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[state=closed]:slide-out-to-left-1/2 data-[state=closed]:slide-out-to-top-[48%] data-[state=open]:slide-in-from-left-1/2 data-[state=open]:slide-in-from-top-[48%] left-[50%] top-[50%] w-auto translate-x-[-50%] translate-y-[-50%] rounded-lg border duration-200',
        props.class,
      )"
      aria-label="Calendar date picker"
    >
      <Calendar
        :model-value="modelValue"
        :default-value="defaultValue"
        :min-date="minDate"
        :max-date="maxDate"
        @update:model-value="handleUpdateModelValue"
        @select="handleSelect"
      />
    </DialogContent>
  </DialogPortal>
</template>
