<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove the unused 'released' enum value from twilio_numbers.status
        // The release() method sets status to 'available', so 'released' is never used
        DB::statement("ALTER TABLE twilio_numbers MODIFY COLUMN status ENUM('available', 'assigned') NOT NULL DEFAULT 'available'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE twilio_numbers MODIFY COLUMN status ENUM('available', 'assigned', 'released') NOT NULL DEFAULT 'available'");
    }
};
