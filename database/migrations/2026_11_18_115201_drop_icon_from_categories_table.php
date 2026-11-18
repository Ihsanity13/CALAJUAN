<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Removes the category "icon" feature (teacher's suggestion).
// A NEW migration instead of editing the old create_categories_table one:
// databases that already ran the old migration (yours and your groupmates')
// only pick up changes from new migration files.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
    }

    // Undo (php artisan migrate:rollback): add the column back, empty.
    // The old icon values can't come back; they were deleted with the column.
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('icon')->nullable();
        });
    }
};
