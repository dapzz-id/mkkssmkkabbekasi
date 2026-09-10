<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * PRODUCTION-SAFE INCREMENTAL MIGRATION:
     * 1. Backfill all existing konten records with deterministic, URL-safe slugs.
     * 2. Resolve any duplicate titles/slugs with collision suffixes (-2, -3, etc.).
     * 3. Ensure no slug is numeric-only or empty.
     * 4. Promote slug index to UNIQUE constraint without data loss.
     */
    public function up(): void
    {
        // 1. Fetch all existing records
        $records = DB::table('konten')->orderBy('id', 'asc')->get();
        $usedSlugs = [];

        foreach ($records as $record) {
            $baseSlug = '';

            if (!empty($record->slug)) {
                $baseSlug = Str::slug($record->slug);
            }

            if (empty($baseSlug)) {
                $rawTitle = trim((string) $record->judul);
                $baseSlug = Str::slug($rawTitle);
            }

            // Fallback if title is empty or produced no alphanumeric characters
            if (empty($baseSlug) || ctype_digit($baseSlug)) {
                $baseSlug = 'konten-' . $record->id;
            }

            // Collision Resolution: Ensure uniqueness deterministically
            $uniqueSlug = $baseSlug;
            $counter = 2;
            while (in_array($uniqueSlug, $usedSlugs, true)) {
                $uniqueSlug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $usedSlugs[] = $uniqueSlug;

            // Safe backfill of SEO Title & Meta Description if previously null
            $cleanDesc = Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $record->deskripsi))), 160);

            $updateData = [
                'slug' => $uniqueSlug,
            ];

            if (empty($record->seo_title)) {
                $updateData['seo_title'] = Str::limit(trim((string) $record->judul), 255);
            }

            if (empty($record->seo_description)) {
                $updateData['seo_description'] = $cleanDesc;
            }

            DB::table('konten')->where('id', $record->id)->update($updateData);
        }

        // 2. Validate zero NULL or duplicate slugs
        $nullCount = DB::table('konten')->whereNull('slug')->orWhere('slug', '')->count();
        if ($nullCount > 0) {
            throw new \RuntimeException("Migration aborted: {$nullCount} records still have empty slugs.");
        }

        // 3. Drop non-unique index if exists, and add UNIQUE constraint
        Schema::table('konten', function (Blueprint $table) {
            $existingIndexes = collect(Schema::getIndexes('konten'))->pluck('name');

            if ($existingIndexes->contains('konten_slug_index')) {
                $table->dropIndex('konten_slug_index');
            }

            if (!$existingIndexes->contains('konten_slug_unique')) {
                $table->unique('slug', 'konten_slug_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('konten', function (Blueprint $table) {
            $existingIndexes = collect(Schema::getIndexes('konten'))->pluck('name');

            if ($existingIndexes->contains('konten_slug_unique')) {
                $table->dropUnique('konten_slug_unique');
            }

            if (!$existingIndexes->contains('konten_slug_index')) {
                $table->index('slug', 'konten_slug_index');
            }
        });
    }
};
