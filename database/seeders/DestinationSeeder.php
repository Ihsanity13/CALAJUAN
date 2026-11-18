<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Destination;
use App\Models\Interest;
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
                'category' => 'Tourist Spot',
                'name' => 'Sample Nature Spot',
                'description' => 'Placeholder description.',
                'is_featured' => true,
                'is_hot' => true,
                'is_new' => false,
                'price_level' => 1,
                'price_note' => 'Entrance ₱50 per adult',
                'interests' => ['Nature', 'Adventure', 'Relaxation'],
                // every day, 7 AM to 5 PM
                'hours' => array_fill_keys(range(0, 6), ['opens_at' => '07:00', 'closes_at' => '17:00']),
            ],
            [
                'category' => 'Restaurant',
                'name' => 'Sample Restaurant',
                'description' => 'Placeholder description.',
                'is_featured' => false,
                'is_hot' => false,
                'is_new' => true,
                'price_level' => 2,
                'price_note' => null,
                'interests' => ['Food', 'Family-friendly'],
                // Tuesday to Sunday, 10 AM to 10 PM (closed Mondays)
                'hours' => array_fill_keys([0, 2, 3, 4, 5, 6], ['opens_at' => '10:00', 'closes_at' => '22:00']),
            ],
            [
                'category' => 'Landmark',
                'name' => 'Sample Landmark',
                'description' => 'Placeholder description.',
                'is_featured' => false,
                'is_hot' => false,
                'is_new' => false,
                'price_level' => 0,
                'price_note' => null,
                'interests' => ['Historical', 'Cultural'],
                'hours' => [],   // hours not listed
            ],
        ];

        foreach ($destinations as $item) {
            // Find the category row by its name, because the destinations
            // table stores the category's ID, not its name.
            $category = Category::where('name', $item['category'])->first();

            // firstOrCreate: skip it if a destination with this name exists.
            // The second array holds the other columns to fill in when creating.
            $destination = Destination::firstOrCreate(
                ['name' => $item['name']],
                [
                    'category_id' => $category->id,
                    'description' => $item['description'],
                    'address' => 'Calauan, Laguna',
                    'latitude' => 14.1467,
                    'longitude' => 121.3167,
                    'is_featured' => $item['is_featured'],
                    'is_hot' => $item['is_hot'],
                    'is_new' => $item['is_new'],
                    'price_level' => $item['price_level'],
                    'price_note' => $item['price_note'],
                ]
            );

            // Only fill in interests and hours for places this seeder just created,
            // so running it again never overwrites changes made in the admin pages.
            if ($destination->wasRecentlyCreated) {
                $destination->interests()->sync(Interest::whereIn('name', $item['interests'])->pluck('id'));
                $destination->syncHours($item['hours']);
            }
        }
    }
}
