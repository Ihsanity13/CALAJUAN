<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // OPENING HOURS: one row per day the place is open.
    // - A place with no rows at all: hours not listed yet.
    // - A place with some rows: the missing days are closed days.
    // - closes_at earlier than opens_at: it closes after midnight (e.g. 6 PM to 2 AM).
    // - opens_at equal to closes_at: open 24 hours that day.
    public function up(): void
    {
        Schema::create('destination_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            // 0 = Sunday ... 6 = Saturday (the same numbering as Carbon's dayOfWeek)
            $table->unsignedTinyInteger('day');
            $table->time('opens_at');
            $table->time('closes_at');
            $table->timestamps();

            // At most one row per day per place. Its index also serves
            // "the hours of destination X" lookups (destination_id comes first).
            $table->unique(['destination_id', 'day']);
        });

        DB::statement('ALTER TABLE destination_hours ADD CONSTRAINT destination_hours_day_range CHECK (day BETWEEN 0 AND 6)');
    }

    public function down(): void
    {
        Schema::dropIfExists('destination_hours');
    }
};
