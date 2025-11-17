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
        Schema::create('imported_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained()->cascadeOnDelete();
            $table->integer('row_number');
            $table->string('status');
            $table->json('raw_data');
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['import_id', 'status']);
            $table->index('row_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imported_rows');
    }
};
