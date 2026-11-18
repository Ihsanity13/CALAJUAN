<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A third role: business owners, who submit and update their own places
        DB::statement('ALTER TABLE users DROP CONSTRAINT users_role_allowed');
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_allowed CHECK (role IN ('admin', 'user', 'owner'))");

        Schema::table('destinations', function (Blueprint $table) {
            // Who manages this place. Null = managed by the admins only.
            // nullOnDelete: if the owner's account is deleted, the place stays.
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();

            // APPROVAL: places submitted by owners start as 'pending' and only
            // appear to the public once an admin sets them to 'approved'.
            // Existing places (all added by admins) become 'approved' by default.
            $table->string('status', 20)->default('approved');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->index('owner_id');   // "my places" for an owner
            $table->index('status');     // the approval queue and public lists
        });

        DB::statement("ALTER TABLE destinations ADD CONSTRAINT destinations_status_allowed CHECK (status IN ('pending', 'approved', 'rejected'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE destinations DROP CONSTRAINT destinations_status_allowed');

        Schema::table('destinations', function (Blueprint $table) {
            $table->dropIndex(['owner_id']);
            $table->dropIndex(['status']);
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropConstrainedForeignId('owner_id');
            $table->dropColumn(['status', 'rejection_reason', 'reviewed_at']);
        });

        // Owners must be turned back into regular users before the old rule fits again
        DB::table('users')->where('role', 'owner')->update(['role' => 'user']);
        DB::statement('ALTER TABLE users DROP CONSTRAINT users_role_allowed');
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_allowed CHECK (role IN ('admin', 'user'))");
    }
};
