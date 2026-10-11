<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payments recorded against invoices. One row per settled invoice for now,
 * but the schema allows several (partial) payments against the same invoice
 * without changes.
 *
 * Money is stored as unsigned BIGINT minor units (cents/paisa), matching the
 * invoices table. A payment has no currency column of its own — it inherits
 * the currency of its invoice, so totals are never mixed across currencies.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();

            $table->unsignedBigInteger('amount')->default(0);
            $table->date('paid_on');

            // cash | bank_transfer | jazzcash | easypaisa | card | other
            $table->string('method', 32)->default('other');
            $table->string('reference', 100)->nullable();

            $table->timestamps();

            // Payments are always listed per user and ordered/filtered by date.
            $table->index(['user_id', 'paid_on']);
            $table->index(['user_id', 'invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
