<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\Sponsor;
use Illuminate\Http\Request;

class DivisiController extends Controller
{

    public function index() {
        $divisiKonten = Divisi::with('konten')->get();
        $divisi = Divisi::all();
        $sponsor = Sponsor::all();
        return view('publik.alldivisi', [
            "divisikonten" => $divisiKonten,
            "sponsor" => $sponsor,
            "divisi" => $divisi
        ]);
    }
    public function show($divisi) {
        $divisiKonten = Divisi::where('uuid', $divisi)
            ->orWhere('nama_divisi', $divisi)
            ->with(['konten' => function($query) {
                $query->orderBy('tanggal_upload', 'desc');
            }])
            ->first();

        $divisi = Divisi::all();
        $sponsor = Sponsor::all();

        return view('publik.divisi', [
            "divisiKonten" => $divisiKonten,
            "sponsor" => $sponsor,
            "divisi" => $divisi
        ]);
    }
}
