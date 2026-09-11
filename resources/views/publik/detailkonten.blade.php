@extends('publik.layouts.main')

@php
    $seoTitle = !empty($konten->seo_title) ? $konten->seo_title : $konten->judul;
    $seoDescription = !empty($konten->seo_description) 
        ? $konten->seo_description 
        : \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($konten->deskripsi))), 160);
    $canonicalUrl = route('konten.show', ['slug' => $konten->slug]);

    $ogImage = asset('img/kegiatan/kegiatan1.jpg');
    $mediaUrls = json_decode($konten->url_media, true) ?? [];
    if (!empty($mediaUrls) && is_array($mediaUrls) && !empty($mediaUrls[0])) {
        $firstMedia = $mediaUrls[0];
        if (!\Illuminate\Support\Str::contains($firstMedia, ['.mp4', '.webm', 'youtube', 'youtu.be'])) {
            $ogImage = url($firstMedia);
        }
    }
    $divisiName = $konten->divisi->nama_divisi ?? 'Umum';
@endphp

@section('seo_tags')
    <meta name="description" content="{{ e($seoDescription) }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:title" content="{{ e($seoTitle) }}">
    <meta property="og:description" content="{{ e($seoDescription) }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="MKKS SMK Kab Bekasi">
    <meta name="author" content="MKKS SMK Kab Bekasi">
    <meta http-equiv="Content-Language" content="id">
    <meta name="geo.placename" content="Bekasi">
    <title>{{ e($seoTitle) }} - MKKS SMK KAB BEKASI</title>
@endsection

@section('json_ld')
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "NewsArticle",
        "headline": {!! json_encode($seoTitle, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!},
        "description": {!! json_encode($seoDescription, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!},
        "image": [{!! json_encode($ogImage, JSON_UNESCAPED_SLASHES) !!}],
        "datePublished": {!! json_encode(\Carbon\Carbon::parse($konten->tanggal_upload)->toIso8601String()) !!},
        "dateModified": {!! json_encode(\Carbon\Carbon::parse($konten->tanggal_upload)->toIso8601String()) !!},
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": {!! json_encode($canonicalUrl, JSON_UNESCAPED_SLASHES) !!}
        },
        "author": {
            "@type": "Organization",
            "name": "MKKS SMK Kab Bekasi"
        },
        "publisher": {
            "@type": "Organization",
            "name": "MKKS SMK Kab Bekasi",
            "logo": {
                "@type": "ImageObject",
                "url": "{{ asset('img/logo/logo-mkks.png') }}"
            }
        }
    }
    </script>
@endsection

@push('styles')
<style>
    /* Public View Refined Light Theme Styles */
    .detail-konten-wrapper {
        background-color: #f8fafc;
        min-height: calc(100vh - 80px);
    }
</style>
@endpush

@section('container')
<div class="detail-konten-wrapper py-6 sm:py-8 lg:py-10">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- NAVIGATION & BREADCRUMB HEADER -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-xs sm:text-sm text-slate-500 font-medium">
                <a href="/" class="hover:text-blue-600 transition-colors">Beranda</a>
                <span class="text-slate-300">/</span>
                @if($konten->id_divisi)
                    <a href="/divisi/{{ $konten->id_divisi }}" class="hover:text-blue-600 transition-colors">Divisi {{ $divisiName }}</a>
                    <span class="text-slate-300">/</span>
                @endif
                <span class="text-slate-900 font-semibold truncate max-w-[200px] sm:max-w-xs">{{ $konten->judul }}</span>
            </div>

            <!-- Back Action Button -->
            <div class="flex items-center gap-2 shrink-0">
                @if($konten->id_divisi)
                    <a href="{{ route('beranda') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold transition-all shadow-2xs">
                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Kembali</span>
                    </a>
                @else
                    <a href="/galeri"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold transition-all shadow-2xs">
                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Kembali ke Galeri</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- MAIN DETAIL CARD (Matches Approved Admin Gallery Show Design) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden divide-y divide-slate-100">
            <!-- Content Info Header -->
            <div class="p-6 sm:p-8 space-y-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        Divisi: {{ $divisiName }}
                    </span>
                </div>

                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-900 leading-snug tracking-tight">
                    {{ $konten->judul }}
                </h1>

                <!-- Metadata Info Row -->
                <div class="flex flex-wrap items-center gap-4 text-xs sm:text-sm text-slate-500 pt-1 border-t border-slate-100">
                    <div class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>{{ \Carbon\Carbon::parse($konten->tanggal_upload)->translatedFormat('l, d F Y - H:i') }} WIB</span>
                    </div>
                    <div class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>Pengunggah: <strong>{{ $konten->user->name ?? 'Admin MKKS' }}</strong></span>
                    </div>
                </div>

                <!-- Deskripsi Section -->
                <div class="pt-4 border-t border-slate-100">
                    <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Deskripsi Kegiatan</h2>
                    <div class="prose prose-slate max-w-none text-sm sm:text-base text-slate-700 leading-relaxed space-y-3">
                        {!! nl2br(e($konten->deskripsi)) !!}
                    </div>
                </div>
            </div>

            <!-- Multi Media Preview Section (Matching Gallery Show Style) -->
            @php
                $mediaList = json_decode($konten->url_media, true) ?? [];
            @endphp
            <div class="p-6 sm:p-8 space-y-4 bg-slate-50/50">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Lampiran Media Galeri</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Total {{ count($mediaList) }} foto & video kegiatan (Klik media untuk memperbesar)</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="galleryMediaGrid">
                    @forelse($mediaList as $index => $mUrl)
                        @php
                            $ext = strtolower(pathinfo($mUrl, PATHINFO_EXTENSION));
                            $isVideo = in_array($ext, ['mp4', 'mov', 'avi', 'webm', 'mkv']);
                        @endphp
                        <div class="group relative aspect-video sm:aspect-square bg-slate-100 rounded-2xl border border-slate-200/90 overflow-hidden shadow-2xs flex items-center justify-center transition-all hover:shadow-md hover:border-blue-300 media-card"
                             data-index="{{ $index }}"
                             data-type="{{ $isVideo ? 'video' : 'photo' }}"
                             data-src="{{ $mUrl }}">

                            @if($isVideo)
                                <video src="{{ $mUrl }}"
                                       controls
                                       preload="metadata"
                                       class="w-full h-full object-contain p-1 rounded-xl">
                                </video>
                                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md text-xs font-bold text-white uppercase bg-indigo-600/90 pointer-events-none shadow-xs">
                                    Video
                                </span>
                                <button type="button"
                                        class="btn-open-lightbox absolute top-2 right-2 w-8 h-8 rounded-xl bg-slate-900/60 hover:bg-slate-900 text-white flex items-center justify-center transition-opacity opacity-0 group-hover:opacity-100 cursor-pointer"
                                        data-index="{{ $index }}"
                                        title="Perbesar video">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                    </svg>
                                </button>
                            @else
                                <img src="{{ $mUrl }}"
                                     alt="{{ $konten->judul }} - Foto {{ $index + 1 }}"
                                     loading="lazy"
                                     class="w-full h-full object-contain p-1 cursor-pointer transition-transform duration-300 group-hover:scale-105 btn-open-lightbox"
                                     data-index="{{ $index }}">
                                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md text-xs font-bold text-white uppercase bg-slate-800/80 pointer-events-none shadow-xs">
                                    Foto
                                </span>
                                <button type="button"
                                        class="btn-open-lightbox absolute top-2 right-2 w-8 h-8 rounded-xl bg-slate-900/60 hover:bg-slate-900 text-white flex items-center justify-center transition-opacity opacity-0 group-hover:opacity-100 cursor-pointer"
                                        data-index="{{ $index }}"
                                        title="Perbesar foto">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                    </svg>
                                </button>
                            @endif

                            <span class="absolute bottom-2 right-2 px-2 py-0.5 rounded text-xs font-semibold text-slate-600 bg-white/90 shadow-2xs pointer-events-none">
                                #{{ $index + 1 }}
                            </span>
                        </div>
                    @empty
                        <div class="col-span-full py-8 text-center text-slate-400 text-sm">
                            Tidak ada file media terlampir.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Social Share & Action Footer -->
            <div class="p-6 sm:p-8 bg-white flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-xs sm:text-sm font-semibold text-slate-700">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                    <span>Bagikan Berita Ini:</span>
                </div>

                <div class="flex items-center gap-2">
                    <!-- WhatsApp -->
                    <a href="https://wa.me/?text={{ urlencode($konten->judul . ' - ' . url()->current()) }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-semibold transition-colors"
                       title="Bagikan ke WhatsApp">
                        <i class="bi bi-whatsapp text-sm"></i>
                        <span>WhatsApp</span>
                    </a>

                    <!-- Facebook -->
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-semibold transition-colors"
                       title="Bagikan ke Facebook">
                        <i class="bi bi-facebook text-sm"></i>
                        <span>Facebook</span>
                    </a>

                    <!-- Twitter / X -->
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($konten->judul) }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-semibold transition-colors"
                       title="Bagikan ke X">
                        <i class="bi bi-twitter-x text-sm"></i>
                        <span>X / Twitter</span>
                    </a>

                    <!-- Copy Link -->
                    <button type="button"
                            id="btnCopyShareLink"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors cursor-pointer"
                            title="Salin Tautan">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span id="btnCopyText">Salin Link</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MEDIA LIGHTBOX / LARGE PREVIEW MODAL (Exact match from admin/galeri/show.blade.php) -->
<div id="lightboxModal"
     class="fixed inset-0 bg-slate-950/95 backdrop-blur-md hidden flex flex-col items-center justify-between p-3 sm:p-6 select-none"
     style="z-index: 99999;"
     role="dialog"
     aria-modal="true"
     aria-label="Media Preview">

    <!-- Persistent Floating Close Button -->
    <button type="button"
            id="btnCloseLightbox"
            class="fixed top-3 right-3 sm:top-5 sm:right-6 z-50 w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-slate-900/85 hover:bg-slate-900 active:bg-black text-white border border-white/20 backdrop-blur-md flex items-center justify-center transition-all cursor-pointer shadow-2xl focus:outline-none focus:ring-2 focus:ring-white/80 active:scale-95"
            aria-label="Tutup preview (Esc)"
            title="Tutup (Esc)">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    <!-- Top Bar: Counter & Title -->
    <div class="w-full max-w-6xl flex items-center gap-2.5 text-white z-10 pt-1 sm:pt-0 pr-14 sm:pr-16 min-w-0">
        <span id="lightboxCounter" class="px-2.5 py-1 rounded-full bg-white/10 text-white font-mono text-xs font-semibold shrink-0 border border-white/10">1 / 1</span>
        <span class="text-slate-500 shrink-0">&bull;</span>
        <span id="lightboxTitle" class="truncate text-xs sm:text-sm text-white/90 font-semibold min-w-0 flex-1">{{ $konten->judul }}</span>
    </div>

    <!-- Center Content: Photo or Video with Navigation Buttons -->
    <div id="lightboxMediaWrapper" class="relative w-full max-w-6xl flex-1 flex items-center justify-center my-auto overflow-hidden">
        <!-- Prev Button -->
        <button type="button"
                id="btnPrevLightbox"
                class="absolute left-1 sm:left-4 z-20 w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-black/60 hover:bg-black/90 active:bg-black text-white border border-white/10 flex items-center justify-center transition-all cursor-pointer focus:ring-2 focus:ring-white shadow-xl active:scale-95"
                aria-label="Media sebelumnya (Panah Kiri)">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <!-- Media Display Area -->
        <div class="w-full h-full flex items-center justify-center max-h-[72vh] sm:max-h-[78vh] p-1 sm:p-2 pointer-events-none">
            <img id="lightboxImg"
                 src=""
                 alt="Preview Foto"
                 class="max-h-[70vh] sm:max-h-[76vh] max-w-[92vw] sm:max-w-[85vw] w-auto h-auto object-contain rounded-xl shadow-2xl hidden pointer-events-auto transition-transform duration-200">

            <video id="lightboxVideo"
                   src=""
                   controls
                   playsinline
                   class="max-h-[70vh] sm:max-h-[76vh] max-w-[92vw] sm:max-w-[85vw] w-auto h-auto object-contain rounded-xl shadow-2xl hidden pointer-events-auto">
            </video>
        </div>

        <!-- Next Button -->
        <button type="button"
                id="btnNextLightbox"
                class="absolute right-1 sm:right-4 z-20 w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-black/60 hover:bg-black/90 active:bg-black text-white border border-white/10 flex items-center justify-center transition-all cursor-pointer focus:ring-2 focus:ring-white shadow-xl active:scale-95"
                aria-label="Media selanjutnya (Panah Kanan)">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>

    <!-- Bottom Bar: Keyboard instruction (desktop) & Quick Close Button (mobile) -->
    <div class="w-full max-w-6xl flex items-center justify-between text-slate-400 text-xs z-10 pt-2 pb-1 sm:pb-0">
        <div class="hidden sm:flex items-center gap-4 text-slate-400">
            <span>Tekan <kbd class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-200 border border-slate-700 font-mono">Esc</kbd> atau klik di luar untuk menutup</span>
            <span>&bull;</span>
            <span>Tekan <kbd class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-200 border border-slate-700 font-mono">&larr;</kbd> / <kbd class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-200 border border-slate-700 font-mono">&rarr;</kbd> untuk navigasi</span>
        </div>
        <div class="sm:hidden w-full flex items-center justify-center">
            <button type="button"
                    id="btnMobileCloseLightbox"
                    class="px-5 py-2 rounded-full bg-slate-900/90 hover:bg-slate-900 active:bg-black text-white text-xs font-semibold backdrop-blur-md flex items-center gap-2 border border-white/20 shadow-xl cursor-pointer active:scale-95 transition-transform">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>Tutup (Klik Di Luar)</span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('lightboxModal');
    const imgEl = document.getElementById('lightboxImg');
    const videoEl = document.getElementById('lightboxVideo');
    const counterEl = document.getElementById('lightboxCounter');
    const btnClose = document.getElementById('btnCloseLightbox');
    const btnMobileClose = document.getElementById('btnMobileCloseLightbox');
    const btnPrev = document.getElementById('btnPrevLightbox');
    const btnNext = document.getElementById('btnNextLightbox');

    const mediaItems = [];
    document.querySelectorAll('.media-card').forEach(function(card) {
        mediaItems.push({
            type: card.getAttribute('data-type'),
            src: card.getAttribute('data-src')
        });
    });

    let currentIndex = 0;

    function openLightbox(index) {
        if (!mediaItems.length) return;
        currentIndex = (index + mediaItems.length) % mediaItems.length;
        renderLightboxItem();
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        modal.classList.add('hidden');
        videoEl.pause();
        videoEl.src = '';
        imgEl.src = '';
        document.body.style.overflow = '';
    }

    function renderLightboxItem() {
        const item = mediaItems[currentIndex];
        if (!item) return;

        counterEl.textContent = `${currentIndex + 1} / ${mediaItems.length}`;

        if (item.type === 'video') {
            imgEl.classList.add('hidden');
            imgEl.src = '';
            videoEl.src = item.src;
            videoEl.classList.remove('hidden');
            videoEl.load();
        } else {
            videoEl.pause();
            videoEl.classList.add('hidden');
            videoEl.src = '';
            imgEl.src = item.src;
            imgEl.classList.remove('hidden');
        }

        // Show/hide prev/next if only 1 item
        if (mediaItems.length <= 1) {
            btnPrev.classList.add('hidden');
            btnNext.classList.add('hidden');
        } else {
            btnPrev.classList.remove('hidden');
            btnNext.classList.remove('hidden');
        }
    }

    function showNext() {
        if (mediaItems.length <= 1) return;
        currentIndex = (currentIndex + 1) % mediaItems.length;
        renderLightboxItem();
    }

    function showPrev() {
        if (mediaItems.length <= 1) return;
        currentIndex = (currentIndex - 1 + mediaItems.length) % mediaItems.length;
        renderLightboxItem();
    }

    // Attach click to triggers
    document.querySelectorAll('.btn-open-lightbox').forEach(function(trigger) {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const idx = parseInt(this.getAttribute('data-index') || '0', 10);
            openLightbox(idx);
        });
    });

    if (btnClose) btnClose.addEventListener('click', closeLightbox);
    if (btnMobileClose) btnMobileClose.addEventListener('click', closeLightbox);
    if (btnNext) btnNext.addEventListener('click', function(e) { e.stopPropagation(); showNext(); });
    if (btnPrev) btnPrev.addEventListener('click', function(e) { e.stopPropagation(); showPrev(); });

    // Click outside media to close
    modal.addEventListener('click', function(e) {
        if (e.target.closest('#lightboxImg') ||
            e.target.closest('#lightboxVideo') ||
            e.target.closest('#btnPrevLightbox') ||
            e.target.closest('#btnNextLightbox') ||
            e.target.closest('#lightboxCounter') ||
            e.target.closest('#lightboxTitle')) {
            return;
        }
        closeLightbox();
    });

    // Touch Swipe Gestures for mobile
    let touchStartX = 0;
    let touchStartY = 0;
    let touchEndX = 0;
    let touchEndY = 0;

    modal.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
        touchStartY = e.changedTouches[0].screenY;
    }, { passive: true });

    modal.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        touchEndY = e.changedTouches[0].screenY;
        handleSwipe();
    }, { passive: true });

    function handleSwipe() {
        const diffX = touchEndX - touchStartX;
        const diffY = touchEndY - touchStartY;
        const absX = Math.abs(diffX);
        const absY = Math.abs(diffY);

        if (absX > 45 && absX > absY) {
            if (diffX < 0) {
                showNext();
            } else {
                showPrev();
            }
        } else if (diffY > 70 && absY > absX) {
            closeLightbox();
        }
    }

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (modal.classList.contains('hidden')) return;

        if (e.key === 'Escape') {
            closeLightbox();
        } else if (e.key === 'ArrowRight') {
            showNext();
        } else if (e.key === 'ArrowLeft') {
            showPrev();
        }
    });

    // Copy link helper
    const btnCopy = document.getElementById('btnCopyShareLink');
    const textCopy = document.getElementById('btnCopyText');
    if (btnCopy) {
        btnCopy.addEventListener('click', function() {
            navigator.clipboard.writeText(window.location.href).then(function() {
                textCopy.textContent = 'Tersalin!';
                btnCopy.classList.add('bg-emerald-100', 'text-emerald-800');
                setTimeout(function() {
                    textCopy.textContent = 'Salin Link';
                    btnCopy.classList.remove('bg-emerald-100', 'text-emerald-800');
                }, 2000);
            });
        });
    }
});
</script>
@endpush
