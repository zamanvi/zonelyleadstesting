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
        // Add 'booking' to source enum and 'closed' to status enum
        \DB::statement("ALTER TABLE leads MODIFY COLUMN source ENUM('form','whatsapp','email','phone','booking') NOT NULL DEFAULT 'form'");
        \DB::statement("ALTER TABLE leads MODIFY COLUMN status ENUM('new','pending','won','lost','closed') NOT NULL DEFAULT 'new'");
    }

    public function down(): void
    {
        \DB::statement("ALTER TABLE leads MODIFY COLUMN source ENUM('form','whatsapp','email','phone') NOT NULL DEFAULT 'form'");
        \DB::statement("ALTER TABLE leads MODIFY COLUMN status ENUM('new','pending','won','lost') NOT NULL DEFAULT 'new'");
    }
};
