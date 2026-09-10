<?php

namespace Database\Seeders;

use App\Models\Pimpinan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PimpinanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Pimpinan::count() === 0) {
            // Ensure target directory in storage/app/public/pimpinan exists
            $storageDir = storage_path('app/public/pimpinan');
            if (!File::exists($storageDir)) {
                File::makeDirectory($storageDir, 0755, true);
            }

            $kiriSrc = public_path('img/kiri.jpg');
            $kananSrc = public_path('img/kanan.jpg');

            $kiriPath = 'pimpinan/pimpinan_1.jpg';
            $kananPath = 'pimpinan/pimpinan_2.jpg';

            if (File::exists($kiriSrc)) {
                File::copy($kiriSrc, storage_path('app/public/' . $kiriPath));
            }
            if (File::exists($kananSrc)) {
                File::copy($kananSrc, storage_path('app/public/' . $kananPath));
            }

            Pimpinan::create([
                'nama' => 'Pimpinan MKKS 1',
                'jabatan' => 'Ketua MKKS SMK Kabupaten Bekasi',
                'foto' => '/storage/' . $kiriPath,
                'urutan' => 1,
                'is_active' => true,
            ]);

            Pimpinan::create([
                'nama' => 'Pimpinan MKKS 2',
                'jabatan' => 'Wakil Ketua MKKS SMK Kabupaten Bekasi',
                'foto' => '/storage/' . $kananPath,
                'urutan' => 2,
                'is_active' => true,
            ]);
        }
    }
}
