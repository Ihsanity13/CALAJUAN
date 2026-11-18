<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    // run() is the method Laravel calls when this seeder executes
    public function run(): void
    {
        // Categories say what a place IS. What it's good for (Nature, Food...)
        // is an interest instead (see InterestSeeder).
        $categories = ['Tourist Spot', 'Restaurant', 'Café', 'Resort', 'Accommodation', 'Landmark', 'Religious', 'Recreation'];

        foreach ($categories as $name) {
            // firstOrCreate: look for a category with this name, and only
            // create it if it doesn't exist yet. This keeps the seeder safe
            // to run more than once without making duplicates.
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
