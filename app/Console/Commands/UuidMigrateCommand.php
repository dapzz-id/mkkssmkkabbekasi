<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class UuidMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uuid:migrate 
                            {--dry-run : Run preflight checks and audit without making changes}
                            {--prepare : Execute additive migrations to add UUID columns}
                            {--backfill : Backfill UUIDs and transitional FKs in dependency order}
                            {--verify : Run reconciliation and verification checks}
                            {--status : Display current status of UUID migration}
                            {--chunk=100 : Number of records to process per batch}
                            {--force : Force operation without interactive confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Production-safe primary key and foreign key UUID migration orchestrator';

    /**
     * Dependency order: Parents first, then children.
     */
    protected array $tableOrder = [
        'divisi',
        'user',
        'pimpinan',
        'sponsor',
        'calendar',
        'konten',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hasAction = $this->option('dry-run')
            || $this->option('prepare')
            || $this->option('backfill')
            || $this->option('verify')
            || $this->option('status');

        if (!$hasAction) {
            $this->components->info('No action specified. Defaulting to --status.');
            return $this->showStatus();
        }

        if ($this->option('status')) {
            return $this->showStatus();
        }

        if ($this->option('dry-run')) {
            return $this->runDryRun();
        }

        // Acquire exclusive lock for mutating actions
        $lockAcquired = $this->acquireExclusiveLock();

        if (!$lockAcquired) {
            $this->components->error(
                'Could not acquire exclusive migration lock. Another migration process is running.'
            );

            return Command::FAILURE;
        }

        try {
            if ($this->option('prepare')) {
                $status = $this->runPrepare();

                if ($status !== Command::SUCCESS) {
                    return $status;
                }
            }

            if ($this->option('backfill')) {
                $status = $this->runBackfill();

                if ($status !== Command::SUCCESS) {
                    return $status;
                }
            }

            if ($this->option('verify')) {
                return $this->runVerify();
            }

            return Command::SUCCESS;
        } finally {
            $this->releaseExclusiveLock();
        }
    }

    /**
     * Acquire MySQL named lock and Laravel cache lock.
     */
    protected function acquireExclusiveLock(): bool
    {
        try {
            $cacheLocked = Cache::lock('mkks_uuid_migration_lock', 600)->get();

            if (!$cacheLocked) {
                return false;
            }

            if (DB::getDriverName() === 'mysql') {
                $dbLock = DB::selectOne(
                    "SELECT GET_LOCK('mkks_uuid_migration', 5) as locked"
                );

                return !empty($dbLock->locked)
                    && (int) $dbLock->locked === 1;
            }

            return true;
        } catch (\Throwable $e) {
            $this->components->warn(
                'Warning: Database lock acquisition error: ' . $e->getMessage()
            );

            // Fall back to cache lock if DB driver does not support GET_LOCK
            return true;
        }
    }

    /**
     * Release exclusive lock.
     */
    protected function releaseExclusiveLock(): void
    {
        try {
            if (DB::getDriverName() === 'mysql') {
                DB::statement(
                    "SELECT RELEASE_LOCK('mkks_uuid_migration')"
                );
            }
        } catch (\Throwable $e) {
            // Ignore if DB doesn't support named locks
        }

        try {
            Cache::lock('mkks_uuid_migration_lock')->forceRelease();
        } catch (\Throwable $e) {
            // Ignore
        }
    }

    /**
     * Show migration status table across all domain tables.
     */
    protected function showStatus(): int
    {
        $this->components->info('=== MKKS UUID Migration Status ===');

        $rows = [];
        $allReady = true;

        foreach ($this->tableOrder as $table) {
            if (!Schema::hasTable($table)) {
                $rows[] = [
                    $table,
                    'N/A',
                    'Table Missing',
                    0,
                    0,
                    '0%',
                    'N/A',
                ];

                $allReady = false;

                continue;
            }

            $hasUuidCol = Schema::hasColumn($table, 'uuid');

            $totalCount = DB::table($table)->count();

            $uuidCount = $hasUuidCol
                ? DB::table($table)
                    ->whereNotNull('uuid')
                    ->where('uuid', '!=', '')
                    ->count()
                : 0;

            $percent = $totalCount > 0
                ? round(($uuidCount / $totalCount) * 100, 1) . '%'
                : '100%';

            $fkStatus = 'N/A';

            if ($table === 'user') {
                $hasFkUuid = Schema::hasColumn('user', 'divisi_uuid');

                if ($hasFkUuid) {
                    if (Schema::hasColumn('user', 'id_divisi')) {
                        $withFk = DB::table('user')
                            ->whereNotNull('id_divisi')
                            ->whereNotNull('divisi_uuid')
                            ->count();

                        $totalWithFk = DB::table('user')
                            ->whereNotNull('id_divisi')
                            ->count();

                        $fkStatus = "divisi_uuid: {$withFk}/{$totalWithFk}";
                    } else {
                        $withFk = DB::table('user')->whereNotNull('divisi_uuid')->count();
                        $fkStatus = "divisi_uuid: {$withFk}/{$totalCount}";
                    }
                } else {
                    $fkStatus = 'Column Missing';
                }
            } elseif ($table === 'konten') {
                $hasUserFk = Schema::hasColumn('konten', 'user_uuid');
                $hasDivisiFk = Schema::hasColumn('konten', 'divisi_uuid');

                if ($hasUserFk && $hasDivisiFk) {
                    if (Schema::hasColumn('konten', 'id_user') && Schema::hasColumn('konten', 'id_divisi')) {
                        $uFk = DB::table('konten')
                            ->whereNotNull('id_user')
                            ->whereNotNull('user_uuid')
                            ->count();

                        $uTot = DB::table('konten')
                            ->whereNotNull('id_user')
                            ->count();

                        $dFk = DB::table('konten')
                            ->whereNotNull('id_divisi')
                            ->whereNotNull('divisi_uuid')
                            ->count();

                        $dTot = DB::table('konten')
                            ->whereNotNull('id_divisi')
                            ->count();

                        $fkStatus = "user: {$uFk}/{$uTot}, divisi: {$dFk}/{$dTot}";
                    } else {
                        $uFk = DB::table('konten')->whereNotNull('user_uuid')->count();
                        $dFk = DB::table('konten')->whereNotNull('divisi_uuid')->count();
                        $fkStatus = "user: {$uFk}/{$totalCount}, divisi: {$dFk}/{$totalCount}";
                    }
                } else {
                    $fkStatus = 'Columns Missing';
                }
            }

            if (!$hasUuidCol || $uuidCount < $totalCount) {
                $allReady = false;
            }

            $rows[] = [
                $table,
                $hasUuidCol ? 'Yes' : 'No',
                $totalCount,
                $uuidCount,
                $percent,
                $fkStatus,
            ];
        }

        $this->table(
            [
                'Table',
                'UUID Col Exists',
                'Total Rows',
                'Rows with UUID',
                'Coverage',
                'Transitional FKs',
            ],
            $rows
        );

        if ($allReady) {
            $this->components->info(
                'All tables have 100% UUID coverage and columns configured.'
            );
        } else {
            $this->components->warn(
                'Migration in progress or pending prepare/backfill steps.'
            );
        }

        return Command::SUCCESS;
    }

    /**
     * Run preflight dry-run check.
     */
    protected function runDryRun(): int
    {
        $this->components->info(
            '=== Preflight Dry-Run Audit (Zero-Mutation Mode) ==='
        );

        $rows = [];
        $orphanFound = false;

        foreach ($this->tableOrder as $table) {
            if (!Schema::hasTable($table)) {
                $this->components->error("Table missing: {$table}");

                return Command::FAILURE;
            }

            $total = DB::table($table)->count();

            $hasUuid = Schema::hasColumn($table, 'uuid')
                ? 'Yes'
                : 'Pending Migration';

            $rows[] = [
                $table,
                $total,
                $hasUuid,
            ];
        }

        $this->table(
            [
                'Table',
                'Total Records',
                'UUID Column Status',
            ],
            $rows
        );

        // Audit Foreign Key integrity before touching data
        $this->components->info(
            'Auditing existing Foreign Key relationships for orphans...'
        );

        // 1. user foreign key check
        if (Schema::hasColumn('user', 'divisi_uuid') && Schema::hasColumn('divisi', 'uuid')) {
            $orphanUsers = DB::table('user')
                ->leftJoin(
                    'divisi',
                    'user.divisi_uuid',
                    '=',
                    'divisi.uuid'
                )
                ->whereNotNull('user.divisi_uuid')
                ->whereNull('divisi.uuid')
                ->count();
            $fkLabel1 = 'user.divisi_uuid -> divisi.uuid';
        } else {
            $orphanUsers = DB::table('user')
                ->leftJoin(
                    'divisi',
                    'user.id_divisi',
                    '=',
                    'divisi.id'
                )
                ->whereNotNull('user.id_divisi')
                ->whereNull('divisi.id')
                ->count();
            $fkLabel1 = 'user.id_divisi -> divisi.id';
        }

        if ($orphanUsers > 0) {
            $this->components->error(
                "CRITICAL: Found {$orphanUsers} orphan user records with invalid {$fkLabel1}!"
            );

            $orphanFound = true;
        } else {
            $this->components->twoColumnDetail(
                $fkLabel1,
                '<fg=green>Clean (0 orphans)</>'
            );
        }

        // 2. konten.user foreign key check
        if (Schema::hasColumn('konten', 'user_uuid') && Schema::hasColumn('user', 'uuid')) {
            $orphanKontenUser = DB::table('konten')
                ->leftJoin(
                    'user',
                    'konten.user_uuid',
                    '=',
                    'user.uuid'
                )
                ->whereNotNull('konten.user_uuid')
                ->whereNull('user.uuid')
                ->count();
            $fkLabel2 = 'konten.user_uuid -> user.uuid';
        } else {
            $orphanKontenUser = DB::table('konten')
                ->leftJoin(
                    'user',
                    'konten.id_user',
                    '=',
                    'user.id'
                )
                ->whereNotNull('konten.id_user')
                ->whereNull('user.id')
                ->count();
            $fkLabel2 = 'konten.id_user -> user.id';
        }

        if ($orphanKontenUser > 0) {
            $this->components->error(
                "CRITICAL: Found {$orphanKontenUser} orphan konten records with invalid {$fkLabel2}!"
            );

            $orphanFound = true;
        } else {
            $this->components->twoColumnDetail(
                $fkLabel2,
                '<fg=green>Clean (0 orphans)</>'
            );
        }

        // 3. konten.divisi foreign key check
        if (Schema::hasColumn('konten', 'divisi_uuid') && Schema::hasColumn('divisi', 'uuid')) {
            $orphanKontenDivisi = DB::table('konten')
                ->leftJoin(
                    'divisi',
                    'konten.divisi_uuid',
                    '=',
                    'divisi.uuid'
                )
                ->whereNotNull('konten.divisi_uuid')
                ->whereNull('divisi.uuid')
                ->count();
            $fkLabel3 = 'konten.divisi_uuid -> divisi.uuid';
        } else {
            $orphanKontenDivisi = DB::table('konten')
                ->leftJoin(
                    'divisi',
                    'konten.id_divisi',
                    '=',
                    'divisi.id'
                )
                ->whereNotNull('konten.id_divisi')
                ->whereNull('divisi.id')
                ->count();
            $fkLabel3 = 'konten.id_divisi -> divisi.id';
        }

        if ($orphanKontenDivisi > 0) {
            $this->components->error(
                "CRITICAL: Found {$orphanKontenDivisi} orphan konten records with invalid {$fkLabel3}!"
            );

            $orphanFound = true;
        } else {
            $this->components->twoColumnDetail(
                $fkLabel3,
                '<fg=green>Clean (0 orphans)</>'
            );
        }

        if ($orphanFound) {
            $this->components->error(
                'Dry-run audit failed due to orphan records.'
            );

            return Command::FAILURE;
        }

        $this->components->info(
            'Dry-run audit completed successfully. Ready for --prepare or --backfill.'
        );

        return Command::SUCCESS;
    }

    /**
     * Run additive migration.
     */
    protected function runPrepare(): int
    {
        $this->components->info(
            'Running additive migrations...'
        );

        $exitCode = $this->call('migrate', [
            '--path' => 'database/migrations/2026_09_10_130001_add_uuid_columns_to_all_tables.php',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            $this->components->error(
                'Additive migration failed.'
            );

            return Command::FAILURE;
        }

        $this->components->info(
            'Additive migrations completed successfully.'
        );

        return Command::SUCCESS;
    }

    /**
     * Backfill canonical UUIDs and transitional FKs in dependency order.
     */
    protected function runBackfill(): int
    {
        $chunkSize = (int) $this->option('chunk');

        if ($chunkSize < 1) {
            $chunkSize = 100;
        }

        $this->components->info(
            "Starting idempotent UUID backfill (Batch size: {$chunkSize})..."
        );

        foreach ($this->tableOrder as $table) {
            if (
                !Schema::hasTable($table)
                || !Schema::hasColumn($table, 'uuid')
            ) {
                $this->components->error(
                    "Table {$table} does not have uuid column. Run --prepare first."
                );

                return Command::FAILURE;
            }

            // ============================================================
            // 1. Backfill primary UUID
            // ============================================================

            $unassignedCount = DB::table($table)
                ->where(function ($query) {
                    $query
                        ->whereNull('uuid')
                        ->orWhere('uuid', '');
                })
                ->count();

            if ($unassignedCount > 0) {
                $this->output->write(
                    "  Backfilling UUIDs for {$table} ({$unassignedCount} records)... "
                );

                $pkCol = Schema::hasColumn($table, 'id') ? 'id' : 'uuid';
                $cursor = DB::table($table)
                    ->where(function ($query) {
                        $query
                            ->whereNull('uuid')
                            ->orWhere('uuid', '');
                    })
                    ->select($pkCol)
                    ->orderBy($pkCol);

                $cursor->chunk(
                    $chunkSize,
                    function ($records) use ($table, $pkCol) {
                        foreach ($records as $record) {
                            DB::table($table)
                                ->where($pkCol, $record->$pkCol)
                                ->where(function ($q) {
                                    $q
                                        ->whereNull('uuid')
                                        ->orWhere('uuid', '');
                                })
                                ->update([
                                    'uuid' => (string) Str::uuid(),
                                ]);
                        }
                    }
                );

                $this->output->writeln(
                    '<fg=green>Done</>'
                );
            } else {
                $this->components->twoColumnDetail(
                    "{$table} primary UUIDs",
                    '<fg=green>Already 100% filled</>'
                );
            }

            // ============================================================
            // 2. Backfill transitional foreign keys for user
            // ============================================================

            if (
                $table === 'user'
                && Schema::hasColumn('user', 'divisi_uuid')
            ) {
                if (Schema::hasColumn('user', 'id_divisi') && Schema::hasColumn('divisi', 'id')) {
                    $unassignedFk = DB::table('user')
                        ->whereNotNull('id_divisi')
                        ->where(function ($q) {
                            $q
                                ->whereNull('divisi_uuid')
                                ->orWhere('divisi_uuid', '');
                        })
                        ->count();

                    if ($unassignedFk > 0) {
                        $this->output->write(
                            "  Mapping user.divisi_uuid from divisi.uuid ({$unassignedFk} records)... "
                        );

                        $cursor = DB::table('user')
                            ->whereNotNull('id_divisi')
                            ->where(function ($q) {
                                $q
                                    ->whereNull('divisi_uuid')
                                    ->orWhere('divisi_uuid', '');
                            })
                            ->select('id', 'id_divisi')
                            ->orderBy('id');

                        $cursor->chunk(
                            $chunkSize,
                            function ($users) {
                                foreach ($users as $user) {
                                    $divisiUuid = DB::table('divisi')
                                        ->where('id', $user->id_divisi)
                                        ->value('uuid');

                                    if ($divisiUuid) {
                                        DB::table('user')
                                            ->where('id', $user->id)
                                            ->where(function ($q) {
                                                $q
                                                    ->whereNull('divisi_uuid')
                                                    ->orWhere('divisi_uuid', '');
                                            })
                                            ->update([
                                                'divisi_uuid' => $divisiUuid,
                                            ]);
                                    }
                                }
                            }
                        );

                        $this->output->writeln(
                            '<fg=green>Done</>'
                        );
                    } else {
                        $this->components->twoColumnDetail(
                            'user.divisi_uuid mapping',
                            '<fg=green>Already 100% mapped</>'
                        );
                    }
                } else {
                    $this->components->twoColumnDetail(
                        'user.divisi_uuid mapping',
                        '<fg=green>Already 100% mapped</>'
                    );
                }
            }

            // ============================================================
            // 3. Backfill transitional foreign keys for konten
            // ============================================================

            if ($table === 'konten') {

                // --------------------------------------------------------
                // konten.user_uuid
                // --------------------------------------------------------

                if (Schema::hasColumn('konten', 'user_uuid')) {
                    if (Schema::hasColumn('konten', 'id_user') && Schema::hasColumn('user', 'id')) {
                        $unassignedUserFk = DB::table('konten')
                            ->whereNotNull('id_user')
                            ->where(function ($q) {
                                $q
                                    ->whereNull('user_uuid')
                                    ->orWhere('user_uuid', '');
                            })
                            ->count();

                        if ($unassignedUserFk > 0) {
                            $this->output->write(
                                "  Mapping konten.user_uuid from user.uuid ({$unassignedUserFk} records)... "
                            );

                            $cursor = DB::table('konten')
                                ->whereNotNull('id_user')
                                ->where(function ($q) {
                                    $q
                                        ->whereNull('user_uuid')
                                        ->orWhere('user_uuid', '');
                                })
                                ->select('id', 'id_user')
                                ->orderBy('id');

                            $cursor->chunk(
                                $chunkSize,
                                function ($items) {
                                    foreach ($items as $item) {
                                        $userUuid = DB::table('user')
                                            ->where('id', $item->id_user)
                                            ->value('uuid');

                                        if ($userUuid) {
                                            DB::table('konten')
                                                ->where('id', $item->id)
                                                ->where(function ($q) {
                                                    $q
                                                        ->whereNull('user_uuid')
                                                        ->orWhere('user_uuid', '');
                                                })
                                                ->update([
                                                    'user_uuid' => $userUuid,
                                                ]);
                                        }
                                    }
                                }
                            );

                            $this->output->writeln(
                                '<fg=green>Done</>'
                            );
                        } else {
                            $this->components->twoColumnDetail(
                                'konten.user_uuid mapping',
                                '<fg=green>Already 100% mapped</>'
                            );
                        }
                    } else {
                        $this->components->twoColumnDetail(
                            'konten.user_uuid mapping',
                            '<fg=green>Already 100% mapped</>'
                        );
                    }
                }

                // --------------------------------------------------------
                // konten.divisi_uuid
                // --------------------------------------------------------

                if (Schema::hasColumn('konten', 'divisi_uuid')) {
                    if (Schema::hasColumn('konten', 'id_divisi') && Schema::hasColumn('divisi', 'id')) {
                        $unassignedDivisiFk = DB::table('konten')
                            ->whereNotNull('id_divisi')
                            ->where(function ($q) {
                                $q
                                    ->whereNull('divisi_uuid')
                                    ->orWhere('divisi_uuid', '');
                            })
                            ->count();

                        if ($unassignedDivisiFk > 0) {
                            $this->output->write(
                                "  Mapping konten.divisi_uuid from divisi.uuid ({$unassignedDivisiFk} records)... "
                            );

                            $cursor = DB::table('konten')
                                ->whereNotNull('id_divisi')
                                ->where(function ($q) {
                                    $q
                                        ->whereNull('divisi_uuid')
                                        ->orWhere('divisi_uuid', '');
                                })
                                ->select('id', 'id_divisi')
                                ->orderBy('id');

                            $cursor->chunk(
                                $chunkSize,
                                function ($items) {
                                    foreach ($items as $item) {
                                        $divisiUuid = DB::table('divisi')
                                            ->where('id', $item->id_divisi)
                                            ->value('uuid');

                                        if ($divisiUuid) {
                                            DB::table('konten')
                                                ->where('id', $item->id)
                                                ->where(function ($q) {
                                                    $q
                                                        ->whereNull('divisi_uuid')
                                                        ->orWhere('divisi_uuid', '');
                                                })
                                                ->update([
                                                    'divisi_uuid' => $divisiUuid,
                                                ]);
                                        }
                                    }
                                }
                            );

                            $this->output->writeln(
                                '<fg=green>Done</>'
                            );
                        } else {
                            $this->components->twoColumnDetail(
                                'konten.divisi_uuid mapping',
                                '<fg=green>Already 100% mapped</>'
                            );
                        }
                    } else {
                        $this->components->twoColumnDetail(
                            'konten.divisi_uuid mapping',
                            '<fg=green>Already 100% mapped</>'
                        );
                    }
                }
            }
        }

        $this->components->info(
            'UUID backfill finished. Running immediate verification...'
        );

        return $this->runVerify();
    }

    /**
     * Run rigorous reconciliation and verification.
     */
    protected function runVerify(): int
    {
        $this->components->info(
            '=== Reconciliation & Verification Checks ==='
        );

        $hasFailures = false;

        // Canonical UUID v4 regex validation
        $uuidRegex = '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-4[0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/';

        foreach ($this->tableOrder as $table) {
            $totalCount = DB::table($table)->count();

            $nullOrEmptyCount = DB::table($table)
                ->where(function ($query) {
                    $query
                        ->whereNull('uuid')
                        ->orWhere('uuid', '');
                })
                ->count();

            // 1. Completeness check
            if ($nullOrEmptyCount > 0) {
                $this->components->error(
                    "FAIL: Table {$table} has {$nullOrEmptyCount}/{$totalCount} rows missing UUID."
                );

                $hasFailures = true;
            } else {
                $this->components->twoColumnDetail(
                    "{$table} UUID completeness ({$totalCount}/{$totalCount})",
                    '<fg=green>PASS</>'
                );
            }

            // 2. Uniqueness check
            $duplicates = DB::table($table)
                ->select(
                    'uuid',
                    DB::raw('count(*) as count')
                )
                ->groupBy('uuid')
                ->having('count', '>', 1)
                ->get();

            if ($duplicates->isNotEmpty()) {
                $this->components->error(
                    "FAIL: Table {$table} has {$duplicates->count()} duplicate UUIDs!"
                );

                $hasFailures = true;
            } else {
                $this->components->twoColumnDetail(
                    "{$table} UUID uniqueness",
                    '<fg=green>PASS</>'
                );
            }

            // 3. Format validation
            $records = DB::table($table)
                ->select('uuid')
                ->get();

            $invalidFormatCount = 0;

            foreach ($records as $r) {
                if (
                    !empty($r->uuid)
                    && !preg_match($uuidRegex, (string) $r->uuid)
                ) {
                    $invalidFormatCount++;
                }
            }

            if ($invalidFormatCount > 0) {
                $this->components->error(
                    "FAIL: Table {$table} has {$invalidFormatCount} malformed UUIDs!"
                );

                $hasFailures = true;
            } else {
                $this->components->twoColumnDetail(
                    "{$table} UUID format canonical v4",
                    '<fg=green>PASS</>'
                );
            }
        }

        // ================================================================
        // 4. FK Integrity and Parity Checks
        // ================================================================

        $this->components->info(
            'Verifying Foreign Key Integrity and Parity...'
        );

        // ---------------------------------------------------------------
        // user.divisi_uuid integrity / parity
        // ---------------------------------------------------------------

        if (Schema::hasColumn('user', 'divisi_uuid')) {
            if (Schema::hasColumn('user', 'id_divisi') && Schema::hasColumn('divisi', 'id')) {
                $mismatchedUserFk = DB::table('user')
                    ->join(
                        'divisi',
                        'user.id_divisi',
                        '=',
                        'divisi.id'
                    )
                    ->whereColumn(
                        'user.divisi_uuid',
                        '!=',
                        'divisi.uuid'
                    )
                    ->count();

                $unmappedUserFk = DB::table('user')
                    ->whereNotNull('id_divisi')
                    ->where(function ($q) {
                        $q
                            ->whereNull('divisi_uuid')
                            ->orWhere('divisi_uuid', '');
                    })
                    ->count();

                if (
                    $mismatchedUserFk > 0
                    || $unmappedUserFk > 0
                ) {
                    $this->components->error(
                        "FAIL: user.divisi_uuid has {$mismatchedUserFk} mismatches and {$unmappedUserFk} unmapped records."
                    );

                    $hasFailures = true;
                } else {
                    $this->components->twoColumnDetail(
                        'user.divisi_uuid <-> divisi.uuid parity',
                        '<fg=green>PASS (0 mismatches)</>'
                    );
                }
            }

            // Zero orphan check
            $orphanUsers = DB::table('user')
                ->whereNotNull('divisi_uuid')
                ->where('divisi_uuid', '!=', '')
                ->whereNotIn('divisi_uuid', DB::table('divisi')->pluck('uuid'))
                ->count();

            if ($orphanUsers > 0) {
                $this->components->error(
                    "FAIL: user.divisi_uuid has {$orphanUsers} orphan foreign keys."
                );

                $hasFailures = true;
            } else {
                $this->components->twoColumnDetail(
                    'user.divisi_uuid zero orphan FKs',
                    '<fg=green>PASS (0 orphans)</>'
                );
            }
        }

        // ---------------------------------------------------------------
        // konten.user_uuid integrity / parity
        // ---------------------------------------------------------------

        if (Schema::hasColumn('konten', 'user_uuid')) {
            if (Schema::hasColumn('konten', 'id_user') && Schema::hasColumn('user', 'id')) {
                $mismatchedKontenUser = DB::table('konten')
                    ->join(
                        'user',
                        'konten.id_user',
                        '=',
                        'user.id'
                    )
                    ->whereColumn(
                        'konten.user_uuid',
                        '!=',
                        'user.uuid'
                    )
                    ->count();

                $unmappedKontenUser = DB::table('konten')
                    ->whereNotNull('id_user')
                    ->where(function ($q) {
                        $q
                            ->whereNull('user_uuid')
                            ->orWhere('user_uuid', '');
                    })
                    ->count();

                if (
                    $mismatchedKontenUser > 0
                    || $unmappedKontenUser > 0
                ) {
                    $this->components->error(
                        "FAIL: konten.user_uuid has {$mismatchedKontenUser} mismatches and {$unmappedKontenUser} unmapped records."
                    );

                    $hasFailures = true;
                } else {
                    $this->components->twoColumnDetail(
                        'konten.user_uuid <-> user.uuid parity',
                        '<fg=green>PASS (0 mismatches)</>'
                    );
                }
            }

            // Zero orphan check
            $orphanKontenUser = DB::table('konten')
                ->whereNotNull('user_uuid')
                ->where('user_uuid', '!=', '')
                ->whereNotIn('user_uuid', DB::table('user')->pluck('uuid'))
                ->count();

            if ($orphanKontenUser > 0) {
                $this->components->error(
                    "FAIL: konten.user_uuid has {$orphanKontenUser} orphan foreign keys."
                );

                $hasFailures = true;
            } else {
                $this->components->twoColumnDetail(
                    'konten.user_uuid zero orphan FKs',
                    '<fg=green>PASS (0 orphans)</>'
                );
            }
        }

        // ---------------------------------------------------------------
        // konten.divisi_uuid integrity / parity
        // ---------------------------------------------------------------

        if (Schema::hasColumn('konten', 'divisi_uuid')) {
            if (Schema::hasColumn('konten', 'id_divisi') && Schema::hasColumn('divisi', 'id')) {
                $mismatchedKontenDivisi = DB::table('konten')
                    ->join(
                        'divisi',
                        'konten.id_divisi',
                        '=',
                        'divisi.id'
                    )
                    ->whereColumn(
                        'konten.divisi_uuid',
                        '!=',
                        'divisi.uuid'
                    )
                    ->count();

                $unmappedKontenDivisi = DB::table('konten')
                    ->whereNotNull('id_divisi')
                    ->where(function ($q) {
                        $q
                            ->whereNull('divisi_uuid')
                            ->orWhere('divisi_uuid', '');
                    })
                    ->count();

                if (
                    $mismatchedKontenDivisi > 0
                    || $unmappedKontenDivisi > 0
                ) {
                    $this->components->error(
                        "FAIL: konten.divisi_uuid has {$mismatchedKontenDivisi} mismatches and {$unmappedKontenDivisi} unmapped records."
                    );

                    $hasFailures = true;
                } else {
                    $this->components->twoColumnDetail(
                        'konten.divisi_uuid <-> divisi.uuid parity',
                        '<fg=green>PASS (0 mismatches)</>'
                    );
                }
            }

            // Zero orphan check
            $orphanKontenDivisi = DB::table('konten')
                ->whereNotNull('divisi_uuid')
                ->where('divisi_uuid', '!=', '')
                ->whereNotIn('divisi_uuid', DB::table('divisi')->pluck('uuid'))
                ->count();

            if ($orphanKontenDivisi > 0) {
                $this->components->error(
                    "FAIL: konten.divisi_uuid has {$orphanKontenDivisi} orphan foreign keys."
                );

                $hasFailures = true;
            } else {
                $this->components->twoColumnDetail(
                    'konten.divisi_uuid zero orphan FKs',
                    '<fg=green>PASS (0 orphans)</>'
                );
            }
        }

        // ================================================================
        // Final verification result
        // ================================================================

        if ($hasFailures) {
            $this->components->error(
                'Reconciliation checks FAILED. Review errors above.'
            );

            return Command::FAILURE;
        }

        $this->components->info(
            'All reconciliation and parity checks PASSED with 100% integrity.'
        );

        return Command::SUCCESS;
    }
}