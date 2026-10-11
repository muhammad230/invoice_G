<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Back-fill a Payment row for every invoice that was already marked "paid"
 * before the payments feature existed.
 *
 * Idempotent: only invoices without any payment row are touched, so running
 * this migration (or re-running the seeding/setup) never creates duplicates.
 *
 * amount   = the invoice total (full settlement)
 * paid_on  = the date the invoice was last updated (when it became "paid")
 * method   = "other" (the original status flip had no method information)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invoices') || !Schema::hasTable('payments')) {
            return;
        }

        $now = now();

        DB::table('invoices')
            ->where('status', 'paid')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('payments')
                    ->whereColumn('payments.invoice_id', 'invoices.id');
            })
            ->select(['id', 'user_id', 'total', 'updated_at', 'created_at'])
            ->orderBy('id')
            ->chunkById(200, function ($invoices) use ($now) {
                $rows = [];

                foreach ($invoices as $invoice) {
                    $settledAt = $invoice->updated_at ?? $invoice->created_at ?? $now;

                    $rows[] = [
                        'user_id'    => $invoice->user_id,
                        'invoice_id' => $invoice->id,
                        'amount'     => $invoice->total,
                        'paid_on'    => substr((string) $settledAt, 0, 10),
                        'method'     => 'other',
                        'reference'  => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($rows !== []) {
                    DB::table('payments')->insert($rows);
                }
            });
    }

    public function down(): void
    {
        // No safe way to know which rows were back-filled; leave them. The
        // create_payments_table migration drops the whole table on rollback.
    }
};
