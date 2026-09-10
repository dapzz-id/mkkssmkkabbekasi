@extends('publik.layouts.main')

@section('container')
    <style>
        /* Gambar/video Berita Utama */
        .main-news-img {
            width: 100%;
            height: 260px;
            /* Tinggi fix */
            object-fit: cover;
            /* Potong biar proporsional */
            border-radius: 8px;
        }

        /* Gambar/video Berita Kecil */
        .news-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 6px;
        }

        /* Carousel */
        .carousel-img {
            width: 100%;
            height: 380px;
            object-fit: cover;
            border-radius: 12px;
        }

        @media (max-width: 768px) {
            .carousel-img {
                height: 250px;
            }

            .main-news-img {
                height: 200px;
            }

            .news-img {
                height: 150px;
            }
        }

        /* Biar caption tidak hilang di mobile */
        .carousel-caption {
            display: block !important;
            bottom: 15px !important;
            z-index: 10;
            padding: 15px 20px;
            background: rgba(0, 0, 0, 0.45);
            /* biar kelihatan */
            border-radius: 10px;
            width: 95%;
            left: 50%;
            transform: translateX(-50%);
        }

        /* Pastikan video/image tidak menimpa caption */
        .carousel-item {
            position: relative;
        }

        .carousel-item video,
        .carousel-item img {
            z-index: 1;
            position: relative;
        }
    </style>

    <div class="py-4 container-fluid">
        <div class="row">
            <!-- Profile Section - Left Column -->
            <div class="mb-4 col-12 col-lg-8 mb-lg-0">
                <div class="profile-section">
                    <!-- Images -->
                    <div class="text-center">
                        <h1 class="mb-3 fw-bold">Kolaborasi Dan Berkembang Bersama</h1>
                        
                        <!-- Dynamic Pimpinan Section (Unlimited, Responsive & Balanced) -->
                        <div class="pimpinan-container mb-4">
                            <div class="pimpinan-grid">
                                @forelse ($pimpinan as $item)
                                    <div class="pimpinan-card">
                                        <div class="pimpinan-image-wrapper">
                                            <img data-src="{{ $item->foto_url }}"
                                                 src="{{ $item->foto_url }}"
                                                 alt="{{ $item->nama }} - {{ $item->jabatan }}"
                                                 class="lazyload pimpinan-image"
                                                 loading="lazy">
                                        </div>
                                        <div class="pimpinan-info">
                                            <h3 class="pimpinan-name">{{ $item->nama }}</h3>
                                            <p class="pimpinan-position">{{ $item->jabatan }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="pimpinan-empty text-muted py-3">
                                        <p class="mb-0">Informasi pimpinan MKKS akan segera diperbarui.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <p class="mb-4 text-muted">Meningkatkan kualitas komunikasi, kompetensi, dan kolaborasi kepala
                            sekolah dengan mewujudkan manajerial yang baik untuk menghasilkan lulusan siap karier.</p>
                    </div>
                </div>
            </div>

            <!-- Calendar Section - Right Column -->
            <div class="col-12 col-lg-4">
                <div class="calendar-section">
                    <h2 class="mb-4 text-center">Jadwal Kegiatan</h2>
                    <div class="calendar-wrapper">
                        <div id="calendar" class="bg-white rounded-lg shadow">
                            <div class="mb-3 calendar-header d-flex justify-content-between align-items-center">
                                <button id="prev" class="btn btn-link text-dark">&lt;</button>
                                <h2 id="monthYear" class="mb-0 h5"></h2>
                                <button id="next" class="btn btn-link text-dark">&gt;</button>
                            </div>
                            <div class="mb-2 calendar-days">
                                <div class="text-center row g-0">
                                    <div class="col">Min</div>
                                    <div class="col">Sen</div>
                                    <div class="col">Sel</div>
                                    <div class="col">Rab</div>
                                    <div class="col">Kam</div>
                                    <div class="col">Jum</div>
                                    <div class="col">Sab</div>
                                </div>
                            </div>
                            <div id="dates">
                                <!-- Dates will be generated by JavaScript -->
                            </div>
                        </div>
                    </div>
                    <p style="color: red; width: 100%; text-align: center; margin-top: 0.9rem; font-size: 0.8rem;">*Note:
                        Klik tanggal yang berwarna biru untuk mengetahui acara/kegiatan yang (telah, sedang, akan) berjalan
                        di MKKS Bekasi</p>
                </div>
            </div>
        </div>

        <div class="mt-5 mb-5">
            <div id="carouselKegiatan" class="carousel slide" data-bs-ride="carousel">
                <!-- Indicators/dots -->
                <div class="carousel-indicators">
                    @foreach ($kontenTerbaru->take(7) as $index => $konten)
                        <button type="button" data-bs-target="#carouselKegiatan" data-bs-slide-to="{{ $index }}"
                            class="{{ $index === 0 ? 'active' : '' }}">
                        </button>
                    @endforeach
                </div>

                <!-- The slideshow/carousel -->
                <div class="carousel-inner">
                    @foreach ($kontenTerbaru->take(7) as $index => $konten)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <div class="carousel-image-container">
                                @php
                                    $media = json_decode($konten->url_media, true);
                                    $firstMedia = $media[0] ?? null;
                                    $extension = $firstMedia
                                        ? strtolower(pathinfo($firstMedia, PATHINFO_EXTENSION))
                                        : '';
                                @endphp
                                @if ($firstMedia)
                                    @if (in_array($extension, ['mp4', 'mov', 'avi']))
                                        <video src="{{ $firstMedia }}" class="d-block w-100 carousel-img" controls
                                            muted></video>
                                    @else
                                        <img data-src="{{ $firstMedia }}" class="lazyload d-block w-100 carousel-img"
                                            alt="{{ $konten->judul }}">
                                    @endif
                                @else
                                    <div class="d-block w-100 carousel-img bg-gray-200"></div>
                                @endif
                            </div>
                            <div class="carousel-caption">
                                <p>{!! strip_tags(Str::limit($konten->deskripsi, 197, '...'), '<b><i><u><sub><sup><q><ruby><rt><rp>') !!}</p>
                            </div>
                        </div>
                    @endforeach
                </div>



                <!-- Left and right controls/icons -->
                <button class="carousel-control-prev btn-prev"
                    style="height:max-content !important; margin-top: auto; margin-bottom: auto; border-top-right-radius: 0.75rem; border-bottom-right-radius: 0.75rem;"
                    type="button" data-bs-target="#carouselKegiatan" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next btn-next"
                    style="height:max-content !important; margin-top: auto; margin-bottom: auto; border-top-left-radius: 0.75rem; border-bottom-left-radius: 0.75rem;"
                    type="button" data-bs-target="#carouselKegiatan" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
        </div>

        <!-- Tambahkan section Berita Terbaru -->
        <div class="mt-5">
            <h2 class="mb-4 text-center">Berita Terbaru</h2>
            <div class="row">
                @foreach ($kontenTerbaru as $index => $konten)
                    @if ($index === 0)
                        <!-- Berita Utama - Konten Paling Baru -->
                        <div class="mb-4 col-12">
                            <div class="card main-news">
                                <div class="row g-0">
                                    <div class="col-md-6">
                                        @php
                                            $media = json_decode($konten->url_media, true);
                                            $firstMedia = $media[0] ?? null;
                                            $extension = $firstMedia
                                                ? strtolower(pathinfo($firstMedia, PATHINFO_EXTENSION))
                                                : '';
                                        @endphp
                                        @if ($firstMedia)
                                            @if (in_array($extension, ['mp4', 'mov', 'avi']))
                                                <video src="{{ $firstMedia }}"
                                                    class="img-fluid rounded-start main-news-img" controls muted></video>
                                            @else
                                                <img data-src="{{ $firstMedia }}"
                                                    class="lazyload img-fluid rounded-start main-news-img"
                                                    alt="{{ $konten->judul }}">
                                            @endif
                                        @else
                                            <div class="img-fluid rounded-start main-news-img bg-gray-200"></div>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <span class="badge bg-primary">{{ $konten->divisi->nama_divisi }}</span>
                                                <small
                                                    class="text-muted ms-2">{{ \Carbon\Carbon::parse($konten->tanggal_upload)->locale('id')->diffForHumans() }}</small>
                                            </div>
                                            <h3 class="card-title">{{ $konten->judul }}</h3>
                                            <p class="card-text">{!! strip_tags(
                                                Str::limit($konten->deskripsi, 197, '...'),
                                                '<b><i><u><br><mark><sub><sup><ul><ol><li><q><ruby><rt><rp>',
                                            ) !!}</p>
                                            <a href="{{ route('konten.show', $konten->slug ?? $konten->id) }}"
                                                class="btn btn-outline-primary">Baca Selengkapnya</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($index <= 6)
                        <!-- 6 Berita Lainnya -->
                        <div class="mb-4 col-md-4">
                            <div class="card h-100">
                                @php
                                    $media = json_decode($konten->url_media, true);
                                    $firstMedia = $media[0] ?? null;
                                    $extension = $firstMedia
                                        ? strtolower(pathinfo($firstMedia, PATHINFO_EXTENSION))
                                        : '';
                                @endphp
                                @if ($firstMedia)
                                    @if (in_array($extension, ['mp4', 'mov', 'avi']))
                                        <video src="{{ $firstMedia }}" class="card-img-top news-img" controls
                                            muted></video>
                                    @else
                                        <img data-src="{{ $firstMedia }}" class="lazyload card-img-top news-img"
                                            alt="{{ $konten->judul }}">
                                    @endif
                                @else
                                    <div class="card-img-top news-img bg-gray-200"></div>
                                @endif
                                <div class="card-body">
                                    <div class="mb-2">
                                        <span class="badge bg-primary">{{ $konten->divisi->nama_divisi }}</span>
                                        <small
                                            class="text-muted ms-2">{{ \Carbon\Carbon::parse($konten->tanggal_upload)->locale('id')->diffForHumans() }}</small>
                                    </div>
                                    <h5 class="card-title">{{ $konten->judul }}</h5>
                                    <p class="card-text">{!! strip_tags(
                                        Str::limit($konten->deskripsi, 97, '...'),
                                        '<b><i><u><mark><sub><sup><ul><ol><li><q><ruby><rt><rp>',
                                    ) !!}</p>
                                    <a href="{{ route('konten.show', $konten->slug ?? $konten->id) }}"
                                        class="btn btn-outline-primary btn-sm">Baca Selengkapnya</a>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- Modal Events -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">Detail Acara</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="eventDetails">
                    <!-- Event details will be filled by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @php
        $events = DB::table('calendar')->select('event_name', 'event_date')->get();
        $eventsArray = [];
        foreach ($events as $event) {
            $eventsArray[$event->event_date][] = $event->event_name;
        }
    @endphp

    <!-- Hapus script lama dan ganti dengan yang baru -->
    <script>
        const events = @json($eventsArray);
    </script>
    <script src="{{ asset('js/jadwal.js') }}"></script>
@endsection