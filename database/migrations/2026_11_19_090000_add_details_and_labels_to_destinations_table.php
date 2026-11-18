<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            // LABELS: "verified" is replaced by two labels the admin can switch on:
            // 🔥 Hot (popular right now) and ✨ New (recently opened or added).
            // Checking submitted places will be done by the approval feature later.
            $table->dropColumn('is_verified');
            $table->boolean('is_hot')->default(false);
            $table->boolean('is_new')->default(false);

            // CONTACT DETAILS (all optional)
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('facebook')->nullable();

            // PRICE: a level the budget filter can compare (0 = Free, 1 = ₱, 2 = ₱₱, 3 = ₱₱₱),
            // plus a free-text note for the details, e.g. "Entrance ₱50, cottages ₱500".
            $table->unsignedTinyInteger('price_level')->nullable();
            $table->string('price_note')->nullable();
        });

        // The database itself rejects price levels other than 0 to 3
        DB::statement('ALTER TABLE destinations ADD CONSTRAINT destinations_price_level_range CHECK (price_level BETWEEN 0 AND 3)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE destinations DROP CONSTRAINT destinations_price_level_range');

        Schema::table('destinations', function (Blueprint $table) {
            $table->dropColumn(['is_hot', 'is_new', 'phone', 'email', 'website', 'facebook', 'price_level', 'price_note']);
            $table->boolean('is_verified')->default(false);
        });
    }
};
