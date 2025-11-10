export interface Account {
    id: number;
    user_id: number;
    name: string;
    type: 'checking' | 'savings' | 'credit';
    bank: string;
    initial_balance: number;
    currency: string;
    archived_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface AccountWithBalance {
    account: Account;
    current_balance: number;
    projected_balance: number;
}

export type AccountFormData = {
    name: string;
    type: string;
    bank: string;
    initial_balance: number;
    currency?: string;
};

export interface AccountStats {
    total_income: number;
    total_expenses: number;
    current_balance: number;
}

export interface TransactionFilters {
    payee?: string;
    amount_min?: number;
    amount_max?: number;
    category_id?: number;
    start_date?: string;
    end_date?: string;
}
