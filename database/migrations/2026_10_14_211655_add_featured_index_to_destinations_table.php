<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PARTIAL INDEX: an index that only includes rows matching the WHERE.
        // Only the featured destinations (about 1% of the table) are indexed,
        // so the index stays tiny and fast. It is sorted by name, which also
        // lets the Home screen's "ORDER BY name" skip the separate sort step.
        // Laravel's Blueprint can't write a partial index, so we use raw SQL.
        DB::statement(
            'CREATE INDEX destinations_featured_idx ON destinations (name) WHERE is_featured = true'
        );
    }

    public function down(): void
    {
        // Undo: remove the index (runs on migrate:rollback)
        DB::statement('DROP INDEX destinations_featured_idx');
    }
};