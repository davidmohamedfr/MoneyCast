<script setup lang="ts">
import CalendarCell from './CalendarCell.vue';
import { getMonthDays } from '@/lib/date-utils';
import { useDateFormatter } from '@/composables/useDateFormatter';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<{
  year: number;
  month: number;
  selectedDate?: Date | null;
  focusedDate?: Date | null;
  minDate?: Date | null;
  maxDate?: Date | null;
  isDateSelected: (date: Date) => boolean;
  isDateToday: (date: Date) => boolean;
  isDateInDisplayMonth: (date: Date) => boolean;
  isDateDisabled: (date: Date) => boolean;
  isDateFocused: (date: Date) => boolean;
  class?: HTMLAttributes['class'];
}>();

const emit = defineEmits<{
  selectDate: [date: Date];
}>();

const { getWeekdayNames } = useDateFormatter();
const weekdays = computed(() => getWeekdayNames.value());
const monthDays = computed(() => getMonthDays(props.year, props.month));

const handleSelectDate = (date: Date) => {
  emit('selectDate', date);
};
</script>

<template>
  <div role="grid" :class="props.class" aria-label="Calendar">
    <!-- Weekday headers -->
    <div role="row" class="mb-2 grid grid-cols-7 gap-1">
      <div
        v-for="(weekday, index) in weekdays"
        :key="index"
        role="columnheader"
        class="flex h-9 w-9 items-center justify-center text-xs font-medium uppercase text-muted-foreground"
      >
        {{ weekday.substring(0, 2) }}
      </div>
    </div>

    <!-- Calendar grid -->
    <div class="space-y-1">
      <div
        v-for="(week, weekIndex) in monthDays"
        :key="weekIndex"
        role="row"
        class="grid grid-cols-7 gap-1"
      >
        <CalendarCell
          v-for="(date, dayIndex) in week"
          :key="dayIndex"
          :date="date"
          :is-selected="isDateSelected(date)"
          :is-today="isDateToday(date)"
          :is-outside-month="!isDateInDisplayMonth(date)"
          :is-disabled="isDateDisabled(date)"
          :is-focused="isDateFocused(date)"
          @select="handleSelectDate"
        />
      </div>
    </div>
  </div>
</template>
