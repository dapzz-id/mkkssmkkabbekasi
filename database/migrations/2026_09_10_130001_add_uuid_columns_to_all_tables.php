<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Phase 1: Expand - Add nullable UUID columns and transitional foreign key UUID columns
     * without modifying, altering, or dropping any existing primary keys or INT columns.
     */
    public function up(): void
    {
        // 1. divisi table
        if (Schema::hasTable('divisi') && !Schema::hasColumn('divisi', 'uuid')) {
            Schema::table('divisi', function (Blueprint $table) {
                $table->char('uuid', 36)->nullable()->after('id');
                $table->unique('uuid', 'divisi_uuid_unique');
            });
        }

        // 2. user table
        if (Schema::hasTable('user') && !Schema::hasColumn('user', 'uuid')) {
            Schema::table('user', function (Blueprint $table) {
                $table->char('uuid', 36)->nullable()->after('id');
                $table->char('divisi_uuid', 36)->nullable()->after('id_divisi');
                $table->unique('uuid', 'user_uuid_unique');
                $table->index('divisi_uuid', 'user_divisi_uuid_index');
            });
        }

        // 3. konten table
        if (Schema::hasTable('konten') && !Schema::hasColumn('konten', 'uuid')) {
            Schema::table('konten', function (Blueprint $table) {
                $table->char('uuid', 36)->nullable()->after('id');
                $table->char('user_uuid', 36)->nullable()->after('id_user');
                $table->char('divisi_uuid', 36)->nullable()->after('id_divisi');
                $table->unique('uuid', 'konten_uuid_unique');
                $table->index('user_uuid', 'konten_user_uuid_index');
                $table->index('divisi_uuid', 'konten_divisi_uuid_index');
            });
        }

        // 4. pimpinan table
        if (Schema::hasTable('pimpinan') && !Schema::hasColumn('pimpinan', 'uuid')) {
            Schema::table('pimpinan', function (Blueprint $table) {
                $table->char('uuid', 36)->nullable()->after('id');
                $table->unique('uuid', 'pimpinan_uuid_unique');
            });
        }

        // 5. sponsor table
        if (Schema::hasTable('sponsor') && !Schema::hasColumn('sponsor', 'uuid')) {
            Schema::table('sponsor', function (Blueprint $table) {
                $table->char('uuid', 36)->nullable()->after('id');
                $table->unique('uuid', 'sponsor_uuid_unique');
            });
        }

        // 6. calendar table
        if (Schema::hasTable('calendar') && !Schema::hasColumn('calendar', 'uuid')) {
            Schema::table('calendar', function (Blueprint $table) {
                $table->char('uuid', 36)->nullable()->after('id');
                $table->unique('uuid', 'calendar_uuid_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     * Reversible rollback for Expand phase: Drops the newly added UUID columns if rolled back.
     */
    public function down(): void
    {
        if (Schema::hasTable('calendar') && Schema::hasColumn('calendar', 'uuid')) {
            Schema::table('calendar', function (Blueprint $table) {
                $table->dropUnique('calendar_uuid_unique');
                $table->dropColumn('uuid');
            });
        }

        if (Schema::hasTable('sponsor') && Schema::hasColumn('sponsor', 'uuid')) {
            Schema::table('sponsor', function (Blueprint $table) {
                $table->dropUnique('sponsor_uuid_unique');
                $table->dropColumn('uuid');
            });
        }

        if (Schema::hasTable('pimpinan') && Schema::hasColumn('pimpinan', 'uuid')) {
            Schema::table('pimpinan', function (Blueprint $table) {
                $table->dropUnique('pimpinan_uuid_unique');
                $table->dropColumn('uuid');
            });
        }

        if (Schema::hasTable('konten') && Schema::hasColumn('konten', 'uuid')) {
            Schema::table('konten', function (Blueprint $table) {
                $table->dropIndex('konten_divisi_uuid_index');
                $table->dropIndex('konten_user_uuid_index');
                $table->dropUnique('konten_uuid_unique');
                $table->dropColumn(['uuid', 'user_uuid', 'divisi_uuid']);
            });
        }

        if (Schema::hasTable('user') && Schema::hasColumn('user', 'uuid')) {
            Schema::table('user', function (Blueprint $table) {
                $table->dropIndex('user_divisi_uuid_index');
                $table->dropUnique('user_uuid_unique');
                $table->dropColumn(['uuid', 'divisi_uuid']);
            });
        }

        if (Schema::hasTable('divisi') && Schema::hasColumn('divisi', 'uuid')) {
            Schema::table('divisi', function (Blueprint $table) {
                $table->dropUnique('divisi_uuid_unique');
                $table->dropColumn('uuid');
            });
        }
    }
};
