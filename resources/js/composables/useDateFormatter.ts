import { computed, ref } from 'vue';

/**
 * Composable for locale-based date formatting
 *
 * Automatically detects browser locale and provides formatting utilities.
 * Supports custom locale override for testing or user preferences.
 */
export function useDateFormatter(customLocale?: string) {
    const locale = ref(customLocale || navigator.language || 'en-US');

    /**
     * Format date for display in input field
     * Uses short format for compactness
     */
    const formatDisplayDate = computed(() => {
        return (date: Date | null): string => {
            if (!date) return '';
            return new Intl.DateTimeFormat(locale.value, {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            }).format(date);
        };
    });

    /**
     * Format date for long display (full month name)
     */
    const formatLongDate = computed(() => {
        return (date: Date | null): string => {
            if (!date) return '';
            return new Intl.DateTimeFormat(locale.value, {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            }).format(date);
        };
    });

    /**
     * Get full month name
     */
    const getMonthName = computed(() => {
        return (year: number, month: number): string => {
            const date = new Date(year, month, 1);
            return new Intl.DateTimeFormat(locale.value, {
                month: 'long',
            }).format(date);
        };
    });

    /**
     * Get weekday names (short format)
     */
    const getWeekdayNames = computed(() => {
        return (): string[] => {
            const days: string[] = [];
            // Start from Sunday (0)
            for (let i = 0; i < 7; i++) {
                const date = new Date(2000, 0, 2 + i); // Jan 2, 2000 is a Sunday
                days.push(
                    new Intl.DateTimeFormat(locale.value, {
                        weekday: 'short',
                    }).format(date),
                );
            }
            return days;
        };
    });

    /**
     * Update locale dynamically
     */
    const setLocale = (newLocale: string) => {
        locale.value = newLocale;
    };

    return {
        locale,
        formatDisplayDate,
        formatLongDate,
        getMonthName,
        getWeekdayNames,
        setLocale,
    };
}
