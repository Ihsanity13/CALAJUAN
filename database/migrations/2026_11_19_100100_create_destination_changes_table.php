<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // CHANGE REQUESTS: when an owner edits a place that is already live, the edit
    // is stored here instead of changing the place. The public keeps seeing the
    // approved version until an admin approves the change.
    public function up(): void
    {
        Schema::create('destination_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            // Who submitted it (kept even if their account is later deleted)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // The full proposed version: {"fields": {...}, "interests": [...], "hours": {...}}
            // jsonb = PostgreSQL's binary JSON type (fast to read, can be indexed)
            $table->jsonb('payload');

            $table->string('status', 20)->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('destination_id');
        });

        DB::statement("ALTER TABLE destination_changes ADD CONSTRAINT destination_changes_status_allowed CHECK (status IN ('pending', 'approved', 'rejected'))");

        // PARTIAL UNIQUE INDEX: at most ONE pending change per place.
        // Editing again replaces the pending change instead of stacking up new ones.
        DB::statement("CREATE UNIQUE INDEX destination_changes_one_pending ON destination_changes (destination_id) WHERE status = 'pending'");

        // Photos uploaded with a change stay hidden until it's approved.
        // Null = a live photo. cascadeOnDelete: deleting a change never makes its photos live.
        Schema::table('destination_images', function (Blueprint $table) {
            $table->foreignId('destination_change_id')->nullable()->constrained()->cascadeOnDelete();
            $table->index('destination_change_id');
        });
    }

    public function down(): void
    {
        Schema::table('destination_images', function (Blueprint $table) {
            $table->dropIndex(['destination_change_id']);
            $table->dropConstrainedForeignId('destination_change_id');
        });

        Schema::dropIfExists('destination_changes');
    }
};
