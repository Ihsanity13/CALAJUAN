<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // INTERESTS: what a place is good for (Nature, Food, Adventure, Relaxation...).
    // Different from the category: a place has ONE category (what it is, e.g. Resort)
    // but can match MANY interests (a resort can be Nature + Relaxation + Food).
    // Recommendations will match these against the interests a user picks.
    public function up(): void
    {
        Schema::create('interests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Same as categories: 'Nature' and 'nature' count as the same name
        DB::statement('CREATE UNIQUE INDEX interests_name_lower_unique ON interests (LOWER(name))');

        // PIVOT TABLE for the many-to-many link between destinations and interests
        Schema::create('destination_interest', function (Blueprint $table) {
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->foreignId('interest_id')->constrained()->cascadeOnDelete();

            // The pair is the key: a place can't have the same interest twice.
            // Its index starts with destination_id, so a second index helps
            // "all places with interest X" (the recommendation and filter queries).
            $table->primary(['destination_id', 'interest_id']);
            $table->index('interest_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destination_interest');
        Schema::dropIfExists('interests');
    }
};
