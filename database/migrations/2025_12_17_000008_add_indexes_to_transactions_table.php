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
        Schema::table('transactions', function (Blueprint $table) {
            // Add composite index for better query performance on pending transactions
            $table->index(['status', 'created_at'], 'transactions_status_created_at_index');

            // Add index for approval queries
            $table->index(['approved_by', 'status'], 'transactions_approved_by_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_status_created_at_index');
            $table->dropIndex('transactions_approved_by_status_index');
        });
    }
};
