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
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#e91e63'); // Hex color for UI
            
            // SEO fields (inherit from Page model pattern)
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('schema_data')->nullable();
            
            // Management fields
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('posts_count')->default(0); // Cache post count
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('slug');
            $table->index(['is_active', 'sort_order']);
            $table->index('posts_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_categories');
    }
};