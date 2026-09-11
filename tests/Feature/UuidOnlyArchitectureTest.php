<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\Divisi;
use App\Models\Konten;
use App\Models\Pimpinan;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class TestQueueJob
{
    use SerializesModels;

    public function __construct(public User $user, public Konten $konten)
    {
    }
}

class UuidOnlyArchitectureTest extends TestCase
{
    use RefreshDatabase;

    protected Divisi $divisi;
    protected Divisi $otherDivisi;
    protected User $superAdmin;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->divisi = Divisi::create([
            'nama_divisi' => 'Teknologi Informasi',
        ]);

        $this->otherDivisi = Divisi::create([
            'nama_divisi' => 'Kurikulum',
        ]);

        $this->superAdmin = User::create([
            'divisi_uuid' => $this->divisi->uuid,
            'name' => 'Super Administrator',
            'username' => 'superadmin_arch',
            'email' => 'superadmin_arch@mkks.id',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
            'alamat' => 'Bekasi',
        ]);

        $this->admin = User::create([
            'divisi_uuid' => $this->divisi->uuid,
            'name' => 'Admin Divisi TI',
            'username' => 'adminti_arch',
            'email' => 'adminti_arch@mkks.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'alamat' => 'Bekasi',
        ]);
    }

    /**
     * Requirement 1-5: Schema verification: uuid is PK, legacy id and legacy FKs do NOT exist.
     */
    public function test_schema_has_uuid_and_no_legacy_columns(): void
    {
        $tables = ['divisi', 'user', 'konten', 'sponsor', 'calendar', 'pimpinan'];

        foreach ($tables as $table) {
            $columns = Schema::getColumnListing($table);

            $this->assertContains('uuid', $columns, "Table {$table} must contain uuid column.");
            $this->assertNotContains('id', $columns, "Table {$table} must NOT contain legacy id column.");
        }

        // user table specific assertions
        $userCols = Schema::getColumnListing('user');
        $this->assertContains('divisi_uuid', $userCols, 'User must contain divisi_uuid');
        $this->assertNotContains('id_divisi', $userCols, 'User must NOT contain id_divisi');

        // konten table specific assertions
        $kontenCols = Schema::getColumnListing('konten');
        $this->assertContains('user_uuid', $kontenCols, 'Konten must contain user_uuid');
        $this->assertContains('divisi_uuid', $kontenCols, 'Konten must contain divisi_uuid');
        $this->assertNotContains('id_user', $kontenCols, 'Konten must NOT contain id_user');
        $this->assertNotContains('id_divisi', $kontenCols, 'Konten must NOT contain id_divisi');
    }

    /**
     * Requirement 6-8: UUID columns are NOT NULL, unique, and valid canonical v4.
     */
    public function test_uuid_columns_are_canonical_v4_unique_and_not_null(): void
    {
        $v4Regex = '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-4[0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/';

        $pimpinan = Pimpinan::create([
            'nama' => 'Drs. Pimpinan Arch, M.Pd.',
            'jabatan' => 'Ketua MKKS',
            'foto' => '/storage/pimpinan/sample.jpg',
            'urutan' => 1,
            'is_active' => true,
        ]);

        $sponsor = Sponsor::create([
            'nama' => 'Industri Mitra Arch',
            'url_image' => 'data:image/png;base64,sample',
        ]);

        $calendar = Calendar::create([
            'event_name' => 'Rapat Pleno Arch',
            'event_date' => '2026-10-10',
        ]);

        $konten = Konten::create([
            'user_uuid' => $this->admin->uuid,
            'divisi_uuid' => $this->divisi->uuid,
            'judul' => 'Kegiatan Divisi TI',
            'deskripsi' => 'Deskripsi kegiatan TI',
            'url_media' => json_encode(['/storage/media/test.jpg']),
            'slug' => 'kegiatan-divisi-ti-arch',
            'tanggal_upload' => '2026-09-11',
        ]);

        $models = [$this->divisi, $this->superAdmin, $this->admin, $pimpinan, $sponsor, $calendar, $konten];

        $uuids = [];
        foreach ($models as $model) {
            $this->assertNotNull($model->uuid);
            $this->assertNotEmpty($model->uuid);
            $this->assertMatchesRegularExpression($v4Regex, $model->uuid);
            $this->assertNotContains($model->uuid, $uuids);
            $uuids[] = $model->uuid;
        }
    }

    /**
     * Requirement 9-10: Foreign keys reference UUID columns only.
     */
    public function test_foreign_keys_reference_uuid_columns_only(): void
    {
        $this->assertEquals($this->divisi->uuid, $this->superAdmin->divisi_uuid);
        $this->assertEquals($this->divisi->nama_divisi, $this->superAdmin->divisi->nama_divisi);

        $konten = Konten::create([
            'user_uuid' => $this->admin->uuid,
            'divisi_uuid' => $this->divisi->uuid,
            'judul' => 'Konten Hubungan Relasional',
            'deskripsi' => 'Deskripsi relasional',
            'url_media' => json_encode(['/storage/media/rel.jpg']),
            'slug' => 'konten-hubungan-relasional',
            'tanggal_upload' => '2026-09-11',
        ]);

        $this->assertEquals($this->admin->uuid, $konten->user->uuid);
        $this->assertEquals($this->divisi->uuid, $konten->divisi->uuid);
    }

    /**
     * Requirement 11: Eloquent find(UUID) works.
     */
    public function test_eloquent_find_by_uuid_works(): void
    {
        $foundUser = User::find($this->superAdmin->uuid);
        $this->assertNotNull($foundUser);
        $this->assertEquals($this->superAdmin->uuid, $foundUser->uuid);

        $foundDivisi = Divisi::find($this->divisi->uuid);
        $this->assertNotNull($foundDivisi);
        $this->assertEquals($this->divisi->uuid, $foundDivisi->uuid);
    }

    /**
     * Requirement 12: Route model binding works with UUID.
     */
    public function test_route_model_binding_works_with_uuid(): void
    {
        $this->actingAs($this->superAdmin);

        $pimpinan = Pimpinan::create([
            'nama' => 'H. Pimpinan Model Binding',
            'jabatan' => 'Bendahara',
            'foto' => '/storage/pimpinan/sample.jpg',
            'urutan' => 3,
            'is_active' => true,
        ]);

        $response = $this->get('/pimpinan/' . $pimpinan->uuid);
        $response->assertStatus(200);
        $response->assertSee('H. Pimpinan Model Binding');
    }

    /**
     * Requirement 13-14: Auth::id() returns UUID and session persistence works.
     */
    public function test_auth_id_returns_uuid_and_session_persists(): void
    {
        $this->actingAs($this->superAdmin);

        $this->assertEquals($this->superAdmin->uuid, Auth::id());
        $this->assertEquals($this->superAdmin->uuid, Auth::user()->uuid);

        // Verify session persists in subsequent requests
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
    }

    /**
     * Requirement 15: Queue serialization and restoration with UUID primary key works.
     */
    public function test_queue_serialization_and_restoration(): void
    {
        $konten = Konten::create([
            'user_uuid' => $this->superAdmin->uuid,
            'divisi_uuid' => $this->divisi->uuid,
            'judul' => 'Konten Queue Serialization',
            'deskripsi' => 'Deskripsi queue',
            'url_media' => json_encode(['/storage/media/q.jpg']),
            'slug' => 'konten-queue-serialization',
            'tanggal_upload' => '2026-09-11',
        ]);

        $job = new TestQueueJob($this->superAdmin, $konten);
        $serialized = serialize($job);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(TestQueueJob::class, $unserialized);
        $this->assertEquals($this->superAdmin->uuid, $unserialized->user->uuid);
        $this->assertEquals($konten->uuid, $unserialized->konten->uuid);
    }

    /**
     * Requirement 16: Password reset works with UUID user.
     */
    public function test_password_reset_token_creation_works(): void
    {
        $token = Password::createToken($this->superAdmin);
        $this->assertNotEmpty($token);
        $this->assertTrue(Password::tokenExists($this->superAdmin, $token));
    }

    /**
     * Requirement 17: CRUD works for all six models.
     */
    public function test_crud_works_for_all_six_models(): void
    {
        // 1. Divisi
        $divisi = Divisi::create(['nama_divisi' => 'Divisi Riset']);
        $this->assertDatabaseHas('divisi', ['uuid' => $divisi->uuid, 'nama_divisi' => 'Divisi Riset']);
        $divisi->update(['nama_divisi' => 'Divisi Riset & Teknologi']);
        $this->assertEquals('Divisi Riset & Teknologi', $divisi->fresh()->nama_divisi);
        $divisiUuid = $divisi->uuid;
        $divisi->delete();
        $this->assertDatabaseMissing('divisi', ['uuid' => $divisiUuid]);

        // 2. User
        $user = User::create([
            'divisi_uuid' => $this->divisi->uuid,
            'name' => 'Crud User',
            'username' => 'cruduser',
            'email' => 'cruduser@mkks.id',
            'password' => Hash::make('secret'),
            'role' => 'admin',
            'alamat' => 'Bekasi',
        ]);
        $this->assertDatabaseHas('user', ['uuid' => $user->uuid, 'username' => 'cruduser']);
        $user->update(['name' => 'Crud User Updated']);
        $this->assertEquals('Crud User Updated', $user->fresh()->name);
        $userUuid = $user->uuid;
        $user->delete();
        $this->assertDatabaseMissing('user', ['uuid' => $userUuid]);

        // 3. Pimpinan
        $pimpinan = Pimpinan::create([
            'nama' => 'Pimpinan Crud',
            'jabatan' => 'Anggota',
            'foto' => '/storage/pimpinan/sample.jpg',
            'urutan' => 4,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('pimpinan', ['uuid' => $pimpinan->uuid]);
        $pimpinan->update(['nama' => 'Pimpinan Crud Renamed']);
        $this->assertEquals('Pimpinan Crud Renamed', $pimpinan->fresh()->nama);
        $pimpinanUuid = $pimpinan->uuid;
        $pimpinan->delete();
        $this->assertDatabaseMissing('pimpinan', ['uuid' => $pimpinanUuid]);

        // 4. Sponsor
        $sponsor = Sponsor::create([
            'nama' => 'Sponsor Crud',
            'url_image' => 'data:image/png;base64,sample',
        ]);
        $this->assertDatabaseHas('sponsor', ['uuid' => $sponsor->uuid]);
        $sponsor->update(['nama' => 'Sponsor Crud Updated']);
        $this->assertEquals('Sponsor Crud Updated', $sponsor->fresh()->nama);
        $sponsorUuid = $sponsor->uuid;
        $sponsor->delete();
        $this->assertDatabaseMissing('sponsor', ['uuid' => $sponsorUuid]);

        // 5. Calendar
        $cal = Calendar::create([
            'event_name' => 'Event Crud',
            'event_date' => '2026-12-01',
        ]);
        $this->assertDatabaseHas('calendar', ['uuid' => $cal->uuid]);
        $cal->update(['event_name' => 'Event Crud Updated']);
        $this->assertEquals('Event Crud Updated', $cal->fresh()->event_name);
        $calUuid = $cal->uuid;
        $cal->delete();
        $this->assertDatabaseMissing('calendar', ['uuid' => $calUuid]);

        // 6. Konten
        $konten = Konten::create([
            'user_uuid' => $this->superAdmin->uuid,
            'divisi_uuid' => $this->divisi->uuid,
            'judul' => 'Konten Crud',
            'deskripsi' => 'Deskripsi konten crud',
            'url_media' => json_encode(['/storage/media/crud.jpg']),
            'slug' => 'konten-crud-test',
            'tanggal_upload' => '2026-09-11',
        ]);
        $this->assertDatabaseHas('konten', ['uuid' => $konten->uuid]);
        $konten->update(['judul' => 'Konten Crud Updated']);
        $this->assertEquals('Konten Crud Updated', $konten->fresh()->judul);
        $kontenUuid = $konten->uuid;
        $konten->delete();
        $this->assertDatabaseMissing('konten', ['uuid' => $kontenUuid]);
    }

    /**
     * Requirement 18: Public canonical slug and UUID redirect.
     */
    public function test_public_canonical_slug_and_uuid_redirect(): void
    {
        $konten = Konten::create([
            'user_uuid' => $this->superAdmin->uuid,
            'divisi_uuid' => $this->divisi->uuid,
            'judul' => 'Konten Publik Canonical Test',
            'deskripsi' => 'Deskripsi canonical',
            'url_media' => json_encode(['/storage/media/canon.jpg']),
            'slug' => 'konten-publik-canonical-test',
            'tanggal_upload' => '2026-09-11',
        ]);

        // Canonical slug returns 200 OK
        $slugResponse = $this->get('/konten/' . $konten->slug);
        $slugResponse->assertStatus(200);
        $slugResponse->assertSee('Konten Publik Canonical Test');

        // UUID redirects 301 to slug
        $uuidResponse = $this->get('/konten/' . $konten->uuid);
        $uuidResponse->assertStatus(301);
        $uuidResponse->assertRedirect(route('konten.show', ['slug' => $konten->slug]));
    }

    /**
     * Requirement 19-20: Admin authorization and division scoping.
     */
    public function test_admin_authorization_and_division_scoping(): void
    {
        // Konten owned by otherDivisi
        $otherKonten = Konten::create([
            'user_uuid' => $this->superAdmin->uuid,
            'divisi_uuid' => $this->otherDivisi->uuid,
            'judul' => 'Konten Milik Kurikulum',
            'deskripsi' => 'Deskripsi kurikulum',
            'url_media' => json_encode(['/storage/media/kuri.jpg']),
            'slug' => 'konten-milik-kurikulum',
            'tanggal_upload' => '2026-09-11',
        ]);

        // Acting as admin of 'Teknologi Informasi' (divisi_uuid !== otherDivisi->uuid)
        $this->actingAs($this->admin);

        // Edit route should be forbidden (403) due to division boundary
        $response = $this->get('/galeri-kelola/edit/' . $otherKonten->uuid);
        $response->assertStatus(403);
    }
}
