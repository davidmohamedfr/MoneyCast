<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { accountSchema, accountUpdateSchema } from '@/lib/validation/account';
import type { Account } from '@/types/account';
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
              initial_balance: props.account.initial_balance,
              currency: props.account.currency,
          }
        : {
              name: '',
              type: 'checking',
              initial_balance: 0,
              currency: 'EUR',
          },
});

const [name, nameAttrs] = defineField('name');
const [type, typeAttrs] = defineField('type');
const [initialBalance, initialBalanceAttrs] = defineField('initial_balance');
const [currency, currencyAttrs] = defineField('currency');

const onSubmit = handleSubmit((values) => {
    emit('submit', values);
});
</script>

<template>
    <form @submit="onSubmit" class="space-y-6">
        <div class="space-y-2">
            <Label for="name">Account Name</Label>
            <Input
                id="name"
                v-model="name"
                v-bind="nameAttrs"
                placeholder="e.g., Checking Account"
                :disabled="isSubmitting"
            />
            <InputError :message="errors.name" />
        </div>

        <div class="space-y-2">
            <Label for="type">Account Type</Label>
            <Select v-model="type" v-bind="typeAttrs" :disabled="isSubmitting">
                <SelectTrigger id="type">
                    <SelectValue placeholder="Select account type" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="checking">Checking</SelectItem>
                    <SelectItem value="savings">Savings</SelectItem>
                    <SelectItem value="credit">Credit</SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.type" />
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
            <InputError :message="errors.initial_balance" />
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
            <InputError :message="errors.currency" />
        </div>

        <div class="flex justify-end gap-3">
            <Button
                type="button"
                variant="outline"
                @click="$router.back()"
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
