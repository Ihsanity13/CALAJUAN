<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // CHECK constraints: rules the DATABASE enforces by itself, so bad data
        // is rejected even if a form, script, or future API forgets to validate.

        // A rating must be 1 to 5 stars.
        DB::statement('ALTER TABLE reviews ADD CONSTRAINT reviews_rating_between_1_and_5 CHECK (rating BETWEEN 1 AND 5)');

        // GPS limits: latitude -90 to 90, longitude -180 to 180.
        DB::statement('ALTER TABLE destinations ADD CONSTRAINT destinations_latitude_range CHECK (latitude BETWEEN -90 AND 90)');
        DB::statement('ALTER TABLE destinations ADD CONSTRAINT destinations_longitude_range CHECK (longitude BETWEEN -180 AND 180)');

        // Only the two known roles are allowed, so a typo like 'Admin' is rejected.
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_allowed CHECK (role IN ('admin', 'user'))");

        // Index on the foreign key column. PostgreSQL does not create one
        // automatically, and the Explore screen will filter destinations by category.
        Schema::table('destinations', function (Blueprint $table) {
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        // Undo everything above, in reverse order (runs on migrate:rollback).
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
        });

        DB::statement('ALTER TABLE users DROP CONSTRAINT users_role_allowed');
        DB::statement('ALTER TABLE destinations DROP CONSTRAINT destinations_longitude_range');
        DB::statement('ALTER TABLE destinations DROP CONSTRAINT destinations_latitude_range');
        DB::statement('ALTER TABLE reviews DROP CONSTRAINT reviews_rating_between_1_and_5');
    }
};
