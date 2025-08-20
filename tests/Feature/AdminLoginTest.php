<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles and permissions
        $this->artisan('db:seed --class=PermissionSeeder');
        $this->artisan('db:seed --class=RoleSeeder');
        $this->artisan('db:seed --class=RolePermissionSeeder');
    }

    /** @test */
    public function admin_user_exists_with_correct_credentials()
    {
        // Create the admin user manually to ensure we know the password
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('Super Admin');

        // Test that user exists
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);

        // Test that password is correct
        $this->assertTrue(Hash::check('password', $user->password));

        // Test login via HTTP request
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function can_access_admin_dashboard_after_login()
    {
        // Create the admin user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com', 
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('Super Admin');

        // Login and access dashboard
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function displays_current_users_in_database()
    {
        // Get all users from database
        $users = User::with('roles')->get();
        
        echo "\n=== Current Users in Database ===\n";
        if ($users->count() === 0) {
            echo "No users found in database.\n";
        } else {
            foreach ($users as $user) {
                $roles = $user->roles->pluck('name')->implode(', ');
                echo "Email: {$user->email} | Name: {$user->name} | Roles: {$roles}\n";
            }
        }
        echo "=== End User List ===\n";
        
        // This test always passes - it's just for displaying info
        $this->assertTrue(true);
    }
}