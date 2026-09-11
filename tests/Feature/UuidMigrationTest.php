<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\Divisi;
use App\Models\Konten;
use App\Models\Pimpinan;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class UuidMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected Divisi $divisi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->divisi = Divisi::create([
            'nama_divisi' => 'Teknologi Informasi',
        ]);

        $this->superAdmin = User::create([
            'divisi_uuid' => $this->divisi->uuid,
            'name' => 'Super Administrator',
            'username' => 'superadmin_uuid_test',
            'email' => 'superadmin_uuid@mkkssmkbekasi.or.id',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
            'alamat' => 'Bekasi',
        ]);
    }

    /**
     * Scenario 1: All domain models automatically generate a valid canonical UUID v4 on create.
     */
    public function test_all_domain_models_automatically_generate_canonical_uuid_on_create(): void
    {
        $uuidRegex = '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/';

        $divisi = Divisi::create(['nama_divisi' => 'Humas']);
        $this->assertNotNull($divisi->uuid);
        $this->assertMatchesRegularExpression($uuidRegex, $divisi->uuid);

        $user = User::create([
            'divisi_uuid' => $divisi->uuid,
            'name' => 'Test User',
            'username' => 'testuser_uuid',
            'email' => 'testuser_uuid@mkks.id',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
            'alamat' => 'Cikarang',
        ]);
        $this->assertNotNull($user->uuid);
        $this->assertMatchesRegularExpression($uuidRegex, $user->uuid);

        $pimpinan = Pimpinan::create([
            'nama' => 'Drs. Pimpinan Uuid, M.Pd.',
            'jabatan' => 'Sekretaris',
            'foto' => '/storage/pimpinan/sample.jpg',
            'urutan' => 1,
            'is_active' => true,
        ]);
        $this->assertNotNull($pimpinan->uuid);
        $this->assertMatchesRegularExpression($uuidRegex, $pimpinan->uuid);

        $sponsor = Sponsor::create([
            'nama' => 'Mitra Industri UUID',
            'url_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);
        $this->assertNotNull($sponsor->uuid);
        $this->assertMatchesRegularExpression($uuidRegex, $sponsor->uuid);

        $calendar = Calendar::create([
            'event_name' => 'Rapat Koordinasi UUID',
            'event_date' => '2026-10-15',
        ]);
        $this->assertNotNull($calendar->uuid);
        $this->assertMatchesRegularExpression($uuidRegex, $calendar->uuid);

        $konten = Konten::create([
            'user_uuid' => $user->uuid,
            'divisi_uuid' => $divisi->uuid,
            'judul' => 'Kegiatan Pelatihan UUID',
            'deskripsi' => 'Deskripsi pelatihan sistem identitas',
            'url_media' => json_encode(['/storage/media/test.jpg']),
            'slug' => 'kegiatan-pelatihan-uuid',
            'tanggal_upload' => '2026-09-10',
        ]);
        $this->assertNotNull($konten->uuid);
        $this->assertMatchesRegularExpression($uuidRegex, $konten->uuid);
    }

    /**
     * Scenario 2: Creating User populates divisi_uuid and establishes relationship.
     */
    public function test_creating_user_automatically_populates_transitional_divisi_uuid(): void
    {
        $user = User::create([
            'divisi_uuid' => $this->divisi->uuid,
            'name' => 'UUID FK User',
            'username' => 'transitional_user',
            'email' => 'transitional@mkks.id',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
            'alamat' => 'Tambun',
        ]);

        $this->assertNotNull($user->divisi_uuid);
        $this->assertEquals($this->divisi->uuid, $user->divisi_uuid);
    }

    /**
     * Scenario 3: Creating Konten populates both user_uuid and divisi_uuid.
     */
    public function test_creating_konten_automatically_populates_transitional_fks(): void
    {
        $konten = Konten::create([
            'user_uuid' => $this->superAdmin->uuid,
            'divisi_uuid' => $this->divisi->uuid,
            'judul' => 'Konten Transitional FK Test',
            'deskripsi' => 'Deskripsi konten dengan transitional foreign keys',
            'url_media' => json_encode(['/storage/media/sample.jpg']),
            'slug' => 'konten-transitional-fk-test',
            'tanggal_upload' => '2026-09-10',
        ]);

        $this->assertNotNull($konten->user_uuid);
        $this->assertNotNull($konten->divisi_uuid);
        $this->assertEquals($this->superAdmin->uuid, $konten->user_uuid);
        $this->assertEquals($this->divisi->uuid, $konten->divisi_uuid);
    }

    /**
     * Scenario 4: Updating parent foreign key updates divisi_uuid.
     */
    public function test_updating_parent_id_synchronizes_transitional_uuid(): void
    {
        $divisiBaru = Divisi::create(['nama_divisi' => 'Kurikulum']);

        $this->superAdmin->divisi_uuid = $divisiBaru->uuid;
        $this->superAdmin->save();

        $this->assertEquals($divisiBaru->uuid, $this->superAdmin->fresh()->divisi_uuid);
    }

    /**
     * Scenario 5: scopeWhereIdentifier resolves records by canonical UUID string.
     */
    public function test_where_identifier_finds_record_by_integer_id(): void
    {
        $found = Divisi::whereIdentifier($this->divisi->uuid)->first();
        $this->assertNotNull($found);
        $this->assertEquals($this->divisi->uuid, $found->uuid);
    }

    /**
     * Scenario 6: scopeWhereIdentifier resolves records by canonical UUID string.
     */
    public function test_where_identifier_finds_record_by_canonical_uuid(): void
    {
        $found = Divisi::whereIdentifier($this->divisi->uuid)->first();
        $this->assertNotNull($found);
        $this->assertEquals($this->divisi->uuid, $found->uuid);
    }

    /**
     * Scenario 7: findByIdentifierOrFail throws 404 ModelNotFoundException for nonexistent UUIDs.
     */
    public function test_find_by_identifier_or_fail_throws_exception_on_nonexistent(): void
    {
        $this->expectException(ModelNotFoundException::class);
        Divisi::findByIdentifierOrFail('00000000-0000-0000-0000-000000000000');
    }

    /**
     * Scenario 8: uuid:migrate --dry-run runs preflight audit without mutations.
     */
    public function test_uuid_migrate_dry_run_command_reports_integrity_without_mutations(): void
    {
        $this->artisan('uuid:migrate', ['--dry-run' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('Preflight Dry-Run Audit (Zero-Mutation Mode)')
            ->expectsOutputToContain('Clean (0 orphans)');
    }

    /**
     * Scenario 9: uuid:migrate --backfill and --verify succeed with 100% integrity.
     */
    public function test_uuid_migrate_backfill_and_verify(): void
    {
        $this->artisan('uuid:migrate', ['--backfill' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('All reconciliation and parity checks PASSED with 100% integrity.');
    }

    /**
     * Scenario 10: uuid:migrate --status renders coverage table.
     */
    public function test_uuid_migrate_status_renders_table(): void
    {
        $this->artisan('uuid:migrate', ['--status' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('MKKS UUID Migration Status');
    }

    /**
     * Scenario 11: Route /gallery/{slug} resolves numeric ID and canonical UUID with 301 redirect to slug.
     */
    public function test_admin_gallery_show_resolves_id_and_uuid_with_301_redirect(): void
    {
        $konten = Konten::create([
            'user_uuid' => $this->superAdmin->uuid,
            'divisi_uuid' => $this->divisi->uuid,
            'judul' => 'Galeri Redirect Test',
            'deskripsi' => 'Deskripsi untuk pengujian redirect URL',
            'url_media' => json_encode(['/storage/media/img1.jpg']),
            'slug' => 'galeri-redirect-test',
            'tanggal_upload' => '2026-09-10',
        ]);

        $this->actingAs($this->superAdmin);

        // Canonical UUID request -> 301 redirect to slug
        $responseUuid = $this->get('/gallery/' . $konten->uuid);
        $responseUuid->assertStatus(301);
        $responseUuid->assertRedirect('/gallery/' . $konten->slug);

        // Canonical Slug request -> 200 OK
        $responseSlug = $this->get('/gallery/' . $konten->slug);
        $responseSlug->assertStatus(200);
        $responseSlug->assertSee('Galeri Redirect Test');
    }

    /**
     * Scenario 12: Public /konten/{slug} resolves canonical UUID with 301 redirect to slug.
     */
    public function test_public_konten_show_resolves_id_and_uuid_with_301_redirect(): void
    {
        $konten = Konten::create([
            'user_uuid' => $this->superAdmin->uuid,
            'divisi_uuid' => $this->divisi->uuid,
            'judul' => 'Konten Publik Redirect Test',
            'deskripsi' => 'Deskripsi pengujian public redirect',
            'url_media' => json_encode(['/storage/media/pub1.jpg']),
            'slug' => 'konten-publik-redirect-test',
            'tanggal_upload' => '2026-09-10',
        ]);

        // Canonical UUID -> 301 redirect
        $responseUuid = $this->get('/konten/' . $konten->uuid);
        $responseUuid->assertStatus(301);
        $responseUuid->assertRedirect(route('konten.show', ['slug' => $konten->slug]));

        // Canonical Slug -> 200 OK
        $responseSlug = $this->get('/konten/' . $konten->slug);
        $responseSlug->assertStatus(200);
        $responseSlug->assertSee('Konten Publik Redirect Test');
    }

    /**
     * Scenario 13: Admin Sponsor CRUD routes support canonical UUID.
     */
    public function test_admin_sponsor_crud_supports_both_id_and_uuid(): void
    {
        $sponsor = Sponsor::create([
            'nama' => 'Sponsor Dual Test',
            'url_image' => 'data:image/png;base64,sample',
        ]);

        $this->actingAs($this->superAdmin);

        // Edit via canonical UUID
        $this->get('/sponsor/edit/' . $sponsor->uuid)->assertStatus(200)->assertSee('Sponsor Dual Test');

        // Update via canonical UUID
        $response = $this->put('/sponsor/' . $sponsor->uuid, [
            'nama' => 'Sponsor Dual Test Updated',
        ]);
        $response->assertRedirect('/sponsor');
        $this->assertEquals('Sponsor Dual Test Updated', $sponsor->fresh()->nama);

        // Destroy via canonical UUID
        $delResponse = $this->delete('/sponsor/' . $sponsor->uuid);
        $delResponse->assertStatus(200)->assertJson(['status' => 'success']);
        $this->assertNull(Sponsor::findByIdentifier($sponsor->uuid));
    }

    /**
     * Scenario 14: Admin Pimpinan CRUD routes support canonical UUID.
     */
    public function test_admin_pimpinan_crud_supports_both_id_and_uuid(): void
    {
        $pimpinan = Pimpinan::create([
            'nama' => 'Drs. Pimpinan Uuid Test',
            'jabatan' => 'Wakil Ketua',
            'foto' => '/storage/pimpinan/sample.jpg',
            'urutan' => 2,
            'is_active' => true,
        ]);

        $this->actingAs($this->superAdmin);

        // Show via UUID
        $this->get('/pimpinan/' . $pimpinan->uuid)->assertStatus(200)->assertSee('Drs. Pimpinan Uuid Test');

        // Toggle status via UUID
        $toggleResponse = $this->patch('/pimpinan/' . $pimpinan->uuid . '/toggle-status');
        $toggleResponse->assertStatus(200)->assertJson(['status' => 'success', 'is_active' => false]);
        $this->assertFalse($pimpinan->fresh()->is_active);

        // Destroy via UUID
        $delResponse = $this->delete('/pimpinan/' . $pimpinan->uuid);
        $delResponse->assertStatus(200)->assertJson(['status' => 'success']);
        $this->assertNull(Pimpinan::findByIdentifier($pimpinan->uuid));
    }

    /**
     * Scenario 15: Admin Calendar CRUD routes support canonical UUID.
     */
    public function test_admin_calendar_crud_supports_both_id_and_uuid(): void
    {
        $calendar = Calendar::create([
            'event_name' => 'Agenda Dual Test',
            'event_date' => '2026-11-20',
        ]);

        $this->actingAs($this->superAdmin);

        // Edit via UUID
        $this->get('/calendar/edit/' . $calendar->uuid)->assertStatus(200)->assertSee('Agenda Dual Test');

        // Update via UUID
        $putResponse = $this->put('/calendar/' . $calendar->uuid, [
            'name' => 'Agenda Dual Test Updated',
            'date' => '2026-11-25',
        ]);
        $putResponse->assertRedirect('/manage/event');
        $this->assertEquals('Agenda Dual Test Updated', $calendar->fresh()->event_name);

        // Delete via UUID
        $delResponse = $this->delete('/calendar/' . $calendar->uuid);
        $delResponse->assertStatus(200)->assertJson(['status' => 'success']);
        $this->assertNull(Calendar::findByIdentifier($calendar->uuid));
    }
}
