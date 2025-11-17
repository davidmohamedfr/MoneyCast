import { z } from 'zod';

export const importSchema = z.object({
    file: z
        .instanceof(File, { message: 'Please select a file' })
        .refine((file) => file.size <= 10 * 1024 * 1024, {
            message: 'File size must be less than 10MB',
        })
        .refine((file) => ['text/csv', 'text/plain', 'application/csv'].includes(file.type), {
            message: 'File must be a CSV file',
        }),
    source_type: z.enum(['csv', 'ofx', 'qfx', 'api'], {
        errorMap: () => ({ message: 'Please select a valid source type' }),
    }),
    account_id: z
        .number()
        .int('Account must be a valid ID')
        .positive('Account ID must be positive')
        .nullable()
        .optional(),
});

export const mappingSchema = z.object({
    date: z.string().min(1, 'Date column is required'),
    amount: z.string().optional(),
    debit: z.string().optional(),
    credit: z.string().optional(),
    payee: z.string().min(1, 'Payee column is required'),
    description: z.string().optional(),
}).refine(
    (data) => data.amount || (data.debit && data.credit),
    {
        message: 'Either amount column or both debit and credit columns are required',
        path: ['amount'],
    }
);

export type ImportSchema = z.infer<typeof importSchema>;
export type MappingSchema = z.infer<typeof mappingSchema>;
