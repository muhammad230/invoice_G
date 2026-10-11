<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audit log of every email sent for an invoice (invoice + reminder).
     */
    public function up(): void
    {
        Schema::create('invoice_emails', function (Blueprint $table) {
            $table->id();

            // Deleting an invoice removes its email history.
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            // Deleting the owner removes their email history.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('to_email');
            $table->string('subject');

            // invoice | reminder
            $table->string('type')->default('invoice');
            // sent | failed
            $table->string('status')->default('sent');

            // Internal failure detail. Never surfaced raw in the UI.
            $table->text('error')->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            // History is always read per invoice, newest first.
            $table->index(['invoice_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_emails');
    }
};
