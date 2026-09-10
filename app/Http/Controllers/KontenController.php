<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\Konten;
use App\Models\Sponsor;
use Illuminate\Http\Request;

class KontenController extends Controller
{
    public function index($divisi, $id_divisi) {
        $konten = Konten::where('id', $id_divisi)->get()[0];

        return view('publik.detailkonten', [
            'konten' => $konten,
            'divisiKonten' => $divisi,
            "divisi" => Divisi::all(),
            'sponsor' => Sponsor::all()
        ]);
    }

    public function show($identifier)
    {
        // 1. Legacy ID compatibility: If numeric, lookup by ID and 301 permanently redirect to canonical slug URL
        if (ctype_digit((string) $identifier)) {
            $konten = Konten::with('divisi')->findOrFail($identifier);
            if (!empty($konten->slug)) {
                return redirect()->route('konten.show', ['slug' => $konten->slug], 301);
            }
        } else {
            // 2. Canonical Slug Lookup
            $konten = Konten::with('divisi')->where('slug', $identifier)->firstOrFail();
        }

        $divisiKonten = $konten->divisi ? $konten->divisi->nama_divisi : 'Umum';
        $divisi = Divisi::all();

        return view('publik.detailkonten', compact('konten', 'divisiKonten', 'divisi'));
    }
}
