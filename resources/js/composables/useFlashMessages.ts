import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from 'vue-sonner';

/**
 * Composable to handle flash messages from Laravel backend
 * Displays toast notifications for success/error messages
 *
 * Accessibility: Respects prefers-reduced-motion
 * ADHD/Autism friendly: Clear confirmation of actions
 */
export function useFlashMessages() {
    const page = usePage();

    watch(
        () => page.props.flash,
        (flash: any) => {
            if (flash?.success) {
                toast.success(flash.success, {
                    duration: 5000,
                    description: 'Your changes have been saved',
                });
            }
            if (flash?.error) {
                toast.error(flash.error, {
                    duration: 7000,
                    description: 'Please try again or contact support',
                });
            }
            if (flash?.info) {
                toast.info(flash.info, {
                    duration: 5000,
                });
            }
        },
        { immediate: true, deep: true },
    );
}
