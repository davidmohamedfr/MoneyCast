import type { Account } from './account';

export type ImportStatus = 'pending' | 'mapping' | 'validating' | 'processing' | 'completed' | 'failed';
export type ImportSource = 'csv' | 'ofx' | 'qfx' | 'api';

export interface Import {
    id: number;
    user_id: number;
    account_id: number | null;
    source_type: ImportSource;
    file_name: string;
    file_path: string;
    status: ImportStatus;
    total_rows: number;
    imported_rows: number;
    skipped_rows: number;
    failed_rows: number;
    mapping: FieldMapping | null;
    error_message: string | null;
    completed_at: string | null;
    created_at: string;
    updated_at: string;
    account?: Account;
    imported_rows_data?: ImportedRow[];
}

export interface ImportedRow {
    id: number;
    import_id: number;
    row_number: number;
    status: string;
    raw_data: Record<string, string>;
    transaction_id: number | null;
    error_message: string | null;
    created_at: string;
    updated_at: string;
}

export interface ParsedTransaction {
    date: string;
    amount: number | null;
    debit: number | null;
    credit: number | null;
    payee: string;
    description: string | null;
    row_number: number;
    raw_data: Record<string, string>;
}

export interface ParsedImportData {
    transactions: ParsedTransaction[];
    detected_columns: FieldMapping;
    sample_rows: Record<string, string>[];
    total_rows: number;
}

export interface ImportSummary {
    total_rows: number;
    imported_count: number;
    skipped_count: number;
    failed_count: number;
    errors: Array<{ row: number; message: string }>;
}

export interface FieldMapping {
    date: string;
    amount?: string;
    debit?: string;
    credit?: string;
    payee: string;
    description?: string;
}

export interface ImportFormData {
    file: File | null;
    source_type: ImportSource;
    account_id: number | null;
}
