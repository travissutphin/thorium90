<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('filename'); // Original filename
            $table->string('stored_filename'); // Stored filename with unique identifier
            $table->string('path'); // Full path to file
            $table->string('disk')->default('public'); // Storage disk
            $table->string('mime_type'); // MIME type
            $table->string('extension'); // File extension
            $table->unsignedBigInteger('size'); // File size in bytes
            $table->string('type'); // image, document, video, audio
            $table->json('metadata')->nullable(); // Additional metadata (dimensions, duration, etc.)
            $table->string('thumbnail_path')->nullable(); // Path to thumbnail if applicable
            $table->string('alt_text')->nullable(); // Alt text for accessibility
            $table->text('description')->nullable(); // File description
            $table->json('tags')->nullable(); // Tags for organization
            $table->boolean('is_public')->default(true); // Public visibility
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade'); // Who uploaded
            $table->timestamp('scanned_at')->nullable(); // Virus scan timestamp
            $table->string('scan_result')->nullable(); // Scan result status
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['type', 'is_public']);
            $table->index(['uploaded_by', 'created_at']);
            $table->index(['mime_type']);
            $table->index(['filename']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
