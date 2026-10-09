<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the extra data the redesigned PDF needs:
 *
 *  - business_profiles: optional tagline, bank/payment details and
 *    signature block (the "source of truth", edited in Settings).
 *  - invoices: snapshot columns for the same values (the invoice form
 *    pre-fills them from the profile, but each invoice keeps its own copy
 *    so old PDFs never change when the profile changes later).
 *  - invoice_items: optional "details" second line under a description.
 *
 * Everything added here is nullable (except payment_method's default),
 * so existing rows keep working with no data back-fill.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_profiles', function (Blueprint $table) {
            $table->string('tagline', 150)->nullable()->after('business_name');
            $table->string('bank_name', 150)->nullable()->after('tax_number');
            $table->string('account_title', 150)->nullable()->after('bank_name');
            $table->string('account_number', 50)->nullable()->after('account_title');
            $table->string('iban', 60)->nullable()->after('account_number');
            $table->string('payment_method', 50)->default('Bank Transfer')->nullable()->after('iban');
            $table->string('signature_name', 150)->nullable()->after('payment_method');
            $table->string('signature_title', 150)->nullable()->after('signature_name');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('business_website', 255)->nullable()->after('business_address');
            $table->string('business_tagline', 150)->nullable()->after('business_website');
            $table->string('business_bank_name', 150)->nullable()->after('business_tagline');
            $table->string('business_account_title', 150)->nullable()->after('business_bank_name');
            $table->string('business_account_number', 50)->nullable()->after('business_account_title');
            $table->string('business_iban', 60)->nullable()->after('business_account_number');
            $table->string('business_payment_method', 50)->default('Bank Transfer')->nullable()->after('business_iban');
            $table->string('business_signature_name', 150)->nullable()->after('business_payment_method');
            $table->string('business_signature_title', 150)->nullable()->after('business_signature_name');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->text('details')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('business_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'tagline', 'bank_name', 'account_title', 'account_number',
                'iban', 'payment_method', 'signature_name', 'signature_title',
            ]);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'business_website', 'business_tagline', 'business_bank_name',
                'business_account_title', 'business_account_number', 'business_iban',
                'business_payment_method', 'business_signature_name', 'business_signature_title',
            ]);
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('details');
        });
    }
};
