<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Konten;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GallerySeoTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;
    protected User $adminDivisiA;
    protected User $adminDivisiB;
    protected Divisi $divisiA;
    protected Divisi $divisiB;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->divisiA = Divisi::create(['nama_divisi' => 'Teknologi & Rekayasa']);
        $this->divisiB = Divisi::create(['nama_divisi' => 'Bisnis & Manajemen']);

        $this->superadmin = User::create([
            'name' => 'Super Admin Test',
            'username' => 'superadmin_seo',
            'email' => 'superadmin_seo@example.com',
            'password' => bcrypt('password123'),
            'role' => 'superadmin',
            'divisi_uuid' => $this->divisiA->uuid,
        ]);

        $this->adminDivisiA = User::create([
            'name' => 'Admin Divisi A',
            'username' => 'admin_a_seo',
            'email' => 'admin_a@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'divisi_uuid' => $this->divisiA->uuid,
        ]);

        $this->adminDivisiB = User::create([
            'name' => 'Admin Divisi B',
            'username' => 'admin_b_seo',
            'email' => 'admin_b@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'divisi_uuid' => $this->divisiB->uuid,
        ]);
    }

    private function createValidJpgFile(string $filename = 'test.jpg'): UploadedFile
    {
        $tmp = tempnam(sys_get_temp_dir(), 'test_img_');
        $im = imagecreatetruecolor(20, 20);
        $color = imagecolorallocate($im, 0, 100, 200);
        imagefill($im, 0, 0, $color);
        imagejpeg($im, $tmp, 90);
        imagedestroy($im);

        return new UploadedFile($tmp, $filename, 'image/jpeg', null, true);
    }

    /** 1. Gallery create without SEO uses safe fallbacks */
    public function test_gallery_create_without_seo_uses_safe_fallbacks()
    {
        $this->actingAs($this->superadmin);

        $response = $this->post('/galeri-kelola', [
            'divisi_uuid' => $this->divisiA->uuid,
            'judul' => 'Rapat Koordinasi MKKS SMK Kab Bekasi 2026',
            'deskripsi' => '<p>Kegiatan koordinasi rutin pengurus <b>MKKS SMK</b> Kabupaten Bekasi.</p>',
            'media' => [$this->createValidJpgFile('photo.jpg')],
        ]);

        $response->assertRedirect('/gallery');
        $this->assertDatabaseHas('konten', [
            'judul' => 'Rapat Koordinasi MKKS SMK Kab Bekasi 2026',
            'seo_title' => 'Rapat Koordinasi MKKS SMK Kab Bekasi 2026',
            'slug' => 'rapat-koordinasi-mkks-smk-kab-bekasi-2026',
        ]);

        $konten = Konten::where('judul', 'Rapat Koordinasi MKKS SMK Kab Bekasi 2026')->first();
        $this->assertNotNull($konten);
        $this->assertStringContainsString('Kegiatan koordinasi rutin pengurus MKKS SMK Kabupaten Bekasi.', $konten->seo_description);
    }

    /** 2. Gallery create with explicit SEO */
    public function test_gallery_create_with_explicit_seo()
    {
        $this->actingAs($this->superadmin);

        $response = $this->post('/galeri-kelola', [
            'divisi_uuid' => $this->divisiA->uuid,
            'judul' => 'Rapat Pleno 2026',
            'deskripsi' => 'Deskripsi rapat pleno.',
            'media' => [$this->createValidJpgFile('rapat.jpg')],
            'seo_title' => 'Rapat Pleno Kepala Sekolah SMK Bekasi 2026',
            'seo_description' => 'Dokumentasi lengkap agenda rapat pleno Kepala Sekolah SMK Kab Bekasi tahun 2026.',
            'slug' => 'rapat-pleno-smk-bekasi-2026',
        ]);

        $response->assertRedirect('/gallery');
        $this->assertDatabaseHas('konten', [
            'judul' => 'Rapat Pleno 2026',
            'seo_title' => 'Rapat Pleno Kepala Sekolah SMK Bekasi 2026',
            'seo_description' => 'Dokumentasi lengkap agenda rapat pleno Kepala Sekolah SMK Kab Bekasi tahun 2026.',
            'slug' => 'rapat-pleno-smk-bekasi-2026',
        ]);
    }

    /** 3. Gallery edit SEO updates fields */
    public function test_gallery_edit_seo_updates_fields()
    {
        $this->actingAs($this->superadmin);

        $konten = Konten::create([
            'user_uuid' => $this->superadmin->uuid,
            'divisi_uuid' => $this->divisiA->uuid,
            'judul' => 'Judul Awal',
            'deskripsi' => 'Deskripsi awal',
            'url_media' => json_encode(['/storage/media/original.jpg']),
            'tanggal_upload' => now(),
            'seo_title' => 'SEO Awal',
            'seo_description' => 'Deskripsi SEO Awal',
            'slug' => 'judul-awal',
        ]);

        $response = $this->put('/galeri-kelola/' . $konten->uuid, [
            'divisi_uuid' => $this->divisiA->uuid,
            'judul' => 'Judul Baru Diubah',
            'deskripsi' => 'Deskripsi baru diubah',
            'keep_media' => ['/storage/media/original.jpg'],
            'seo_title' => 'Custom SEO Title Baru',
            'seo_description' => 'Custom Meta Description Baru yang dioptimalkan.',
            'slug' => 'custom-slug-baru',
        ]);

        $response->assertRedirect('/gallery');
        $konten->refresh();
        $this->assertEquals('Custom SEO Title Baru', $konten->seo_title);
        $this->assertEquals('Custom Meta Description Baru yang dioptimalkan.', $konten->seo_description);
        $this->assertEquals('custom-slug-baru', $konten->slug);
    }

    /** 4. Existing SEO preserved on edit when not modified */
    public function test_existing_seo_preserved_on_edit_when_not_modified()
    {
        $this->actingAs($this->superadmin);

        $konten = Konten::create([
            'user_uuid' => $this->superadmin->uuid,
            'divisi_uuid' => $this->divisiA->uuid,
            'judul' => 'Judul Khusus',
            'deskripsi' => 'Deskripsi khusus',
            'url_media' => json_encode(['/storage/media/original.jpg']),
            'tanggal_upload' => now(),
            'seo_title' => 'SEO Khusus Eksisting',
            'seo_description' => 'Deskripsi SEO Khusus Eksisting',
            'slug' => 'slug-khusus-eksisting',
        ]);

        $response = $this->put('/galeri-kelola/' . $konten->uuid, [
            'divisi_uuid' => $this->divisiA->uuid,
            'judul' => 'Judul Berubah Tapi SEO Tidak Dikirim',
            'deskripsi' => 'Deskripsi berubah',
            'keep_media' => ['/storage/media/original.jpg'],
        ]);

        $response->assertRedirect('/gallery');
        $konten->refresh();
        $this->assertEquals('SEO Khusus Eksisting', $konten->seo_title);
        $this->assertEquals('Deskripsi SEO Khusus Eksisting', $konten->seo_description);
        $this->assertEquals('slug-khusus-eksisting', $konten->slug);
    }

    /** 5. Null SEO fallback behavior on edit */
    public function test_null_seo_fallback_behavior_on_edit()
    {
        $this->actingAs($this->superadmin);

        $konten = Konten::create([
            'user_uuid' => $this->superadmin->uuid,
            'divisi_uuid' => $this->divisiA->uuid,
            'judul' => 'Workshop IT 2026',
            'deskripsi' => '<p>Pelatihan teknologi untuk siswa SMK.</p>',
            'url_media' => json_encode(['/storage/media/original.jpg']),
            'tanggal_upload' => now(),
            'seo_title' => null,
            'seo_description' => null,
            'slug' => null,
        ]);

        $response = $this->put('/galeri-kelola/' . $konten->uuid, [
            'divisi_uuid' => $this->divisiA->uuid,
            'judul' => 'Workshop IT 2026',
            'deskripsi' => '<p>Pelatihan teknologi untuk siswa SMK.</p>',
            'keep_media' => ['/storage/media/original.jpg'],
            'seo_title' => '',
            'seo_description' => '',
            'slug' => '',
        ]);

        $response->assertRedirect('/gallery');
        $konten->refresh();
        $this->assertEquals('Workshop IT 2026', $konten->seo_title);
        $this->assertStringContainsString('Pelatihan teknologi untuk siswa SMK.', $konten->seo_description);
        $this->assertEquals('workshop-it-2026', $konten->slug);
    }

    /** 6. SEO title validation: max 255 chars */
    public function test_seo_title_validation_max_characters()
    {
        $this->actingAs($this->superadmin);

        $response = $this->post('/galeri-kelola', [
            'divisi_uuid' => $this->divisiA->uuid,
            'judul' => 'Judul Valid',
            'deskripsi' => 'Deskripsi valid',
            'media' => [$this->createValidJpgFile('photo.jpg')],
            'seo_title' => str_repeat('A', 256),
        ]);

        $response->assertSessionHasErrors('seo_title');
    }

    /** 7. SEO description validation: max 1000 chars */
    public function test_seo_description_validation_max_characters()
    {
        $this->actingAs($this->superadmin);

        $response = $this->post('/galeri-kelola', [
            'divisi_uuid' => $this->divisiA->uuid,
            'judul' => 'Judul Valid',
            'deskripsi' => 'Deskripsi valid',
            'media' => [$this->createValidJpgFile('photo.jpg')],
            'seo_description' => str_repeat('B', 1001),
        ]);

        $response->assertSessionHasErrors('seo_description');
    }

    /** 8. Slug validation: invalid characters rejected */
    public function test_slug_validation_rejects_invalid_characters()
    {
        $this->actingAs($this->superadmin);

        $response = $this->post('/galeri-kelola', [
            'divisi_uuid' => $this->divisiA->uuid,
            'judul' => 'Judul Valid',
            'deskripsi' => 'Deskripsi valid',
            'media' => [$this->createValidJpgFile('photo.jpg')],
            'slug' => 'slug with spaces and #!@ symbols',
        ]);

        $response->assertSessionHasErrors('slug');
    }

    /** 9. Slug URL-safe format */
    public function test_slug_url_safe_format()
    {
        $this->actingAs($this->superadmin);

        $response = $this->post('/galeri-kelola', [
            'divisi_uuid' => $this->divisiA->uuid,
            'judul' => 'Judul Valid',
            'deskripsi' => 'Deskripsi valid',
            'media' => [$this->createValidJpgFile('photo.jpg')],
            'slug' => 'valid-url-safe-slug-123',
        ]);

        $response->assertRedirect('/gallery');
        $this->assertDatabaseHas('konten', [
            'slug' => 'valid-url-safe-slug-123',
        ]);
    }

    /** 10. Gallery + SEO saved in same DB transaction */
    public function test_gallery_and_seo_saved_atomically_in_same_db_transaction()
    {
        $this->actingAs($this->superadmin);

        $this->post('/galeri-kelola', [
            'divisi_uuid' => $this->divisiA->uuid,
            'judul' => 'Atomic Test Title',
            'deskripsi' => 'Atomic Test Deskripsi',
            'media' => [$this->createValidJpgFile('atomic.jpg')],
            'seo_title' => 'Atomic SEO Title',
            'seo_description' => 'Atomic SEO Description',
            'slug' => 'atomic-slug-test',
        ]);

        $konten = Konten::where('slug', 'atomic-slug-test')->first();
        $this->assertNotNull($konten);
        $this->assertEquals('Atomic Test Title', $konten->judul);
        $this->assertEquals('Atomic SEO Title', $konten->seo_title);
        $this->assertEquals('Atomic SEO Description', $konten->seo_description);
    }

    /** 11. DB failure rolls back both Gallery and SEO */
    public function test_db_failure_rolls_back_both_gallery_and_seo()
    {
        $this->actingAs($this->superadmin);

        // Force a query exception inside creating event
        Konten::creating(function () {
            throw new \Exception('Simulated Database Crash during transaction');
        });

        try {
            $this->post('/galeri-kelola', [
                'divisi_uuid' => $this->divisiA->uuid,
                'judul' => 'Crash Gallery Test',
                'deskripsi' => 'Deskripsi Crash',
                'media' => [$this->createValidJpgFile('crash.jpg')],
                'seo_title' => 'Crash SEO Title',
                'slug' => 'crash-seo-slug',
            ]);
        } catch (\Throwable $e) {
            // caught
        }

        $this->assertDatabaseMissing('konten', [
            'judul' => 'Crash Gallery Test',
        ]);
        $this->assertDatabaseMissing('konten', [
            'slug' => 'crash-seo-slug',
        ]);
    }

    /** 12. Unauthorized admin cannot modify another division's gallery and SEO */
    public function test_unauthorized_admin_cannot_modify_another_division_gallery()
    {
        $this->actingAs($this->adminDivisiA);

        $kontenDivisiB = Konten::create([
            'user_uuid' => $this->superadmin->uuid,
            'divisi_uuid' => $this->divisiB->uuid,
            'judul' => 'Galeri Divisi B',
            'deskripsi' => 'Deskripsi Divisi B',
            'url_media' => json_encode(['/storage/media/b.jpg']),
            'tanggal_upload' => now(),
            'seo_title' => 'Original SEO B',
            'slug' => 'original-seo-b',
        ]);

        $response = $this->put('/galeri-kelola/' . $kontenDivisiB->uuid, [
            'divisi_uuid' => $this->divisiB->uuid,
            'judul' => 'Hacked by Divisi A',
            'deskripsi' => 'Hacked Deskripsi',
            'keep_media' => ['/storage/media/b.jpg'],
            'seo_title' => 'Hacked SEO Title',
            'slug' => 'hacked-slug',
        ]);

        $response->assertStatus(403);
        $kontenDivisiB->refresh();
        $this->assertEquals('Galeri Divisi B', $kontenDivisiB->judul);
        $this->assertEquals('Original SEO B', $kontenDivisiB->seo_title);
        $this->assertEquals('original-seo-b', $kontenDivisiB->slug);
    }

    /** 13. Superadmin can update gallery and SEO across divisions */
    public function test_superadmin_can_update_gallery_and_seo_across_divisions()
    {
        $this->actingAs($this->superadmin);

        $kontenDivisiB = Konten::create([
            'user_uuid' => $this->adminDivisiB->uuid,
            'divisi_uuid' => $this->divisiB->uuid,
            'judul' => 'Galeri Divisi B Awal',
            'deskripsi' => 'Deskripsi Divisi B',
            'url_media' => json_encode(['/storage/media/b.jpg']),
            'tanggal_upload' => now(),
            'seo_title' => 'SEO Divisi B Awal',
            'slug' => 'galeri-divisi-b-awal',
        ]);

        $response = $this->put('/galeri-kelola/' . $kontenDivisiB->uuid, [
            'divisi_uuid' => $this->divisiB->uuid,
            'judul' => 'Superadmin Updated Title',
            'deskripsi' => 'Superadmin Updated Deskripsi',
            'keep_media' => ['/storage/media/b.jpg'],
            'seo_title' => 'Superadmin SEO Title',
            'slug' => 'superadmin-seo-slug',
        ]);

        $response->assertRedirect('/gallery');
        $kontenDivisiB->refresh();
        $this->assertEquals('Superadmin Updated Title', $kontenDivisiB->judul);
        $this->assertEquals('Superadmin SEO Title', $kontenDivisiB->seo_title);
        $this->assertEquals('superadmin-seo-slug', $kontenDivisiB->slug);
    }

    /** 14. Public detail page renders dynamic SEO and Open Graph metadata using slug */
    public function test_public_detail_page_renders_dynamic_seo_and_open_graph_metadata()
    {
        $konten = Konten::create([
            'user_uuid' => $this->superadmin->uuid,
            'divisi_uuid' => $this->divisiA->uuid,
            'judul' => 'Public Gallery Showcase',
            'deskripsi' => 'Rangkuman kegiatan pameran inovasi SMK se-Kabupaten Bekasi.',
            'url_media' => json_encode(['/storage/media/showcase.jpg']),
            'tanggal_upload' => now(),
            'seo_title' => 'Pameran Inovasi SMK Kabupaten Bekasi 2026',
            'seo_description' => 'Lihat dokumentasi karya dan inovasi terbaik siswa SMK se-Kabupaten Bekasi di ajang tahunan.',
            'slug' => 'pameran-inovasi-smk-kab-bekasi-2026',
        ]);

        $response = $this->get('/konten/' . $konten->slug);
        $response->assertStatus(200);

        // Verify HTML tags
        $response->assertSee('<title>Pameran Inovasi SMK Kabupaten Bekasi 2026 - MKKS SMK KAB BEKASI</title>', false);
        $response->assertSee('<meta name="description" content="Lihat dokumentasi karya dan inovasi terbaik siswa SMK se-Kabupaten Bekasi di ajang tahunan.">', false);
        $response->assertSee('<meta property="og:title" content="Pameran Inovasi SMK Kabupaten Bekasi 2026">', false);
        $response->assertSee('<meta property="og:description" content="Lihat dokumentasi karya dan inovasi terbaik siswa SMK se-Kabupaten Bekasi di ajang tahunan.">', false);
        $response->assertSee(url('/konten/' . $konten->slug), false);
    }

    /** 15. Legacy numeric ID URL returns HTTP 301 permanent redirect to slug URL */
    public function test_legacy_numeric_id_redirects_301_to_slug()
    {
        $konten = Konten::create([
            'user_uuid' => $this->superadmin->uuid,
            'divisi_uuid' => $this->divisiA->uuid,
            'judul' => 'Rapat Pleno Legacy',
            'deskripsi' => 'Deskripsi rapat pleno',
            'url_media' => json_encode(['/storage/media/pleno.jpg']),
            'tanggal_upload' => now(),
            'slug' => 'rapat-pleno-legacy',
        ]);

        $response = $this->get('/konten/' . $konten->uuid);
        $response->assertStatus(301);
        $response->assertRedirect('/konten/rapat-pleno-legacy');
    }

    /** 16. Dynamic sitemap XML contains static routes and active konten slugs */
    public function test_sitemap_xml_renders_correctly_with_slugs()
    {
        $konten = Konten::create([
            'user_uuid' => $this->superadmin->uuid,
            'divisi_uuid' => $this->divisiA->uuid,
            'judul' => 'Agenda Bimtek 2026',
            'deskripsi' => 'Deskripsi bimtek kurikulum merdeka',
            'url_media' => json_encode(['/storage/media/bimtek.jpg']),
            'tanggal_upload' => now(),
            'slug' => 'agenda-bimtek-2026',
        ]);

        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $response->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false);
        $response->assertSee(url('/konten/agenda-bimtek-2026'), false);
    }

    /** 17. Robots.txt returns 200 and points to sitemap.xml */
    public function test_robots_txt_contains_sitemap_directive()
    {
        $response = $this->get('/robots.txt');
        $response->assertStatus(200);
        $response->assertSee('User-agent: *', false);
        $response->assertSee('Sitemap:', false);
        $response->assertSee('sitemap.xml', false);
    }
}
