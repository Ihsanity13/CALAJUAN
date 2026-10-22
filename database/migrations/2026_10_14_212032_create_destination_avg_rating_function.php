<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Index for the function below. It looks up reviews by destination_id,
        // but the unique index on reviews starts with user_id, so it can't help.
        // Without this index, every call would scan the whole reviews table.
        Schema::table('reviews', function (Blueprint $table) {
            $table->index('destination_id');
        });

        // unprepared() runs the SQL exactly as written. The function body is
        // wrapped in $$ ... $$ (dollar quoting) so the semicolon inside it
        // doesn't end the statement early.
        DB::unprepared(<<<'SQL'
            -- CREATE OR REPLACE: create the function, or update it if it exists.
            -- It takes one input (a destination ID) and returns a number.
            CREATE OR REPLACE FUNCTION destination_avg_rating(p_destination_id BIGINT)
            RETURNS NUMERIC
            LANGUAGE sql
            -- STABLE: tells PostgreSQL the function only reads data, never changes it,
            -- so it can be used safely inside queries.
            STABLE
            AS $$
                -- Average all ratings for this destination, rounded to 2 decimals.
                -- With no reviews yet, AVG gives NULL ("no rating"), which is
                -- more honest than showing 0 stars.
                SELECT ROUND(AVG(rating), 2)
                FROM reviews
                WHERE destination_id = p_destination_id;
            $$;
        SQL);
    }

    public function down(): void
    {
        // Undo: remove the function, then the index (runs on migrate:rollback)
        DB::unprepared('DROP FUNCTION IF EXISTS destination_avg_rating(BIGINT)');

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['destination_id']);
        });
    }
};
