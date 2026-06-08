<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add points balance to users
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('points')->default(0)->after('agreed_terms_at');
        });

        // Full points transaction log
        Schema::create('user_points_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('points');                        // positive = earned, negative = spent
            $table->string('event', 60);                     // machine key: 'referral_join', 'first_lead', etc.
            $table->string('reason');                        // human-readable description
            $table->unsignedBigInteger('related_user_id')->nullable(); // who triggered it
            $table->timestamps();

            $table->index(['user_id', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_points_log');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('points');
        });
    }
};
