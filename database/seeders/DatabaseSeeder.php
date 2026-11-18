<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // DatabaseSeeder is the main seeder. "--seed" always runs this one,
    // and it calls the others.
    public function run(): void
    {
        // Run the category seeder first. Destinations will need categories
        // to exist, so the order matters. Interests come before
        // destinations too, because the sample places are tagged with them.
        $this->call([
            CategorySeeder::class,
            InterestSeeder::class,
            DestinationSeeder::class,
        ]);

        // forceCreate skips the $fillable protection. We need it here because
        // 'role' is not fillable, so create() would ignore it.
        // The password is written as plain text, but the 'hashed' cast in the
        // User model encrypts it automatically before saving.
        User::forceCreate([
            'name' => 'Admin',
            'email' => 'admin@calajuan.test',
            'password' => 'password',
            'role' => 'admin',
        ]);

        // A business owner, for trying the /business pages and the approval queue
        $owner = User::forceCreate([
            'name' => 'Test Owner',
            'email' => 'owner@calajuan.test',
            'password' => 'password',
            'role' => 'owner',
        ]);

        // Already verified, so the test owner can add places right away
        $owner->ownerVerification()->create([
            'business_name' => 'Test Business',
            'category_id' => Category::where('name', 'Café')->value('id'),
            'business_address' => 'Calauan, Laguna',
            'business_phone' => '0917 000 0000',
            'applicant_role' => 'owner',
            'registration_type' => 'dti',
            'registration_number' => 'TEST-0001',
        ])->forceFill(['status' => 'approved', 'reviewed_at' => now()])->save();

        User::forceCreate([
            'name' => 'Test User',
            'email' => 'user@calajuan.test',
            'password' => 'password',
            'role' => 'user',
        ]);
    }
}
