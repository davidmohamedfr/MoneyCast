<script setup lang="ts">
import CalendarGrid from './CalendarGrid.vue';
import CalendarHeader from './CalendarHeader.vue';
import { Button } from '@/components/ui/button';
import { useDatePicker } from '@/composables/useDatePicker';
import { addMonths, getFirstDayOfMonth, getLastDayOfMonth } from '@/lib/date-utils';
import { computed, onMounted, onUnmounted, ref, type HTMLAttributes } from 'vue';

const props = withDefaults(defineProps<{
  modelValue?: Date | string | null;
  defaultValue?: Date | string;
  minDate?: Date | null;
  maxDate?: Date | null;
  class?: HTMLAttributes['class'];
}>(), {
  modelValue: null,
  defaultValue: undefined,
  minDate: null,
  maxDate: null,
});

const emit = defineEmits<{
  'update:modelValue': [value: Date | null];
  select: [value: Date | null];
}>();

const calendarRef = ref<HTMLElement | null>(null);

const datePicker = useDatePicker({
  modelValue: props.modelValue,
  defaultValue: props.defaultValue,
  minDate: props.minDate,
  maxDate: props.maxDate,
  onChange: (date) => {
    emit('update:modelValue', date);
    emit('select', date);
  },
});

/**
 * Keyboard navigation handler
 */
const handleKeydown = (event: KeyboardEvent) => {
  const { focusedDate, displayMonth, displayYear, selectDate, previousMonth, nextMonth } = datePicker;

  if (!focusedDate.value) return;

  const currentDate = new Date(focusedDate.value);
  let newDate: Date | null = null;
  let preventDefault = true;

  switch (event.key) {
    case 'ArrowLeft':
      // Move to previous day
      newDate = new Date(currentDate);
      newDate.setDate(currentDate.getDate() - 1);
      break;

    case 'ArrowRight':
      // Move to next day
      newDate = new Date(currentDate);
      newDate.setDate(currentDate.getDate() + 1);
      break;

    case 'ArrowUp':
      // Move to previous week
      newDate = new Date(currentDate);
      newDate.setDate(currentDate.getDate() - 7);
      break;

    case 'ArrowDown':
      // Move to next week
      newDate = new Date(currentDate);
      newDate.setDate(currentDate.getDate() + 7);
      break;

    case 'Home':
      // Move to first day of month
      newDate = getFirstDayOfMonth(displayYear.value, displayMonth.value);
      break;

    case 'End':
      // Move to last day of month
      newDate = getLastDayOfMonth(displayYear.value, displayMonth.value);
      break;

    case 'PageUp':
      // Move to previous month (same day)
      newDate = addMonths(currentDate, -1);
      previousMonth();
      break;

    case 'PageDown':
      // Move to next month (same day)
      newDate = addMonths(currentDate, 1);
      nextMonth();
      break;

    case 'Enter':
    case ' ':
      // Select focused date
      selectDate(currentDate);
      break;

    default:
      preventDefault = false;
  }

  if (preventDefault) {
    event.preventDefault();
  }

  if (newDate) {
    // Check if date is disabled
    if (datePicker.isDateDisabled.value(newDate)) {
      return;
    }

    // Update focused date
    focusedDate.value = newDate;

    // Update display month/year if needed
    if (newDate.getMonth() !== displayMonth.value || newDate.getFullYear() !== displayYear.value) {
      displayMonth.value = newDate.getMonth();
      displayYear.value = newDate.getFullYear();
    }
  }
};

onMounted(() => {
  // Add keyboard listener
  calendarRef.value?.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  // Remove keyboard listener
  calendarRef.value?.removeEventListener('keydown', handleKeydown);
});

const handleSelectDate = (date: Date) => {
  datePicker.selectDate(date);
};

const handleTodayClick = () => {
  datePicker.selectToday();
};

const handleClearClick = () => {
  datePicker.clearSelection();
};

const handleMonthUpdate = (month: number) => {
  datePicker.setDisplayMonth(month);
};

const handleYearUpdate = (year: number) => {
  datePicker.setDisplayYear(year);
};
</script>

<template>
  <div
    ref="calendarRef"
    :class="props.class"
    class="p-3"
  >
    <CalendarHeader
      :year="datePicker.displayYear.value"
      :month="datePicker.displayMonth.value"
      @previous-month="datePicker.previousMonth"
      @next-month="datePicker.nextMonth"
      @update:month="handleMonthUpdate"
      @update:year="handleYearUpdate"
    />

    <CalendarGrid
      :year="datePicker.displayYear.value"
      :month="datePicker.displayMonth.value"
      :selected-date="datePicker.selectedDate.value"
      :focused-date="datePicker.focusedDate.value"
      :min-date="minDate"
      :max-date="maxDate"
      :is-date-selected="datePicker.isDateSelected.value"
      :is-date-today="datePicker.isDateToday.value"
      :is-date-in-display-month="datePicker.isDateInDisplayMonth.value"
      :is-date-disabled="datePicker.isDateDisabled.value"
      :is-date-focused="datePicker.isDateFocused.value"
      @select-date="handleSelectDate"
    />

    <!-- Action buttons -->
    <div class="mt-4 flex gap-2 border-t border-border pt-4">
      <Button
        type="button"
        variant="outline"
        size="sm"
        class="flex-1"
        @click="handleTodayClick"
      >
        Today
      </Button>
      <Button
        type="button"
        variant="outline"
        size="sm"
        class="flex-1"
        @click="handleClearClick"
      >
        Clear
      </Button>
    </div>
  </div>
</template>
