<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Konten;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class DatabaseForeignKeyTest extends TestCase
{
    use RefreshDatabase;

    protected Divisi $divisi;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->divisi = Divisi::create(['nama_divisi' => 'Teknologi Informasi']);
        $this->user = User::create([
            'divisi_uuid' => $this->divisi->uuid,
            'name' => 'FK Test User',
            'username' => 'fk_test_user',
            'email' => 'fk_test_user@mkks.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'alamat' => 'Bekasi',
        ]);
    }

    /**
     * Test 1: Valid parent + child insert succeeds with UUID foreign keys.
     */
    public function test_valid_parent_and_child_insert_succeeds(): void
    {
        $konten = Konten::create([
            'user_uuid' => $this->user->uuid,
            'divisi_uuid' => $this->divisi->uuid,
            'judul' => 'Valid FK Konten',
            'deskripsi' => 'Deskripsi konten dengan FK valid',
            'url_media' => json_encode(['/storage/media/valid.jpg']),
            'slug' => 'valid-fk-konten',
            'tanggal_upload' => '2026-09-10 10:00:00',
        ]);

        $this->assertNotNull($konten->uuid);
        $this->assertEquals($this->user->uuid, $konten->user_uuid);
        $this->assertEquals($this->divisi->uuid, $konten->divisi_uuid);
    }

    /**
     * Test 2: Inserting child with non-existent parent UUID triggers foreign key constraint violation.
     */
    public function test_invalid_parent_uuid_is_rejected_by_foreign_key_constraint(): void
    {
        // Only run constraint rejection test if driver enforces foreign keys (MySQL / SQLite with pragma)
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        }

        $fakeUuid = (string) Str::uuid();

        $this->expectException(QueryException::class);

        DB::table('user')->insert([
            'uuid' => (string) Str::uuid(),
            'divisi_uuid' => $fakeUuid, // Non-existent parent UUID
            'name' => 'Invalid FK User',
            'username' => 'invalid_fk_user',
            'email' => 'invalid_fk@mkks.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);
    }

    /**
     * Test 3: Valid relationship update succeeds.
     */
    public function test_valid_relationship_update_succeeds(): void
    {
        $newDivisi = Divisi::create(['nama_divisi' => 'Humas']);

        $this->user->divisi_uuid = $newDivisi->uuid;
        $this->user->save();

        $this->assertEquals($newDivisi->uuid, $this->user->fresh()->divisi_uuid);
    }

    /**
     * Test 4: Updating child with invalid parent UUID triggers foreign key constraint violation.
     */
    public function test_invalid_relationship_update_is_rejected(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        }

        $this->expectException(QueryException::class);

        DB::table('user')
            ->where('uuid', $this->user->uuid)
            ->update(['divisi_uuid' => (string) Str::uuid()]);
    }

    /**
     * Test 5: Deleting parent cascades to children when configured with ON DELETE CASCADE.
     */
    public function test_delete_parent_cascades_to_child(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        }

        $konten = Konten::create([
            'user_uuid' => $this->user->uuid,
            'divisi_uuid' => $this->divisi->uuid,
            'judul' => 'Cascade Test Konten',
            'deskripsi' => 'Konten untuk testing cascade',
            'url_media' => json_encode(['/storage/media/cascade.jpg']),
            'slug' => 'cascade-test-konten',
            'tanggal_upload' => '2026-09-10 10:00:00',
        ]);

        $userUuid = $this->user->uuid;
        $kontenUuid = $konten->uuid;

        // Delete parent Divisi
        $this->divisi->delete();

        // Verifying cascade occurred
        $this->assertNull(User::find($userUuid));
        $this->assertNull(Konten::find($kontenUuid));
    }
}
