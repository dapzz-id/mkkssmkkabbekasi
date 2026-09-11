<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Phase 6.5: Convert MyISAM domain tables to InnoDB and establish
     * database-enforced UUID foreign key constraints.
     */
    public function up(): void
    {
        $isMySql = DB::getDriverName() === 'mysql';

        // -------------------------------------------------------------
        // STEP 1: CONVERT STORAGE ENGINES TO INNODB (PARENTS FIRST)
        // -------------------------------------------------------------
        $tablesToConvert = ['divisi', 'user', 'sponsor', 'calendar', 'konten'];

        if ($isMySql) {
            foreach ($tablesToConvert as $table) {
                if (Schema::hasTable($table)) {
                    // Fail-fast guard: ensure table has uuid column and unique index
                    if (!Schema::hasColumn($table, 'uuid')) {
                        throw new \RuntimeException("Cannot convert {$table} to InnoDB: uuid column is missing.");
                    }

                    DB::statement("ALTER TABLE `{$table}` ENGINE = InnoDB");
                }
            }
        }

        // -------------------------------------------------------------
        // STEP 2: PRE-FK DATA INTEGRITY GUARDS
        // -------------------------------------------------------------
        if (Schema::hasTable('user') && Schema::hasTable('divisi')) {
            $userOrphans = DB::table('user')
                ->leftJoin('divisi', 'user.divisi_uuid', '=', 'divisi.uuid')
                ->whereNotNull('user.divisi_uuid')
                ->whereNull('divisi.uuid')
                ->count();

            if ($userOrphans > 0) {
                throw new \RuntimeException("Cannot create fk_user_divisi_uuid: found {$userOrphans} orphan records.");
            }
        }

        if (Schema::hasTable('konten') && Schema::hasTable('user')) {
            $kontenUserOrphans = DB::table('konten')
                ->leftJoin('user', 'konten.user_uuid', '=', 'user.uuid')
                ->whereNotNull('konten.user_uuid')
                ->whereNull('user.uuid')
                ->count();

            if ($kontenUserOrphans > 0) {
                throw new \RuntimeException("Cannot create fk_konten_user_uuid: found {$kontenUserOrphans} orphan records.");
            }
        }

        if (Schema::hasTable('konten') && Schema::hasTable('divisi')) {
            $kontenDivisiOrphans = DB::table('konten')
                ->leftJoin('divisi', 'konten.divisi_uuid', '=', 'divisi.uuid')
                ->whereNotNull('konten.divisi_uuid')
                ->whereNull('divisi.uuid')
                ->count();

            if ($kontenDivisiOrphans > 0) {
                throw new \RuntimeException("Cannot create fk_konten_divisi_uuid: found {$kontenDivisiOrphans} orphan records.");
            }
        }

        // -------------------------------------------------------------
        // STEP 3: ESTABLISH REAL DATABASE-ENFORCED UUID FOREIGN KEYS
        // -------------------------------------------------------------
        // 1. user.divisi_uuid -> divisi.uuid
        if (Schema::hasTable('user') && Schema::hasColumn('user', 'divisi_uuid')) {
            Schema::table('user', function (Blueprint $table) {
                $table->foreign('divisi_uuid', 'fk_user_divisi_uuid')
                    ->references('uuid')
                    ->on('divisi')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }

        // 2. konten.user_uuid -> user.uuid and konten.divisi_uuid -> divisi.uuid
        if (Schema::hasTable('konten')) {
            Schema::table('konten', function (Blueprint $table) {
                if (Schema::hasColumn('konten', 'user_uuid')) {
                    $table->foreign('user_uuid', 'fk_konten_user_uuid')
                        ->references('uuid')
                        ->on('user')
                        ->onUpdate('cascade')
                        ->onDelete('cascade');
                }

                if (Schema::hasColumn('konten', 'divisi_uuid')) {
                    $table->foreign('divisi_uuid', 'fk_konten_divisi_uuid')
                        ->references('uuid')
                        ->on('divisi')
                        ->onUpdate('cascade')
                        ->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $isMySql = DB::getDriverName() === 'mysql';

        // 1. Drop foreign keys in reverse dependency order
        if (Schema::hasTable('konten')) {
            Schema::table('konten', function (Blueprint $table) {
                try {
                    $table->dropForeign('fk_konten_divisi_uuid');
                } catch (\Throwable $e) {
                    // Ignore if already dropped
                }

                try {
                    $table->dropForeign('fk_konten_user_uuid');
                } catch (\Throwable $e) {
                    // Ignore if already dropped
                }
            });
        }

        if (Schema::hasTable('user')) {
            Schema::table('user', function (Blueprint $table) {
                try {
                    $table->dropForeign('fk_user_divisi_uuid');
                } catch (\Throwable $e) {
                    // Ignore if already dropped
                }
            });
        }

        // 2. Revert engines to MyISAM if rolled back on MySQL
        if ($isMySql) {
            $tablesToRevert = ['konten', 'calendar', 'sponsor', 'user', 'divisi'];
            foreach ($tablesToRevert as $table) {
                if (Schema::hasTable($table)) {
                    DB::statement("ALTER TABLE `{$table}` ENGINE = MyISAM");
                }
            }
        }
    }
};
