import { z } from 'zod'

export const accountSchema = z.object({
  name: z.string().min(1, 'Account name is required').max(255),
  type: z.enum(['checking', 'savings', 'credit'], {
    errorMap: () => ({ message: 'Please select a valid account type' }),
  }),
  initial_balance: z
    .number({ required_error: 'Initial balance is required' })
    .min(0, 'Initial balance must be positive'),
  currency: z.string().length(3, 'Currency must be 3 characters').default('EUR'),
})

export const accountUpdateSchema = z.object({
  name: z.string().min(1, 'Account name is required').max(255),
  type: z.enum(['checking', 'savings', 'credit'], {
    errorMap: () => ({ message: 'Please select a valid account type' }),
  }),
})

export type AccountFormValues = z.infer<typeof accountSchema>
export type AccountUpdateFormValues = z.infer<typeof accountUpdateSchema>
