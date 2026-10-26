<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // EXPRESSION INDEX: a unique index built on LOWER(name) instead of name.
        // PostgreSQL then treats 'Nature' and 'nature' as the same value
        // and rejects the second one. Laravel's Blueprint can't express this,
        // so we write the SQL directly.
        DB::statement('CREATE UNIQUE INDEX categories_name_lower_unique ON categories (LOWER(name))');
    }

    public function down(): void
    {
        // Undo: remove the index (runs on migrate:rollback)
        DB::statement('DROP INDEX categories_name_lower_unique');
    }
};
