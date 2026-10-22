<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // DatabaseSeeder is the main seeder. "--seed" always runs this one,
    // and it calls the others.
    public function run(): void
    {
        // Run the category seeder first. Destinations will need categories
        // to exist, so the order matters.
        $this->call([
            CategorySeeder::class,
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

        User::forceCreate([
            'name' => 'Test User',
            'email' => 'user@calajuan.test',
            'password' => 'password',
            'role' => 'user',
        ]);
    }
}
