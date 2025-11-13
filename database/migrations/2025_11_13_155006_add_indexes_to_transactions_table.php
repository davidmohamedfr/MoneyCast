<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Composite index for user-scoped queries with date filtering
            $table->index(['user_id', 'date'], 'transactions_user_id_date_index');

            // Composite index for account balance calculations
            $table->index(['account_id', 'date'], 'transactions_account_id_date_index');

            // Index for category filtering
            $table->index('category_id', 'transactions_category_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_user_id_date_index');
            $table->dropIndex('transactions_account_id_date_index');
            $table->dropIndex('transactions_category_id_index');
        });
    }
};
