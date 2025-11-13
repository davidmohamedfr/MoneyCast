import { isToday, isSameDay, addMonths, parseDate, toISODateString } from '@/lib/date-utils';
import { computed, ref, watch } from 'vue';

export interface UseDatePickerOptions {
  modelValue?: Date | string | null;
  defaultValue?: Date | string;
  minDate?: Date | null;
  maxDate?: Date | null;
  onChange?: (date: Date | null) => void;
}

/**
 * Composable for date picker state management
 *
 * Handles:
 * - Selected date state
 * - Display month/year state
 * - Open/close state
 * - Date selection logic
 * - Keyboard navigation
 */
export function useDatePicker(options: UseDatePickerOptions = {}) {
  // Parse initial value
  const initialDate = parseDate(options.modelValue || options.defaultValue || null);

  // Selected date state
  const selectedDate = ref<Date | null>(initialDate);

  // Display month/year (current view)
  const today = new Date();
  const displayMonth = ref(initialDate?.getMonth() ?? today.getMonth());
  const displayYear = ref(initialDate?.getFullYear() ?? today.getFullYear());

  // Open/close state
  const isOpen = ref(false);

  // Focused date for keyboard navigation (default to selected or today)
  const focusedDate = ref<Date | null>(selectedDate.value || today);

  /**
   * Check if a date is selected
   */
  const isDateSelected = computed(() => {
    return (date: Date): boolean => {
      return isSameDay(date, selectedDate.value);
    };
  });

  /**
   * Check if a date is today
   */
  const isDateToday = computed(() => {
    return (date: Date): boolean => {
      return isToday(date);
    };
  });

  /**
   * Check if a date is in the current display month
   */
  const isDateInDisplayMonth = computed(() => {
    return (date: Date): boolean => {
      return date.getMonth() === displayMonth.value && date.getFullYear() === displayYear.value;
    };
  });

  /**
   * Check if a date is disabled
   */
  const isDateDisabled = computed(() => {
    return (date: Date): boolean => {
      if (options.minDate && date < options.minDate) return true;
      if (options.maxDate && date > options.maxDate) return true;
      return false;
    };
  });

  /**
   * Check if a date is focused (for keyboard navigation)
   */
  const isDateFocused = computed(() => {
    return (date: Date): boolean => {
      return isSameDay(date, focusedDate.value);
    };
  });

  /**
   * Select a date
   */
  const selectDate = (date: Date) => {
    if (isDateDisabled.value(date)) return;

    selectedDate.value = date;
    displayMonth.value = date.getMonth();
    displayYear.value = date.getFullYear();
    focusedDate.value = date;

    options.onChange?.(date);
  };

  /**
   * Select today
   */
  const selectToday = () => {
    const today = new Date();
    selectDate(today);
  };

  /**
   * Clear selection
   */
  const clearSelection = () => {
    selectedDate.value = null;
    options.onChange?.(null);
  };

  /**
   * Navigate to previous month
   */
  const previousMonth = () => {
    const newDate = addMonths(new Date(displayYear.value, displayMonth.value, 1), -1);
    displayMonth.value = newDate.getMonth();
    displayYear.value = newDate.getFullYear();
  };

  /**
   * Navigate to next month
   */
  const nextMonth = () => {
    const newDate = addMonths(new Date(displayYear.value, displayMonth.value, 1), 1);
    displayMonth.value = newDate.getMonth();
    displayYear.value = newDate.getFullYear();
  };

  /**
   * Navigate to today's month
   */
  const goToToday = () => {
    const today = new Date();
    displayMonth.value = today.getMonth();
    displayYear.value = today.getFullYear();
    focusedDate.value = today;
  };

  /**
   * Set display month directly
   */
  const setDisplayMonth = (month: number) => {
    displayMonth.value = month;
  };

  /**
   * Set display year directly
   */
  const setDisplayYear = (year: number) => {
    displayYear.value = year;
  };

  /**
   * Open picker
   */
  const open = () => {
    isOpen.value = true;
    // Reset focused date to selected or today
    focusedDate.value = selectedDate.value || today;
  };

  /**
   * Close picker
   */
  const close = () => {
    isOpen.value = false;
  };

  /**
   * Toggle picker
   */
  const toggle = () => {
    if (isOpen.value) {
      close();
    } else {
      open();
    }
  };

  /**
   * Format selected date for display
   */
  const formattedDate = computed(() => {
    if (!selectedDate.value) return '';
    return new Intl.DateTimeFormat(navigator.language, {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    }).format(selectedDate.value);
  });

  /**
   * Get ISO date string for form submission
   */
  const isoDateString = computed(() => {
    if (!selectedDate.value) return '';
    return toISODateString(selectedDate.value);
  });

  /**
   * Watch for external changes to modelValue
   */
  watch(() => options.modelValue, (newValue) => {
    const newDate = parseDate(newValue);
    if (newDate) {
      selectedDate.value = newDate;
      displayMonth.value = newDate.getMonth();
      displayYear.value = newDate.getFullYear();
      focusedDate.value = newDate;
    }
  });

  return {
    // State
    selectedDate,
    displayMonth,
    displayYear,
    isOpen,
    focusedDate,
    // Computed
    isDateSelected,
    isDateToday,
    isDateInDisplayMonth,
    isDateDisabled,
    isDateFocused,
    formattedDate,
    isoDateString,
    // Actions
    selectDate,
    selectToday,
    clearSelection,
    previousMonth,
    nextMonth,
    goToToday,
    setDisplayMonth,
    setDisplayYear,
    open,
    close,
    toggle,
  };
}
