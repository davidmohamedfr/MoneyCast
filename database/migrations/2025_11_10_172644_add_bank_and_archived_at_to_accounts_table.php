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
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('bank')->nullable()->after('currency');
            $table->softDeletes();
        });

        DB::table('accounts')->whereNull('bank')->update(['bank' => 'Default Bank']);

        Schema::table('accounts', function (Blueprint $table) {
            $table->string('bank')->nullable(false)->change();
            $table->dropUnique(['user_id', 'name']);
            $table->unique(['user_id', 'bank', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'bank', 'name']);
            $table->unique(['user_id', 'name']);

            $table->dropSoftDeletes();
            $table->dropColumn('bank');
        });
    }
};
