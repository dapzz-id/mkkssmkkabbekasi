<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Konten;
use App\Models\User;
use App\Models\Divisi;
use Carbon\Carbon;
use Illuminate\Support\Str;

class KontenSeeder extends Seeder
{
    public function run()
    {
        $firstUser = User::first();
        $userUuid = $firstUser ? $firstUser->uuid : null;

        $divisiMap = Divisi::all()->keyBy('nama_divisi');

        $kontenData = [
            [
                'nama_divisi' => 'Kurikulum',
                'media' => 'img/konten/kurikulum1.jpg',
                'tanggal_upload' => Carbon::now()->subDays(5),
                'judul' => 'Workshop Pengembangan Kurikulum Merdeka',
                'deskripsi' => 'Workshop pengembangan kurikulum merdeka yang diselenggarakan oleh MKKS SMK Kabupaten Bekasi bertujuan untuk meningkatkan pemahaman kepala sekolah tentang implementasi kurikulum merdeka.'
            ],
            [
                'nama_divisi' => 'Kesiswaan',
                'media' => 'img/konten/kesiswaan1.jpg',
                'tanggal_upload' => Carbon::now()->subDays(4),
                'judul' => 'Pembinaan OSIS SMK Se-Kabupaten Bekasi',
                'deskripsi' => 'Kegiatan pembinaan OSIS SMK se-Kabupaten Bekasi dilaksanakan untuk meningkatkan kualitas kepemimpinan siswa dan koordinasi antar sekolah.'
            ],
            [
                'nama_divisi' => 'Hubin',
                'media' => 'img/konten/sarpras1.jpg',
                'tanggal_upload' => Carbon::now()->subDays(3),
                'judul' => 'Peningkatan Fasilitas Pembelajaran SMK',
                'deskripsi' => 'Program peningkatan fasilitas pembelajaran di SMK se-Kabupaten Bekasi untuk mendukung pembelajaran yang lebih efektif dan berkualitas.'
            ],
            [
                'nama_divisi' => 'Humas',
                'media' => 'img/konten/humas1.jpg',
                'tanggal_upload' => Carbon::now()->subDays(2),
                'judul' => 'Penandatanganan MoU dengan Industri',
                'deskripsi' => 'MKKS SMK Kabupaten Bekasi menjalin kerjasama dengan berbagai industri untuk meningkatkan kualitas lulusan SMK.'
            ],
            [
                'nama_divisi' => 'Korwil',
                'media' => 'img/konten/sdm1.jpg',
                'tanggal_upload' => Carbon::now()->subDays(1),
                'judul' => 'Pelatihan Kompetensi Guru SMK',
                'deskripsi' => 'Program pelatihan kompetensi guru SMK se-Kabupaten Bekasi untuk meningkatkan kualitas pembelajaran di sekolah.'
            ],
            [
                'nama_divisi' => 'Kurikulum',
                'media' => 'img/konten/kurikulum2.jpg',
                'tanggal_upload' => Carbon::now(),
                'judul' => 'Evaluasi Implementasi Kurikulum Merdeka',
                'deskripsi' => 'Kegiatan evaluasi implementasi kurikulum merdeka di SMK se-Kabupaten Bekasi untuk mengidentifikasi kendala dan solusi dalam penerapannya.'
            ]
        ];

        foreach ($kontenData as $data) {
            $divisiUuid = isset($divisiMap[$data['nama_divisi']]) ? $divisiMap[$data['nama_divisi']]->uuid : null;
            Konten::firstOrCreate(
                ['judul' => $data['judul']],
                [
                    'user_uuid' => $userUuid,
                    'divisi_uuid' => $divisiUuid,
                    'url_media' => json_encode(['/storage/' . $data['media']]),
                    'tanggal_upload' => $data['tanggal_upload'],
                    'deskripsi' => $data['deskripsi'],
                    'slug' => Str::slug($data['judul']),
                ]
            );
        }
    }
} 
