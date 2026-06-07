<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_charges', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['lead_fee', 'affiliate_commission', 'buyer_referral_commission']);
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('state_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->integer('priority')->default(0); // higher = wins conflict
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['type', 'is_active']);
            $table->index(['category_id', 'state_id', 'city_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_charges');
    }
};
