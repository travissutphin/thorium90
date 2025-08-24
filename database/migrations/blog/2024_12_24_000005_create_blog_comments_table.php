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
        // Only create comments table if comments feature is enabled by default
        if (config('blog.features.comments', true)) {
            Schema::create('blog_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('blog_post_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
                $table->foreignId('parent_id')->nullable()->constrained('blog_comments')->onDelete('cascade');
                
                // Comment content
                $table->text('content');
                $table->string('author_name')->nullable(); // For non-registered users if enabled
                $table->string('author_email')->nullable();
                $table->string('author_website')->nullable();
                
                // Moderation
                $table->enum('status', ['pending', 'approved', 'spam', 'rejected'])->default('pending');
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users');
                
                // Spam protection
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent')->nullable();
                
                $table->timestamps();
                
                // Indexes for performance
                $table->index(['blog_post_id', 'status']);
                $table->index(['user_id', 'status']);
                $table->index(['parent_id', 'status']);
                $table->index(['status', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_comments');
    }
};