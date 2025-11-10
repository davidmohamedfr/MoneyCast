<script setup lang="ts">
import { computed } from 'vue'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { transactionSchema } from '@/lib/validation/transaction'
import type { Transaction } from '@/types/transaction'
import type { Account } from '@/types/account'
import type { Category } from '@/types/category'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import InputError from '@/components/InputError.vue'

const props = defineProps<{
  transaction?: Transaction
  accounts: Account[]
  categories: Category[]
  isEdit?: boolean
}>()

const emit = defineEmits<{
  submit: [values: any]
}>()

const validationSchema = toTypedSchema(transactionSchema)

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
        account_id: props.accounts[0]?.id,
        type: 'expense',
        amount: 0,
        payee: '',
        date: new Date().toISOString().split('T')[0],
        category_id: null,
        description: null,
        notes: null,
      },
})

const [accountId, accountIdAttrs] = defineField('account_id')
const [type, typeAttrs] = defineField('type')
const [amount, amountAttrs] = defineField('amount')
const [payee, payeeAttrs] = defineField('payee')
const [date, dateAttrs] = defineField('date')
const [categoryId, categoryIdAttrs] = defineField('category_id')
const [description, descriptionAttrs] = defineField('description')
const [notes, notesAttrs] = defineField('notes')

const filteredCategories = computed(() => {
  if (!values.type || values.type === 'transfer') return []
  return props.categories.filter((cat) => cat.type === values.type)
})

const onSubmit = handleSubmit((values) => {
  emit('submit', values)
})
</script>

<template>
  <form @submit="onSubmit" class="space-y-6">
    <div class="space-y-2">
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
            {{ account.name }}
          </SelectItem>
        </SelectContent>
      </Select>
      <InputError :message="errors.account_id" />
    </div>

    <div class="space-y-2">
      <Label for="type">Type</Label>
      <Select v-model="type" v-bind="typeAttrs" :disabled="isSubmitting">
        <SelectTrigger id="type">
          <SelectValue placeholder="Select type" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="income">Income</SelectItem>
          <SelectItem value="expense">Expense</SelectItem>
          <SelectItem value="transfer">Transfer</SelectItem>
        </SelectContent>
      </Select>
      <InputError :message="errors.type" />
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
      <InputError :message="errors.amount" />
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
      <InputError :message="errors.payee" />
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
      <InputError :message="errors.date" />
    </div>

    <div v-if="filteredCategories.length > 0" class="space-y-2">
      <Label for="category_id">Category</Label>
      <Select
        v-model="categoryId"
        v-bind="categoryIdAttrs"
        :disabled="isSubmitting"
      >
        <SelectTrigger id="category_id">
          <SelectValue placeholder="Select category (optional)" />
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
      <InputError :message="errors.category_id" />
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
      <InputError :message="errors.description" />
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
      <InputError :message="errors.notes" />
    </div>

    <div class="flex justify-end gap-4">
      <Button type="submit" :disabled="isSubmitting">
        {{ isEdit ? 'Update Transaction' : 'Create Transaction' }}
      </Button>
    </div>
  </form>
</template>
