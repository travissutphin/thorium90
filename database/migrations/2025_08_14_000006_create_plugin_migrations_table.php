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
        Schema::create('plugin_migrations', function (Blueprint $table) {
            $table->id();
            $table->string('plugin_id');
            $table->string('migration');
            $table->integer('batch');
            $table->timestamp('migrated_at');
            
            // Unique constraint to prevent duplicate migrations
            $table->unique(['plugin_id', 'migration']);
            
            // Indexes for performance
            $table->index(['plugin_id']);
            $table->index(['plugin_id', 'batch']);
            $table->index(['migrated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugin_migrations');
    }
};
