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
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            
            // Core content fields (similar to pages table)
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->text('excerpt')->nullable();
            $table->enum('status', ['draft', 'published', 'scheduled'])->default('draft');
            $table->boolean('is_featured')->default(false);
            
            // SEO fields (inherit from Page model pattern)
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('schema_type')->default('BlogPosting');
            $table->json('schema_data')->nullable();
            
            // Blog-specific relationships
            $table->foreignId('blog_category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Blog-specific fields
            $table->string('featured_image')->nullable();
            $table->text('featured_image_alt')->nullable();
            
            // AEO enhancement fields (inherit pattern from pages)
            $table->json('topics')->nullable();
            $table->json('keywords')->nullable();
            $table->json('faq_data')->nullable();
            $table->integer('reading_time')->nullable();
            $table->string('content_type')->default('blog_post');
            $table->integer('content_score')->nullable();
            
            // Blog engagement metrics
            $table->integer('view_count')->default(0);
            $table->integer('like_count')->default(0);
            $table->integer('comment_count')->default(0);
            $table->integer('share_count')->default(0);
            
            // Publishing
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['status', 'published_at']);
            $table->index(['blog_category_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('slug');
            $table->index(['is_featured', 'status']);
            $table->index('view_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};