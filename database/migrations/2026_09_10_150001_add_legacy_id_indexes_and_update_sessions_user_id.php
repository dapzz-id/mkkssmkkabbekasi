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
        $isMySql = DB::getDriverName() === 'mysql';

        // 1. Update sessions.user_id to VARCHAR(36) NULL for UUID compatibility
        if (Schema::hasTable('sessions')) {
            if ($isMySql) {
                DB::statement("ALTER TABLE `sessions` MODIFY `user_id` VARCHAR(36) NULL DEFAULT NULL");
            } else {
                Schema::table('sessions', function (Blueprint $table) {
                    $table->string('user_id', 36)->nullable()->change();
                });
            }
        }

        // 2. Add standalone secondary index on id for domain tables where auto_increment is used
        // In MySQL, when PRIMARY KEY(id) is eventually replaced by PRIMARY KEY(uuid),
        // the AUTO_INCREMENT column MUST still have a key.
        // We add idx_{table}_legacy_id only if id is not already covered by another standalone key.

        $tablesToIndex = ['divisi', 'user', 'konten', 'sponsor', 'pimpinan'];
        // Note: 'calendar' already has UNIQUE KEY 'calendar_id_unique' (`id`), so it does not need a duplicate index.

        foreach ($tablesToIndex as $table) {
            if (Schema::hasTable($table)) {
                $indexName = "idx_{$table}_legacy_id";
                
                if ($isMySql) {
                    $existingIndex = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
                    if (empty($existingIndex)) {
                        DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` (`id`)");
                    }
                } else {
                    Schema::table($table, function (Blueprint $table) use ($indexName) {
                        $table->index('id', $indexName);
                    });
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $isMySql = DB::getDriverName() === 'mysql';

        // 1. Revert standalone secondary indexes
        $tablesToIndex = ['divisi', 'user', 'konten', 'sponsor', 'pimpinan'];
        foreach ($tablesToIndex as $table) {
            if (Schema::hasTable($table)) {
                $indexName = "idx_{$table}_legacy_id";
                if ($isMySql) {
                    $existingIndex = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
                    if (!empty($existingIndex)) {
                        DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
                    }
                } else {
                    Schema::table($table, function (Blueprint $table) use ($indexName) {
                        $table->dropIndex($indexName);
                    });
                }
            }
        }

        // 2. Revert sessions.user_id to BIGINT UNSIGNED NULL
        if (Schema::hasTable('sessions')) {
            if ($isMySql) {
                DB::statement("ALTER TABLE `sessions` MODIFY `user_id` BIGINT UNSIGNED NULL DEFAULT NULL");
            } else {
                Schema::table('sessions', function (Blueprint $table) {
                    $table->unsignedBigInteger('user_id')->nullable()->change();
                });
            }
        }
    }
};
