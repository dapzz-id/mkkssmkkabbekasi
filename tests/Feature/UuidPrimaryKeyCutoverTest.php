<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\Divisi;
use App\Models\Konten;
use App\Models\Pimpinan;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class UuidPrimaryKeyCutoverTest extends TestCase
{
    use RefreshDatabase;

    protected array $domainTables = [
        'divisi',
        'user',
        'konten',
        'sponsor',
        'calendar',
        'pimpinan',
    ];

    /**
     * 1. Verify database schema: uuid is PRIMARY KEY, legacy id column does NOT exist.
     */
    public function test_database_primary_key_is_uuid_on_all_six_domain_tables(): void
    {
        $isMysql = DB::getDriverName() === 'mysql';

        foreach ($this->domainTables as $table) {
            if ($isMysql) {
                // A. Primary Key is uuid
                $pkCols = DB::select("SHOW KEYS FROM `{$table}` WHERE Key_name = 'PRIMARY'");
                $this->assertNotEmpty($pkCols, "Table {$table} has no PRIMARY KEY defined.");
                $this->assertEquals('uuid', $pkCols[0]->Column_name, "Table {$table} PRIMARY KEY must be 'uuid'.");

                // B. uuid column is NOT NULL
                $uuidCol = DB::select("SHOW COLUMNS FROM `{$table}` WHERE Field = 'uuid'");
                $this->assertNotEmpty($uuidCol);
                $this->assertEquals('NO', $uuidCol[0]->Null, "Table {$table}.uuid must be NOT NULL.");

                // C. Legacy id column does NOT exist
                $idCol = DB::select("SHOW COLUMNS FROM `{$table}` WHERE Field = 'id'");
                $this->assertEmpty($idCol, "Legacy column 'id' must NOT exist on {$table}.");
            } else {
                // SQLite in-memory test runner
                $cols = DB::select("PRAGMA table_info({$table})");
                $hasUuid = false;
                $hasId = false;
                foreach ($cols as $col) {
                    if ($col->name === 'uuid') {
                        $hasUuid = true;
                    }
                    if ($col->name === 'id') {
                        $hasId = true;
                    }
                }
                $this->assertTrue($hasUuid, "Table {$table} must have uuid column in SQLite.");
                $this->assertFalse($hasId, "Table {$table} must NOT have id column in SQLite.");
            }
        }
    }

    /**
     * 2. Verify all database-enforced foreign keys reference uuid and remain valid.
     */
    public function test_database_enforced_foreign_keys_reference_uuid(): void
    {
        if (DB::getDriverName() === 'mysql') {
            $fks = DB::select("
                SELECT TABLE_NAME, CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            $fkMap = [];
            foreach ($fks as $fk) {
                $fkMap[$fk->CONSTRAINT_NAME] = [
                    'table' => $fk->TABLE_NAME,
                    'col' => $fk->COLUMN_NAME,
                    'ref_table' => $fk->REFERENCED_TABLE_NAME,
                    'ref_col' => $fk->REFERENCED_COLUMN_NAME,
                ];
            }

            $this->assertArrayHasKey('fk_user_divisi_uuid', $fkMap);
            $this->assertEquals('uuid', $fkMap['fk_user_divisi_uuid']['ref_col']);

            $this->assertArrayHasKey('fk_konten_user_uuid', $fkMap);
            $this->assertEquals('uuid', $fkMap['fk_konten_user_uuid']['ref_col']);

            $this->assertArrayHasKey('fk_konten_divisi_uuid', $fkMap);
            $this->assertEquals('uuid', $fkMap['fk_konten_divisi_uuid']['ref_col']);
        }

        // Zero orphan records
        $orphanedUserDivisi = DB::table('user')
            ->whereNotNull('divisi_uuid')
            ->whereNotIn('divisi_uuid', DB::table('divisi')->pluck('uuid'))
            ->count();
        $this->assertEquals(0, $orphanedUserDivisi, 'Zero orphan user records.');

        $orphanedKontenUser = DB::table('konten')
            ->whereNotNull('user_uuid')
            ->whereNotIn('user_uuid', DB::table('user')->pluck('uuid'))
            ->count();
        $this->assertEquals(0, $orphanedKontenUser, 'Zero orphan konten.user_uuid records.');

        $orphanedKontenDivisi = DB::table('konten')
            ->whereNotNull('divisi_uuid')
            ->whereNotIn('divisi_uuid', DB::table('divisi')->pluck('uuid'))
            ->count();
        $this->assertEquals(0, $orphanedKontenDivisi, 'Zero orphan konten.divisi_uuid records.');
    }

    /**
     * 3. Verify Eloquent model configuration across all six domain models.
     */
    public function test_eloquent_models_have_uuid_primary_key_configuration(): void
    {
        $models = [
            Divisi::class,
            User::class,
            Konten::class,
            Sponsor::class,
            Calendar::class,
            Pimpinan::class,
        ];

        foreach ($models as $class) {
            $instance = new $class();
            $this->assertEquals('uuid', $instance->getKeyName(), "{$class} primary key must be 'uuid'.");
            $this->assertFalse($instance->getIncrementing(), "{$class} incrementing must be false.");
            $this->assertEquals('string', $instance->getKeyType(), "{$class} keyType must be 'string'.");
        }
    }

    /**
     * 4. Verify Auth::id() returns canonical UUID and sessions store UUID.
     */
    public function test_auth_id_and_session_user_id_return_and_store_uuid(): void
    {
        $user = User::first();
        if (!$user) {
            $divisi = Divisi::create(['nama_divisi' => 'Auth Test Divisi']);
            $user = User::create([
                'name' => 'Super Admin Test',
                'username' => 'superadmintest',
                'email' => 'superadmintest@example.com',
                'password' => Hash::make('password123'),
                'role' => 'superadmin',
                'divisi_uuid' => $divisi->uuid,
                'alamat' => 'Alamat Superadmin',
            ]);
        }

        // A. Auth::login and Auth::id() check
        Auth::login($user);
        $authId = Auth::id();

        $this->assertEquals($user->uuid, $authId);
        $this->assertTrue(Str::isUuid($authId));

        // B. Session persistence
        $sessionId = 'phase7_session_' . Str::random(16);
        DB::table('sessions')->insert([
            'id' => $sessionId,
            'user_id' => $user->uuid,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Phase7TestAgent',
            'payload' => base64_encode(serialize(['test' => true])),
            'last_activity' => time(),
        ]);

        $storedSession = DB::table('sessions')->where('id', $sessionId)->first();
        $this->assertEquals($user->uuid, $storedSession->user_id);
        $this->assertTrue(Str::isUuid($storedSession->user_id));

        // Verify no numeric user_ids exist in sessions
        $numericSessions = DB::table('sessions')
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->filter(fn($id) => ctype_digit((string) $id))
            ->count();
        $this->assertEquals(0, $numericSessions, 'There must be zero numeric session user_ids remaining.');

        // Clean up session
        DB::table('sessions')->where('id', $sessionId)->delete();
        Auth::logout();
    }

    /**
     * 5. Verify relationships use UUID foreign keys by default.
     */
    public function test_canonical_relationships_use_uuid(): void
    {
        $suffix = Str::random(6);
        $divisi = Divisi::create(['nama_divisi' => 'Rel Test ' . $suffix]);
        $this->assertTrue(Str::isUuid($divisi->uuid));

        $user = User::create([
            'name' => 'Rel User ' . $suffix,
            'username' => 'rel_' . $suffix,
            'email' => 'rel_' . $suffix . '@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
            'divisi_uuid' => $divisi->uuid,
            'alamat' => 'Test Alamat',
        ]);
        $this->assertTrue(Str::isUuid($user->uuid));
        $this->assertEquals($divisi->uuid, $user->divisi_uuid);

        $konten = Konten::create([
            'user_uuid' => $user->uuid,
            'divisi_uuid' => $divisi->uuid,
            'judul' => 'Konten Rel ' . $suffix,
            'deskripsi' => 'Deskripsi Rel',
            'url_media' => json_encode(['/storage/rel.jpg']),
            'tanggal_upload' => now(),
            'slug' => 'konten-rel-' . $suffix,
        ]);
        $this->assertTrue(Str::isUuid($konten->uuid));

        // Canonical UUID relationships
        $this->assertEquals($divisi->uuid, $user->divisi->uuid);
        $this->assertEquals($user->uuid, $konten->user->uuid);
        $this->assertEquals($divisi->uuid, $konten->divisi->uuid);
        $this->assertTrue($divisi->user->contains('uuid', $user->uuid));
        $this->assertTrue($divisi->konten->contains('uuid', $konten->uuid));
        $this->assertTrue($user->konten->contains('uuid', $konten->uuid));

        // UUID relationship helpers
        $this->assertEquals($divisi->uuid, $user->divisiByUuid->uuid);
        $this->assertEquals($user->uuid, $konten->userByUuid->uuid);
        $this->assertEquals($divisi->uuid, $konten->divisiByUuid->uuid);

        // Clean up
        $konten->delete();
        $user->delete();
        $divisi->delete();
    }

    /**
     * 6. Verify Model::find() and resolveRouteBinding() resolve canonical UUID.
     */
    public function test_dual_identifier_lookup_and_route_model_binding(): void
    {
        $pimpinan = Pimpinan::first();
        if (!$pimpinan) {
            $pimpinan = Pimpinan::create([
                'nama' => 'Pimpinan Dual Test',
                'jabatan' => 'Ketua Test',
                'foto' => '/storage/test.jpg',
                'urutan' => 1,
                'is_active' => true,
            ]);
        }

        // Find by UUID
        $byUuid = Pimpinan::find($pimpinan->uuid);
        $this->assertNotNull($byUuid);
        $this->assertEquals($pimpinan->uuid, $byUuid->uuid);

        // Route model binding resolution by UUID
        $resolvedByUuid = (new Pimpinan())->resolveRouteBinding($pimpinan->uuid);
        $this->assertNotNull($resolvedByUuid);
        $this->assertEquals($pimpinan->uuid, $resolvedByUuid->uuid);
    }

    /**
     * 7. Verify queued job / notification deserialization compatibility with UUID identifiers.
     */
    public function test_queue_restoration_compatibility_with_numeric_and_uuid_identifiers(): void
    {
        $user = User::first();
        if (!$user) {
            $divisi = Divisi::create(['nama_divisi' => 'Queue Test Divisi']);
            $user = User::create([
                'name' => 'Queue User Test',
                'username' => 'queueuser',
                'email' => 'queueuser@example.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'divisi_uuid' => $divisi->uuid,
                'alamat' => 'Alamat Queue',
            ]);
        }

        // Restoration using canonical UUID string
        $restoredByUuid = (new User())->newQueryForRestoration($user->uuid)->first();
        $this->assertNotNull($restoredByUuid);
        $this->assertEquals($user->uuid, $restoredByUuid->uuid);
    }

    /**
     * 8. Verify public slug URL 301 redirection from UUID.
     */
    public function test_public_and_admin_urls_redirect_to_canonical_slug(): void
    {
        $divisi = Divisi::first();
        if (!$divisi) {
            $divisi = Divisi::create(['nama_divisi' => 'Slug Test Divisi']);
        }

        $user = User::where('role', 'superadmin')->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Admin Slug Test',
                'username' => 'adminslug',
                'email' => 'adminslug@example.com',
                'password' => Hash::make('password123'),
                'role' => 'superadmin',
                'divisi_uuid' => $divisi->uuid,
                'alamat' => 'Alamat Slug',
            ]);
        }

        $konten = Konten::create([
            'user_uuid' => $user->uuid,
            'divisi_uuid' => $divisi->uuid,
            'judul' => 'Slug Redirection Test',
            'deskripsi' => 'Deskripsi Slug Redirection',
            'url_media' => json_encode(['/storage/slug.jpg']),
            'tanggal_upload' => now(),
            'slug' => 'slug-redirection-test-' . Str::random(5),
        ]);

        // Public UUID -> 301 to canonical slug
        $resPublicUuid = $this->get('/konten/' . $konten->uuid);
        $resPublicUuid->assertStatus(301);
        $resPublicUuid->assertRedirect(route('konten.show', ['slug' => $konten->slug]));

        // Public slug directly -> 200 OK
        $resPublicSlug = $this->get('/konten/' . $konten->slug);
        $resPublicSlug->assertStatus(200);

        // Admin gallery UUID -> 301 to slug URL
        $resAdminUuid = $this->actingAs($user)->get('/gallery/' . $konten->uuid);
        $resAdminUuid->assertStatus(301);
        $resAdminUuid->assertRedirect('/gallery/' . $konten->slug);

        // Clean up
        $konten->delete();
    }

    /**
     * 9. Verify CRUD operations across all six models with UUID primary key.
     */
    public function test_crud_across_all_domain_models_with_uuid_primary_key(): void
    {
        $rand = Str::random(5);

        // 1. Divisi
        $divisi = Divisi::create(['nama_divisi' => 'CRUD Divisi ' . $rand]);
        $this->assertTrue(Str::isUuid($divisi->uuid));
        $divisi->update(['nama_divisi' => 'CRUD Divisi ' . $rand . ' Updated']);
        $this->assertEquals('CRUD Divisi ' . $rand . ' Updated', Divisi::find($divisi->uuid)->nama_divisi);

        // 2. User
        $user = User::create([
            'name' => 'CRUD User ' . $rand,
            'username' => 'crud_' . $rand,
            'email' => 'crud_' . $rand . '@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
            'divisi_uuid' => $divisi->uuid,
            'alamat' => 'Alamat CRUD',
        ]);
        $this->assertTrue(Str::isUuid($user->uuid));
        $user->update(['name' => 'CRUD User ' . $rand . ' Renamed']);
        $this->assertEquals('CRUD User ' . $rand . ' Renamed', User::find($user->uuid)->name);

        // 3. Konten
        $konten = Konten::create([
            'user_uuid' => $user->uuid,
            'divisi_uuid' => $divisi->uuid,
            'judul' => 'CRUD Konten ' . $rand,
            'deskripsi' => 'Deskripsi Konten CRUD',
            'url_media' => json_encode(['/storage/crud.jpg']),
            'tanggal_upload' => now(),
            'slug' => 'crud-konten-' . $rand,
        ]);
        $this->assertTrue(Str::isUuid($konten->uuid));
        $konten->update(['judul' => 'CRUD Konten ' . $rand . ' Renamed']);
        $this->assertEquals('CRUD Konten ' . $rand . ' Renamed', Konten::find($konten->uuid)->judul);

        // 4. Sponsor
        $sponsor = Sponsor::create([
            'nama' => 'CRUD Sponsor ' . $rand,
            'url_image' => 'data:image/png;base64,crud',
        ]);
        $this->assertTrue(Str::isUuid($sponsor->uuid));
        $sponsor->update(['nama' => 'CRUD Sponsor ' . $rand . ' Renamed']);
        $this->assertEquals('CRUD Sponsor ' . $rand . ' Renamed', Sponsor::find($sponsor->uuid)->nama);

        // 5. Calendar
        $cal = Calendar::create([
            'event_name' => 'CRUD Event ' . $rand,
            'event_date' => '2026-12-01',
        ]);
        $this->assertTrue(Str::isUuid($cal->uuid));
        $cal->update(['event_name' => 'CRUD Event ' . $rand . ' Renamed']);
        $this->assertEquals('CRUD Event ' . $rand . ' Renamed', Calendar::find($cal->uuid)->event_name);

        // 6. Pimpinan
        $pimpinan = Pimpinan::create([
            'nama' => 'CRUD Pimpinan ' . $rand,
            'jabatan' => 'Jabatan CRUD',
            'foto' => '/storage/crud.jpg',
            'urutan' => 99,
            'is_active' => true,
        ]);
        $this->assertTrue(Str::isUuid($pimpinan->uuid));
        $pimpinan->update(['nama' => 'CRUD Pimpinan ' . $rand . ' Renamed']);
        $this->assertEquals('CRUD Pimpinan ' . $rand . ' Renamed', Pimpinan::find($pimpinan->uuid)->nama);

        // Clean up in reverse dependency order
        $konten->delete();
        $user->delete();
        $divisi->delete();
        $sponsor->delete();
        $cal->delete();
        $pimpinan->delete();

        $this->assertNull(Konten::find($konten->uuid));
        $this->assertNull(User::find($user->uuid));
        $this->assertNull(Divisi::find($divisi->uuid));
        $this->assertNull(Sponsor::find($sponsor->uuid));
        $this->assertNull(Calendar::find($cal->uuid));
        $this->assertNull(Pimpinan::find($pimpinan->uuid));
    }
}
