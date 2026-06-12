<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hunt_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('city');
            $table->string('state', 100)->nullable();
            $table->string('category');
            $table->enum('status', ['draft', 'running', 'paused', 'completed'])->default('draft');
            $table->unsignedInteger('total_found')->default(0);
            $table->unsignedInteger('total_contacted')->default(0);
            $table->unsignedInteger('total_replied')->default(0);
            $table->unsignedInteger('total_registered')->default(0);
            $table->string('sms_template_key', 100)->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('hunt_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('hunt_campaigns')->cascadeOnDelete();
            $table->string('business_name');
            $table->string('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->boolean('has_website')->default(false);
            $table->string('website_url')->nullable();
            $table->string('place_id', 200)->nullable();
            $table->string('rating', 10)->nullable();
            $table->unsignedSmallInteger('review_count')->default(0);
            $table->enum('status', ['found', 'selected', 'contacted', 'replied', 'registered', 'skipped'])->default('found');
            $table->timestamp('sms_sent_at')->nullable();
            $table->foreignId('registered_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hunt_leads');
        Schema::dropIfExists('hunt_campaigns');
    }
};
