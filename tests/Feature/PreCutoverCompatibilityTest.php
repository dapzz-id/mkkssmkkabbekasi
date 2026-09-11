<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Konten;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class PreCutoverCompatibilityTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Test sessions.user_id can store a 36-character UUID string without error.
     */
    public function test_sessions_table_can_store_uuid_in_user_id(): void
    {
        $dummySessionId = Str::random(40);
        $testUuid = (string) Str::uuid();

        DB::table('sessions')->insert([
            'id' => $dummySessionId,
            'user_id' => $testUuid,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit/CompatibilityTest',
            'payload' => base64_encode('test-payload'),
            'last_activity' => time(),
        ]);

        $inserted = DB::table('sessions')->where('id', $dummySessionId)->first();
        $this->assertNotNull($inserted);
        $this->assertEquals($testUuid, $inserted->user_id);

        // Clean up
        DB::table('sessions')->where('id', $dummySessionId)->delete();
    }

    /**
     * Test sessions table preserves existing numeric and null user_id values.
     */
    public function test_sessions_table_handles_numeric_and_null_user_ids(): void
    {
        $dummySessionId1 = Str::random(40);
        $dummySessionId2 = Str::random(40);

        DB::table('sessions')->insert([
            'id' => $dummySessionId1,
            'user_id' => '1',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit/CompatibilityTest',
            'payload' => base64_encode('numeric-session'),
            'last_activity' => time(),
        ]);

        DB::table('sessions')->insert([
            'id' => $dummySessionId2,
            'user_id' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit/CompatibilityTest',
            'payload' => base64_encode('guest-session'),
            'last_activity' => time(),
        ]);

        $row1 = DB::table('sessions')->where('id', $dummySessionId1)->first();
        $this->assertNotNull($row1);
        $this->assertEquals('1', $row1->user_id);

        $row2 = DB::table('sessions')->where('id', $dummySessionId2)->first();
        $this->assertNotNull($row2);
        $this->assertNull($row2->user_id);

        // Clean up
        DB::table('sessions')->whereIn('id', [$dummySessionId1, $dummySessionId2])->delete();
    }

    /**
     * Test full authentication lifecycle (login, auth request, session persist, logout).
     */
    public function test_authentication_session_lifecycle_with_updated_sessions_table(): void
    {
        $uniqueSuffix = Str::random(6);
        $divisi = Divisi::first() ?? Divisi::create(['nama_divisi' => 'Divisi Test ' . $uniqueSuffix]);

        $user = User::create([
            'name' => 'Session Test User',
            'username' => 'sessiontest_' . $uniqueSuffix,
            'email' => 'sessiontest_' . $uniqueSuffix . '@example.com',
            'password' => Hash::make('secretpassword123'),
            'role' => 'superadmin',
            'id_divisi' => $divisi->id,
            'alamat' => 'Alamat Test',
        ]);

        \Illuminate\Support\Facades\Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => \Illuminate\Support\Facades\Http::response([
                'success' => true,
                'hostname' => 'mkkssmkbekasi.or.id',
                'action' => 'login',
                'challenge_ts' => now()->toIso8601String(),
            ], 200),
        ]);

        // Login POST
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'secretpassword123',
            'cf-turnstile-response' => 'fake-test-token',
        ]);

        // Should redirect to dashboard or intended
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        // Authenticated request
        $dashResponse = $this->actingAs($user)->get('/dashboard');
        $dashResponse->assertStatus(200);

        // Logout
        $logoutResponse = $this->actingAs($user)->post('/logout');
        $logoutResponse->assertRedirect('/login');
        $this->assertGuest();

        // Clean up user
        $user->delete();
    }

    /**
     * Test bi-directional synchronization in HasDualIdentifier.
     */
    public function test_bidirectional_transitional_fk_sync_in_has_dual_identifier(): void
    {
        $uniqueSuffix = Str::random(6);
        $divisi = Divisi::create(['nama_divisi' => 'Sync Test ' . $uniqueSuffix]);
        $this->assertNotEmpty($divisi->uuid);

        // 1. Create User specifying divisi_uuid directly instead of id_divisi
        $user = new User([
            'name' => 'Sync User Test',
            'username' => 'sync_' . $uniqueSuffix,
            'email' => 'sync_' . $uniqueSuffix . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'divisi_uuid' => $divisi->uuid,
            'alamat' => 'Alamat Sync',
        ]);
        $user->save();

        // id_divisi should be automatically synchronized to $divisi->id
        $this->assertEquals($divisi->id, $user->id_divisi);
        $this->assertEquals($divisi->uuid, $user->divisi_uuid);

        // 2. Create Konten specifying user_uuid and divisi_uuid directly
        $konten = new Konten([
            'user_uuid' => $user->uuid,
            'divisi_uuid' => $divisi->uuid,
            'judul' => 'Konten Sync Test ' . $uniqueSuffix,
            'deskripsi' => 'Deskripsi Sync Test',
            'url_media' => json_encode(['/storage/test.jpg']),
            'tanggal_upload' => now(),
            'slug' => 'konten-sync-test-' . $uniqueSuffix,
        ]);
        $konten->save();

        $this->assertEquals($user->id, $konten->id_user);
        $this->assertEquals($divisi->id, $konten->id_divisi);

        // 3. Test explicit UUID relationship helpers
        $this->assertEquals($divisi->id, $user->divisiByUuid->id);
        $this->assertEquals($divisi->id, $konten->divisiByUuid->id);
        $this->assertEquals($user->id, $konten->userByUuid->id);

        // Clean up
        $konten->delete();
        $user->delete();
        $divisi->delete();
    }

    /**
     * Test legacy id secondary indexes exist to satisfy MySQL AUTO_INCREMENT rule.
     */
    public function test_standalone_legacy_id_indexes_exist(): void
    {
        $isMySql = DB::getDriverName() === 'mysql';
        $tables = ['divisi', 'user', 'konten', 'sponsor', 'pimpinan'];
        foreach ($tables as $table) {
            $indexName = "idx_{$table}_legacy_id";
            if ($isMySql) {
                $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            } else {
                $indexes = DB::select("SELECT name FROM sqlite_master WHERE type = 'index' AND tbl_name = ? AND name = ?", [$table, $indexName]);
            }
            $this->assertNotEmpty($indexes, "Expected index {$indexName} to exist on table {$table}");
        }

        // Calendar has calendar_id_unique covering id
        if ($isMySql) {
            $calIndexes = DB::select("SHOW INDEX FROM `calendar` WHERE Key_name = 'calendar_id_unique'");
        } else {
            $calIndexes = DB::select("SELECT name FROM sqlite_master WHERE type = 'index' AND tbl_name = 'calendar' AND name LIKE '%calendar_id_unique%'");
        }
        $this->assertNotEmpty($calIndexes, "Expected calendar_id_unique on calendar table");
    }
}
