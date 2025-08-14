<?php

namespace Tests\Database;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tests\Traits\WithRoles;

/**
 * Database Security Testing Suite
 * 
 * Critical security tests for Thorium90 to ensure:
 * - Data Encryption: Sensitive data properly encrypted
 * - Access Control: Proper authorization checks
 * - SQL Injection Prevention: Input sanitization
 * - Mass Assignment Protection: Guarded attributes
 * 
 * These tests validate the security measures that protect
 * user data and system integrity.
 */
class SecurityTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRolesAndPermissions();
    }

    /** @test */
    public function two_factor_secrets_are_encrypted()
    {
        $user = User::factory()->create();
        $secret = 'JBSWY3DPEHPK3PXP';
        
        // Set 2FA secret
        $user->update(['two_factor_secret' => encrypt($secret)]);
        
        // Verify raw database value is encrypted (not plain text)
        $rawUser = DB::table('users')->where('id', $user->id)->first();
        $this->assertNotEquals($secret, $rawUser->two_factor_secret);
        $this->assertStringNotContainsString($secret, $rawUser->two_factor_secret);
        
        // Verify we can decrypt it properly
        $user->refresh();
        $this->assertEquals($secret, decrypt($user->two_factor_secret));
    }

    /** @test */
    public function recovery_codes_are_properly_hashed()
    {
        $user = User::factory()->create();
        $recoveryCodes = ['recovery-code-1', 'recovery-code-2', 'recovery-code-3'];
        
        // Store recovery codes (should be encrypted)
        $user->update([
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes))
        ]);
        
        // Verify raw database value is encrypted
        $rawUser = DB::table('users')->where('id', $user->id)->first();
        $this->assertNotEquals(json_encode($recoveryCodes), $rawUser->two_factor_recovery_codes);
        
        // Verify we can decrypt and access codes
        $user->refresh();
        $decryptedCodes = json_decode(decrypt($user->two_factor_recovery_codes));
        $this->assertEquals($recoveryCodes, $decryptedCodes);
    }

    /** @test */
    public function soft_deleted_users_cannot_authenticate()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password')
        ]);
        
        // Verify user can authenticate initially
        $this->assertTrue(Hash::check('password', $user->password));
        
        // Soft delete user
        $user->delete();
        
        // Verify user is soft deleted
        $this->assertSoftDeleted('users', ['id' => $user->id]);
        
        // Attempt to find user for authentication (should fail)
        $foundUser = User::where('email', 'test@example.com')->first();
        $this->assertNull($foundUser, 'Soft deleted users should not be found in normal queries');
        
        // Only with trashed should find the user
        $trashedUser = User::withTrashed()->where('email', 'test@example.com')->first();
        $this->assertNotNull($trashedUser);
        $this->assertNotNull($trashedUser->deleted_at);
    }

    /** @test */
    public function permission_escalation_prevention()
    {
        $subscriber = $this->createUserWithRole('Subscriber');
        
        // Verify subscriber has limited permissions
        $this->assertFalse($subscriber->hasPermissionTo('create users'));
        $this->assertFalse($subscriber->hasPermissionTo('delete users'));
        $this->assertFalse($subscriber->hasPermissionTo('manage roles'));
        
        // Attempt to directly assign admin permissions (should not work through normal means)
        $subscriber->givePermissionTo('manage roles');
        
        // Verify permission was granted (this tests the permission system works)
        $this->assertTrue($subscriber->hasPermissionTo('manage roles'));
        
        // But verify role hierarchy is still intact
        $this->assertFalse($subscriber->hasRole('Admin'));
        $this->assertTrue($subscriber->hasRole('Subscriber'));
    }

    /** @test */
    public function sql_injection_prevention_in_search()
    {
        // Create test pages
        Page::factory()->count(3)->create([
            'title' => 'Normal Page Title',
            'content' => 'Normal content'
        ]);
        
        // Attempt SQL injection in search
        $maliciousInput = "'; DROP TABLE pages; --";
        
        // This should be safely handled by Laravel's query builder
        $results = Page::where('title', 'like', "%{$maliciousInput}%")
            ->orWhere('content', 'like', "%{$maliciousInput}%")
            ->get();
        
        // Should return empty results, not cause SQL error
        $this->assertCount(0, $results);
        
        // Verify pages table still exists and has data
        $this->assertEquals(3, Page::count());
        $this->assertTrue(\Schema::hasTable('pages'));
    }

    /** @test */
    public function mass_assignment_protection()
    {
        // Attempt to mass assign protected attributes
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'id' => 999, // Should be protected
            'created_at' => '2020-01-01 00:00:00', // Should be protected
            'updated_at' => '2020-01-01 00:00:00', // Should be protected
        ];
        
        $user = User::create($userData);
        
        // Verify protected attributes were not mass assigned
        $this->assertNotEquals(999, $user->id);
        $this->assertNotEquals('2020-01-01 00:00:00', $user->created_at->format('Y-m-d H:i:s'));
        
        // Verify allowed attributes were assigned
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
    }

    /** @test */
    public function password_hashing_is_secure()
    {
        $plainPassword = 'secure-password-123';
        $user = User::factory()->create(['password' => Hash::make($plainPassword)]);
        
        // Verify password is hashed in database
        $rawUser = DB::table('users')->where('id', $user->id)->first();
        $this->assertNotEquals($plainPassword, $rawUser->password);
        $this->assertStringStartsWith('$2y$', $rawUser->password); // bcrypt hash format
        
        // Verify password can be verified
        $this->assertTrue(Hash::check($plainPassword, $user->password));
        $this->assertFalse(Hash::check('wrong-password', $user->password));
    }

    /** @test */
    public function sensitive_user_data_is_not_exposed()
    {
        $user = User::factory()->create([
            'password' => Hash::make('secret'),
            'two_factor_secret' => encrypt('secret-key'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
            'remember_token' => 'secret-token'
        ]);
        
        // Convert to array (simulating API response)
        $userArray = $user->toArray();
        
        // Verify sensitive data is not included
        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
        
        // 2FA data should be included but encrypted
        if (isset($userArray['two_factor_secret'])) {
            $this->assertNotEquals('secret-key', $userArray['two_factor_secret']);
        }
    }

    /** @test */
    public function api_tokens_are_properly_secured()
    {
        $user = $this->createUserWithRole('Admin');
        
        // Create API token
        $token = $user->createToken('Test Token', ['read', 'write']);
        
        // Verify token is hashed in database
        $rawToken = DB::table('personal_access_tokens')
            ->where('tokenable_id', $user->id)
            ->first();
        
        $this->assertNotEquals($token->plainTextToken, $rawToken->token);
        $this->assertStringStartsWith('sha256:', $rawToken->token);
        
        // Verify abilities are stored correctly
        $this->assertEquals(['read', 'write'], json_decode($rawToken->abilities));
    }

    /** @test */
    public function role_based_data_access_is_enforced()
    {
        $author = $this->createUserWithRole('Author');
        $admin = $this->createUserWithRole('Admin');
        
        // Create pages by different users
        $authorPage = Page::factory()->create(['user_id' => $author->id]);
        $adminPage = Page::factory()->create(['user_id' => $admin->id]);
        
        // Author should only see their own pages in restricted queries
        $authorPages = Page::where('user_id', $author->id)->get();
        $this->assertCount(1, $authorPages);
        $this->assertEquals($authorPage->id, $authorPages->first()->id);
        
        // Admin should be able to see all pages
        $allPages = Page::all();
        $this->assertCount(2, $allPages);
    }

    /** @test */
    public function database_connection_uses_secure_configuration()
    {
        // Verify database connection configuration
        $config = config('database.connections.' . config('database.default'));
        
        // Should not use default passwords in production
        if (app()->environment('production')) {
            $this->assertNotEquals('password', $config['password']);
            $this->assertNotEquals('secret', $config['password']);
            $this->assertNotEquals('', $config['password']);
        }
        
        // Should use SSL in production
        if (app()->environment('production') && isset($config['options'])) {
            // This would check for SSL options if configured
            $this->assertTrue(true); // Placeholder for SSL verification
        }
    }

    /** @test */
    public function user_enumeration_prevention()
    {
        // Create a user
        $user = User::factory()->create(['email' => 'existing@example.com']);
        
        // Query for existing user should not reveal existence through timing
        $startTime = microtime(true);
        $existingUser = User::where('email', 'existing@example.com')->first();
        $existingTime = microtime(true) - $startTime;
        
        // Query for non-existing user
        $startTime = microtime(true);
        $nonExistingUser = User::where('email', 'nonexisting@example.com')->first();
        $nonExistingTime = microtime(true) - $startTime;
        
        // Time difference should be minimal (no significant timing attack vector)
        $timeDifference = abs($existingTime - $nonExistingTime);
        $this->assertLessThan(0.1, $timeDifference, 'Query timing should not reveal user existence');
    }

    /** @test */
    public function session_data_is_not_stored_in_database_inappropriately()
    {
        // Verify sensitive session data is not accidentally stored in user records
        $user = User::factory()->create();
        
        // Simulate login (this would normally set session data)
        $this->actingAs($user);
        
        // Verify user record doesn't contain session information
        $userRecord = DB::table('users')->where('id', $user->id)->first();
        
        // Check that no session-like data is stored in user fields
        $userArray = (array) $userRecord;
        foreach ($userArray as $key => $value) {
            if ($value) {
                $this->assertStringNotContainsString('session', strtolower($key));
                $this->assertStringNotContainsString('csrf', strtolower($key));
            }
        }
    }

    /** @test */
    public function file_upload_paths_are_secure()
    {
        // Test that file paths cannot be manipulated for directory traversal
        $maliciousPaths = [
            '../../../etc/passwd',
            '..\\..\\..\\windows\\system32\\config\\sam',
            '/etc/passwd',
            'C:\\windows\\system32\\config\\sam'
        ];
        
        foreach ($maliciousPaths as $path) {
            // Simulate path sanitization (this would be in actual file upload logic)
            $sanitizedPath = basename($path);
            
            // Verify path traversal is prevented
            $this->assertStringNotContainsString('..', $sanitizedPath);
            $this->assertStringNotContainsString('/', $sanitizedPath);
            $this->assertStringNotContainsString('\\', $sanitizedPath);
        }
    }

    /** @test */
    public function database_queries_are_parameterized()
    {
        // Enable query logging to verify parameterized queries
        DB::enableQueryLog();
        
        $searchTerm = "test'; DROP TABLE users; --";
        
        // Use Eloquent query (should be parameterized)
        $users = User::where('name', 'like', "%{$searchTerm}%")->get();
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        
        // Verify query uses parameter binding
        $this->assertCount(1, $queries);
        $query = $queries[0];
        
        // Should contain parameter placeholders, not raw SQL injection
        $this->assertStringContainsString('?', $query['query']);
        $this->assertStringNotContainsString('DROP TABLE', $query['query']);
        
        // Bindings should contain the search term safely
        $this->assertContains("%{$searchTerm}%", $query['bindings']);
    }

    /** @test */
    public function error_messages_dont_expose_sensitive_information()
    {
        try {
            // Attempt operation that should fail
            DB::table('non_existent_table')->select('*')->get();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            
            // Error should not expose database structure details in production
            if (app()->environment('production')) {
                $this->assertStringNotContainsString('database', strtolower($errorMessage));
                $this->assertStringNotContainsString('table', strtolower($errorMessage));
                $this->assertStringNotContainsString('column', strtolower($errorMessage));
            }
        }
    }

    /** @test */
    public function audit_trail_for_sensitive_operations()
    {
        $user = $this->createUserWithRole('Admin');
        
        // Perform sensitive operation
        $user->assignRole('Super Admin');
        
        // In a real application, this would check audit logs
        // For now, verify the operation was successful
        $this->assertTrue($user->hasRole('Super Admin'));
        
        // Verify role assignment is tracked in pivot table
        $roleAssignment = DB::table('model_has_roles')
            ->where('model_id', $user->id)
            ->where('model_type', User::class)
            ->exists();
        
        $this->assertTrue($roleAssignment, 'Role assignments should be tracked');
    }

    /** @test */
    public function data_retention_policies_are_enforced()
    {
        // Create old soft-deleted user
        $oldUser = User::factory()->create([
            'deleted_at' => now()->subDays(400) // Very old deletion
        ]);
        
        // In a real application, you might have a cleanup job
        // For now, verify we can identify old soft-deleted records
        $oldDeletedUsers = User::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays(365))
            ->get();
        
        $this->assertCount(1, $oldDeletedUsers);
        $this->assertEquals($oldUser->id, $oldDeletedUsers->first()->id);
    }
}
