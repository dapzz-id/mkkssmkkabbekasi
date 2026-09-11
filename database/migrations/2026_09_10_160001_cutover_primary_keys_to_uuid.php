<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Dependency order: Parents first, children last.
     */
    protected array $tables = [
        'divisi',
        'user',
        'konten',
        'sponsor',
        'calendar',
        'pimpinan',
    ];

    /**
     * Run the migrations.
     * Phase 7: UUID Primary Key Cutover.
     * Changes authoritative PRIMARY KEY from `id` to `uuid` while retaining `id` as AUTO_INCREMENT.
     */
    public function up(): void
    {
        $isMySql = DB::getDriverName() === 'mysql';

        if ($isMySql) {
            // STEP 1: PRE-FLIGHT SAFETY GUARDS
            foreach ($this->tables as $table) {
                if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'uuid') || !Schema::hasColumn($table, 'id')) {
                    throw new \RuntimeException("Table {$table} missing required id or uuid column.");
                }

                // Verify 0 null UUIDs
                $nullCount = DB::table($table)->whereNull('uuid')->orWhere('uuid', '')->count();
                if ($nullCount > 0) {
                    throw new \RuntimeException("Cannot cutover {$table}: found {$nullCount} null/empty UUIDs.");
                }

                // Verify secondary index on id exists so AUTO_INCREMENT is never unindexed
                $indexes = DB::select("
                    SELECT INDEX_NAME 
                    FROM information_schema.STATISTICS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                      AND TABLE_NAME = ? 
                      AND COLUMN_NAME = 'id' 
                      AND INDEX_NAME != 'PRIMARY'
                ", [$table]);

                if (empty($indexes)) {
                    $idxName = "idx_{$table}_legacy_id";
                    DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$idxName}` (`id`)");
                }
            }

            // STEP 2: PRIMARY KEY CUTOVER (PARENTS FIRST)
            foreach ($this->tables as $table) {
                // 1. Ensure uuid is NOT NULL
                DB::statement("ALTER TABLE `{$table}` MODIFY `uuid` CHAR(36) NOT NULL");

                // 2. Drop existing PK on id and establish PK on uuid
                DB::statement("ALTER TABLE `{$table}` DROP PRIMARY KEY, ADD PRIMARY KEY (`uuid`)");

                // 3. Drop redundant unique index on uuid if present (PRIMARY KEY inherently guarantees uniqueness)
                $uniqueIndexes = DB::select("
                    SELECT INDEX_NAME 
                    FROM information_schema.STATISTICS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                      AND TABLE_NAME = ? 
                      AND COLUMN_NAME = 'uuid' 
                      AND NON_UNIQUE = 0 
                      AND INDEX_NAME != 'PRIMARY'
                ", [$table]);

                foreach ($uniqueIndexes as $ui) {
                    DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$ui->INDEX_NAME}`");
                }
            }
        } else {
            // SQLite test compatibility fallback
            // In SQLite, primary keys are defined at create-time; ensure compatibility in tests
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $isMySql = DB::getDriverName() === 'mysql';

        if ($isMySql) {
            // Revert in reverse dependency order (Children first)
            $reverseTables = array_reverse($this->tables);

            foreach ($reverseTables as $table) {
                // 1. Restore unique index on uuid
                $uniqueName = "{$table}_uuid_unique";
                $existing = DB::select("
                    SELECT INDEX_NAME 
                    FROM information_schema.STATISTICS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                      AND TABLE_NAME = ? 
                      AND INDEX_NAME = ?
                ", [$table, $uniqueName]);

                if (empty($existing)) {
                    DB::statement("ALTER TABLE `{$table}` ADD UNIQUE KEY `{$uniqueName}` (`uuid`)");
                }

                // 2. Restore primary key on id
                DB::statement("ALTER TABLE `{$table}` DROP PRIMARY KEY, ADD PRIMARY KEY (`id`)");
            }
        }
    }
};
