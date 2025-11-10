<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { transactionSchema } from '@/lib/validation/transaction';
import type { Account } from '@/types/account';
import type { Category } from '@/types/category';
import type { Transaction } from '@/types/transaction';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import { computed, ref } from 'vue';

const props = defineProps<{
    transaction?: Transaction;
    accounts: Account[];
    categories: Category[];
    isEdit?: boolean;
}>();

const emit = defineEmits<{
    submit: [values: any];
}>();

const validationSchema = toTypedSchema(transactionSchema);

// Auto-select first account if only one exists
const defaultAccountId = computed(() => {
    if (props.transaction) return props.transaction.account_id;
    if (props.accounts.length === 1) return props.accounts[0].id;
    return props.accounts[0]?.id;
});

const { errors, defineField, handleSubmit, isSubmitting, values } = useForm({
    validationSchema,
    initialValues: props.transaction
        ? {
              account_id: props.transaction.account_id,
              type: props.transaction.type,
              amount: parseFloat(props.transaction.amount),
              payee: props.transaction.payee,
              date: props.transaction.date,
              category_id: props.transaction.category_id,
              description: props.transaction.description,
              notes: props.transaction.notes,
          }
        : {
              account_id: defaultAccountId.value,
              type: 'expense',
              amount: 0,
              payee: '',
              date: new Date().toISOString().split('T')[0],
              category_id: null,
              description: null,
              notes: null,
          },
});

const [accountId, accountIdAttrs] = defineField('account_id');
const [type, typeAttrs] = defineField('type');
const [amount, amountAttrs] = defineField('amount');
const [payee, payeeAttrs] = defineField('payee');
const [date, dateAttrs] = defineField('date');
const [categoryId, categoryIdAttrs] = defineField('category_id');
const [description, descriptionAttrs] = defineField('description');
const [notes, notesAttrs] = defineField('notes');

const filteredCategories = computed(() => {
    if (!values.type || values.type === 'transfer') return [];
    return props.categories.filter((cat) => cat.type === values.type);
});

// Collapsible state for optional fields - persisted per session
const showOptionalFields = ref(
    sessionStorage.getItem('transaction-form-optional-fields') === 'true',
);

// Persist collapsible state
const toggleOptionalFields = (open: boolean) => {
    showOptionalFields.value = open;
    sessionStorage.setItem('transaction-form-optional-fields', String(open));
};

const onSubmit = handleSubmit((values) => {
    emit('submit', values);
});
</script>

<template>
    <form @submit="onSubmit" class="space-y-6">
        <!-- Required Fields Section -->
        <div class="space-y-4 rounded-lg border border-border bg-muted/30 p-4">
            <h3
                class="flex items-center gap-2 text-sm font-semibold text-foreground"
            >
                <Icon
                    name="asterisk"
                    class="h-3 w-3 text-destructive"
                    aria-hidden="true"
                />
                Required Information
            </h3>
            <div class="space-y-4">
                <!-- Only show account selector if more than one account -->
                <div v-if="accounts.length > 1" class="space-y-2">
                    <Label for="account_id">Account</Label>
                    <Select
                        v-model="accountId"
                        v-bind="accountIdAttrs"
                        :disabled="isSubmitting"
                    >
                        <SelectTrigger id="account_id">
                            <SelectValue placeholder="Select account" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="account in accounts"
                                :key="account.id"
                                :value="account.id"
                            >
                                <div class="flex flex-col">
                                    <span class="font-medium">{{
                                        account.name
                                    }}</span>
                                    <span class="text-xs text-muted-foreground">
                                        {{ account.bank }}
                                    </span>
                                </div>
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError
                        :message="errors.account_id"
                        help-text="Choose the account this transaction belongs to"
                    />
                </div>

                <!-- Show account name when only one account exists -->
                <div v-else-if="accounts.length === 1" class="space-y-2">
                    <Label>Account</Label>
                    <div
                        class="flex flex-col rounded-md border border-input bg-muted/50 px-3 py-2"
                    >
                        <span class="font-medium text-foreground">{{
                            accounts[0].name
                        }}</span>
                        <span class="text-sm text-muted-foreground">
                            {{ accounts[0].bank }}
                        </span>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="type">Type</Label>
                    <Select
                        v-model="type"
                        v-bind="typeAttrs"
                        :disabled="isSubmitting"
                    >
                        <SelectTrigger id="type">
                            <SelectValue placeholder="Select type" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="income">Income</SelectItem>
                            <SelectItem value="expense">Expense</SelectItem>
                            <SelectItem value="transfer">Transfer</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError
                        :message="errors.type"
                        help-text="Income (money in) or Expense (money out)"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="amount">Amount</Label>
                    <Input
                        id="amount"
                        v-model="amount"
                        v-bind="amountAttrs"
                        type="number"
                        step="0.01"
                        min="0.01"
                        placeholder="0.00"
                        :disabled="isSubmitting"
                    />
                    <InputError
                        :message="errors.amount"
                        help-text="Enter the transaction amount (e.g., 49.99)"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="payee">Payee</Label>
                    <Input
                        id="payee"
                        v-model="payee"
                        v-bind="payeeAttrs"
                        placeholder="e.g., Grocery Store"
                        :disabled="isSubmitting"
                    />
                    <InputError
                        :message="errors.payee"
                        help-text="Who did you pay or who paid you?"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="date">Date</Label>
                    <Input
                        id="date"
                        v-model="date"
                        v-bind="dateAttrs"
                        type="date"
                        :disabled="isSubmitting"
                    />
                    <InputError
                        :message="errors.date"
                        help-text="When did this transaction occur?"
                    />
                </div>
            </div>
        </div>

        <!-- Optional Fields - Collapsible -->
        <Collapsible
            :open="showOptionalFields"
            @update:open="toggleOptionalFields"
        >
            <CollapsibleTrigger as-child>
                <Button
                    type="button"
                    variant="ghost"
                    class="focus-standard w-full justify-between"
                    :disabled="isSubmitting"
                >
                    <span class="flex items-center gap-2">
                        <Icon
                            name="settings"
                            class="h-4 w-4"
                            aria-hidden="true"
                        />
                        Additional Details (Optional)
                    </span>
                    <Icon
                        :name="
                            showOptionalFields ? 'chevron-up' : 'chevron-down'
                        "
                        class="h-4 w-4 transition-transform"
                        aria-hidden="true"
                    />
                </Button>
            </CollapsibleTrigger>
            <CollapsibleContent class="space-y-4 pt-4">
                <div v-if="filteredCategories.length > 0" class="space-y-2">
                    <Label for="category_id">Category</Label>
                    <Select
                        v-model="categoryId"
                        v-bind="categoryIdAttrs"
                        :disabled="isSubmitting"
                    >
                        <SelectTrigger id="category_id">
                            <SelectValue
                                placeholder="Select category (optional)"
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="category in filteredCategories"
                                :key="category.id"
                                :value="category.id"
                            >
                                {{ category.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError
                        :message="errors.category_id"
                        help-text="Helps organize and analyze your spending"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Input
                        id="description"
                        v-model="description"
                        v-bind="descriptionAttrs"
                        placeholder="Optional description"
                        :disabled="isSubmitting"
                    />
                    <InputError
                        :message="errors.description"
                        help-text="Add more context about this transaction"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="notes">Notes</Label>
                    <Input
                        id="notes"
                        v-model="notes"
                        v-bind="notesAttrs"
                        placeholder="Optional notes"
                        :disabled="isSubmitting"
                    />
                    <InputError
                        :message="errors.notes"
                        help-text="Private notes for your reference"
                    />
                </div>
            </CollapsibleContent>
        </Collapsible>

        <!-- Actions -->
        <div class="flex justify-end gap-4 border-t border-border pt-4">
            <Button
                type="submit"
                :disabled="isSubmitting"
                class="focus-standard"
            >
                {{ isEdit ? 'Update Transaction' : 'Create Transaction' }}
            </Button>
        </div>
    </form>
</template>
