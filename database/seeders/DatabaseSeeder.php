<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Appeler le seeder admin
        $this->call([
            AdminSeeder::class, // Assurez-vous que c'est AdminSeeder et non UsersTableSeeder
            // Ajoutez ici vos autres seeders...
        ]);
    }
}