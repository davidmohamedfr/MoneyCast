import { router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

interface KeyboardShortcut {
    key: string;
    ctrlKey?: boolean;
    metaKey?: boolean;
    altKey?: boolean;
    description: string;
    action: () => void;
}

export function useKeyboardShortcuts(
    shortcuts: KeyboardShortcut[] = [],
    options: { enabled?: boolean } = {}
) {
    const enabled = ref(options.enabled ?? true);

    // Detect platform for modifier key (Cmd on macOS, Ctrl elsewhere)
    const isMac =
        /Mac|iPhone|iPod|iPad/.test(navigator.platform) ||
        navigator.userAgent.includes('Mac');

    const isInputElement = (target: EventTarget | null): boolean => {
        if (!target) return false;
        return (
            target instanceof HTMLInputElement ||
            target instanceof HTMLTextAreaElement ||
            target instanceof HTMLSelectElement ||
            (target as HTMLElement).isContentEditable
        );
    };

    const handleKeyDown = (e: KeyboardEvent) => {
        if (!enabled.value) return;
        if (isInputElement(e.target)) return;

        // Platform-aware modifier key detection
        // macOS: Cmd (metaKey)
        // Windows/Linux: Ctrl (ctrlKey)
        const modifierPressed = isMac ? e.metaKey : e.ctrlKey;
        const otherModifier = isMac ? e.ctrlKey : e.metaKey;

        // Navigation shortcuts: Cmd/Ctrl + Shift + key
        // Cmd/Ctrl+Shift+D → Dashboard
        if (
            modifierPressed &&
            e.shiftKey &&
            e.key.toLowerCase() === 'd' &&
            !otherModifier &&
            !e.altKey
        ) {
            e.preventDefault();
            router.visit('/dashboard');
            return;
        }

        // Cmd/Ctrl+Shift+A → Accounts
        if (
            modifierPressed &&
            e.shiftKey &&
            e.key.toLowerCase() === 'a' &&
            !otherModifier &&
            !e.altKey
        ) {
            e.preventDefault();
            router.visit('/accounts');
            return;
        }

        // Cmd/Ctrl+Shift+T → Transactions
        if (
            modifierPressed &&
            e.shiftKey &&
            e.key.toLowerCase() === 't' &&
            !otherModifier &&
            !e.altKey
        ) {
            e.preventDefault();
            router.visit('/transactions');
            return;
        }

        // Action shortcuts: Cmd/Ctrl + Shift + key
        // Cmd/Ctrl+Shift+N → New Transaction
        if (
            modifierPressed &&
            e.shiftKey &&
            e.key.toLowerCase() === 'n' &&
            !otherModifier &&
            !e.altKey
        ) {
            e.preventDefault();
            router.visit('/transactions/create');
            return;
        }

        // Help shortcut (no modifier - safe special character)
        // '?' → Show keyboard shortcuts help
        if (
            e.key === '?' &&
            !e.metaKey &&
            !e.ctrlKey &&
            !e.altKey &&
            !e.shiftKey
        ) {
            e.preventDefault();
            window.dispatchEvent(new CustomEvent('show-keyboard-shortcuts'));
            return;
        }

        // Custom shortcuts from component
        for (const shortcut of shortcuts) {
            const keyMatch = e.key === shortcut.key;
            const ctrlMatch =
                shortcut.ctrlKey === undefined ||
                shortcut.ctrlKey === e.ctrlKey;
            const metaMatch =
                shortcut.metaKey === undefined ||
                shortcut.metaKey === e.metaKey;
            const altMatch =
                shortcut.altKey === undefined || shortcut.altKey === e.altKey;

            if (keyMatch && ctrlMatch && metaMatch && altMatch) {
                e.preventDefault();
                shortcut.action();
                return;
            }
        }
    };

    onMounted(() => {
        window.addEventListener('keydown', handleKeyDown);
    });

    onUnmounted(() => {
        window.removeEventListener('keydown', handleKeyDown);
    });

    return {
        enabled,
    };
}

// Built-in shortcuts for the application
// Platform-aware: Cmd+Shift on macOS, Ctrl+Shift on Windows/Linux
export const DEFAULT_SHORTCUTS = [
    {
        key: 'Cmd/Ctrl+Shift+D',
        description: 'Go to Dashboard',
        action: 'navigation',
    },
    {
        key: 'Cmd/Ctrl+Shift+A',
        description: 'Go to Accounts',
        action: 'navigation',
    },
    {
        key: 'Cmd/Ctrl+Shift+T',
        description: 'Go to Transactions',
        action: 'navigation',
    },
    {
        key: 'Cmd/Ctrl+Shift+N',
        description: 'New Transaction',
        action: 'action',
    },
    { key: '?', description: 'Show Shortcuts', action: 'help' },
] as const;
