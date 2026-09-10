<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KontenSeeder extends Seeder
{
    public function run()
    {
        $konten = [
            [
                'id_user' => 1,
                'id_divisi' => 1,
                'url_image' => 'img/konten/kurikulum1.jpg',
                'tanggal_upload' => Carbon::now()->subDays(5),
                'judul' => 'Workshop Pengembangan Kurikulum Merdeka',
                'deskripsi' => 'Workshop pengembangan kurikulum merdeka yang diselenggarakan oleh MKKS SMK Kabupaten Bekasi bertujuan untuk meningkatkan pemahaman kepala sekolah tentang implementasi kurikulum merdeka.'
            ],
            [
                'id_user' => 1,
                'id_divisi' => 2,
                'url_image' => 'img/konten/kesiswaan1.jpg',
                'tanggal_upload' => Carbon::now()->subDays(4),
                'judul' => 'Pembinaan OSIS SMK Se-Kabupaten Bekasi',
                'deskripsi' => 'Kegiatan pembinaan OSIS SMK se-Kabupaten Bekasi dilaksanakan untuk meningkatkan kualitas kepemimpinan siswa dan koordinasi antar sekolah.'
            ],
            [
                'id_user' => 1,
                'id_divisi' => 3,
                'url_image' => 'img/konten/sarpras1.jpg',
                'tanggal_upload' => Carbon::now()->subDays(3),
                'judul' => 'Peningkatan Fasilitas Pembelajaran SMK',
                'deskripsi' => 'Program peningkatan fasilitas pembelajaran di SMK se-Kabupaten Bekasi untuk mendukung pembelajaran yang lebih efektif dan berkualitas.'
            ],
            [
                'id_user' => 1,
                'id_divisi' => 4,
                'url_image' => 'img/konten/humas1.jpg',
                'tanggal_upload' => Carbon::now()->subDays(2),
                'judul' => 'Penandatanganan MoU dengan Industri',
                'deskripsi' => 'MKKS SMK Kabupaten Bekasi menjalin kerjasama dengan berbagai industri untuk meningkatkan kualitas lulusan SMK.'
            ],
            [
                'id_user' => 1,
                'id_divisi' => 5,
                'url_image' => 'img/konten/sdm1.jpg',
                'tanggal_upload' => Carbon::now()->subDays(1),
                'judul' => 'Pelatihan Kompetensi Guru SMK',
                'deskripsi' => 'Program pelatihan kompetensi guru SMK se-Kabupaten Bekasi untuk meningkatkan kualitas pembelajaran di sekolah.'
            ],
            [
                'id_user' => 1,
                'id_divisi' => 1,
                'url_image' => 'img/konten/kurikulum2.jpg',
                'tanggal_upload' => Carbon::now(),
                'judul' => 'Evaluasi Implementasi Kurikulum Merdeka',
                'deskripsi' => 'Kegiatan evaluasi implementasi kurikulum merdeka di SMK se-Kabupaten Bekasi untuk mengidentifikasi kendala dan solusi dalam penerapannya.'
            ]
        ];

        DB::table('konten')->insert($konten);
    }
} 
