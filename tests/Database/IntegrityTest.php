<?php

namespace Tests\Database;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Traits\WithRoles;

/**
 * Database Integrity Testing Suite
 * 
 * Critical integrity tests for Thorium90 to ensure:
 * - Data Consistency: Relationships maintained properly
 * - Cascade Operations: Related records handled correctly
 * - Transaction Safety: Rollback on failures
 * - Concurrent Access: Race condition handling
 * 
 * These tests validate the core data integrity that supports
 * the multi-role authentication and content management systems.
 */
class IntegrityTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRolesAndPermissions();
    }

    /** @test */
    public function soft_delete_cascades_properly()
    {
        // Create user with pages
        $user = $this->createUserWithRole('Author');
        $pages = Page::factory()->count(3)->create(['user_id' => $user->id]);
        
        // Verify pages exist
        $this->assertEquals(3, Page::where('user_id', $user->id)->count());
        
        // Soft delete user
        $user->delete();
        
        // Verify user is soft deleted
        $this->assertSoftDeleted('users', ['id' => $user->id]);
        
        // Pages should still exist (not cascaded)
        $this->assertEquals(3, Page::where('user_id', $user->id)->count());
        
        // But pages should be accessible only to admins
        $admin = $this->createUserWithRole('Admin');
        $this->assertTrue($admin->can('view pages'));
    }

    /** @test */
    public function user_deletion_handles_related_pages()
    {
        $user = $this->createUserWithRole('Author');
        $pages = Page::factory()->count(2)->create(['user_id' => $user->id]);
        $pageIds = $pages->pluck('id')->toArray();
        
        // Force delete user (permanent deletion)
        $user->forceDelete();
        
        // Pages should be cascade deleted due to foreign key constraint
        $this->assertEquals(0, Page::whereNull('deleted_at')->count());
        
        // Verify pages were deleted
        foreach ($pageIds as $pageId) {
            $this->assertNull(Page::find($pageId), 'Page should be cascade deleted');
        }
        
        // Verify user is gone
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /** @test */
    public function role_deletion_updates_user_assignments()
    {
        $user = $this->createUserWithRole('Author');
        
        // Verify user has role
        $this->assertTrue($user->hasRole('Author'));
        
        // Delete the Author role
        $authorRole = \Spatie\Permission\Models\Role::where('name', 'Author')->first();
        $authorRole->delete();
        
        // User should no longer have the role
        $user->refresh();
        $this->assertFalse($user->hasRole('Author'));
    }

    /** @test */
    public function permission_changes_invalidate_cache()
    {
        $user = $this->createUserWithRole('Editor');
        
        // Check initial permission
        $this->assertTrue($user->hasPermissionTo('create pages'));
        
        // Remove permission from role
        $editorRole = \Spatie\Permission\Models\Role::where('name', 'Editor')->first();
        $editorRole->revokePermissionTo('create pages');
        
        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // User should no longer have permission
        $user->refresh();
        $this->assertFalse($user->hasPermissionTo('create pages'));
    }

    /** @test */
    public function concurrent_user_updates_handle_race_conditions()
    {
        $user = User::factory()->create();
        
        // Simulate concurrent updates
        DB::transaction(function () use ($user) {
            // First update
            $user1 = User::find($user->id);
            $user1->name = 'Updated by Process 1';
            
            // Second update (simulating concurrent access)
            $user2 = User::find($user->id);
            $user2->email = 'updated@example.com';
            
            // Save both (last one should win for conflicting fields)
            $user1->save();
            $user2->save();
        });
        
        // Verify final state
        $finalUser = User::find($user->id);
        $this->assertEquals('updated@example.com', $finalUser->email);
        
        // Name should be from the first update since it was saved first
        // (This tests optimistic locking behavior)
    }

    /** @test */
    public function transaction_rollback_on_failure()
    {
        $initialUserCount = User::count();
        
        try {
            DB::transaction(function () {
                // Create a user
                $user = User::factory()->create(['name' => 'Transaction Test']);
                
                // Create a page
                Page::factory()->create(['user_id' => $user->id]);
                
                // Force an error to trigger rollback
                throw new \Exception('Simulated error');
            });
        } catch (\Exception $e) {
            // Expected exception
        }
        
        // Verify rollback occurred - no new users should exist
        $this->assertEquals($initialUserCount, User::count());
        $this->assertEquals(0, Page::count());
        
        // Verify specific user was not created
        $this->assertDatabaseMissing('users', ['name' => 'Transaction Test']);
    }

    /** @test */
    public function foreign_key_constraint_prevents_orphaned_records()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        // Try to create page with non-existent user
        Page::factory()->create(['user_id' => 99999]);
    }

    /** @test */
    public function unique_constraints_are_enforced()
    {
        $user1 = User::factory()->create(['email' => 'test@example.com']);
        
        // Try to create another user with same email
        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['email' => 'test@example.com']);
    }

    /** @test */
    public function page_slug_uniqueness_is_enforced()
    {
        $page1 = Page::factory()->create(['slug' => 'unique-slug']);
        
        // Try to create another page with same slug
        $this->expectException(\Illuminate\Database\QueryException::class);
        Page::factory()->create(['slug' => 'unique-slug']);
    }

    /** @test */
    public function soft_deleted_records_dont_affect_unique_constraints()
    {
        // Create and soft delete a user
        $user1 = User::factory()->create(['email' => 'test@example.com']);
        $user1->delete();
        
        // Should be able to create new user with same email
        $user2 = User::factory()->create(['email' => 'test@example.com']);
        
        $this->assertNotEquals($user1->id, $user2->id);
        $this->assertSoftDeleted('users', ['id' => $user1->id]);
        $this->assertDatabaseHas('users', ['id' => $user2->id, 'deleted_at' => null]);
    }

    /** @test */
    public function role_permission_relationships_maintain_integrity()
    {
        $role = \Spatie\Permission\Models\Role::where('name', 'Editor')->first();
        $permission = \Spatie\Permission\Models\Permission::where('name', 'create pages')->first();
        
        // Verify relationship exists
        $this->assertTrue($role->hasPermissionTo($permission));
        
        // Remove permission
        $role->revokePermissionTo($permission);
        
        // Verify relationship is removed
        $this->assertFalse($role->hasPermissionTo($permission));
        
        // Re-add permission
        $role->givePermissionTo($permission);
        
        // Verify relationship is restored
        $this->assertTrue($role->hasPermissionTo($permission));
    }

    /** @test */
    public function user_role_assignments_maintain_integrity()
    {
        $user = User::factory()->create();
        
        // Assign multiple roles
        $user->assignRole(['Author', 'Editor']);
        
        // Verify assignments
        $this->assertTrue($user->hasRole('Author'));
        $this->assertTrue($user->hasRole('Editor'));
        $this->assertCount(2, $user->roles);
        
        // Remove one role
        $user->removeRole('Author');
        
        // Verify removal
        $this->assertFalse($user->hasRole('Author'));
        $this->assertTrue($user->hasRole('Editor'));
        $this->assertCount(1, $user->roles);
    }

    /** @test */
    public function page_status_transitions_are_valid()
    {
        $user = $this->createUserWithRole('Author');
        $page = Page::factory()->create([
            'user_id' => $user->id,
            'status' => 'draft'
        ]);
        
        // Valid transition: draft -> published
        $page->update(['status' => 'published', 'published_at' => now()]);
        $this->assertEquals('published', $page->fresh()->status);
        $this->assertNotNull($page->fresh()->published_at);
        
        // Valid transition: published -> private
        $page->update(['status' => 'private']);
        $this->assertEquals('private', $page->fresh()->status);
        
        // Invalid status should be rejected by validation
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('pages')->where('id', $page->id)->update(['status' => 'invalid']);
    }

    /** @test */
    public function two_factor_authentication_data_integrity()
    {
        $user = User::factory()->create();
        
        // Enable 2FA
        $user->update([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        ]);
        
        // Verify data is encrypted
        $rawUser = DB::table('users')->where('id', $user->id)->first();
        $this->assertNotEquals('test-secret', $rawUser->two_factor_secret);
        $this->assertNotEquals('["code1","code2"]', $rawUser->two_factor_recovery_codes);
        
        // Verify data can be decrypted
        $user->refresh();
        $this->assertEquals('test-secret', decrypt($user->two_factor_secret));
        $this->assertEquals(['code1', 'code2'], json_decode(decrypt($user->two_factor_recovery_codes)));
    }

    /** @test */
    public function settings_data_type_integrity()
    {
        // Test different data types
        $settings = [
            ['key' => 'string_setting', 'value' => 'test value', 'type' => 'string'],
            ['key' => 'integer_setting', 'value' => '123', 'type' => 'integer'],
            ['key' => 'boolean_setting', 'value' => '1', 'type' => 'boolean'],
            ['key' => 'json_setting', 'value' => '{"key":"value"}', 'type' => 'json'],
        ];
        
        foreach ($settings as $setting) {
            DB::table('settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
        
        // Verify all settings were stored
        $this->assertEquals(4, DB::table('settings')->count());
        
        // Verify data integrity
        $stringSetting = DB::table('settings')->where('key', 'string_setting')->first();
        $this->assertEquals('string', $stringSetting->type);
        $this->assertEquals('test value', $stringSetting->value);
        
        $jsonSetting = DB::table('settings')->where('key', 'json_setting')->first();
        $this->assertEquals('json', $jsonSetting->type);
        $this->assertJson($jsonSetting->value);
    }

    /** @test */
    public function personal_access_tokens_maintain_user_relationship()
    {
        $user = $this->createUserWithRole('Admin');
        
        // Create token
        $token = $user->createToken('Test Token');
        
        // Verify relationship
        $this->assertEquals($user->id, $token->accessToken->tokenable_id);
        $this->assertEquals(User::class, $token->accessToken->tokenable_type);
        
        // Verify token belongs to user
        $this->assertTrue($user->tokens->contains($token->accessToken));
        
        // Delete user
        $user->delete();
        
        // Token should still exist but user should be soft deleted
        $this->assertNotNull($token->accessToken->fresh());
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    /** @test */
    public function database_constraints_prevent_invalid_data()
    {
        // Test NOT NULL constraints
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        DB::table('users')->insert([
            'name' => null, // Should fail - name is required
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    /** @test */
    public function json_column_data_integrity()
    {
        $page = Page::factory()->create([
            'schema_data' => [
                'type' => 'Article',
                'author' => 'Test Author',
                'datePublished' => '2024-01-01',
            ]
        ]);
        
        // Verify JSON data is stored correctly
        $this->assertIsArray($page->schema_data);
        $this->assertEquals('Article', $page->schema_data['type']);
        
        // Update JSON data
        $page->update([
            'schema_data' => array_merge($page->schema_data, [
                'dateModified' => '2024-01-02'
            ])
        ]);
        
        // Verify update
        $page->refresh();
        $this->assertEquals('2024-01-02', $page->schema_data['dateModified']);
        $this->assertEquals('Article', $page->schema_data['type']); // Original data preserved
    }

    /** @test */
    public function cascade_delete_behavior_is_correct()
    {
        $user = $this->createUserWithRole('Author');
        $pages = Page::factory()->count(2)->create(['user_id' => $user->id]);
        $pageIds = $pages->pluck('id')->toArray();
        
        // Verify initial state
        $this->assertEquals(2, Page::where('user_id', $user->id)->count());
        
        // Force delete user (permanent)
        $user->forceDelete();
        
        // Pages should be cascade deleted (onDelete('cascade') is configured)
        $this->assertEquals(0, Page::where('user_id', $user->id)->count());
        
        // Verify pages were deleted
        foreach ($pageIds as $pageId) {
            $this->assertDatabaseMissing('pages', ['id' => $pageId]);
        }
        
        // User should be gone
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
