<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed permissions first
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            RolePermissionSeeder::class,
            SettingsSeeder::class,
        ]);

        // Create test user
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Assign Super Admin role to test user
        $testUser->assignRole('Super Admin');

        // Create additional test users with different roles
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('Admin');

        $editor = User::factory()->create([
            'name' => 'Editor User',
            'email' => 'editor@example.com',
        ]);
        $editor->assignRole('Editor');

        $author = User::factory()->create([
            'name' => 'Author User',
            'email' => 'author@example.com',
        ]);
        $author->assignRole('Author');

        $subscriber = User::factory()->create([
            'name' => 'Subscriber User',
            'email' => 'subscriber@example.com',
        ]);
        $subscriber->assignRole('Subscriber');
    }
}
