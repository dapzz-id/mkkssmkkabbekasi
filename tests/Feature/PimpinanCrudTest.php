<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Pimpinan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PimpinanCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $regularAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $divisi = Divisi::create(['nama_divisi' => 'Teknologi']);

        $this->superAdmin = User::create([
            'divisi_uuid' => $divisi->uuid,
            'name' => 'Super Administrator',
            'username' => 'superadmin',
            'email' => 'superadmin@mkkssmkbekasi.or.id',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
            'alamat' => 'Bekasi',
        ]);

        $this->regularAdmin = User::create([
            'divisi_uuid' => $divisi->uuid,
            'name' => 'Regular Admin',
            'username' => 'adminbiasa',
            'email' => 'adminbiasa@mkkssmkbekasi.or.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'alamat' => 'Bekasi',
        ]);
    }

    public function test_public_homepage_renders_active_pimpinan_dynamically(): void
    {
        Pimpinan::create([
            'nama' => 'Drs. H. Pimpinan Satu, M.Pd.',
            'jabatan' => 'Ketua MKKS SMK Kabupaten Bekasi',
            'foto' => '/storage/pimpinan/sample1.jpg',
            'urutan' => 1,
            'is_active' => true,
        ]);

        Pimpinan::create([
            'nama' => 'Dra. Hj. Pimpinan Dua, M.M.',
            'jabatan' => 'Wakil Ketua MKKS',
            'foto' => '/storage/pimpinan/sample2.jpg',
            'urutan' => 2,
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Drs. H. Pimpinan Satu, M.Pd.');
        $response->assertSee('Ketua MKKS SMK Kabupaten Bekasi');
        $response->assertSee('Dra. Hj. Pimpinan Dua, M.M.');
        $response->assertSee('Wakil Ketua MKKS');
    }

    public function test_inactive_pimpinan_is_not_displayed_on_public_page(): void
    {
        Pimpinan::create([
            'nama' => 'Pimpinan Aktif',
            'jabatan' => 'Ketua MKKS',
            'foto' => '/storage/pimpinan/sample1.jpg',
            'urutan' => 1,
            'is_active' => true,
        ]);

        Pimpinan::create([
            'nama' => 'Pimpinan Nonaktif',
            'jabatan' => 'Mantan Pengurus',
            'foto' => '/storage/pimpinan/sample2.jpg',
            'urutan' => 2,
            'is_active' => false,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Pimpinan Aktif');
        $response->assertDontSee('Pimpinan Nonaktif');
    }

    public function test_public_homepage_renders_unlimited_pimpinan_counts(): void
    {
        // Test with 5 pimpinan
        for ($i = 1; $i <= 5; $i++) {
            Pimpinan::create([
                'nama' => "Pengurus Ke-$i",
                'jabatan' => "Jabatan Pengurus $i",
                'foto' => "/storage/pimpinan/sample$i.jpg",
                'urutan' => $i,
                'is_active' => true,
            ]);
        }

        $response = $this->get('/');

        $response->assertStatus(200);
        for ($i = 1; $i <= 5; $i++) {
            $response->assertSee("Pengurus Ke-$i");
            $response->assertSee("Jabatan Pengurus $i");
        }
    }

    public function test_public_homepage_renders_10_pimpinan_without_hardcoded_limits(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Pimpinan::create([
                'nama' => "Pimpinan Lengkap $i",
                'jabatan' => "Jabatan Ke-$i",
                'foto' => "/storage/pimpinan/p$i.jpg",
                'urutan' => $i,
                'is_active' => true,
            ]);
        }

        $response = $this->get('/');

        $response->assertStatus(200);
        for ($i = 1; $i <= 10; $i++) {
            $response->assertSee("Pimpinan Lengkap $i");
        }
    }

    public function test_regular_admin_is_forbidden_from_managing_pimpinan(): void
    {
        $this->actingAs($this->regularAdmin);

        $response = $this->get('/pimpinan');
        $response->assertStatus(403);

        $response = $this->get('/pimpinan/tambah');
        $response->assertStatus(403);

        $response = $this->post('/pimpinan', [
            'nama' => 'Test',
            'jabatan' => 'Test',
        ]);
        $response->assertStatus(403);
    }

    public function test_superadmin_can_access_pimpinan_management_page(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->get('/pimpinan');

        $response->assertStatus(200);
        $response->assertSee('Pimpinan MKKS');
        $response->assertSee('Tambah Pimpinan');
    }

    public function test_superadmin_can_create_new_pimpinan(): void
    {
        $this->actingAs($this->superAdmin);

        $file = UploadedFile::fake()->image('ketua.jpg', 600, 450);

        $response = $this->post('/pimpinan', [
            'nama' => 'H. Ahmad Supriyadi, S.Pd., M.M.',
            'jabatan' => 'Ketua MKKS SMK Kabupaten Bekasi',
            'foto' => $file,
            'urutan' => 1,
            'is_active' => 1,
        ]);

        $response->assertRedirect('/pimpinan');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pimpinan', [
            'nama' => 'H. Ahmad Supriyadi, S.Pd., M.M.',
            'jabatan' => 'Ketua MKKS SMK Kabupaten Bekasi',
            'urutan' => 1,
            'is_active' => true,
        ]);

        $pimpinan = Pimpinan::where('nama', 'H. Ahmad Supriyadi, S.Pd., M.M.')->first();
        $storedPath = str_replace('/storage/', '', $pimpinan->foto);
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_superadmin_can_update_pimpinan_and_replace_photo(): void
    {
        $this->actingAs($this->superAdmin);

        $oldFile = UploadedFile::fake()->image('old.jpg');
        $oldPath = $oldFile->store('pimpinan', 'public');

        $pimpinan = Pimpinan::create([
            'nama' => 'Nama Lama',
            'jabatan' => 'Jabatan Lama',
            'foto' => '/storage/' . $oldPath,
            'urutan' => 5,
            'is_active' => true,
        ]);

        $newFile = UploadedFile::fake()->image('new.jpg');

        $response = $this->put("/pimpinan/{$pimpinan->uuid}", [
            'nama' => 'Nama Baru Diperbarui',
            'jabatan' => 'Jabatan Baru',
            'foto' => $newFile,
            'urutan' => 2,
            'is_active' => 1,
        ]);

        $response->assertRedirect('/pimpinan');
        $response->assertSessionHas('success');

        $pimpinan->refresh();
        $this->assertEquals('Nama Baru Diperbarui', $pimpinan->nama);
        $this->assertEquals('Jabatan Baru', $pimpinan->jabatan);
        $this->assertEquals(2, $pimpinan->urutan);

        // Old file deleted, new file exists
        Storage::disk('public')->assertMissing($oldPath);
        $newPath = str_replace('/storage/', '', $pimpinan->foto);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_superadmin_can_toggle_pimpinan_active_status(): void
    {
        $this->actingAs($this->superAdmin);

        $pimpinan = Pimpinan::create([
            'nama' => 'Pimpinan Toggle',
            'jabatan' => 'Bendahara',
            'foto' => '/storage/sample.jpg',
            'urutan' => 1,
            'is_active' => true,
        ]);

        $response = $this->patch("/pimpinan/{$pimpinan->uuid}/toggle-status");

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'is_active' => false,
        ]);

        $this->assertFalse($pimpinan->fresh()->is_active);
    }

    public function test_superadmin_can_delete_pimpinan(): void
    {
        $this->actingAs($this->superAdmin);

        $file = UploadedFile::fake()->image('todelete.jpg');
        $path = $file->store('pimpinan', 'public');

        $pimpinan = Pimpinan::create([
            'nama' => 'Pimpinan Dihapus',
            'jabatan' => 'Anggota',
            'foto' => '/storage/' . $path,
            'urutan' => 1,
            'is_active' => true,
        ]);

        $response = $this->delete("/pimpinan/{$pimpinan->uuid}");

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing('pimpinan', ['uuid' => $pimpinan->uuid]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_superadmin_can_create_pimpinan_via_ajax_with_json_response(): void
    {
        $this->actingAs($this->superAdmin);

        $file = UploadedFile::fake()->image('ajax_pimpinan.jpg', 800, 600);

        $response = $this->postJson('/pimpinan', [
            'nama' => 'Pimpinan Via Ajax',
            'jabatan' => 'Sekretaris MKKS',
            'foto' => $file,
            'urutan' => 3,
            'is_active' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Data pimpinan berhasil ditambahkan.',
        ]);

        $this->assertDatabaseHas('pimpinan', [
            'nama' => 'Pimpinan Via Ajax',
            'jabatan' => 'Sekretaris MKKS',
        ]);
    }

    public function test_superadmin_can_update_pimpinan_via_ajax_with_json_response(): void
    {
        $this->actingAs($this->superAdmin);

        $pimpinan = Pimpinan::create([
            'nama' => 'Nama Sebelum Ajax',
            'jabatan' => 'Jabatan Sebelum Ajax',
            'foto' => '/storage/test.jpg',
            'urutan' => 1,
            'is_active' => true,
        ]);

        $response = $this->putJson("/pimpinan/{$pimpinan->uuid}", [
            'nama' => 'Nama Sesudah Ajax',
            'jabatan' => 'Jabatan Sesudah Ajax',
            'urutan' => 1,
            'is_active' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Data pimpinan berhasil diperbarui.',
        ]);

        $this->assertEquals('Nama Sesudah Ajax', $pimpinan->fresh()->nama);
    }
}
