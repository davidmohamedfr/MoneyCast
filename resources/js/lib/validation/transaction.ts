import { z } from 'zod'

export const transactionSchema = z.object({
  account_id: z
    .number({ required_error: 'Account is required' })
    .int('Account must be a valid ID')
    .positive('Account ID must be positive'),
  type: z.enum(['income', 'expense', 'transfer'], {
    errorMap: () => ({ message: 'Please select a valid transaction type' }),
  }),
  amount: z
    .number({ required_error: 'Amount is required' })
    .positive('Amount must be greater than zero')
    .finite('Amount must be a valid number'),
  payee: z.string().min(1, 'Payee is required').max(255, 'Payee is too long'),
  date: z.string().min(1, 'Date is required'),
  category_id: z
    .number()
    .int('Category must be a valid ID')
    .positive('Category ID must be positive')
    .nullable()
    .optional(),
  description: z
    .string()
    .max(1000, 'Description is too long')
    .nullable()
    .optional(),
  notes: z.string().nullable().optional(),
})

export type TransactionSchema = z.infer<typeof transactionSchema>
