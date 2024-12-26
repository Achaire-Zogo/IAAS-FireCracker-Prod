<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\SystemImageSeeder;
use Database\Seeders\VmOfferSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user',
        ]);

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $this->call([
            SystemImageSeeder::class,
            VmOfferSeeder::class
        ]);
    }
}
