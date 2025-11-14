<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->boolean('is_opening_balance')->default(false)->after('account_id');
            $table->index('is_opening_balance', 'transactions_is_opening_balance_index');
        });

        // Mark existing opening balance transactions
        DB::table('transactions')
            ->where('payee', 'Opening Balance')
            ->update(['is_opening_balance' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_is_opening_balance_index');
            $table->dropColumn('is_opening_balance');
        });
    }
};
