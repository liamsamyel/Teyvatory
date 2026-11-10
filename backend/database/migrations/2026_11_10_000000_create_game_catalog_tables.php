<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_releases', function (Blueprint $table) {
            $table->id();
            $table->string('source', 80);
            $table->string('source_version', 120);
            $table->string('game_version', 40)->nullable();
            $table->string('source_revision', 80)->nullable();
            $table->string('content_sha256', 64)->nullable();
            $table->string('status', 20)->default('staging');
            $table->boolean('is_active')->default(false);
            $table->text('attribution')->nullable();
            $table->timestampTz('imported_at')->nullable();
            $table->timestampsTz();

            $table->unique(['source', 'source_version']);
            $table->index(['is_active', 'status']);
        });

        Schema::create('elements', function (Blueprint $table) {
            $table->id();
            $table->string('key', 32)->unique();
            $table->string('name', 40);
            $table->string('color', 7)->nullable();
            $table->timestampsTz();
        });

        Schema::create('weapon_types', function (Blueprint $table) {
            $table->id();
            $table->string('key', 32)->unique();
            $table->string('name', 40);
            $table->timestampsTz();
        });

        Schema::create('stat_types', function (Blueprint $table) {
            $table->id();
            $table->string('key', 48)->unique();
            $table->string('name', 80);
            $table->string('unit', 20)->nullable();
            $table->timestampsTz();
        });

        Schema::create('artifact_slots', function (Blueprint $table) {
            $table->id();
            $table->string('key', 32)->unique();
            $table->string('name', 40);
            $table->unsignedTinyInteger('sort_order')->unique();
            $table->timestampsTz();
        });

        Schema::create('characters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_release_id')->constrained()->restrictOnDelete();
            $table->string('game_id', 64);
            $table->string('name', 100);
            $table->foreignId('element_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('weapon_type_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rarity')->nullable();
            $table->string('icon_key', 120)->nullable();
            $table->timestampsTz();

            $table->unique(['catalog_release_id', 'game_id']);
            $table->index(['catalog_release_id', 'name']);
            $table->index(['catalog_release_id', 'element_id', 'rarity']);
        });

        Schema::create('weapons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_release_id')->constrained()->restrictOnDelete();
            $table->string('game_id', 64);
            $table->string('name', 100);
            $table->foreignId('weapon_type_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rarity')->nullable();
            $table->string('icon_key', 120)->nullable();
            $table->foreignId('secondary_stat_type_id')->nullable()->constrained('stat_types')->nullOnDelete();
            $table->timestampsTz();

            $table->unique(['catalog_release_id', 'game_id']);
            $table->index(['catalog_release_id', 'name']);
        });

        Schema::create('artifact_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_release_id')->constrained()->restrictOnDelete();
            $table->string('game_id', 64);
            $table->string('name', 100);
            $table->string('icon_key', 120)->nullable();
            $table->timestampsTz();

            $table->unique(['catalog_release_id', 'game_id']);
            $table->index(['catalog_release_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artifact_sets');
        Schema::dropIfExists('weapons');
        Schema::dropIfExists('characters');
        Schema::dropIfExists('artifact_slots');
        Schema::dropIfExists('stat_types');
        Schema::dropIfExists('weapon_types');
        Schema::dropIfExists('elements');
        Schema::dropIfExists('catalog_releases');
    }
};
