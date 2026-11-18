<?php

namespace Database\Seeders;

use App\Models\Interest;
use Illuminate\Database\Seeder;

class InterestSeeder extends Seeder
{
    // The interests from the project brief. Visitors pick these to get recommendations.
    public function run(): void
    {
        // name => pill color (see Interest::COLORS)
        $interests = [
            'Nature' => 'green',
            'Food' => 'orange',
            'Adventure' => 'red',
            'Relaxation' => 'blue',
            'Historical' => 'brown',
            'Cultural' => 'purple',
            'Family-friendly' => 'pink',
        ];

        foreach ($interests as $name => $color) {
            // firstOrCreate: safe to run more than once
            Interest::firstOrCreate(['name' => $name], ['color' => $color]);
        }
    }
}
