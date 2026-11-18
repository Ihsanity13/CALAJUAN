<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // DATA MIGRATION: categories now say what a place IS (Restaurant, Resort...),
    // and interests say what it's GOOD FOR (Nature, Food...).
    // Three old categories had the same name as an interest, so they're renamed.
    // Their places get the matching interest, so no information is lost.
    private const RENAMES = [
        // old category => [new category, interest to give its places]
        'Nature' => ['Tourist Spot', 'Nature'],
        'Food' => ['Restaurant', 'Food'],
        'Historical' => ['Landmark', 'Historical'],
    ];

    // Place types from the project brief that didn't exist yet
    private const NEW_CATEGORIES = ['Café', 'Resort', 'Accommodation'];

    public function up(): void
    {
        DB::transaction(function () {
            foreach (self::RENAMES as $old => [$new, $interestName]) {
                $category = $this->findCategory($old);

                // Skip if the old one is gone, or the new name is already taken
                if (! $category || $this->findCategory($new)) {
                    continue;
                }

                // Give its places the matching interest (create the interest if needed)
                $interestId = $this->findOrCreateInterest($interestName);
                $destinationIds = DB::table('destinations')->where('category_id', $category->id)->pluck('id');

                foreach ($destinationIds as $destinationId) {
                    // insertOrIgnore: skip places that already have this interest
                    DB::table('destination_interest')->insertOrIgnore([
                        'destination_id' => $destinationId,
                        'interest_id' => $interestId,
                    ]);
                }

                DB::table('categories')->where('id', $category->id)
                    ->update(['name' => $new, 'updated_at' => now()]);
            }

            foreach (self::NEW_CATEGORIES as $name) {
                if (! $this->findCategory($name)) {
                    DB::table('categories')->insert(['name' => $name, 'created_at' => now(), 'updated_at' => now()]);
                }
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            // Remove the added categories, but only if no place uses them yet
            foreach (self::NEW_CATEGORIES as $name) {
                $category = $this->findCategory($name);
                if ($category && ! DB::table('destinations')->where('category_id', $category->id)->exists()) {
                    DB::table('categories')->where('id', $category->id)->delete();
                }
            }

            // Rename back. The interests given to places are kept (harmless).
            foreach (self::RENAMES as $old => [$new]) {
                $category = $this->findCategory($new);
                if ($category && ! $this->findCategory($old)) {
                    DB::table('categories')->where('id', $category->id)
                        ->update(['name' => $old, 'updated_at' => now()]);
                }
            }
        });
    }

    // Case-insensitive, like the LOWER(name) unique index
    private function findCategory(string $name): ?object
    {
        return DB::table('categories')->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();
    }

    private function findOrCreateInterest(string $name): int
    {
        $interest = DB::table('interests')->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();

        return $interest
            ? $interest->id
            : DB::table('interests')->insertGetId(['name' => $name, 'created_at' => now(), 'updated_at' => now()]);
    }
};
