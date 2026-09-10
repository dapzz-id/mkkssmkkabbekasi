@extends('publik.layouts.main')

@section('container')

<style>
    .featured-img {
        width: 100%;
        height: 300px;
        object-fit: cover;
        border-radius: 6px;
    }

    .card-img-top {
        height: 200px;
        object-fit: cover;
        border-radius: 6px;
    }
</style>

<div class="galeri-container">
    <div class="container container-spacing">
        <h2 class="mb-4 text-center">Galeri Berita</h2>

        <div class="row g-4">

            @foreach($konten as $index => $item)

                @php
                    // FIX: url_media bisa berupa JSON list
                    $mediaList = json_decode($item->url_media, true);
                    $firstMedia = is_array($mediaList) ? ($mediaList[0] ?? null) : $item->url_media;

                    // Ekstensi file
                    $ext = strtolower(pathinfo($firstMedia, PATHINFO_EXTENSION));
                @endphp

                {{-- =========================== --}}
                {{-- BERITA UTAMA (INDEX 0)       --}}
                {{-- =========================== --}}
                @if($index === 0)
                    <div class="col-12">
                        <div class="card shadow-sm featured-news">
                            <div class="row g-0">
                                
                                {{-- Media --}}
                                <div class="col-md-6">
                                    <div class="position-relative">

                                        @if(in_array($ext, ['mp4', 'mov', 'avi']))
                                            <video src="{{ $firstMedia }}"
                                                class="featured-img w-100"
                                                controls muted>
                                            </video>
                                        @else
                                            <img data-src="{{ $firstMedia }}"
                                                 class="lazyload featured-img"
                                                 alt="{{ $item->judul }}">
                                        @endif

                                        <div class="position-absolute top-0 start-0 m-3">
                                            <span class="badge bg-primary">
                                                {{ $item->divisi->nama_divisi }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Deskripsi --}}
                                <div class="col-md-6">
                                    <div class="card-body d-flex flex-column py-4">

                                        <small class="text-muted mb-2">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            {{ \Carbon\Carbon::parse($item->tanggal_upload)->locale('id')->isoFormat('D MMMM Y') }}
                                        </small>

                                        <h3 class="card-title">{{ $item->judul }}</h3>

                                        <p class="card-text flex-grow-1">
                                            {!! strip_tags(Str::limit($item->deskripsi, 197, '...'),
                                                '<b><i><u><br><mark><sub><sup><ul><ol><li><q><ruby><rt><rp>') !!}
                                        </p>

                                        <a href="{{ route('konten.show', $item->slug ?? $item->id) }}"
                                           class="btn btn-primary mt-auto">
                                            Baca Selengkapnya
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                {{-- =========================== --}}
                {{-- BERITA LAINNYA               --}}
                {{-- =========================== --}}
                @else
                    <div class="col-md-6 col-lg-4">
                        <div class="card shadow-sm h-100">

                            <div class="position-relative">

                                @if(in_array($ext, ['mp4', 'mov', 'avi']))
                                    <video src="{{ $firstMedia }}"
                                        class="card-img-top"
                                        controls muted>
                                    </video>
                                @else
                                    <img data-src="{{ $firstMedia }}"
                                         class="lazyload card-img-top"
                                         alt="{{ $item->judul }}">
                                @endif

                                <div class="position-absolute top-0 start-0 m-2">
                                    <span class="badge bg-primary">
                                        {{ $item->divisi->nama_divisi }}
                                    </span>
                                </div>
                            </div>

                            <div class="card-body">
                                <small class="text-muted d-block mb-2">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    {{ \Carbon\Carbon::parse($item->tanggal_upload)->locale('id')->isoFormat('D MMMM Y') }}
                                </small>

                                <h5 class="card-title">{{ $item->judul }}</h5>

                                <p class="card-text text-muted">
                                    {!! strip_tags(Str::limit($item->deskripsi, 97, '...'),
                                        '<b><i><u><br><mark><sub><sup><ul><ol><li><q><ruby><rt><rp>') !!}
                                </p>
                            </div>

                            <div class="card-footer bg-white border-0">
                                <a href="{{ route('konten.show', $item->slug ?? $item->id) }}"
                                   class="btn btn-outline-primary w-100">
                                    Baca Selengkapnya
                                </a>
                            </div>

                        </div>
                    </div>
                @endif

            @endforeach
        </div>

        {{-- PAGINATION --}}
        <div class="mt-5 d-flex justify-content-center">
            {{ $konten->links() }}
        </div>

    </div>
</div>

@endsection
