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

        User::factory()->create([
            'name' => 'Guruh Wijanarko, S.T',
            'role' => 'superadmin',
            'username' => 'smktelekomunikasitelesandi',
            'id_divisi' => 6,
            'alamat' => 'Mekarsari Raya Jl. KH. Mochammad - Mekarsari Tambun Selatan, Bekasi 17510',
            'password' => Hash::make('smdevcreative@smktelesandi'),
            'email' => 'smktelesandi@gmail.com'
        ]);
    }
}
