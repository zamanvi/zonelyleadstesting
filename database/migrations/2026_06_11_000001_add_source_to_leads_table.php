<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->enum('source', ['form', 'whatsapp', 'email', 'phone'])->default('form')->after('seller_id');
        });

        // Backfill existing leads based on service field
        \DB::statement("UPDATE leads SET source = 'whatsapp' WHERE service = 'WhatsApp Click'");
        \DB::statement("UPDATE leads SET source = 'phone'    WHERE service = 'Phone Call'");
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
