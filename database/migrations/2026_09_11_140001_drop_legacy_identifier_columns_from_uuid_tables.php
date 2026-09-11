<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Domain tables participating in UUID architecture.
     */
    protected array $domainTables = [
        'divisi',
        'user',
        'konten',
        'sponsor',
        'calendar',
        'pimpinan',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // =====================================================================
        // PHASE 8.10 — PRE-FLIGHT SAFETY GATE
        // =====================================================================
        $this->runPreflightSafetyGate();

        // =====================================================================
        // PHASE 8.11 — DDL CUTOVER
        // =====================================================================
        $this->executeDdlCutover();
    }

    /**
     * Pre-flight assertions before any DDL is executed.
     */
    protected function runPreflightSafetyGate(): void
    {
        $driver = DB::getDriverName();

        // Gate A: Verify UUID is PRIMARY KEY on all domain tables
        foreach ($this->domainTables as $table) {
            if (!Schema::hasTable($table)) {
                throw new \RuntimeException("Pre-flight Gate Failed: Table '{$table}' does not exist.");
            }

            if (!Schema::hasColumn($table, 'uuid')) {
                throw new \RuntimeException("Pre-flight Gate Failed: Table '{$table}' does not have 'uuid' column.");
            }

            if ($driver === 'mysql') {
                $dbName = config('database.connections.mysql.database');
                $pkColumns = DB::table('information_schema.KEY_COLUMN_USAGE')
                    ->where('TABLE_SCHEMA', $dbName)
                    ->where('TABLE_NAME', $table)
                    ->where('CONSTRAINT_NAME', 'PRIMARY')
                    ->pluck('COLUMN_NAME')
                    ->toArray();

                if (!in_array('uuid', $pkColumns, true)) {
                    throw new \RuntimeException(
                        "Pre-flight Gate A Failed: Table '{$table}' does not have 'uuid' as PRIMARY KEY. Found: " . implode(', ', $pkColumns)
                    );
                }
            }
        }

        // Gate B: Verify UUID columns: NOT NULL, unique, valid canonical v4
        $uuidV4Regex = '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-4[0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/';

        foreach ($this->domainTables as $table) {
            $totalCount = DB::table($table)->count();
            if ($totalCount === 0) {
                continue;
            }

            // Check NOT NULL
            $nullCount = DB::table($table)
                ->where(function ($q) {
                    $q->whereNull('uuid')->orWhere('uuid', '');
                })
                ->count();

            if ($nullCount > 0) {
                throw new \RuntimeException(
                    "Pre-flight Gate B Failed: Table '{$table}' has {$nullCount} rows with NULL or empty UUID."
                );
            }

            // Check Unique
            $duplicateCount = DB::table($table)
                ->select('uuid', DB::raw('count(*) as c'))
                ->groupBy('uuid')
                ->having('c', '>', 1)
                ->count();

            if ($duplicateCount > 0) {
                throw new \RuntimeException(
                    "Pre-flight Gate B Failed: Table '{$table}' has {$duplicateCount} duplicate UUIDs."
                );
            }

            // Check Canonical v4 Format
            $records = DB::table($table)->select('uuid')->get();
            foreach ($records as $r) {
                if (!preg_match($uuidV4Regex, (string) $r->uuid)) {
                    throw new \RuntimeException(
                        "Pre-flight Gate B Failed: Table '{$table}' contains non-v4 UUID: '{$r->uuid}'."
                    );
                }
            }
        }

        // Gate C: Verify no active foreign key references legacy integer PKs
        if ($driver === 'mysql') {
            $dbName = config('database.connections.mysql.database');
            $legacyFks = DB::table('information_schema.KEY_COLUMN_USAGE')
                ->where('TABLE_SCHEMA', $dbName)
                ->whereIn('REFERENCED_TABLE_NAME', $this->domainTables)
                ->where('REFERENCED_COLUMN_NAME', 'id')
                ->get();

            if ($legacyFks->isNotEmpty()) {
                $details = $legacyFks->map(function ($fk) {
                    return "{$fk->TABLE_NAME}.{$fk->COLUMN_NAME} -> {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME} ({$fk->CONSTRAINT_NAME})";
                })->implode('; ');

                throw new \RuntimeException(
                    "Pre-flight Gate C Failed: Active foreign keys reference legacy integer 'id': {$details}"
                );
            }
        }

        // Gate D: Verify transitional FK parity (mismatch MUST equal zero)
        // user.id_divisi parity
        if (Schema::hasColumn('user', 'id_divisi') && Schema::hasColumn('divisi', 'id')) {
            $mismatchedUserDivisi = DB::table('user')
                ->join('divisi', 'user.id_divisi', '=', 'divisi.id')
                ->whereColumn('user.divisi_uuid', '!=', 'divisi.uuid')
                ->count();

            if ($mismatchedUserDivisi > 0) {
                throw new \RuntimeException(
                    "Pre-flight Gate D Failed: user.id_divisi has {$mismatchedUserDivisi} mismatches with user.divisi_uuid."
                );
            }
        }

        // konten.id_user parity
        if (Schema::hasColumn('konten', 'id_user') && Schema::hasColumn('user', 'id')) {
            $mismatchedKontenUser = DB::table('konten')
                ->join('user', 'konten.id_user', '=', 'user.id')
                ->whereColumn('konten.user_uuid', '!=', 'user.uuid')
                ->count();

            if ($mismatchedKontenUser > 0) {
                throw new \RuntimeException(
                    "Pre-flight Gate D Failed: konten.id_user has {$mismatchedKontenUser} mismatches with konten.user_uuid."
                );
            }
        }

        // konten.id_divisi parity
        if (Schema::hasColumn('konten', 'id_divisi') && Schema::hasColumn('divisi', 'id')) {
            $mismatchedKontenDivisi = DB::table('konten')
                ->join('divisi', 'konten.id_divisi', '=', 'divisi.id')
                ->whereColumn('konten.divisi_uuid', '!=', 'divisi.uuid')
                ->count();

            if ($mismatchedKontenDivisi > 0) {
                throw new \RuntimeException(
                    "Pre-flight Gate D Failed: konten.id_divisi has {$mismatchedKontenDivisi} mismatches with konten.divisi_uuid."
                );
            }
        }

        // Gate E: Verify zero orphan UUID foreign keys
        if (Schema::hasColumn('user', 'divisi_uuid')) {
            $orphanUsers = DB::table('user')
                ->whereNotNull('divisi_uuid')
                ->where('divisi_uuid', '!=', '')
                ->whereNotIn('divisi_uuid', DB::table('divisi')->pluck('uuid'))
                ->count();

            if ($orphanUsers > 0) {
                throw new \RuntimeException(
                    "Pre-flight Gate E Failed: user.divisi_uuid has {$orphanUsers} orphan records not found in divisi.uuid."
                );
            }
        }

        if (Schema::hasColumn('konten', 'user_uuid')) {
            $orphanKontenUser = DB::table('konten')
                ->whereNotNull('user_uuid')
                ->where('user_uuid', '!=', '')
                ->whereNotIn('user_uuid', DB::table('user')->pluck('uuid'))
                ->count();

            if ($orphanKontenUser > 0) {
                throw new \RuntimeException(
                    "Pre-flight Gate E Failed: konten.user_uuid has {$orphanKontenUser} orphan records not found in user.uuid."
                );
            }
        }

        if (Schema::hasColumn('konten', 'divisi_uuid')) {
            $orphanKontenDivisi = DB::table('konten')
                ->whereNotNull('divisi_uuid')
                ->where('divisi_uuid', '!=', '')
                ->whereNotIn('divisi_uuid', DB::table('divisi')->pluck('uuid'))
                ->count();

            if ($orphanKontenDivisi > 0) {
                throw new \RuntimeException(
                    "Pre-flight Gate E Failed: konten.divisi_uuid has {$orphanKontenDivisi} orphan records not found in divisi.uuid."
                );
            }
        }
    }

    /**
     * Execute DDL Cutover: dynamically drop indexes/FKs and drop legacy columns.
     */
    protected function executeDdlCutover(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $this->rebuildSqliteTable('divisi', ['id']);
            $this->rebuildSqliteTable('user', ['id', 'id_divisi']);
            $this->rebuildSqliteTable('konten', ['id', 'id_user', 'id_divisi']);
            $this->rebuildSqliteTable('sponsor', ['id']);
            $this->rebuildSqliteTable('calendar', ['id']);
            $this->rebuildSqliteTable('pimpinan', ['id']);
            return;
        }

        // ---------------------------------------------------------------------
        // 1. DROP TRANSITIONAL FK COLUMNS (MySQL)
        // ---------------------------------------------------------------------

        // Table: user -> drop id_divisi
        if (Schema::hasColumn('user', 'id_divisi')) {
            $this->dropColumnSafely('user', 'id_divisi');
        }

        // Table: konten -> drop id_user and id_divisi
        if (Schema::hasColumn('konten', 'id_user')) {
            $this->dropColumnSafely('konten', 'id_user');
        }

        if (Schema::hasColumn('konten', 'id_divisi')) {
            $this->dropColumnSafely('konten', 'id_divisi');
        }

        // ---------------------------------------------------------------------
        // 2. DROP LEGACY INTEGER PRIMARY KEY COLUMNS (MySQL)
        // ---------------------------------------------------------------------
        foreach ($this->domainTables as $table) {
            if (Schema::hasColumn($table, 'id')) {
                $this->dropColumnSafely($table, 'id');
            }
        }
    }

    /**
     * Rebuild an SQLite table dropping specified legacy columns and enforcing uuid PRIMARY KEY.
     */
    protected function rebuildSqliteTable(string $table, array $columnsToDrop): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        $tableInfo = DB::select("PRAGMA table_info(`{$table}`)");
        if (empty($tableInfo)) {
            DB::statement('PRAGMA foreign_keys = ON');
            return;
        }

        $allCols = array_map(fn($c) => $c->name, $tableInfo);
        $keepCols = array_values(array_filter($allCols, fn($c) => !in_array($c, $columnsToDrop, true)));

        $colDefs = [];
        foreach ($keepCols as $col) {
            if ($col === 'uuid') {
                $colDefs[] = '`uuid` CHAR(36) NOT NULL PRIMARY KEY';
            } else {
                $type = 'TEXT';
                foreach ($tableInfo as $ti) {
                    if ($ti->name === $col) {
                        $type = $ti->type ?: 'TEXT';
                        break;
                    }
                }
                $colDefs[] = "`{$col}` {$type}";
            }
        }

        if ($table === 'user') {
            $colDefs[] = 'FOREIGN KEY (`divisi_uuid`) REFERENCES `divisi` (`uuid`) ON DELETE CASCADE ON UPDATE CASCADE';
        } elseif ($table === 'konten') {
            $colDefs[] = 'FOREIGN KEY (`user_uuid`) REFERENCES `user` (`uuid`) ON DELETE CASCADE ON UPDATE CASCADE';
            $colDefs[] = 'FOREIGN KEY (`divisi_uuid`) REFERENCES `divisi` (`uuid`) ON DELETE CASCADE ON UPDATE CASCADE';
        }

        $tempTable = "{$table}__phase8";
        DB::statement("DROP TABLE IF EXISTS `{$tempTable}`");
        $createSql = "CREATE TABLE `{$tempTable}` (" . implode(', ', $colDefs) . ")";
        DB::statement($createSql);

        $quotedCols = implode(', ', array_map(fn($c) => "`{$c}`", $keepCols));
        if (!empty($keepCols)) {
            DB::statement("INSERT INTO `{$tempTable}` ({$quotedCols}) SELECT {$quotedCols} FROM `{$table}`");
        }

        DB::statement("DROP TABLE `{$table}`");
        DB::statement("ALTER TABLE `{$tempTable}` RENAME TO `{$table}`");

        DB::statement('PRAGMA foreign_keys = ON');
    }

    /**
     * Dynamically clean up any foreign keys or indexes on a column before dropping it.
     */
    protected function dropColumnSafely(string $table, string $column): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $dbName = config('database.connections.mysql.database');

            // A. If column has AUTO_INCREMENT, strip it first so MySQL allows index drops
            $colMeta = DB::table('information_schema.COLUMNS')
                ->where('TABLE_SCHEMA', $dbName)
                ->where('TABLE_NAME', $table)
                ->where('COLUMN_NAME', $column)
                ->first();

            if ($colMeta && str_contains(strtolower($colMeta->EXTRA ?? ''), 'auto_increment')) {
                $colType = $colMeta->COLUMN_TYPE;
                DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` {$colType} NULL");
            }

            // B. Drop any foreign keys defined ON this column
            $foreignKeys = DB::table('information_schema.KEY_COLUMN_USAGE')
                ->where('TABLE_SCHEMA', $dbName)
                ->where('TABLE_NAME', $table)
                ->where('COLUMN_NAME', $column)
                ->whereNotNull('REFERENCED_TABLE_NAME')
                ->pluck('CONSTRAINT_NAME')
                ->unique();

            foreach ($foreignKeys as $fkName) {
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fkName}`");
            }

            // C. Drop any indexes that specifically index this column (excluding PRIMARY)
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Column_name = ?", [$column]);
            $droppedIndexes = [];
            foreach ($indexes as $idx) {
                $keyName = $idx->Key_name;
                if ($keyName !== 'PRIMARY' && !in_array($keyName, $droppedIndexes, true)) {
                    DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$keyName}`");
                    $droppedIndexes[] = $keyName;
                }
            }

            // D. Drop column
            DB::statement("ALTER TABLE `{$table}` DROP COLUMN `{$column}`");
        }
    }

    /**
     * Reverse the migrations.
     *
     * Irreversible without database restore.
     */
    public function down(): void
    {
        throw new \RuntimeException(
            'Phase 8 schema modification is effectively irreversible without database restore.'
        );
    }
};
