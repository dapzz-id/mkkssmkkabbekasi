@extends('publik.layouts.main')

@section('container')
<section class="divisi-section">
    <div class="container container-spacing">
        <h2 class="mb-5 text-center">Divisi</h2>
        <div class="row g-4">
            @foreach ($divisikonten as $item)
            <div class="col-md-6">
                <div class="shadow-sm card h-100">
                    @if ($item->konten->isNotEmpty())
                        @php
                            $firstKonten = $item->konten->first();
                        @endphp
                        <img data-src="{{ $firstKonten->url_image }}" class="lazyload card-img-top" alt="{{ $item->nama_divisi }}" style="height: 250px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title">{{ $item->nama_divisi }}</h5>
                            <p class="card-text">{{ $firstKonten->judul }}</p>
                            <a href="{{ route('konten.show', $firstKonten->slug ?? $firstKonten->id) }}" class="btn btn-outline-primary btn-selengkapnya">Selengkapnya</a>
                        </div>
                    @else
                        <div class="card-body">
                            <h5 class="card-title">{{ $item->nama_divisi }}</h5>
                            <p class="card-text">Konten tidak tersedia.</p>
                            <a href="/divisi/{{ $item->nama_divisi }}" class="btn btn-outline-primary btn-selengkapnya">Selengkapnya</a>
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
