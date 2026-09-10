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
        Schema::table('konten', function (Blueprint $table) {
            if (!Schema::hasColumn('konten', 'seo_title')) {
                $table->string('seo_title', 255)->nullable()->after('deskripsi');
            }
            if (!Schema::hasColumn('konten', 'seo_description')) {
                $table->text('seo_description')->nullable()->after('seo_title');
            }
            if (!Schema::hasColumn('konten', 'slug')) {
                $table->string('slug', 191)->nullable()->index('konten_slug_index')->after('seo_description');
            } else {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE konten MODIFY slug VARCHAR(191) NULL');
                if (!collect(Schema::getIndexes('konten'))->pluck('name')->contains('konten_slug_index')) {
                    $table->index('slug', 'konten_slug_index');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('konten', function (Blueprint $table) {
            if (collect(Schema::getIndexes('konten'))->pluck('name')->contains('konten_slug_index')) {
                $table->dropIndex('konten_slug_index');
            }
            $colsToDrop = [];
            foreach (['seo_title', 'seo_description', 'slug'] as $col) {
                if (Schema::hasColumn('konten', $col)) {
                    $colsToDrop[] = $col;
                }
            }
            if (!empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });
    }
};
