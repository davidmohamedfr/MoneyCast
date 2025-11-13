<script setup lang="ts">
import DatePickerContent from './DatePickerContent.vue';
import DatePickerTrigger from './DatePickerTrigger.vue';
import { parseDate, toISODateString } from '@/lib/date-utils';
import { useDateFormatter } from '@/composables/useDateFormatter';
import { useVModel } from '@vueuse/core';
import { DialogRoot } from 'reka-ui';
import { computed, ref, type HTMLAttributes } from 'vue';

const props = withDefaults(defineProps<{
  modelValue?: Date | string | null;
  defaultValue?: Date | string;
  disabled?: boolean;
  readonly?: boolean;
  minDate?: Date | null;
  maxDate?: Date | null;
  locale?: string;
  placeholder?: string;
  name?: string;
  id?: string;
  required?: boolean;
  class?: HTMLAttributes['class'];
}>(), {
  modelValue: null,
  defaultValue: undefined,
  disabled: false,
  readonly: false,
  minDate: null,
  maxDate: null,
  locale: undefined,
  placeholder: 'Select date',
  required: false,
});

const emit = defineEmits<{
  'update:modelValue': [value: Date | string | null];
  change: [value: Date | null];
}>();

const isOpen = ref(false);

// Parse modelValue to Date
const dateValue = computed(() => {
  return parseDate(props.modelValue);
});

// Format date for display
const { formatDisplayDate } = useDateFormatter(props.locale);
const formattedDate = computed(() => formatDisplayDate.value(dateValue.value));

// Handle date selection
const handleSelect = (date: Date | null) => {
  if (date) {
    // Emit as ISO string for form compatibility
    const isoString = toISODateString(date);
    emit('update:modelValue', isoString);
    emit('change', date);
    // Close dialog after selection
    isOpen.value = false;
  }
};

// Handle model value update (for clear action)
const handleUpdateModelValue = (date: Date | null) => {
  if (date === null) {
    emit('update:modelValue', null);
    emit('change', null);
    isOpen.value = false;
  }
};
</script>

<template>
  <div :class="props.class">
    <DialogRoot v-model:open="isOpen">
      <DatePickerTrigger
        :formatted-date="formattedDate"
        :placeholder="placeholder"
        :disabled="disabled || readonly"
        :required="required"
        :id="id"
        :name="name"
      />

      <DatePickerContent
        :model-value="dateValue"
        :default-value="defaultValue"
        :min-date="minDate"
        :max-date="maxDate"
        @update:model-value="handleUpdateModelValue"
        @select="handleSelect"
      />
    </DialogRoot>

    <!-- Hidden input for form submission -->
    <input
      v-if="name"
      type="hidden"
      :name="name"
      :value="dateValue ? toISODateString(dateValue) : ''"
    />
  </div>
</template>
