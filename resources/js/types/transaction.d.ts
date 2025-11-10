import type { Account } from './account';
import type { Category } from './category';

export type TransactionType = 'income' | 'expense' | 'transfer';

export interface Transaction {
    id: number;
    user_id: number;
    account_id: number;
    category_id: number | null;
    type: TransactionType;
    amount: string;
    payee: string;
    description: string | null;
    date: string;
    notes: string | null;
    related_transaction_id: number | null;
    created_at: string;
    updated_at: string;
    account?: Account;
    category?: Category;
    related_transaction?: Transaction;
}

export interface TransactionFormData {
    account_id: number;
    type: TransactionType;
    amount: number;
    payee: string;
    date: string;
    category_id?: number | null;
    description?: string | null;
    notes?: string | null;
}
