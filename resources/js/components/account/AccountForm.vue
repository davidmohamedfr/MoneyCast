<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { accountSchema, accountUpdateSchema } from '@/lib/validation/account';
import type { Account } from '@/types/account';
import { router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';

const props = defineProps<{
    account?: Account;
    isEdit?: boolean;
}>();

const emit = defineEmits<{
    submit: [values: any];
}>();

const validationSchema = props.isEdit
    ? toTypedSchema(accountUpdateSchema)
    : toTypedSchema(accountSchema);

const { errors, defineField, handleSubmit, isSubmitting } = useForm({
    validationSchema,
    initialValues: props.account
        ? {
              name: props.account.name,
              type: props.account.type,
              bank: props.account.bank,
              initial_balance: props.account.initial_balance,
              currency: props.account.currency,
          }
        : {
              name: '',
              type: 'checking',
              bank: '',
              initial_balance: 0,
              currency: 'EUR',
          },
});

const [name, nameAttrs] = defineField('name');
const [type, typeAttrs] = defineField('type');
const [bank, bankAttrs] = defineField('bank');
const [initialBalance, initialBalanceAttrs] = defineField('initial_balance');
const [currency, currencyAttrs] = defineField('currency');

const onSubmit = handleSubmit((values) => {
    emit('submit', values);
});
</script>

<template>
    <form @submit="onSubmit" class="space-y-6">
        <div class="space-y-2">
            <Label for="name">
                Account Name
                <span class="text-destructive" aria-label="required">*</span>
            </Label>
            <Input
                id="name"
                v-model="name"
                v-bind="nameAttrs"
                placeholder="e.g., Checking Account"
                :disabled="isSubmitting"
                required
                aria-required="true"
            />
            <InputError
                :message="errors.name"
                help-text="Give your account a recognizable name (e.g., Main Checking, Emergency Savings)"
            />
        </div>

        <div class="space-y-2">
            <Label for="type">
                Account Type
                <span class="text-destructive" aria-label="required">*</span>
            </Label>
            <select
                id="type"
                v-model="type"
                v-bind="typeAttrs"
                :disabled="isSubmitting"
                required
                aria-required="true"
                class="flex h-9 w-full items-center rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50"
            >
                <option value="">Select account type</option>
                <option value="checking">Checking</option>
                <option value="savings">Savings</option>
                <option value="credit">Credit</option>
            </select>
            <InputError
                :message="errors.type"
                help-text="Checking for daily expenses, Savings for long-term goals, Credit for credit cards"
            />
        </div>

        <div class="space-y-2">
            <Label for="bank">
                Bank
                <span class="text-destructive" aria-label="required">*</span>
            </Label>
            <Input
                id="bank"
                v-model="bank"
                v-bind="bankAttrs"
                placeholder="e.g., Bank of America"
                :disabled="isSubmitting"
                required
                aria-required="true"
            />
            <InputError
                :message="errors.bank"
                help-text="Enter the name of your financial institution"
            />
        </div>

        <div v-if="!isEdit" class="space-y-2">
            <Label for="initial_balance">Initial Balance</Label>
            <Input
                id="initial_balance"
                v-model.number="initialBalance"
                v-bind="initialBalanceAttrs"
                type="number"
                step="0.01"
                placeholder="0.00"
                :disabled="isSubmitting"
            />
            <InputError
                :message="errors.initial_balance"
                help-text="Your current account balance. This helps track changes from this point forward."
            />
        </div>

        <div v-if="!isEdit" class="space-y-2">
            <Label for="currency">Currency</Label>
            <Input
                id="currency"
                v-model="currency"
                v-bind="currencyAttrs"
                placeholder="EUR"
                maxlength="3"
                :disabled="isSubmitting"
            />
            <InputError
                :message="errors.currency"
                help-text="Three-letter currency code (e.g., USD, EUR, GBP)"
            />
        </div>

        <div class="flex justify-end gap-3">
            <Button
                type="button"
                variant="outline"
                @click="router.visit('/accounts')"
                :disabled="isSubmitting"
            >
                Cancel
            </Button>
            <Button type="submit" :disabled="isSubmitting">
                {{
                    isSubmitting
                        ? 'Saving...'
                        : isEdit
                          ? 'Update Account'
                          : 'Create Account'
                }}
            </Button>
        </div>
    </form>
</template>
