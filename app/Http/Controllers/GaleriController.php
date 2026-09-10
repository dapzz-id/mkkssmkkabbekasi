<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\Konten;
use App\Models\Sponsor;
use Illuminate\Http\Request;

class GaleriController extends Controller
{
    public function index() {
        // Ambil semua data divisi
        $divisi = Divisi::all();

        // Ambil semua data konten dan urutkan berdasarkan tanggal terbaru
        $konten = Konten::orderBy('tanggal_upload', 'desc')->paginate(11);

        // Kembalikan view dengan data
        return view('publik.galeri', [
            "sponsor" => Sponsor::all(),
            "konten" => $konten,
            "divisi" => $divisi
        ]);
    }



}
