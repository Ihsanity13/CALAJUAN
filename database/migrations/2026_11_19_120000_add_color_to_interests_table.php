<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Each interest ("experience" for visitors) gets a color for its pill.
    // Only the names from Interest::COLORS are allowed; the CSS has one style per color.
    private const DEFAULTS = [
        'nature' => 'green',
        'food' => 'orange',
        'adventure' => 'red',
        'relaxation' => 'blue',
        'historical' => 'brown',
        'cultural' => 'purple',
        'family-friendly' => 'pink',
    ];

    public function up(): void
    {
        Schema::table('interests', function (Blueprint $table) {
            $table->string('color', 20)->default('gray');
        });

        DB::statement("ALTER TABLE interests ADD CONSTRAINT interests_color_allowed CHECK (color IN ('green', 'orange', 'red', 'blue', 'teal', 'brown', 'purple', 'pink', 'gold', 'gray'))");

        // Give the existing interests their usual colors
        foreach (self::DEFAULTS as $name => $color) {
            DB::table('interests')->whereRaw('LOWER(name) = ?', [$name])->update(['color' => $color]);
        }
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE interests DROP CONSTRAINT interests_color_allowed');

        Schema::table('interests', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
