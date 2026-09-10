<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KegiatanImageSeeder extends Seeder
{
    public function run()
    {
        $images = [
            [
                'title' => 'Kegiatan 1',
                'image' => 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('img/kegiatan/kegiatan1.jpg'))),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Kegiatan 2',
                'image' => 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('img/kegiatan/kegiatan2.jpg'))),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Kegiatan 3',
                'image' => 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('img/kegiatan/kegiatan3.jpg'))),
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        DB::table('kegiatan_images')->insert($images);
    }
}
