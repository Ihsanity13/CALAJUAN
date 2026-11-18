<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Business owners now sign up in TWO steps:
    // 1. Sign-up asks only the basics (business name, type, phone) -> status 'draft'
    // 2. After the account exists, they send the permit details -> status 'pending'
    // So the step-2 columns must be empty-able while it's a draft.
    public function up(): void
    {
        Schema::table('owner_verifications', function (Blueprint $table) {
            $table->string('business_address')->nullable()->change();
            $table->string('applicant_role', 20)->nullable()->change();
            $table->string('registration_type', 20)->nullable()->change();
            $table->string('registration_number', 50)->nullable()->change();

            // Type of business (Café, Restaurant, Resort...), from the categories.
            // nullOnDelete: deleting a category never deletes an owner's details.
            $table->foreignId('category_id')->nullable()->after('business_name')->constrained()->nullOnDelete();
        });

        DB::statement('ALTER TABLE owner_verifications DROP CONSTRAINT owner_verifications_status_allowed');
        DB::statement("ALTER TABLE owner_verifications ADD CONSTRAINT owner_verifications_status_allowed CHECK (status IN ('draft', 'pending', 'approved', 'rejected'))");
    }

    public function down(): void
    {
        // Drafts can't fit the old rules (missing details), so they're removed
        DB::table('owner_verifications')->where('status', 'draft')->delete();

        DB::statement('ALTER TABLE owner_verifications DROP CONSTRAINT owner_verifications_status_allowed');
        DB::statement("ALTER TABLE owner_verifications ADD CONSTRAINT owner_verifications_status_allowed CHECK (status IN ('pending', 'approved', 'rejected'))");

        Schema::table('owner_verifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->string('business_address')->nullable(false)->change();
            $table->string('applicant_role', 20)->nullable(false)->change();
            $table->string('registration_type', 20)->nullable(false)->change();
            $table->string('registration_number', 50)->nullable(false)->change();
        });
    }
};
