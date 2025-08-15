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
        Schema::create('plugin_states', function (Blueprint $table) {
            $table->id();
            $table->string('plugin_id')->unique();
            $table->string('version');
            $table->boolean('enabled')->default(false);
            $table->timestamp('installed_at');
            $table->timestamp('enabled_at')->nullable();
            $table->integer('migration_batch')->default(0);
            $table->json('settings')->nullable();
            $table->json('navigation')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['plugin_id']);
            $table->index(['enabled']);
            $table->index(['installed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugin_states');
    }
};
