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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->foreignId('destination_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->string('type');
            $table->string('status')->default('pending');
            $table->text('description')->nullable();
            $table->string('reference')->nullable();
            $table->foreignId('initiated_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'type']);
            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
