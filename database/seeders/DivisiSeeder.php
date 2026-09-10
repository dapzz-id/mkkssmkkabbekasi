<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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

        DB::table('divisi')->insert($divisi);
    }
} 
