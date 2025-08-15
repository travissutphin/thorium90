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
            // Template system fields
            $table->string('template')->default('core-page')->after('schema_data');
            $table->string('layout')->nullable()->after('template');
            $table->string('theme')->nullable()->after('layout');
            $table->json('blocks')->nullable()->after('theme');
            $table->json('template_config')->nullable()->after('blocks');
            
            // Add indexes for template fields
            $table->index('template');
            $table->index(['template', 'layout']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropIndex(['pages_template_index']);
            $table->dropIndex(['pages_template_layout_index']);
            $table->dropColumn([
                'template',
                'layout', 
                'theme',
                'blocks',
                'template_config'
            ]);
        });
    }
};
