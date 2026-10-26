<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    // run() is the method Laravel calls when this seeder executes
    public function run(): void
    {
        // Starter categories. Replace these with the ones your teammates decide on.
        $categories = ['Nature', 'Food', 'Historical', 'Religious', 'Recreation'];

        foreach ($categories as $name) {
            // firstOrCreate: look for a category with this name, and only
            // create it if it doesn't exist yet. This keeps the seeder safe
            // to run more than once without making duplicates.
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
