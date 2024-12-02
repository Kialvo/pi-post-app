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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->string('url')->unique();
            $table->string('title')->nullable();
            $table->string('translated_title')->nullable();
            $table->longText('original_post_content'); // Large text for original content
            $table->longText('translated_post_content')->nullable(); // Nullable for untranslated posts
            $table->longText('summary')->nullable(); // Nullable for optional summaries
            $table->timestamp('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
