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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('client_id')->constrained();

            $table->string('invoice_number');
            $table->date('invoice_date');
            $table->date('due_date')->nullable();

            $table->string('currency', 3)->default('USD');

            // All money columns stored as unsigned BIGINT minor units (cents/paisa).
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('total')->default(0);

            $table->string('status', 16)->default('pending'); // draft|pending|paid

            // Optional business fields from the original invoice form stored alongside.
            $table->string('business_name')->nullable();
            $table->string('business_email')->nullable();
            $table->string('business_phone')->nullable();
            $table->string('business_address', 500)->nullable();
            $table->text('logo_data')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            // invoice_number must be unique per user.
            $table->unique(['user_id', 'invoice_number']);
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'due_date']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
