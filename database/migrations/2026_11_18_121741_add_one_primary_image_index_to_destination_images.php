<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PARTIAL UNIQUE INDEX: among the rows WHERE is_primary = true,
        // each destination_id may appear only once. So the DATABASE itself
        // refuses a second primary photo for the same destination, even if
        // some code forgets to unset the old one first.
        // Laravel's Blueprint can't write a partial index, so we use raw SQL.
        DB::statement(
            'CREATE UNIQUE INDEX destination_images_one_primary_idx
             ON destination_images (destination_id) WHERE is_primary = true'
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX destination_images_one_primary_idx');
    }
};
