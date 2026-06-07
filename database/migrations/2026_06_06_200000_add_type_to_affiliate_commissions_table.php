<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliate_commissions', function (Blueprint $table) {
            $table->enum('referral_type', ['seller', 'buyer'])->default('seller')->after('note');
            $table->unsignedInteger('points_awarded')->default(0)->after('referral_type');
        });
    }

    public function down(): void
    {
        Schema::table('affiliate_commissions', function (Blueprint $table) {
            $table->dropColumn(['referral_type', 'points_awarded']);
        });
    }
};
