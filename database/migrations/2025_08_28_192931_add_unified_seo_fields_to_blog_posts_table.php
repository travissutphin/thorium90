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
        Schema::table('blog_posts', function (Blueprint $table) {
            // Unified SEO keyword structure - replaces keywords, topics, meta_keywords
            $table->json('seo_keywords')->nullable()->after('meta_keywords');
            
            // Enhanced tags with SEO context and AI attribution
            $table->json('enhanced_tags')->nullable()->after('seo_keywords');
            
            // Content optimization metadata
            $table->json('optimization_data')->nullable()->after('enhanced_tags');
            
            // AI generation tracking
            $table->timestamp('ai_optimized_at')->nullable()->after('optimization_data');
            $table->string('ai_model_used')->nullable()->after('ai_optimized_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn([
                'seo_keywords',
                'enhanced_tags',
                'optimization_data',
                'ai_optimized_at',
                'ai_model_used'
            ]);
        });
    }
};
