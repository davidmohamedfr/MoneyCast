<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add composite index on transactions for efficient balance calculations
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['user_id', 'account_id', 'date'], 'idx_transactions_user_account_date');
        });

        // Add index on accounts archived_at for efficient filtering
        Schema::table('accounts', function (Blueprint $table) {
            $table->index('archived_at', 'idx_accounts_archived_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_transactions_user_account_date');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropIndex('idx_accounts_archived_at');
        });
    }
};
