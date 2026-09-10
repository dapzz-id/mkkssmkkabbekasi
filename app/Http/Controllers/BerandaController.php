<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\Konten;
use App\Models\Pimpinan;
use App\Models\Sponsor;
use Illuminate\Http\Request;

class BerandaController extends Controller
{
    public function index() {
        $sponsor = Sponsor::all();
        $divisi = Divisi::all();
        $pimpinan = Pimpinan::active()->ordered()->get();
        $kontenTerbaru = Konten::with(['user', 'divisi'])
                        ->orderBy('tanggal_upload', 'desc')
                        ->take(7)  // Ambil 7 konten terbaru
                        ->get();

        return view('publik.beranda', [
            "sponsor" => $sponsor,
            "divisi" => $divisi,
            "pimpinan" => $pimpinan,
            "kontenTerbaru" => $kontenTerbaru
        ]);
    }

    public function selengkapnya() {
        $sponsor = Sponsor::all();
        return view('publik.selengkapnya', [
            "sponsor" => $sponsor,
            "divisi" => Divisi::all()
        ]);
    }

    public function jadwal() {
        $divisi = Divisi::all();
        return view('publik.jadwal', [
            "divisi" => $divisi
        ]);
    }
}
