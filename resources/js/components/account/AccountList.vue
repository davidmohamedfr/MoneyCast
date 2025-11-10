<script setup lang="ts">
import type { AccountWithBalance } from '@/types/account';
import { computed } from 'vue';
import AccountCard from './AccountCard.vue';

interface Props {
    accounts: AccountWithBalance[];
}

const props = defineProps<Props>();

const accountsByBank = computed(() => {
    const grouped = new Map<string, AccountWithBalance[]>();

    props.accounts.forEach((accData) => {
        const bank = accData.account.bank;
        if (!grouped.has(bank)) {
            grouped.set(bank, []);
        }
        grouped.get(bank)!.push(accData);
    });

    const sorted = Array.from(grouped.entries()).map(([bank, accounts]) => ({
        bank,
        accounts: accounts.sort((a, b) =>
            a.account.name.localeCompare(b.account.name),
        ),
    }));

    return sorted.sort((a, b) => a.bank.localeCompare(b.bank));
});
</script>

<template>
    <div class="space-y-8">
        <div
            v-for="group in accountsByBank"
            :key="group.bank"
            class="space-y-4"
        >
            <h3 class="text-lg font-semibold">{{ group.bank }}</h3>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <AccountCard
                    v-for="accountData in group.accounts"
                    :key="accountData.account.id"
                    :account-data="accountData"
                />
            </div>
        </div>
    </div>
</template>
