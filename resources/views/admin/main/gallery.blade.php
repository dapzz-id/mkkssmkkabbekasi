@extends('admin.layouts.main')

@section('container')
    <div class="p-4 sm:p-6 lg:p-8">
        <!-- TOP PAGE HEADER (Matches Approved Reference Screenshot) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3.5">
                <!-- Blue Icon Badge -->
                <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight leading-tight">
                        Gallery
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5 font-medium">
                        Kelola data galeri kegiatan MKKS SMK Kab Bekasi
                    </p>
                </div>
            </div>

            <!-- Primary Action Button: + Tambah Galeri -->
            <a href="/galeri-kelola/tambah"
               id="btn-addGallery"
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold rounded-xl shadow-xs transition-all cursor-pointer shrink-0 focus:ring-2 focus:ring-blue-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Galeri</span>
            </a>
        </div>

        <!-- Search & Filter Toolbar with Server-Side Autocomplete and Debounce -->
        @php
            $hasGalleryFilters = request()->filled('search') || (request()->filled('media') && request('media') !== 'all') || (request()->filled('date_order') && request('date_order') !== 'all');
        @endphp
        <x-admin.search-filter
            action="/gallery"
            suggestionsUrl="/gallery/suggestions"
            placeholder="Cari judul, konten, atau divisi..."
            searchName="search"
            :hasActiveFilters="$hasGalleryFilters"
            resetUrl="/gallery">

            <!-- Filter: Media -->
            <div class="relative w-full sm:w-44 shrink-0">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                </div>
                <select name="media"
                        id="filterMediaType"
                        onchange="this.form.submit()"
                        class="w-full pl-9 pr-8 py-2.5 bg-white border border-slate-200/90 rounded-xl text-xs sm:text-sm font-medium text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-2xs appearance-none cursor-pointer min-h-[44px]">
                    <option value="all" {{ request('media', 'all') === 'all' ? 'selected' : '' }}>Semua Media</option>
                    <option value="foto" {{ request('media') === 'foto' ? 'selected' : '' }}>Foto Saja</option>
                    <option value="video" {{ request('media') === 'video' ? 'selected' : '' }}>Video Saja</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <!-- Filter: Tanggal / Urutan -->
            <div class="relative w-full sm:w-44 shrink-0">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <select name="date_order"
                        id="filterDateOrder"
                        onchange="this.form.submit()"
                        class="w-full pl-9 pr-8 py-2.5 bg-white border border-slate-200/90 rounded-xl text-xs sm:text-sm font-medium text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-2xs appearance-none cursor-pointer min-h-[44px]">
                    <option value="all" {{ request('date_order', 'all') === 'all' ? 'selected' : '' }}>Semua Tanggal</option>
                    <option value="newest" {{ request('date_order') === 'newest' ? 'selected' : '' }}>Terbaru</option>
                    <option value="oldest" {{ request('date_order') === 'oldest' ? 'selected' : '' }}>Terlama</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>
        </x-admin.search-filter>

        <!-- Success Session Alert -->
        @if(session('success'))
            <div class="mb-5 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 text-sm font-medium rounded-r-xl shadow-2xs flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 text-sm font-bold">&times;</button>
            </div>
        @endif

        <!-- GALLERY CONTENT CONTAINER (Table + Pagination) -->
        <div id="galleryContent">
            @include('admin.gallery-table', ['data' => $data])
        </div>
    </div>

    <!-- PREVIEW MODAL (Used by Green View Buttons and Thumbnails) -->
    <div id="galleryPreviewModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden transition-opacity duration-200"
         aria-labelledby="modalTitle"
         role="dialog"
         aria-modal="true">
        
        <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200 p-5 sm:p-6 relative animate-in fade-in zoom-in-95 duration-150">
            <!-- Close Button -->
            <button type="button"
                    id="btnClosePreviewModal"
                    class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 bg-slate-100 hover:bg-slate-200 p-2 rounded-full transition-colors"
                    aria-label="Tutup Preview">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Modal Header Badge & Title -->
            <div class="pr-10 mb-4">
                <span id="modalDivisionBadge" class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-600 border border-blue-200 mb-2">
                    Divisi
                </span>
                <h3 id="modalTitle" class="text-lg sm:text-xl font-bold text-slate-900 leading-snug">
                    Judul Kegiatan
                </h3>
            </div>

            <!-- Media Container -->
            <div id="modalMediaContainer" class="w-full aspect-[16/10] bg-slate-900 rounded-xl overflow-hidden mb-4 flex items-center justify-center shadow-inner">
                <img id="modalPreviewImg" src="" alt="Preview Media" class="max-h-full max-w-full object-contain">
                <video id="modalPreviewVideo" src="" controls class="max-h-full max-w-full hidden"></video>
            </div>

            <!-- Description -->
            <div>
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Deskripsi Konten</h4>
                <div id="modalDescription" class="text-sm text-slate-700 leading-relaxed max-h-40 overflow-y-auto pr-2"></div>
            </div>
        </div>
    </div>

    <!-- Script Logic: AJAX Pagination, Live Search, Preview Modal, Delete Confirmation -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            attachGalleryTableEvents();

            // Handle AJAX pagination
            $(document).on('click', '.pagination-gallery a', function(event) {
                event.preventDefault();
                let href = $(this).attr('href');
                if (href && href !== '#' && href.indexOf('gallery-page=') !== -1) {
                    let page = href.split('gallery-page=')[1].split('&')[0];
                    if (page) {
                        fetchGalleryContent(page);
                    }
                }
            });

            function renderGallerySkeleton() {
                return `
                    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden animate-pulse">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-200/80 text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    <th class="py-4 px-4 text-center w-14">No</th>
                                    <th class="py-4 px-4 w-28">Media</th>
                                    <th class="py-4 px-4 w-36">Divisi</th>
                                    <th class="py-4 px-4">Judul</th>
                                    <th class="py-4 px-4 w-72">Konten</th>
                                    <th class="py-4 px-4 text-center w-36">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                ${Array(4).fill(0).map(() => `
                                    <tr class="h-20">
                                        <td class="py-4 px-4 text-center"><div class="h-4 w-6 bg-slate-200 rounded mx-auto"></div></td>
                                        <td class="py-4 px-4"><div class="w-24 aspect-[16/10] bg-slate-200 rounded-lg"></div></td>
                                        <td class="py-4 px-4"><div class="h-6 w-24 bg-slate-200 rounded-full"></div></td>
                                        <td class="py-4 px-4"><div class="h-4 w-52 bg-slate-200 rounded"></div></td>
                                        <td class="py-4 px-4"><div class="h-3 w-40 bg-slate-200 rounded mb-1.5"></div><div class="h-3 w-32 bg-slate-200 rounded"></div></td>
                                        <td class="py-4 px-4 text-center"><div class="h-8 w-24 bg-slate-200 rounded-lg mx-auto"></div></td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            }

            function fetchGalleryContent(page) {
                // UI State 1: Data fetching uses Skeleton Loading
                $('#galleryContent').html(renderGallerySkeleton());

                $.ajax({
                    url: "/gallery?gallery-page=" + page,
                    success: function(data) {
                        $('#galleryContent').html(data);
                        history.pushState(null, null, "/gallery");
                        attachGalleryTableEvents();
                    },
                    error: function() {
                        location.reload();
                    }
                });
            }
        });


        // Attach listeners for Edit, Preview, and Delete
        function attachGalleryTableEvents() {
            // Edit Button
            document.querySelectorAll('.btn-updateGallery').forEach(function(btn) {
                let cloned = btn.cloneNode(true);
                btn.parentNode.replaceChild(cloned, btn);
                cloned.addEventListener('click', function(e) {
                    e.stopPropagation();
                    let id = this.getAttribute('data-idGallery');
                    window.location.href = '/galeri-kelola/edit/' + id;
                });
            });

            // Preview Modal Triggers (View button or thumbnail click)
            const modal = document.getElementById('galleryPreviewModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalBadge = document.getElementById('modalDivisionBadge');
            const modalDesc = document.getElementById('modalDescription');
            const modalImg = document.getElementById('modalPreviewImg');
            const modalVideo = document.getElementById('modalPreviewVideo');
            const btnClose = document.getElementById('btnClosePreviewModal');

            document.querySelectorAll('.btn-previewTrigger').forEach(function(btn) {
                let cloned = btn.cloneNode(true);
                btn.parentNode.replaceChild(cloned, btn);
                cloned.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const title = this.getAttribute('data-title') || 'Detail Galeri';
                    const division = this.getAttribute('data-division') || 'Umum';
                    const desc = this.getAttribute('data-desc') || '-';
                    const src = this.getAttribute('data-src') || '';
                    const isVideo = this.getAttribute('data-isvideo') === '1';

                    modalTitle.textContent = title;
                    modalBadge.textContent = division;
                    modalDesc.textContent = desc;

                    if (isVideo) {
                        modalImg.classList.add('hidden');
                        modalVideo.src = src;
                        modalVideo.classList.remove('hidden');
                    } else {
                        modalVideo.classList.add('hidden');
                        modalVideo.src = '';
                        modalImg.src = src;
                        modalImg.classList.remove('hidden');
                    }

                    modal.classList.remove('hidden');
                });
            });

            if (btnClose) {
                btnClose.addEventListener('click', function() {
                    modal.classList.add('hidden');
                    modalVideo.pause();
                });
            }

            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.classList.add('hidden');
                        modalVideo.pause();
                    }
                });
            }

            // Delete Button SweetAlert2
            document.querySelectorAll('.btn-deleteGallery').forEach(function(btn) {
                let cloned = btn.cloneNode(true);
                btn.parentNode.replaceChild(cloned, btn);
                cloned.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    let id = this.getAttribute('data-idGallery');
                    let nama = this.getAttribute('data-namaGallery') || 'konten ini';

                    Swal.fire({
                        title: "Hapus Konten Galeri?",
                        text: "Apakah Anda yakin ingin menghapus \"" + nama + "\"? Tindakan ini tidak dapat dibatalkan.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#dc2626",
                        cancelButtonColor: "#64748b",
                        confirmButtonText: "Ya, Hapus",
                        cancelButtonText: "Batal",
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('/galeri-kelola/' + id, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json'
                                }
                            }).then(res => res.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: 'Data galeri berhasil dihapus.',
                                        timer: 1200,
                                        showConfirmButton: false
                                    }).then(() => location.reload());
                                } else {
                                    Swal.fire('Gagal!', data.message || 'Data gagal dihapus.', 'error');
                                }
                            }).catch(() => {
                                Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
                            });
                        }
                    });
                });
            });
        }
    </script>
@endsection