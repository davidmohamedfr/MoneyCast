export interface Account {
    id: number;
    user_id: number;
    name: string;
    type: 'checking' | 'savings' | 'credit';
    initial_balance: number;
    currency: string;
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
    initial_balance: number;
    currency?: string;
};
