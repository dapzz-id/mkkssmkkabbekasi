<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\Konten;
use App\Models\Sponsor;
use Illuminate\Http\Request;

class KontenController extends Controller
{
    public function index($divisi, $id_konten) {
        $konten = Konten::whereIdentifier($id_konten)->firstOrFail();

        return view('publik.detailkonten', [
            'konten' => $konten,
            'divisiKonten' => $divisi,
            "divisi" => Divisi::all(),
            'sponsor' => Sponsor::all()
        ]);
    }

    public function show($identifier)
    {
        if (preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', (string) $identifier)) {
            // UUID compatibility: lookup by UUID and 301 redirect to canonical slug URL
            $konten = Konten::with('divisi')->where('uuid', $identifier)->firstOrFail();
            if (!empty($konten->slug)) {
                return redirect()->route('konten.show', ['slug' => $konten->slug], 301);
            }
        } else {
            // Canonical Slug Lookup
            $konten = Konten::with('divisi')->where('slug', $identifier)->firstOrFail();
        }

        $divisiKonten = $konten->divisi ? $konten->divisi->nama_divisi : 'Umum';
        $divisi = Divisi::all();

        return view('publik.detailkonten', compact('konten', 'divisiKonten', 'divisi'));
    }
}
