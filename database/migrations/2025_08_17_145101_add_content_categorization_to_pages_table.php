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
        Schema::table('pages', function (Blueprint $table) {
            // Content categorization fields for AEO optimization
            $table->json('topics')->nullable()->after('schema_data'); // Topic/category taxonomy
            $table->json('keywords')->nullable()->after('topics'); // SEO keywords array
            $table->json('faq_data')->nullable()->after('keywords'); // FAQ content for FAQ schema
            $table->integer('reading_time')->nullable()->after('faq_data'); // Estimated reading time in minutes
            $table->string('content_type')->default('general')->after('reading_time'); // Content categorization
            $table->decimal('content_score', 3, 2)->nullable()->after('content_type'); // Content quality score
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn([
                'topics',
                'keywords', 
                'faq_data',
                'reading_time',
                'content_type',
                'content_score'
            ]);
        });
    }
};
