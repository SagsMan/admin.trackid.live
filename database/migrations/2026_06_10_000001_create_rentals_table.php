<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_category_id')->nullable()->constrained('rental_categories')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('category')->nullable(); // vehicle, property, equipment, electronics, other
            $table->decimal('price_per_day', 10, 2);
            $table->string('currency', 10)->default('USD');
            $table->string('location');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('images')->nullable();
            $table->string('owner_id')->nullable();
            $table->string('owner_name');
            $table->string('owner_phone')->nullable();
            $table->string('owner_email')->nullable();
            $table->enum('status', ['available', 'unavailable', 'maintenance'])->default('available');
            $table->boolean('is_active')->default(true);
            $table->integer('min_days')->default(1);
            $table->integer('max_days')->nullable();
            $table->text('terms')->nullable();
            $table->json('features')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('rental_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained('rentals')->cascadeOnDelete();
            $table->string('renter_id')->nullable();
            $table->string('renter_name');
            $table->string('renter_email');
            $table->string('renter_phone')->nullable();
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->integer('total_days');
            $table->decimal('price_per_day', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->string('currency', 10)->default('USD');
            $table->enum('status', ['pending', 'confirmed', 'active', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_bookings');
        Schema::dropIfExists('rentals');
        Schema::dropIfExists('rental_categories');
    }
};
