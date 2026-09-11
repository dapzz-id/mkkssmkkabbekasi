<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Konten;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GalleryFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Divisi $divisi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->divisi = Divisi::create(['nama_divisi' => 'Multimedia']);

        $this->user = User::create([
            'divisi_uuid' => $this->divisi->uuid,
            'name' => 'Admin Gallery',
            'username' => 'admingallery',
            'email' => 'gallery@mkkssmkbekasi.or.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'alamat' => 'Bekasi',
        ]);
    }

    public function test_unauthenticated_user_is_redirected_from_gallery(): void
    {
        $response = $this->get('/gallery');
        $response->assertRedirect('/login');
    }

    public function test_gallery_displays_all_contents_by_default(): void
    {
        Konten::create([
            'user_uuid' => $this->user->uuid,
            'divisi_uuid' => $this->divisi->uuid,
            'url_media' => json_encode(['/storage/konten/foto1.jpg']),
            'tanggal_upload' => '2026-01-10 10:00:00',
            'judul' => 'Kegiatan Workshop SMK',
            'deskripsi' => 'Pelatihan guru dan tenaga pendidik',
        ]);

        $response = $this->actingAs($this->user)->get('/gallery');

        $response->assertOk();
        $response->assertSee('Kegiatan Workshop SMK');
        $response->assertSee('Gallery');
    }

    public function test_gallery_filters_by_search_keyword_in_title_and_description(): void
    {
        Konten::create([
            'user_uuid' => $this->user->uuid,
            'divisi_uuid' => $this->divisi->uuid,
            'url_media' => json_encode(['/storage/konten/foto1.jpg']),
            'tanggal_upload' => '2026-01-10 10:00:00',
            'judul' => 'Lomba Kompetensi Siswa',
            'deskripsi' => 'Pelaksanaan LKS tingkat Kabupaten Bekasi',
        ]);

        Konten::create([
            'user_uuid' => $this->user->uuid,
            'divisi_uuid' => $this->divisi->uuid,
            'url_media' => json_encode(['/storage/konten/foto2.jpg']),
            'tanggal_upload' => '2026-01-11 10:00:00',
            'judul' => 'Rapat Koordinasi Kepala Sekolah',
            'deskripsi' => 'Membahas kurikulum merdeka',
        ]);

        // Search for 'LKS'
        $response = $this->actingAs($this->user)->get('/gallery?search=LKS');
        $response->assertOk();
        $response->assertSee('Lomba Kompetensi Siswa');
        $response->assertDontSee('Rapat Koordinasi Kepala Sekolah');

        // Search for 'kurikulum' in description
        $response = $this->actingAs($this->user)->get('/gallery?search=kurikulum');
        $response->assertOk();
        $response->assertSee('Rapat Koordinasi Kepala Sekolah');
        $response->assertDontSee('Lomba Kompetensi Siswa');
    }

    public function test_gallery_filters_by_media_type(): void
    {
        Konten::create([
            'user_uuid' => $this->user->uuid,
            'divisi_uuid' => $this->divisi->uuid,
            'url_media' => json_encode(['/storage/konten/image_kegiatan.png']),
            'tanggal_upload' => '2026-01-10 10:00:00',
            'judul' => 'Dokumentasi Foto LKS',
            'deskripsi' => 'Dokumentasi foto',
        ]);

        Konten::create([
            'user_uuid' => $this->user->uuid,
            'divisi_uuid' => $this->divisi->uuid,
            'url_media' => json_encode(['/storage/konten/video_profil.mp4']),
            'tanggal_upload' => '2026-01-11 10:00:00',
            'judul' => 'Video Profil MKKS',
            'deskripsi' => 'Video penjelasan profil',
        ]);

        // Filter 'foto'
        $responseFoto = $this->actingAs($this->user)->get('/gallery?media=foto');
        $responseFoto->assertOk();
        $responseFoto->assertSee('Dokumentasi Foto LKS');
        $responseFoto->assertDontSee('Video Profil MKKS');

        // Filter 'video'
        $responseVideo = $this->actingAs($this->user)->get('/gallery?media=video');
        $responseVideo->assertOk();
        $responseVideo->assertSee('Video Profil MKKS');
        $responseVideo->assertDontSee('Dokumentasi Foto LKS');
    }

    public function test_gallery_respects_per_page_whitelist(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            Konten::create([
                'user_uuid' => $this->user->uuid,
                'divisi_uuid' => $this->divisi->uuid,
                'url_media' => json_encode(["/storage/konten/item{$i}.jpg"]),
                'tanggal_upload' => sprintf('2026-01-%02d 10:00:00', $i),
                'judul' => "Konten Acara Ke-{$i}",
                'deskripsi' => "Deskripsi acara {$i}",
            ]);
        }

        // Whitelisted per_page=2: exactly 2 items should show
        $response = $this->actingAs($this->user)->get('/gallery?per_page=2');
        $response->assertOk();
        $kontenData = $response->viewData('data');
        $this->assertEquals(2, $kontenData->count());
        $this->assertEquals(15, $kontenData->total());

        // Invalid per_page=999 should fall back to default 5
        $responseInvalid = $this->actingAs($this->user)->get('/gallery?per_page=999');
        $responseInvalid->assertOk();
        $kontenDefault = $responseInvalid->viewData('data');
        $this->assertEquals(5, $kontenDefault->count());
    }

    public function test_gallery_pagination_links_contain_query_string(): void
    {
        for ($i = 1; $i <= 6; $i++) {
            Konten::create([
                'user_uuid' => $this->user->uuid,
                'divisi_uuid' => $this->divisi->uuid,
                'url_media' => "/storage/konten/item{$i}.jpg",
                'tanggal_upload' => "2026-01-{$i} 10:00:00",
                'judul' => "Acara Workshop Ke-{$i}",
                'deskripsi' => "Deskripsi workshop {$i}",
            ]);
        }

        $response = $this->actingAs($this->user)->get('/gallery?search=Workshop&per_page=2');
        $response->assertOk();

        // Check that page 2 link contains both 'search=Workshop' and 'per_page=2'
        $response->assertSee('search=Workshop');
        $response->assertSee('per_page=2');
    }

    public function test_gallery_suggestions_endpoint_requires_auth_and_min_2_chars(): void
    {
        // Unauthenticated
        $this->getJson('/gallery/suggestions?term=work')->assertStatus(200)->assertJson([]);

        Konten::create([
            'user_uuid' => $this->user->uuid,
            'divisi_uuid' => $this->divisi->uuid,
            'url_media' => json_encode(['/storage/konten/item1.jpg']),
            'tanggal_upload' => '2026-01-01 10:00:00',
            'judul' => 'Workshop Robotika SMK',
            'deskripsi' => 'Pelatihan robotika',
        ]);

        // term less than 2 chars returns empty array
        $resShort = $this->actingAs($this->user)->getJson('/gallery/suggestions?term=w');
        $resShort->assertOk()->assertExactJson([]);

        // term >= 2 chars returns structured suggestions
        $res = $this->actingAs($this->user)->getJson('/gallery/suggestions?term=work');
        $res->assertOk();
        $firstItem = $res->json()[0];
        $this->assertEquals('Workshop Robotika SMK', $firstItem['label']);
        $this->assertEquals('Multimedia', $firstItem['sub']);
        $this->assertEquals('Workshop Robotika SMK', $firstItem['value']);

        // Compatible with ?q= parameter
        $resQ = $this->actingAs($this->user)->getJson('/gallery/suggestions?q=work');
        $resQ->assertOk();
        $this->assertEquals('Workshop Robotika SMK', $resQ->json()[0]['label']);
    }

    public function test_sponsor_and_event_suggestions_endpoints(): void
    {
        \App\Models\Sponsor::create([
            'nama' => 'PT Astra Honda Motor',
            'url_image' => '/storage/sponsor/honda.png',
        ]);

        $resSponsor = $this->actingAs($this->user)->getJson('/sponsor/suggestions?term=Astra');
        $resSponsor->assertOk();
        $this->assertEquals('PT Astra Honda Motor', $resSponsor->json()[0]['label']);
        $this->assertEquals('Mitra Sponsor', $resSponsor->json()[0]['sub']);
        $this->assertEquals('PT Astra Honda Motor', $resSponsor->json()[0]['value']);

        // Superadmin for user & event
        $superAdmin = User::create([
            'divisi_uuid' => $this->divisi->uuid,
            'name' => 'Super User',
            'username' => 'superuser',
            'email' => 'superuser@mkkssmkbekasi.or.id',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'role' => 'superadmin',
            'alamat' => 'Bekasi',
        ]);

        \App\Models\Calendar::create([
            'event_name' => 'LKS Tingkat Provinsi',
            'event_date' => '2026-05-15',
        ]);

        $resEvent = $this->actingAs($superAdmin)->getJson('/manage/event/suggestions?term=LKS');
        $resEvent->assertOk();
        $this->assertEquals('LKS Tingkat Provinsi', $resEvent->json()[0]['label']);
        $this->assertEquals('LKS Tingkat Provinsi', $resEvent->json()[0]['value']);

        // User suggestion
        $resUser = $this->actingAs($superAdmin)->getJson('/manage/user/suggestions?term=Super');
        $resUser->assertOk();
        $this->assertEquals('Super User', $resUser->json()[0]['label']);
    }

    public function test_gallery_searches_by_divisi_name_and_divisi_prefix(): void
    {
        $divisiAkuntansi = Divisi::create(['nama_divisi' => 'Akuntansi']);

        Konten::create([
            'user_uuid' => $this->user->uuid,
            'divisi_uuid' => $divisiAkuntansi->uuid,
            'url_media' => json_encode(['/storage/konten/akuntansi.jpg']),
            'tanggal_upload' => '2026-02-01 10:00:00',
            'judul' => 'Olimpiade Akuntansi Keuangan',
            'deskripsi' => 'Lomba akuntansi tingkat Jawa Barat',
        ]);

        // Superadmin can see all divisions
        $superAdmin = User::create([
            'divisi_uuid' => $this->divisi->uuid,
            'name' => 'Super User Admin',
            'username' => 'superuseradmin',
            'email' => 'superuseradmin@mkkssmkbekasi.or.id',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'role' => 'superadmin',
            'alamat' => 'Bekasi',
        ]);

        // 1. Search direct by division name 'Akuntansi'
        $response = $this->actingAs($superAdmin)->get('/gallery?search=Akuntansi');
        $response->assertOk();
        $response->assertSee('Olimpiade Akuntansi Keuangan');

        // 2. Search with prefix 'divisi Akuntansi'
        $responsePrefix = $this->actingAs($superAdmin)->get('/gallery?search=divisi+Akuntansi');
        $responsePrefix->assertOk();
        $responsePrefix->assertSee('Olimpiade Akuntansi Keuangan');

        // 3. Search by content keyword with prefix 'konten Jawa Barat'
        $responseKonten = $this->actingAs($superAdmin)->get('/gallery?search=konten+Jawa+Barat');
        $responseKonten->assertOk();
        $responseKonten->assertSee('Olimpiade Akuntansi Keuangan');
    }

    public function test_gallery_suggestions_include_divisi_and_content_matches(): void
    {
        Konten::create([
            'user_uuid' => $this->user->uuid,
            'divisi_uuid' => $this->divisi->uuid,
            'url_media' => json_encode(['/storage/konten/item99.jpg']),
            'tanggal_upload' => '2026-02-01 10:00:00',
            'judul' => 'Workshop Animasi 3D',
            'deskripsi' => 'Pelatihan intensif modeling karakter blender',
        ]);

        // 1. Suggestion by division name 'Multimedia'
        $resDivisi = $this->actingAs($this->user)->getJson('/gallery/suggestions?term=Multi');
        $resDivisi->assertOk();
        $this->assertNotEmpty($resDivisi->json());
        $divisiItem = collect($resDivisi->json())->firstWhere('value', 'Multimedia');
        $this->assertNotNull($divisiItem);
        $this->assertEquals('Divisi Multimedia', $divisiItem['label']);

        // 2. Suggestion by literal 'divisi' returns divisions
        $resLiteral = $this->actingAs($this->user)->getJson('/gallery/suggestions?term=divisi');
        $resLiteral->assertOk();
        $this->assertNotEmpty($resLiteral->json());
        $hasMulti = collect($resLiteral->json())->firstWhere('value', 'Multimedia');
        $this->assertNotNull($hasMulti);

        // 3. Suggestion by content keyword 'blender'
        $resDesc = $this->actingAs($this->user)->getJson('/gallery/suggestions?term=blender');
        $resDesc->assertOk();
        $this->assertNotEmpty($resDesc->json());
        $contentItem = $resDesc->json()[0];
        $this->assertEquals('Workshop Animasi 3D', $contentItem['label']);
        $this->assertStringContainsString('blender', $contentItem['sub']);
    }
}

