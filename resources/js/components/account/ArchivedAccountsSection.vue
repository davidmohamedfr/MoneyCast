<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import type { AccountWithBalance } from '@/types/account';
import { ref } from 'vue';
import AccountList from './AccountList.vue';

interface Props {
    archivedAccounts: AccountWithBalance[];
}

defineProps<Props>();

const isOpen = ref(false);
</script>

<template>
    <Collapsible
        v-if="archivedAccounts.length > 0"
        v-model:open="isOpen"
        class="space-y-4"
    >
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">
                Archived Accounts ({{ archivedAccounts.length }})
            </h2>
            <CollapsibleTrigger as-child>
                <Button variant="ghost" size="sm">
                    {{ isOpen ? 'Hide' : 'Show' }}
                </Button>
            </CollapsibleTrigger>
        </div>

        <CollapsibleContent>
            <AccountList :accounts="archivedAccounts" />
        </CollapsibleContent>
    </Collapsible>
</template>
