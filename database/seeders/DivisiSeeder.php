<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Divisi;

class DivisiSeeder extends Seeder
{
    public function run()
    {
        $divisi = [
            ['nama_divisi' => 'Kurikulum'],
            ['nama_divisi' => 'Kesiswaan'],
            ['nama_divisi' => 'Hubin'],
            ['nama_divisi' => 'Humas'],
            ['nama_divisi' => 'Korwil'],
            ['nama_divisi' => 'ICT']
        ];

        foreach ($divisi as $item) {
            Divisi::firstOrCreate(
                ['nama_divisi' => $item['nama_divisi']],
                $item
            );
        }
    }
} 
