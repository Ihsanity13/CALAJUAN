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
        Schema::create('reviews', function (Blueprint $table) {
            // Primary key: an auto-numbered ID that uniquely identifies each review
            $table->id();

            // Who wrote the review. Links to the users table.
            // cascadeOnDelete: if the user is deleted, their reviews are deleted too
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Which destination was reviewed. Links to the destinations table.
            // cascadeOnDelete: if the destination is deleted, its reviews go with it
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();

            // The star rating (1 to 5). A tiny whole number that can't be negative.
            // The 1-to-5 limit will be enforced later by validation.
            $table->unsignedTinyInteger('rating');

            // The written review. nullable = the user may leave it empty.
            $table->text('comment')->nullable();

            // Adds created_at and updated_at columns, filled in automatically by Laravel
            $table->timestamps();

            // A user can review a destination only once.
            // The database itself rejects a second review from the same user.
            $table->unique(['user_id', 'destination_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
