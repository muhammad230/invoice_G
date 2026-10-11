<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Contact form submissions from the public "Contact us" page.
     * Email is not sent yet — messages are stored here for follow-up.
     */
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 255);
            $table->text('message');

            // Store the sender IP so we can spot spam / abuse patterns.
            $table->string('ip', 45)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
