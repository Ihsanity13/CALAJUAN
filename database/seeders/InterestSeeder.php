<?php

namespace Database\Seeders;

use App\Models\Interest;
use Illuminate\Database\Seeder;

class InterestSeeder extends Seeder
{
    // The interests from the project brief. Visitors pick these to get recommendations.
    public function run(): void
    {
        $interests = ['Nature', 'Food', 'Adventure', 'Relaxation', 'Historical', 'Cultural', 'Family-friendly'];

        foreach ($interests as $name) {
            // firstOrCreate: safe to run more than once
            Interest::firstOrCreate(['name' => $name]);
        }
    }
}
