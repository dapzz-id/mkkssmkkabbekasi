<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Konten;
use App\Models\Pimpinan;
use App\Models\Sponsor;
use App\Models\User;
use App\Services\SecureUploadValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SecureUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;
    protected User $admin;
    protected Divisi $divisiA;
    protected Divisi $divisiB;
    protected SecureUploadValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->divisiA = Divisi::create(['nama_divisi' => 'Teknologi Informasi']);
        $this->divisiB = Divisi::create(['nama_divisi' => 'Kesenian']);

        $this->superadmin = User::create([
            'name' => 'Super Admin Test',
            'username' => 'superadmin_test',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'superadmin',
            'id_divisi' => $this->divisiA->id,
        ]);

        $this->admin = User::create([
            'name' => 'Regular Admin Test',
            'username' => 'admin_test',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'id_divisi' => $this->divisiA->id,
        ]);

        $this->validator = new SecureUploadValidator();
    }

    /** Helper to create genuine valid raster image UploadedFile */
    protected function createValidImage(string $filename, string $format = 'jpeg'): UploadedFile
    {
        $temp = tempnam(sys_get_temp_dir(), 'test_img_');
        $im = imagecreatetruecolor(20, 20);
        $color = imagecolorallocate($im, 0, 100, 200);
        imagefill($im, 0, 0, $color);

        $mime = 'image/jpeg';
        if ($format === 'png') {
            imagepng($im, $temp);
            $mime = 'image/png';
        } elseif ($format === 'gif') {
            imagegif($im, $temp);
            $mime = 'image/gif';
        } elseif ($format === 'webp') {
            imagewebp($im, $temp);
            $mime = 'image/webp';
        } else {
            imagejpeg($im, $temp, 90);
            $mime = 'image/jpeg';
        }
        imagedestroy($im);

        return new UploadedFile($temp, $filename, $mime, null, true);
    }

    /** Helper to create genuine minimum valid MP4 container UploadedFile */
    protected function createValidMp4(string $filename, int $exactBytes = 0): UploadedFile
    {
        $temp = tempnam(sys_get_temp_dir(), 'test_mp4_');
        
        // Minimal valid MP4 header:
        // ftyp box: size 24 bytes (4), 'ftyp' (4), 'isom' (4), minor 0x200 (4), comp brands 'isommp42' (8)
        $ftyp = pack('N', 24) . 'ftyp' . 'isom' . pack('N', 512) . 'isommp42';
        // mdat box header: size 8 bytes (4), 'mdat' (4)
        $mdatHeader = pack('N', 8) . 'mdat';
        $content = $ftyp . $mdatHeader;

        if ($exactBytes > 0 && $exactBytes >= strlen($content)) {
            $handle = fopen($temp, 'wb');
            fwrite($handle, $content);
            $remaining = $exactBytes - strlen($content);
            if ($remaining > 0) {
                // Pad with zeros to exact bytes
                $chunk = str_repeat("\0", min(8192, $remaining));
                while ($remaining > 0) {
                    $writeSize = min(strlen($chunk), $remaining);
                    fwrite($handle, substr($chunk, 0, $writeSize));
                    $remaining -= $writeSize;
                }
            }
            fclose($handle);
        } else {
            file_put_contents($temp, $content);
        }

        return new UploadedFile($temp, $filename, 'video/mp4', null, true);
    }

    /** Helper to create safe SVG */
    protected function createSafeSvg(string $filename): UploadedFile
    {
        $temp = tempnam(sys_get_temp_dir(), 'test_svg_');
        $svg = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL .
               '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">' .
               '<circle cx="50" cy="50" r="40" fill="#0055ff"/>' .
               '</svg>';
        file_put_contents($temp, $svg);

        return new UploadedFile($temp, $filename, 'image/svg+xml', null, true);
    }

    /** Helper to create inert dummy file */
    protected function createInertFile(string $filename, string $content, string $mime = 'text/plain'): UploadedFile
    {
        $temp = tempnam(sys_get_temp_dir(), 'test_inert_');
        file_put_contents($temp, $content);

        return new UploadedFile($temp, $filename, $mime, null, true);
    }

    // ==========================================
    // 1. VALID FILES ACCEPTANCE
    // ==========================================

    public function test_valid_jpg_accepted(): void
    {
        $file = $this->createValidImage('photo.jpg', 'jpeg');
        $result = $this->validator->validateGalleryMedia($file);
        $this->assertEquals('jpg', $result['extension']);
        $this->assertEquals('image/jpeg', $result['mime']);
        $this->assertEquals('image', $result['type']);
    }

    public function test_valid_jpeg_accepted(): void
    {
        $file = $this->createValidImage('photo.jpeg', 'jpeg');
        $result = $this->validator->validateGalleryMedia($file);
        $this->assertEquals('jpg', $result['extension']);
    }

    public function test_valid_png_accepted(): void
    {
        $file = $this->createValidImage('logo.png', 'png');
        $result = $this->validator->validateGalleryMedia($file);
        $this->assertEquals('png', $result['extension']);
        $this->assertEquals('image/png', $result['mime']);
    }

    public function test_valid_gif_accepted_for_sponsor(): void
    {
        $file = $this->createValidImage('anim.gif', 'gif');
        $result = $this->validator->validateSponsorLogo($file);
        $this->assertEquals('gif', $result['extension']);
        $this->assertEquals('image/gif', $result['mime']);
    }

    public function test_valid_webp_accepted_for_pimpinan(): void
    {
        $file = $this->createValidImage('leader.webp', 'webp');
        $result = $this->validator->validatePimpinanPhoto($file);
        $this->assertEquals('webp', $result['extension']);
        $this->assertEquals('image/webp', $result['mime']);
    }

    public function test_valid_mp4_accepted_for_gallery(): void
    {
        $file = $this->createValidMp4('video.mp4');
        $result = $this->validator->validateGalleryMedia($file);
        $this->assertEquals('mp4', $result['extension']);
        $this->assertEquals('video', $result['type']);
    }

    public function test_safe_svg_accepted_for_sponsor(): void
    {
        $file = $this->createSafeSvg('sponsor.svg');
        $result = $this->validator->validateSponsorLogo($file);
        $this->assertEquals('svg', $result['extension']);
    }

    // ==========================================
    // 2. INVALID EXTENSIONS & SPOOFING REJECTION
    // ==========================================

    public function test_renamed_php_to_jpg_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $file = $this->createInertFile('payload.jpg', '<?php echo "evil"; ?>', 'application/x-php');
        $this->validator->validateGalleryMedia($file);
    }

    public function test_html_disguised_as_jpg_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $file = $this->createInertFile('page.jpg', '<html><body>Fake Image</body></html>', 'text/html');
        $this->validator->validateGalleryMedia($file);
    }

    public function test_js_disguised_as_png_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $file = $this->createInertFile('script.png', 'console.log("malicious");', 'text/javascript');
        $this->validator->validateGalleryMedia($file);
    }

    public function test_php_extension_directly_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $file = $this->createInertFile('shell.php', '<?php phpinfo(); ?>');
        $this->validator->validateGalleryMedia($file);
    }

    public function test_double_extension_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $file = $this->createInertFile('exploit.php.png', "\x89PNG\x0d\x0a\x1a\x0a");
        $this->validator->validateGalleryMedia($file);
    }

    public function test_path_traversal_filename_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $file = $this->createInertFile('../../etc/passwd.jpg', 'fake content');
        $this->validator->validateGalleryMedia($file);
    }

    public function test_null_byte_filename_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $file = $this->createInertFile("image.jpg\0.php", 'fake content');
        $this->validator->validateGalleryMedia($file);
    }

    // ==========================================
    // 3. MAGIC BYTE & STRUCTURAL VALIDATION
    // ==========================================

    public function test_fake_jpeg_header_rejected(): void
    {
        $this->expectException(ValidationException::class);
        // Does not start with FF D8 FF
        $file = $this->createInertFile('corrupt.jpg', 'NOT_JPEG_HEADER_CONTENT', 'image/jpeg');
        $this->validator->validateGalleryMedia($file);
    }

    public function test_fake_png_header_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $file = $this->createInertFile('fake.png', 'NOT_PNG_HEADER_CONTENT', 'image/png');
        $this->validator->validateGalleryMedia($file);
    }

    public function test_malformed_mp4_missing_ftyp_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $file = $this->createInertFile('fake.mp4', 'RAW_BINARY_DATA_WITHOUT_FTYP', 'video/mp4');
        $this->validator->validateGalleryMedia($file);
    }

    // ==========================================
    // 4. SVG ACTIVE CONTENT & XXE SECURITY
    // ==========================================

    public function test_svg_with_script_tag_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $badSvg = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script><circle cx="5" cy="5" r="5"/></svg>';
        $file = $this->createInertFile('xss.svg', $badSvg, 'image/svg+xml');
        $this->validator->validateSponsorLogo($file);
    }

    public function test_svg_with_onload_attribute_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $badSvg = '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"><circle cx="5" cy="5" r="5"/></svg>';
        $file = $this->createInertFile('xss_event.svg', $badSvg, 'image/svg+xml');
        $this->validator->validateSponsorLogo($file);
    }

    public function test_svg_with_onerror_attribute_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $badSvg = '<svg xmlns="http://www.w3.org/2000/svg"><image href="x" onerror="alert(1)"/></svg>';
        $file = $this->createInertFile('xss_onerror.svg', $badSvg, 'image/svg+xml');
        $this->validator->validateSponsorLogo($file);
    }

    public function test_svg_with_javascript_uri_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $badSvg = '<svg xmlns="http://www.w3.org/2000/svg"><a href="javascript:alert(1)"><text>Click</text></a></svg>';
        $file = $this->createInertFile('xss_uri.svg', $badSvg, 'image/svg+xml');
        $this->validator->validateSponsorLogo($file);
    }

    public function test_svg_with_foreign_object_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $badSvg = '<svg xmlns="http://www.w3.org/2000/svg"><foreignObject><body><p>Test</p></body></foreignObject></svg>';
        $file = $this->createInertFile('foreign.svg', $badSvg, 'image/svg+xml');
        $this->validator->validateSponsorLogo($file);
    }

    public function test_svg_with_xxe_doctype_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $badSvg = '<!DOCTYPE svg [ <!ENTITY xxe SYSTEM "file:///etc/passwd"> ]><svg xmlns="http://www.w3.org/2000/svg">&xxe;</svg>';
        $file = $this->createInertFile('xxe.svg', $badSvg, 'image/svg+xml');
        $this->validator->validateSponsorLogo($file);
    }

    public function test_svg_malformed_xml_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $badSvg = '<svg xmlns="http://www.w3.org/2000/svg"><unclosed_tag></svg>';
        $file = $this->createInertFile('broken.svg', $badSvg, 'image/svg+xml');
        $this->validator->validateSponsorLogo($file);
    }

    // ==========================================
    // 5. EXACT 100 MiB MP4 BOUNDARY
    // ==========================================

    public function test_exact_100_mib_mp4_accepted(): void
    {
        // 104,857,600 bytes boundary test
        $exactLimit = 104857600;
        $file = $this->createValidMp4('boundary_100mib.mp4', $exactLimit);
        $result = $this->validator->validateGalleryMedia($file);
        $this->assertEquals('mp4', $result['extension']);
        $this->assertEquals($exactLimit, $result['size']);
    }

    public function test_oversized_100_mib_plus_one_byte_mp4_rejected(): void
    {
        $this->expectException(ValidationException::class);
        // 104,857,601 bytes boundary test: must reject!
        $overLimit = 104857601;
        $file = $this->createValidMp4('over_100mib.mp4', $overLimit);
        $this->validator->validateGalleryMedia($file);
    }

    // ==========================================
    // 6. GALLERY CONTROLLER END-TO-END TESTS
    // ==========================================

    public function test_gallery_create_successful(): void
    {
        $img = $this->createValidImage('gallery1.jpg', 'jpeg');
        $mp4 = $this->createValidMp4('gallery2.mp4');

        $response = $this->actingAs($this->admin)->post('/gallery', [
            'id_divisi' => $this->divisiA->id,
            'judul' => 'Kegiatan Workshop Robotik',
            'deskripsi' => 'Pelaksanaan workshop robotik SMK Bekasi',
            'media' => [$img, $mp4],
        ]);

        $response->assertRedirect('/gallery');
        $this->assertDatabaseHas('konten', [
            'judul' => 'Kegiatan Workshop Robotik',
            'id_divisi' => $this->divisiA->id,
        ]);

        $konten = Konten::where('judul', 'Kegiatan Workshop Robotik')->first();
        $urls = json_decode($konten->url_media, true);
        $this->assertCount(2, $urls);
        $this->assertStringContainsString('.jpg', $urls[0]);
        $this->assertStringContainsString('.mp4', $urls[1]);
    }

    public function test_gallery_edit_preserves_old_media_and_appends_new_media(): void
    {
        $initialKonten = Konten::create([
            'id_user' => $this->admin->id,
            'id_divisi' => $this->divisiA->id,
            'judul' => 'Galeri Awal',
            'deskripsi' => 'Deskripsi awal',
            'tanggal_upload' => now(),
            'url_media' => json_encode([
                '/storage/media/old_photo_1.jpg',
                '/storage/media/old_photo_2.jpg',
            ]),
        ]);

        $newImg = $this->createValidImage('new_photo_3.png', 'png');

        $response = $this->actingAs($this->admin)->put("/gallery/{$initialKonten->id}", [
            'id_divisi' => $this->divisiA->id,
            'judul' => 'Galeri Diperbarui',
            'deskripsi' => 'Deskripsi baru',
            'keep_media' => ['/storage/media/old_photo_1.jpg', '/storage/media/old_photo_2.jpg'],
            'media' => [$newImg],
        ]);

        $response->assertRedirect('/gallery');

        $updated = Konten::find($initialKonten->id);
        $finalUrls = json_decode($updated->url_media, true);

        // Expect exactly 3 media items: 2 kept old + 1 appended new
        $this->assertCount(3, $finalUrls);
        $this->assertEquals('/storage/media/old_photo_1.jpg', $finalUrls[0]);
        $this->assertEquals('/storage/media/old_photo_2.jpg', $finalUrls[1]);
        $this->assertStringContainsString('.png', $finalUrls[2]);
    }

    public function test_gallery_edit_rejects_foreign_keep_media(): void
    {
        // Gallery 1
        $galeri1 = Konten::create([
            'id_user' => $this->admin->id,
            'id_divisi' => $this->divisiA->id,
            'judul' => 'Galeri 1',
            'deskripsi' => 'Desc 1',
            'tanggal_upload' => now(),
            'url_media' => json_encode(['/storage/media/galeri1_pic.jpg']),
        ]);

        // Attempt to inject foreign media into Galeri 1
        $response = $this->actingAs($this->admin)->put("/gallery/{$galeri1->id}", [
            'id_divisi' => $this->divisiA->id,
            'judul' => 'Galeri 1 Edited',
            'deskripsi' => 'Desc 1 Edited',
            'keep_media' => [
                '/storage/media/galeri1_pic.jpg',
                '/storage/media/FOREIGN_PRIVATE_MEDIA.jpg',
                '/storage/media/other_tenant_file.mp4',
            ],
        ]);

        $response->assertRedirect('/gallery');

        $fresh = Konten::find($galeri1->id);
        $mediaList = json_decode($fresh->url_media, true);

        // Foreign media must be strictly filtered out! Only galeri1_pic.jpg kept.
        $this->assertCount(1, $mediaList);
        $this->assertEquals('/storage/media/galeri1_pic.jpg', $mediaList[0]);
        $this->assertNotContains('/storage/media/FOREIGN_PRIVATE_MEDIA.jpg', $mediaList);
    }

    public function test_gallery_edit_explicit_old_media_deletion(): void
    {
        // Create actual storage file to verify deletion post-commit
        Storage::disk('public')->put('media/to_be_deleted.jpg', 'old content');
        Storage::disk('public')->put('media/to_be_kept.jpg', 'keep content');

        $galeri = Konten::create([
            'id_user' => $this->admin->id,
            'id_divisi' => $this->divisiA->id,
            'judul' => 'Galeri Hapus Sebagian',
            'deskripsi' => 'Desc',
            'tanggal_upload' => now(),
            'url_media' => json_encode([
                '/storage/media/to_be_deleted.jpg',
                '/storage/media/to_be_kept.jpg',
            ]),
        ]);

        // Admin only sends 'to_be_kept.jpg' in keep_media
        $response = $this->actingAs($this->admin)->put("/gallery/{$galeri->id}", [
            'id_divisi' => $this->divisiA->id,
            'judul' => 'Galeri Tetap Ada',
            'deskripsi' => 'Desc',
            'keep_media' => ['/storage/media/to_be_kept.jpg'],
        ]);

        $response->assertRedirect('/gallery');

        // File to_be_deleted should be removed from disk
        Storage::disk('public')->assertMissing('media/to_be_deleted.jpg');
        // File to_be_kept must remain
        Storage::disk('public')->assertExists('media/to_be_kept.jpg');
    }

    public function test_gallery_db_failure_rolls_back_and_cleans_new_staged_files(): void
    {
        // Mock DB failure by triggering an invalid SQL state or intercepting Konten::create
        $newImg = $this->createValidImage('will_rollback.jpg', 'jpeg');

        // Force DB error by submitting invalid user foreign key
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } catch (\Throwable $e) {}

        // Trigger DB failure by mocking an exception inside transaction
        DB::listen(function ($query) {
            if (str_contains($query->sql, 'insert into `konten`') || str_contains($query->sql, 'insert into "konten"')) {
                throw new \Exception('Simulated database crash during insert');
            }
        });

        $initialFiles = Storage::disk('public')->allFiles('media');

        $response = $this->actingAs($this->admin)->post('/gallery', [
            'id_divisi' => $this->divisiA->id,
            'judul' => 'Galeri Gagal DB',
            'deskripsi' => 'Desc',
            'media' => [$newImg],
        ]);

        $response->assertSessionHasErrors('media');

        // Verify zero new orphan files remain on disk
        $afterFiles = Storage::disk('public')->allFiles('media');
        $this->assertEquals(count($initialFiles), count($afterFiles));
    }

    // ==========================================
    // 7. SPONSOR CONTROLLER TESTS
    // ==========================================

    public function test_sponsor_create_successful(): void
    {
        $logo = $this->createValidImage('sponsor.png', 'png');

        $response = $this->actingAs($this->superadmin)->post('/sponsor', [
            'nama' => 'PT Mitra Solusi',
            'url_image' => $logo,
        ]);

        $response->assertRedirect('/sponsor');
        $this->assertDatabaseHas('sponsor', [
            'nama' => 'PT Mitra Solusi',
        ]);

        $sponsor = Sponsor::where('nama', 'PT Mitra Solusi')->first();
        $this->assertStringStartsWith('data:image/png;base64,', $sponsor->url_image);
    }

    public function test_sponsor_edit_without_new_file_preserves_old_logo(): void
    {
        $sponsor = Sponsor::create([
            'nama' => 'Sponsor Lama',
            'url_image' => 'data:image/png;base64,ORIGINAL_LOGO_DATA_KEEP_ME',
        ]);

        $response = $this->actingAs($this->superadmin)->put("/sponsor/{$sponsor->id}", [
            'nama' => 'Sponsor Nama Baru',
        ]);

        $response->assertRedirect('/sponsor');

        $fresh = Sponsor::find($sponsor->id);
        $this->assertEquals('Sponsor Nama Baru', $fresh->nama);
        $this->assertEquals('data:image/png;base64,ORIGINAL_LOGO_DATA_KEEP_ME', $fresh->url_image);
    }

    public function test_sponsor_rejects_malicious_svg(): void
    {
        $badSvg = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert("XSS")</script></svg>';
        $badFile = $this->createInertFile('evil_logo.svg', $badSvg, 'image/svg+xml');

        $response = $this->actingAs($this->superadmin)->post('/sponsor', [
            'nama' => 'Sponsor Jahat',
            'url_image' => $badFile,
        ]);

        $response->assertSessionHasErrors('url_image');
        $this->assertDatabaseMissing('sponsor', ['nama' => 'Sponsor Jahat']);
    }

    // ==========================================
    // 8. PIMPINAN CONTROLLER TESTS
    // ==========================================

    public function test_pimpinan_create_successful(): void
    {
        $photo = $this->createValidImage('leader.jpg', 'jpeg');

        $response = $this->actingAs($this->superadmin)->post('/pimpinan', [
            'nama' => 'Drs. H. Ahmad Subardjo, M.Pd.',
            'jabatan' => 'Ketua MKKS SMK',
            'foto' => $photo,
            'urutan' => 1,
            'is_active' => 1,
        ]);

        $response->assertRedirect('/pimpinan');
        $this->assertDatabaseHas('pimpinan', [
            'nama' => 'Drs. H. Ahmad Subardjo, M.Pd.',
            'jabatan' => 'Ketua MKKS SMK',
        ]);

        $pimpinan = Pimpinan::where('nama', 'Drs. H. Ahmad Subardjo, M.Pd.')->first();
        $this->assertStringStartsWith('/storage/pimpinan/', $pimpinan->foto);
    }

    public function test_pimpinan_edit_without_photo_preserves_existing_photo(): void
    {
        $pimpinan = Pimpinan::create([
            'nama' => 'Pimpinan 1',
            'jabatan' => 'Sekretaris',
            'foto' => '/storage/pimpinan/old_leader_photo.jpg',
            'urutan' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->superadmin)->put("/pimpinan/{$pimpinan->id}", [
            'nama' => 'Pimpinan 1 Ganti Nama',
            'jabatan' => 'Sekretaris Utama',
        ]);

        $response->assertRedirect('/pimpinan');

        $fresh = Pimpinan::find($pimpinan->id);
        $this->assertEquals('Pimpinan 1 Ganti Nama', $fresh->nama);
        $this->assertEquals('Sekretaris Utama', $fresh->jabatan);
        $this->assertEquals('/storage/pimpinan/old_leader_photo.jpg', $fresh->foto);
    }

    public function test_pimpinan_rejects_executable_disguised_as_photo(): void
    {
        $fakePhoto = $this->createInertFile('virus.jpg', 'MZ' . str_repeat("\0", 50) . 'DOS mode executable');

        $response = $this->actingAs($this->superadmin)->post('/pimpinan', [
            'nama' => 'Fake Leader',
            'jabatan' => 'Hacker',
            'foto' => $fakePhoto,
        ]);

        $response->assertSessionHasErrors('foto');
        $this->assertDatabaseMissing('pimpinan', ['nama' => 'Fake Leader']);
    }

    public function test_regular_admin_forbidden_from_managing_pimpinan(): void
    {
        $photo = $this->createValidImage('leader.jpg', 'jpeg');

        $response = $this->actingAs($this->admin)->post('/pimpinan', [
            'nama' => 'Unauthorized Leader',
            'jabatan' => 'Bendahara',
            'foto' => $photo,
        ]);

        $response->assertStatus(403);
    }
}
