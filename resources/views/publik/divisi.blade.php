@extends('publik.layouts.main')

@section('container')
<section class="divisi-section">

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

    <div class="container container-spacing">
        <div class="mb-5 text-center">
            <h2 class="section-title">Divisi {{ $divisiKonten->nama_divisi }}</h2>
            <div class="section-divider"></div>
        </div>

        <div class="row g-4">

            @foreach ($divisiKonten->konten as $index => $konten)

                @php
                    // Ambil media pertama
                    $mediaList = json_decode($konten->url_media, true);
                    $firstMedia = is_array($mediaList) ? ($mediaList[0] ?? null) : $konten->url_media;

                    // Cek ekstensi
                    $ext = strtolower(pathinfo($firstMedia, PATHINFO_EXTENSION));

                    // Apakah video?
                    $isVideo = in_array($ext, ['mp4','mov','avi','mkv','webm']);
                @endphp

                {{-- ======================================== --}}
                {{-- KONTEN PERTAMA (FULL WIDTH) --}}
                {{-- ======================================== --}}
                @if($index === 0)
                    <div class="col-12 mb-4">
                        <div class="card featured-news">
                            <div class="row g-0">

                                {{-- Media --}}
                                <div class="col-md-6">
                                    <div class="position-relative">

                                        @if($isVideo)
                                            <video src="{{ $firstMedia }}"
                                                class="featured-img w-100"
                                                controls muted>
                                            </video>
                                        @else
                                            <img data-src="{{ $firstMedia }}"
                                                class="lazyload featured-img"
                                                alt="{{ $konten->judul }}">
                                        @endif

                                    </div>
                                </div>

                                {{-- Konten --}}
                                <div class="col-md-6">
                                    <div class="card-body d-flex flex-column h-100 py-4">

                                        <div class="mb-2">
                                            <span class="badge bg-primary">{{ $divisiKonten->nama_divisi }}</span>
                                            <small class="text-muted ms-2">
                                                {{ \Carbon\Carbon::parse($konten->tanggal_upload)->locale('id')->diffForHumans() }}
                                            </small>
                                        </div>

                                        <h3 class="card-title">{{ $konten->judul }}</h3>

                                        <p class="card-text flex-grow-1">
                                            {!! strip_tags(Str::limit($konten->deskripsi, 197, '...'),
                                                '<b><i><u><mark><br><sub><sup><ul><ol><li><q><ruby><rt><rp>') !!}
                                        </p>

                                        <a href="{{ route('konten.show', $konten->slug ?? $konten->id) }}"
                                           class="btn btn-primary mt-auto">
                                            Selengkapnya
                                        </a>

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                {{-- ======================================== --}}
                {{-- KONTEN LIST (GRID) --}}
                {{-- ======================================== --}}
                @else
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 article-card">

                            {{-- Media --}}
                            <div class="card-img-wrapper">

                                @if($isVideo)
                                    <video src="{{ $firstMedia }}"
                                        class="card-img-top"
                                        controls muted>
                                    </video>
                                @else
                                    <img data-src="{{ $firstMedia }}"
                                        class="lazyload card-img-top"
                                        alt="{{ $konten->judul }}">
                                @endif

                            </div>

                            {{-- Konten --}}
                            <div class="card-body d-flex flex-column">

                                <div class="mb-2">
                                    <span class="badge bg-primary">{{ $divisiKonten->nama_divisi }}</span>
                                    <small class="text-muted ms-2">
                                        {{ \Carbon\Carbon::parse($konten->tanggal_upload)->locale('id')->diffForHumans() }}
                                    </small>
                                </div>

                                <h5 class="card-title">{{ $konten->judul }}</h5>

                                <p class="card-text flex-grow-1">
                                    {!! strip_tags(Str::limit($konten->deskripsi, 147, '...'),
                                        '<b><i><u><br><mark><sub><sup><ul><ol><li><q><ruby><rt><rp>') !!}
                                </p>

                                <a href="{{ route('konten.show', $konten->slug ?? $konten->id) }}"
                                   class="btn btn-outline-primary mt-auto">
                                   Selengkapnya
                                </a>

                            </div>
                        </div>
                    </div>
                @endif

            @endforeach

        </div>
    </div>

</section>
@endsection
