<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Destination;
use Illuminate\Database\Seeder;

class DestinationSeeder extends Seeder
{
    public function run(): void
    {
        // PLACEHOLDER DATA: replace these with the real destinations your
        // teammates collect. The coordinates below are only the approximate
        // center of Calauan, so every sample pin lands in the same spot.
        $destinations = [
            [
                'category' => 'Nature',
                'name' => 'Sample Nature Spot',
                'description' => 'Placeholder description.',
                'address' => 'Calauan, Laguna',
                'latitude' => 14.1467,
                'longitude' => 121.3167,
                'is_featured' => true,
                'is_verified' => true,
            ],
            [
                'category' => 'Food',
                'name' => 'Sample Restaurant',
                'description' => 'Placeholder description.',
                'address' => 'Calauan, Laguna',
                'latitude' => 14.1467,
                'longitude' => 121.3167,
                'is_featured' => false,
                'is_verified' => true,
            ],
            [
                'category' => 'Historical',
                'name' => 'Sample Landmark',
                'description' => 'Placeholder description.',
                'address' => 'Calauan, Laguna',
                'latitude' => 14.1467,
                'longitude' => 121.3167,
                'is_featured' => false,
                'is_verified' => false,
            ],
        ];

        foreach ($destinations as $item) {
            // Find the category row by its name, because the destinations
            // table stores the category's ID, not its name.
            $category = Category::where('name', $item['category'])->first();

            // firstOrCreate: skip it if a destination with this name exists.
            // The second array holds the other columns to fill in when creating.
            Destination::firstOrCreate(
                ['name' => $item['name']],
                [
                    'category_id' => $category->id,
                    'description' => $item['description'],
                    'address' => $item['address'],
                    'latitude' => $item['latitude'],
                    'longitude' => $item['longitude'],
                    'is_featured' => $item['is_featured'],
                    'is_verified' => $item['is_verified'],
                ]
            );
        }
    }
}