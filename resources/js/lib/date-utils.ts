/**
 * Date Utilities
 *
 * Core date manipulation functions for the DatePicker component.
 * Uses native Date API for simplicity and zero dependencies.
 */

/**
 * Format a date to locale string
 */
export function formatDate(date: Date, locale: string = navigator.language, options?: Intl.DateTimeFormatOptions): string {
  return new Intl.DateTimeFormat(locale, options).format(date);
}

/**
 * Parse a date string to Date object
 * Supports: ISO strings, YYYY-MM-DD, and Date objects
 */
export function parseDate(value: Date | string | null | undefined): Date | null {
  if (!value) return null;
  if (value instanceof Date) return value;

  const date = new Date(value);
  return isValidDate(date) ? date : null;
}

/**
 * Check if a date is valid
 */
export function isValidDate(date: any): date is Date {
  return date instanceof Date && !isNaN(date.getTime());
}

/**
 * Check if two dates are the same day
 */
export function isSameDay(date1: Date | null, date2: Date | null): boolean {
  if (!date1 || !date2) return false;
  return (
    date1.getFullYear() === date2.getFullYear() &&
    date1.getMonth() === date2.getMonth() &&
    date1.getDate() === date2.getDate()
  );
}

/**
 * Check if a date is today
 */
export function isToday(date: Date): boolean {
  return isSameDay(date, new Date());
}

/**
 * Add months to a date
 */
export function addMonths(date: Date, months: number): Date {
  const newDate = new Date(date);
  newDate.setMonth(newDate.getMonth() + months);
  return newDate;
}

/**
 * Get the first day of a month
 */
export function getFirstDayOfMonth(year: number, month: number): Date {
  return new Date(year, month, 1);
}

/**
 * Get the last day of a month
 */
export function getLastDayOfMonth(year: number, month: number): Date {
  return new Date(year, month + 1, 0);
}

/**
 * Get all days in a month including padding from previous/next month
 * Returns a 2D array representing weeks
 */
export function getMonthDays(year: number, month: number): Date[][] {
  const firstDay = getFirstDayOfMonth(year, month);
  const lastDay = getLastDayOfMonth(year, month);

  // Get day of week (0 = Sunday, 6 = Saturday)
  const startDayOfWeek = firstDay.getDay();
  const daysInMonth = lastDay.getDate();

  const days: Date[][] = [];
  let week: Date[] = [];

  // Add padding days from previous month
  const prevMonthLastDay = getLastDayOfMonth(year, month - 1);
  const prevMonthDaysCount = prevMonthLastDay.getDate();

  for (let i = startDayOfWeek - 1; i >= 0; i--) {
    week.push(new Date(year, month - 1, prevMonthDaysCount - i));
  }

  // Add current month days
  for (let day = 1; day <= daysInMonth; day++) {
    if (week.length === 7) {
      days.push(week);
      week = [];
    }
    week.push(new Date(year, month, day));
  }

  // Add padding days from next month
  const remainingDays = 7 - week.length;
  for (let day = 1; day <= remainingDays; day++) {
    week.push(new Date(year, month + 1, day));
  }

  if (week.length > 0) {
    days.push(week);
  }

  return days;
}

/**
 * Get month name
 */
export function getMonthName(month: number, locale: string = navigator.language, format: 'long' | 'short' = 'long'): string {
  const date = new Date(2000, month, 1);
  return formatDate(date, locale, { month: format });
}

/**
 * Get weekday names
 */
export function getWeekdayNames(locale: string = navigator.language, format: 'long' | 'short' | 'narrow' = 'short'): string[] {
  const days: string[] = [];
  // Start from Sunday (0)
  for (let i = 0; i < 7; i++) {
    const date = new Date(2000, 0, 2 + i); // Jan 2, 2000 is a Sunday
    days.push(formatDate(date, locale, { weekday: format }));
  }
  return days;
}

/**
 * Format date to ISO string (YYYY-MM-DD) for form submission
 */
export function toISODateString(date: Date): string {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

/**
 * Check if date is within range
 */
export function isDateInRange(date: Date, minDate?: Date | null, maxDate?: Date | null): boolean {
  if (minDate && date < minDate) return false;
  if (maxDate && date > maxDate) return false;
  return true;
}
