<script setup lang="ts">
import { Button } from '@/components/ui/button';
import Icon from '@/components/Icon.vue';
import MonthDropdown from './MonthDropdown.vue';
import YearDropdown from './YearDropdown.vue';
import { type HTMLAttributes } from 'vue';

const props = defineProps<{
  year: number;
  month: number;
  class?: HTMLAttributes['class'];
}>();

const emit = defineEmits<{
  previousMonth: [];
  nextMonth: [];
  'update:month': [month: number];
  'update:year': [year: number];
}>();

const handlePreviousMonth = () => {
  emit('previousMonth');
};

const handleNextMonth = () => {
  emit('nextMonth');
};

const handleMonthChange = (month: number) => {
  emit('update:month', month);
};

const handleYearChange = (year: number) => {
  emit('update:year', year);
};
</script>

<template>
  <div :class="props.class" class="flex items-center justify-between pb-4">
    <Button
      type="button"
      variant="outline"
      size="icon"
      aria-label="Previous month"
      @click="handlePreviousMonth"
    >
      <Icon name="chevron-left" class="h-4 w-4 text-foreground" aria-hidden="true" />
    </Button>

    <div
      id="month-year-label"
      class="flex items-center gap-1"
      aria-live="polite"
      aria-atomic="true"
    >
      <MonthDropdown
        :selected-month="month"
        :selected-year="year"
        @update:selected-month="handleMonthChange"
      />
      <YearDropdown
        :selected-year="year"
        @update:selected-year="handleYearChange"
      />
    </div>

    <Button
      type="button"
      variant="outline"
      size="icon"
      aria-label="Next month"
      @click="handleNextMonth"
    >
      <Icon name="chevron-right" class="h-4 w-4 text-foreground" aria-hidden="true" />
    </Button>
  </div>
</template>
