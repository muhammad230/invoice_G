<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * A single demo user with 3 clients and 6 invoices (mixed statuses,
     * currencies, due dates) so you can log in and see the dashboard,
     * clients page, invoices page, and invoice show page all with real data.
     */
    public function run(): void
    {
        // 1. Demo user --------------------------------------------------
        $user = User::firstOrCreate(
            ['email' => 'demo@invoiceflow.test'],
            [
                'name'              => 'Demo User',
                'email_verified_at' => Date::now(),
                'password'          => Hash::make('password123'),
            ]
        );

        // 2. Three clients ----------------------------------------------
        $acme     = Client::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Acme Industries'],
            [
                'email'   => 'billing@acme.test',
                'phone'   => '+1 (555) 010-2000',
                'address' => "100 Main St\nNew York, NY 10001",
            ]
        );

        $pixel = Client::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Pixel Studio Co.'],
            [
                'email'   => 'accounts@pixel.test',
                'phone'   => '+44 20 7946 0000',
                'address' => "221B Baker St\nLondon NW1 6XE, UK",
            ]
        );

        $karachi = Client::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Karachi Crafts'],
            [
                'email'   => 'finance@karachi-crafts.test',
                'phone'   => '+92 21 111 000 222',
                'address' => "Shop 14, Empress Market\nKarachi 74000, Pakistan",
            ]
        );

        // Helper: build items & totals for an invoice ---------------
        $buildInvoice = function (
            string $number,
            Client $client,
            array  $lineItems,
            string $currency,
            string $status,
            int    $daysIssuedAgo,
            int    $daysUntilDue,
            float  $taxPct = 0,
            float  $discountFixed = 0,
            string $notes = ''
        ) use ($user) {
            $invoiceDate = Date::today()->subDays($daysIssuedAgo);
            $dueDate     = Date::today()->addDays($daysUntilDue);

            // Build invoice items
            $subtotalCents = 0;
            $itemPayload   = [];
            foreach ($lineItems as [$desc, $qty, $priceDecimal]) {
                $priceCents = (int) round($priceDecimal * 100);
                $totalCents = $qty * $priceCents;
                $subtotalCents += $totalCents;

                $itemPayload[] = [
                    'description' => $desc,
                    'quantity'    => $qty,
                    'price'       => $priceCents,
                    'total'       => $totalCents,
                ];
            }

            $taxCents      = (int) round(($subtotalCents * $taxPct) / 100);
            $beforeDiscCents = $subtotalCents + $taxCents;
            $discCents     = (int) round($discountFixed * 100);
            $totalCents    = max(0, $beforeDiscCents - $discCents);

            $invoice = Invoice::updateOrCreate(
                ['user_id' => $user->id, 'invoice_number' => $number],
                [
                    'client_id'        => $client->id,
                    'invoice_date'     => $invoiceDate,
                    'due_date'         => $dueDate,
                    'currency'         => $currency,
                    'status'           => $status,
                    'subtotal'         => $subtotalCents,
                    'tax_percent'      => round($taxPct, 2),
                    'tax_amount'       => $taxCents,
                    'discount'         => $discCents,
                    'total'            => $totalCents,
                    'business_name'    => $user->name . ' Studio',
                    'business_email'   => 'hello@' . strtolower(explode(' ', $user->name)[0]) . '.test',
                    'business_phone'   => '+1 (555) 111-2222',
                    'business_address' => "123 Creative Ave\nBrooklyn, NY 11201",
                    'notes'            => $notes,
                ]
            );

            // Replace items every time (simple & deterministic).
            $invoice->items()->delete();
            foreach ($itemPayload as $row) {
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'description' => $row['description'],
                    'quantity'    => $row['quantity'],
                    'price'       => $row['price'],
                    'total'       => $row['total'],
                ]);
            }
        };

        // 3. Paid invoices ----------------------------------------------
        $buildInvoice(
            'INV-001', $acme,
            [
                ['Brand identity design',        1, 1500.00],
                ['Logo variations (3 versions)', 1,  500.00],
            ],
            'USD', Invoice::STATUS_PAID,
            daysIssuedAgo: 60, daysUntilDue: -30,
            taxPct: 8.0, discountFixed: 0,
            notes: "Thank you for your business!\nNet 30 payment terms."
        );

        $buildInvoice(
            'INV-002', $pixel,
            [
                ['Website mockups (desktop + mobile)', 2,  650.00],
                ['Figma prototype revisions',          3,  150.00],
                ['User research report',               1,  400.00],
            ],
            'GBP', Invoice::STATUS_PAID,
            daysIssuedAgo: 45, daysUntilDue: -15,
            taxPct: 0.0, discountFixed: 50.00,
            notes: 'Paid via Wise on invoice date.'
        );

        // 4. Pending (not overdue) invoices ----------------------------
        $buildInvoice(
            'INV-003', $karachi,
            [
                ['Hand-embroidered cushions (set of 4)', 10, 2500.00],
                ['Shipping across Pakistan',              1,  500.00],
            ],
            'PKR', Invoice::STATUS_PENDING,
            daysIssuedAgo: 3, daysUntilDue: 27,
            taxPct: 17.0, discountFixed: 0,
            notes: 'Advance 30% received. Balance due on delivery.'
        );

        $buildInvoice(
            'INV-004', $acme,
            [
                ['Monthly website maintenance',        1,  750.00],
                ['Emergency bug fixes (3 tickets)',    3,  120.00],
                ['SEO audit & recommendations',        1,  350.00],
            ],
            'USD', Invoice::STATUS_PENDING,
            daysIssuedAgo: 8, daysUntilDue: 22,
            taxPct: 8.0, discountFixed: 0
        );

        // 5. Overdue invoices (pending + due date already passed) -----
        $buildInvoice(
            'INV-005', $pixel,
            [
                ['Content calendar, Q4', 1, 900.00],
                ['Social posts (30x)',  30,  60.00],
            ],
            'GBP', Invoice::STATUS_PENDING,
            daysIssuedAgo: 40, daysUntilDue: -10,
            taxPct: 0.0, discountFixed: 0,
            notes: 'Reminder sent. Please settle at your earliest convenience.'
        );

        $buildInvoice(
            'INV-006', $acme,
            [
                ['Custom dashboard UI design', 1, 2400.00],
            ],
            'EUR', Invoice::STATUS_PENDING,
            daysIssuedAgo: 50, daysUntilDue: -20,
            taxPct: 21.0, discountFixed: 100.00,
            notes: 'Net 30 terms. Final deliverables already accepted.'
        );
    }
}
