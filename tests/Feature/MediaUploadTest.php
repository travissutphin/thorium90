<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class MediaUploadTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles and permissions for testing
        $this->createRolesAndPermissions();
        
        // Create the test storage disk
        Storage::fake('public');
        
        // Create storage directories
        Storage::disk('public')->makeDirectory('media/images/2025/01');
        Storage::disk('public')->makeDirectory('media/documents/2025/01');
        Storage::disk('public')->makeDirectory('media/videos/2025/01');
        Storage::disk('public')->makeDirectory('media/audios/2025/01');
    }

    /** @test */
    public function authenticated_user_with_upload_permission_can_access_media_index()
    {
        $user = $this->createUserWithRole('Admin');
        
        $response = $this->actingAs($user)
            ->get(route('admin.media.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/media/index')
            ->has('media')
            ->has('stats')
        );
    }

    /** @test */
    public function user_without_view_media_permission_cannot_access_media_index()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->get(route('admin.media.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function authenticated_user_can_access_media_upload_page()
    {
        $user = $this->createUserWithRole('Editor');
        
        $response = $this->actingAs($user)
            ->get(route('admin.media.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/media/create')
        );
    }

    /** @test */
    public function user_can_upload_image_file()
    {
        $user = $this->createUserWithRole('Editor');
        $file = UploadedFile::fake()->image('test-image.jpg', 800, 600);

        $response = $this->actingAs($user)
            ->post(route('admin.media.store'), [
                'files' => [$file],
                'is_public' => true,
            ]);

        $response->assertRedirect(route('admin.media.index'));
        $response->assertSessionHas('success');

        // Assert database record was created
        $this->assertDatabaseHas('media', [
            'filename' => 'test-image.jpg',
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'uploaded_by' => $user->id,
            'is_public' => true,
        ]);

        // Assert file was stored
        $media = Media::where('filename', 'test-image.jpg')->first();
        Storage::disk('public')->assertExists($media->path);
    }

    /** @test */
    public function user_can_upload_document_file()
    {
        $user = $this->createUserWithRole('Author');
        $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($user)
            ->post(route('admin.media.store'), [
                'files' => [$file],
                'is_public' => false,
            ]);

        $response->assertRedirect(route('admin.media.index'));

        $this->assertDatabaseHas('media', [
            'filename' => 'document.pdf',
            'mime_type' => 'application/pdf',
            'type' => 'document',
            'uploaded_by' => $user->id,
            'is_public' => false,
        ]);
    }

    /** @test */
    public function user_can_upload_multiple_files()
    {
        $user = $this->createUserWithRole('Editor');
        $files = [
            UploadedFile::fake()->image('image1.jpg'),
            UploadedFile::fake()->image('image2.png'),
            UploadedFile::fake()->create('document.pdf', 500, 'application/pdf'),
        ];

        $response = $this->actingAs($user)
            ->post(route('admin.media.store'), [
                'files' => $files,
                'is_public' => true,
            ]);

        $response->assertRedirect(route('admin.media.index'));

        // Assert all files were uploaded
        $this->assertEquals(3, Media::count());
        $this->assertDatabaseHas('media', ['filename' => 'image1.jpg']);
        $this->assertDatabaseHas('media', ['filename' => 'image2.png']);
        $this->assertDatabaseHas('media', ['filename' => 'document.pdf']);
    }

    /** @test */
    public function upload_fails_with_invalid_file_type()
    {
        $user = $this->createUserWithRole('Editor');
        
        // Create a fake executable file
        $file = UploadedFile::fake()->create('virus.exe', 100, 'application/x-msdownload');

        $response = $this->actingAs($user)
            ->post(route('admin.media.store'), [
                'files' => [$file],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('upload_results');

        // Assert no database record was created
        $this->assertEquals(0, Media::count());
    }

    /** @test */
    public function upload_fails_with_oversized_file()
    {
        $user = $this->createUserWithRole('Editor');
        
        // Create a file larger than the maximum allowed (using fake size)
        $file = UploadedFile::fake()->create('large-image.jpg', 15000, 'image/jpeg'); // 15MB

        $response = $this->actingAs($user)
            ->post(route('admin.media.store'), [
                'files' => [$file],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('upload_results');

        // Assert no database record was created
        $this->assertEquals(0, Media::count());
    }

    /** @test */
    public function user_can_view_media_details()
    {
        $user = $this->createUserWithRole('Editor');
        $media = Media::factory()->create([
            'uploaded_by' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('admin.media.show', $media));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/media/show')
            ->has('media')
            ->where('media.id', $media->id)
        );
    }

    /** @test */
    public function user_can_edit_media_metadata()
    {
        $user = $this->createUserWithRole('Editor');
        $media = Media::factory()->create([
            'uploaded_by' => $user->id,
            'alt_text' => 'Original alt text',
        ]);

        $response = $this->actingAs($user)
            ->put(route('admin.media.update', $media), [
                'alt_text' => 'Updated alt text',
                'description' => 'Updated description',
                'tags' => ['tag1', 'tag2'],
                'is_public' => false,
            ]);

        $response->assertRedirect(route('admin.media.show', $media));

        $media->refresh();
        $this->assertEquals('Updated alt text', $media->alt_text);
        $this->assertEquals('Updated description', $media->description);
        $this->assertEquals(['tag1', 'tag2'], $media->tags);
        $this->assertFalse($media->is_public);
    }

    /** @test */
    public function user_can_delete_media()
    {
        $user = $this->createUserWithRole('Editor');
        
        // Create and store a fake file
        $file = UploadedFile::fake()->image('delete-test.jpg');
        Storage::disk('public')->putFileAs('media/images/2025/01', $file, 'delete-test.jpg');
        
        $media = Media::factory()->create([
            'uploaded_by' => $user->id,
            'path' => 'media/images/2025/01/delete-test.jpg',
            'filename' => 'delete-test.jpg',
        ]);

        $response = $this->actingAs($user)
            ->delete(route('admin.media.destroy', $media));

        $response->assertRedirect(route('admin.media.index'));

        // Assert media was soft deleted
        $this->assertSoftDeleted('media', ['id' => $media->id]);
        
        // Assert file was deleted from storage
        Storage::disk('public')->assertMissing('media/images/2025/01/delete-test.jpg');
    }

    /** @test */
    public function user_can_perform_bulk_delete()
    {
        $user = $this->createUserWithRole('Admin');
        
        $media1 = Media::factory()->create(['uploaded_by' => $user->id]);
        $media2 = Media::factory()->create(['uploaded_by' => $user->id]);
        $media3 = Media::factory()->create(['uploaded_by' => $user->id]);

        $response = $this->actingAs($user)
            ->post(route('admin.media.bulk-action'), [
                'action' => 'delete',
                'media_ids' => [$media1->id, $media2->id],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert selected media were soft deleted
        $this->assertSoftDeleted('media', ['id' => $media1->id]);
        $this->assertSoftDeleted('media', ['id' => $media2->id]);
        
        // Assert unselected media still exists
        $this->assertDatabaseHas('media', [
            'id' => $media3->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function api_upload_endpoint_works()
    {
        $user = $this->createUserWithRole('Editor');
        $file = UploadedFile::fake()->image('api-test.jpg');

        $response = $this->actingAs($user)
            ->postJson(route('admin.media.upload-api'), [
                'file' => $file,
                'alt_text' => 'API uploaded image',
                'is_public' => true,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'media' => [
                'filename' => 'api-test.jpg',
            ],
        ]);

        $this->assertDatabaseHas('media', [
            'filename' => 'api-test.jpg',
            'alt_text' => 'API uploaded image',
            'uploaded_by' => $user->id,
        ]);
    }

    /** @test */
    public function media_statistics_are_calculated_correctly()
    {
        $user = $this->createUserWithRole('Admin');
        
        // Create media of different types
        Media::factory()->create(['type' => 'image', 'size' => 1000]);
        Media::factory()->create(['type' => 'image', 'size' => 2000]);
        Media::factory()->create(['type' => 'document', 'size' => 3000]);
        Media::factory()->create(['type' => 'video', 'size' => 4000]);

        $response = $this->actingAs($user)
            ->get(route('admin.media.index'));

        $response->assertInertia(fn ($page) => $page
            ->where('stats.total_files', 4)
            ->where('stats.total_size', 10000)
            ->where('stats.images_count', 2)
            ->where('stats.documents_count', 1)
            ->where('stats.videos_count', 1)
        );
    }

    /** @test */
    public function media_search_functionality_works()
    {
        $user = $this->createUserWithRole('Admin');
        
        Media::factory()->create([
            'filename' => 'vacation-photo.jpg',
            'alt_text' => 'Beach vacation',
        ]);
        Media::factory()->create([
            'filename' => 'work-document.pdf',
            'description' => 'Important work document',
        ]);

        $response = $this->actingAs($user)
            ->get(route('admin.media.index', ['search' => 'vacation']));

        $response->assertInertia(fn ($page) => $page
            ->where('media.total', 1)
            ->where('media.data.0.filename', 'vacation-photo.jpg')
        );
    }

    /** @test */
    public function media_type_filtering_works()
    {
        $user = $this->createUserWithRole('Admin');
        
        Media::factory()->create(['type' => 'image']);
        Media::factory()->create(['type' => 'document']);
        Media::factory()->create(['type' => 'video']);

        $response = $this->actingAs($user)
            ->get(route('admin.media.index', ['type' => 'image']));

        $response->assertInertia(fn ($page) => $page
            ->where('media.total', 1)
            ->where('media.data.0.type', 'image')
        );
    }
}
