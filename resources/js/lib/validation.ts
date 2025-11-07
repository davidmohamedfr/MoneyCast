import { z } from 'zod';

/**
 * Profile update validation schema
 * Mirrors Laravel validation rules in ProfileUpdateRequest
 */
export const profileUpdateSchema = z.object({
    name: z
        .string()
        .min(1, 'Name is required')
        .max(255, 'Name must not exceed 255 characters'),
    email: z
        .string()
        .min(1, 'Email is required')
        .email('Must be a valid email address')
        .max(255, 'Email must not exceed 255 characters')
        .toLowerCase(),
});

export type ProfileUpdateData = z.infer<typeof profileUpdateSchema>;

/**
 * Password update validation schema
 * Mirrors Laravel Password::defaults() rules
 */
export const passwordUpdateSchema = z
    .object({
        current_password: z
            .string()
            .min(1, 'Current password is required'),
        password: z
            .string()
            .min(8, 'Password must be at least 8 characters')
            .regex(/[a-z]/, 'Password must contain at least one lowercase letter')
            .regex(/[A-Z]/, 'Password must contain at least one uppercase letter')
            .regex(/[0-9]/, 'Password must contain at least one number')
            .regex(
                /[^a-zA-Z0-9]/,
                'Password must contain at least one special character',
            ),
        password_confirmation: z.string().min(1, 'Password confirmation is required'),
    })
    .refine((data) => data.password === data.password_confirmation, {
        message: 'Passwords must match',
        path: ['password_confirmation'],
    });

export type PasswordUpdateData = z.infer<typeof passwordUpdateSchema>;

/**
 * Two-factor authentication validation schema
 * For 6-digit verification codes
 */
export const twoFactorCodeSchema = z.object({
    code: z
        .string()
        .length(6, 'Code must be exactly 6 digits')
        .regex(/^\d+$/, 'Code must contain only digits'),
});

export type TwoFactorCodeData = z.infer<typeof twoFactorCodeSchema>;
