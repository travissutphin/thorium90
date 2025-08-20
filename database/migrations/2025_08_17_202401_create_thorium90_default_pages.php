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
        // Clear existing pages data only (not structure)
        DB::statement('DELETE FROM pages');
        
        // Reset auto-increment for different database types
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE pages AUTO_INCREMENT = 1');
        } elseif (DB::getDriverName() === 'sqlite') {
            DB::statement('DELETE FROM sqlite_sequence WHERE name="pages"');
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER SEQUENCE pages_id_seq RESTART WITH 1');
        }
        
        // The pages table structure already exists, we're just seeding default pages
        // This will be handled by the seeder
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear the default pages (keep custom ones that might be added later)
        DB::statement('DELETE FROM pages WHERE slug IN (
            "about", "features", "pricing", "contact", "privacy-policy", 
            "terms-of-service", "cookie-policy", "refund-policy", "faq", 
            "help-center", "documentation", "support", "blog", "case-studies", 
            "resources", "news", "sitemap", "404-error", "coming-soon"
        )');
    }
};
