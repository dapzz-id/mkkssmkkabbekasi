<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            DivisiSeeder::class
        ]);

        $ictDivisi = \App\Models\Divisi::where('nama_divisi', 'ICT')->first();

        User::factory()->create([
            'name' => 'Guruh Wijanarko, S.T',
            'role' => 'superadmin',
            'username' => 'smktelekomunikasitelesandi',
            'divisi_uuid' => $ictDivisi?->uuid,
            'alamat' => 'Mekarsari Raya Jl. KH. Mochammad - Mekarsari Tambun Selatan, Bekasi 17510',
            'password' => Hash::make('smdevcreative@smktelesandi'),
            'email' => 'smktelesandi@gmail.com'
        ]);
    }
}
