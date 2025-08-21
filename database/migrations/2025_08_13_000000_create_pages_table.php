<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            // Database-specific optimizations
            if (DB::getDriverName() === 'mysql') {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
            }
            
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->text('excerpt')->nullable();
            $table->enum('status', ['draft', 'published', 'private'])->default('draft');
            $table->boolean('is_featured')->default(false);
            
            // SEO Fields
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            
            // Schema.org Fields
            $table->string('schema_type')->default('WebPage');
            $table->json('schema_data')->nullable();
            
            // Relationships
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Timestamps
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['status', 'published_at']);
            $table->index(['is_featured', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
