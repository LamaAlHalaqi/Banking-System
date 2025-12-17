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
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('subject');
            $table->text('description');
            $table->string('priority')->default('medium');
            $table->string('status')->default('open');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'priority']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
