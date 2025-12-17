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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('account_number')->unique();
            $table->string('account_type');
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('interest_rate', 5, 4)->default(0);
            $table->string('state')->default('active');
            $table->foreignId('parent_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->decimal('overdraft_limit', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['user_id', 'account_type']);
            $table->index('account_number');
            $table->index('state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
