<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_charges', function (Blueprint $table) {
            $table->boolean('is_promotion')->default(false)->after('is_active');
            $table->string('promotion_label')->nullable()->after('is_promotion');
        });
    }

    public function down(): void
    {
        Schema::table('platform_charges', function (Blueprint $table) {
            $table->dropColumn(['is_promotion', 'promotion_label']);
        });
    }
};
