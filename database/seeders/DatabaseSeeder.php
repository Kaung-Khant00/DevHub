<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

if (!User::where('role', 'SUPER_ADMIN')->exists()) {
    User::create([
        'name' => "Super Admin",
        'email' => env('SUPER_ADMIN_EMAIL'),
        'password' => Hash::make(env('SUPER_ADMIN_PASSWORD')),
        'role' => 'SUPER_ADMIN'
    ]);
}
    }
}
