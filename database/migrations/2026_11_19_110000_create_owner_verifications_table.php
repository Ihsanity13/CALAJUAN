<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // OWNER VERIFICATION: proof that a business owner account belongs to a real,
    // registered business. Sent when the owner signs up; an admin checks it.
    // Until it's approved, the owner can't add or edit places.
    public function up(): void
    {
        Schema::create('owner_verifications', function (Blueprint $table) {
            $table->id();
            // One verification per owner account
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('business_name');
            $table->string('business_address');
            $table->string('business_phone', 30);
            // owner / manager / representative
            $table->string('applicant_role', 20);

            // Which government registration they have, and its number
            // (dti / sec / cda / mayors_permit, see OwnerVerification::REGISTRATION_TYPES)
            $table->string('registration_type', 20);
            $table->string('registration_number', 50);

            // A photo or scan of that permit/certificate, on the PRIVATE disk
            // (storage/app/private): never reachable by a public URL, only through
            // an admin-only route. Nullable only for seeded test accounts.
            $table->string('document_path')->nullable();

            $table->string('status', 20)->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        DB::statement("ALTER TABLE owner_verifications ADD CONSTRAINT owner_verifications_status_allowed CHECK (status IN ('pending', 'approved', 'rejected'))");
        DB::statement("ALTER TABLE owner_verifications ADD CONSTRAINT owner_verifications_type_allowed CHECK (registration_type IN ('dti', 'sec', 'cda', 'mayors_permit'))");
        DB::statement("ALTER TABLE owner_verifications ADD CONSTRAINT owner_verifications_role_allowed CHECK (applicant_role IN ('owner', 'manager', 'representative'))");

        // The same registration number can't be claimed by two accounts at once
        // (a rejected claim doesn't count, so the real owner can still sign up).
        DB::statement("CREATE UNIQUE INDEX owner_verifications_one_claim ON owner_verifications (registration_type, LOWER(registration_number)) WHERE status <> 'rejected'");
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_verifications');
    }
};
