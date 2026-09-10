<div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
    <!-- Desktop Table View -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse" id="tableGallery">
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
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse ($data as $konten)
                    @php
                        $media = json_decode($konten->url_media, true);
                        $firstMedia = (!empty($media) && isset($media[0])) ? $media[0] : null;
                        $extension = $firstMedia ? strtolower(pathinfo($firstMedia, PATHINFO_EXTENSION)) : '';
                        $isVideo = in_array($extension, ['mp4', 'mov', 'avi']);
                        
                        // Color styling for Division badges
                        $divisiName = $konten->divisi->nama_divisi ?? 'Umum';
                        $divisiLower = strtolower($divisiName);
                        $badgeClass = 'bg-slate-50 text-slate-700 border-slate-200';
                        $badgeIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>';

                        if (str_contains($divisiLower, 'ict') || str_contains($divisiLower, 'it')) {
                            $badgeClass = 'bg-blue-50 text-blue-600 border-blue-200';
                            $badgeIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>';
                        } elseif (str_contains($divisiLower, 'kurikulum')) {
                            $badgeClass = 'bg-purple-50 text-purple-600 border-purple-200';
                            $badgeIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>';
                        } elseif (str_contains($divisiLower, 'kesiswaan')) {
                            $badgeClass = 'bg-emerald-50 text-emerald-600 border-emerald-200';
                            $badgeIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>';
                        } elseif (str_contains($divisiLower, 'humas')) {
                            $badgeClass = 'bg-amber-50 text-amber-600 border-amber-200';
                            $badgeIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>';
                        }
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition-colors gallery-data-row"
                        data-judul="{{ strtolower($konten->judul) }}"
                        data-konten="{{ strtolower(strip_tags($konten->deskripsi)) }}"
                        data-divisi="{{ strtolower($divisiName) }}"
                        data-media-type="{{ $isVideo ? 'video' : 'foto' }}"
                        data-date="{{ $konten->created_at ? $konten->created_at->format('Y-m-d') : '' }}">
                        
                        <!-- No -->
                        <td class="py-4 px-4 text-center font-semibold text-slate-500 text-sm">
                            {{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}
                        </td>

                        <!-- Media Thumbnail -->
                        <td class="py-4 px-4">
                            @if ($firstMedia)
                                <div class="relative w-24 aspect-[16/10] rounded-lg overflow-hidden border border-slate-200 shadow-2xs bg-slate-100 group cursor-pointer btn-previewTrigger"
                                     data-title="{{ $konten->judul }}"
                                     data-division="{{ $divisiName }}"
                                     data-desc="{{ strip_tags($konten->deskripsi) }}"
                                     data-src="{{ $firstMedia }}"
                                     data-isvideo="{{ $isVideo ? '1' : '0' }}">
                                    @if ($isVideo)
                                        <video src="{{ $firstMedia }}" class="w-full h-full object-cover" muted></video>
                                        <div class="absolute inset-0 bg-slate-900/30 flex items-center justify-center text-white">
                                            <svg class="w-5 h-5 drop-shadow" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                                        </div>
                                    @else
                                        <img src="{{ $firstMedia }}"
                                             alt="{{ $konten->judul }}"
                                             loading="lazy"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                    @endif
                                </div>
                            @else
                                <div class="w-24 aspect-[16/10] rounded-lg bg-slate-100 border border-dashed border-slate-300 flex items-center justify-center text-slate-400 text-xs font-medium">
                                    No media
                                </div>
                            @endif
                        </td>

                        <!-- Divisi Badge -->
                        <td class="py-4 px-4">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $badgeClass }}">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    {!! $badgeIcon !!}
                                </svg>
                                <span>{{ $divisiName }}</span>
                            </span>
                        </td>

                        <!-- Judul -->
                        <td class="py-4 px-4">
                            <h3 class="font-semibold text-slate-900 text-sm leading-snug line-clamp-2 max-w-sm" title="{{ $konten->judul }}">
                                {{ $konten->judul }}
                            </h3>
                        </td>

                        <!-- Konten Snippet -->
                        <td class="py-4 px-4">
                            <p class="text-xs text-slate-500 line-clamp-2 max-w-xs leading-relaxed">
                                {{ strip_tags($konten->deskripsi) }}
                            </p>
                        </td>

                        <!-- Action Buttons (Edit Blue, View Green, Delete Red) -->
                        <td class="py-4 px-4 text-center">
                            <div class="inline-flex items-center justify-center gap-1.5">
                                {{-- Edit Button (Blue) --}}
                                <button type="button"
                                        class="btn-updateGallery w-8 h-8 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white flex items-center justify-center shadow-2xs transition-colors cursor-pointer"
                                        data-idGallery="{{ $konten->id }}"
                                        title="Edit Galeri"
                                        aria-label="Edit Galeri {{ $konten->judul }}">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>

                                {{-- View / Detail Button (Green) --}}
                                <a href="{{ url('/gallery/' . $konten->slug) }}"
                                   class="w-8 h-8 rounded-lg bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white flex items-center justify-center shadow-2xs transition-colors cursor-pointer"
                                   title="Lihat Detail Galeri"
                                   aria-label="Lihat Detail {{ $konten->judul }}">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>

                                {{-- Delete Button (Red) --}}
                                <button type="button"
                                        class="btn-deleteGallery w-8 h-8 rounded-lg bg-red-600 hover:bg-red-700 active:bg-red-800 text-white flex items-center justify-center shadow-2xs transition-colors cursor-pointer"
                                        data-idGallery="{{ $konten->id }}"
                                        data-namaGallery="{{ $konten->judul }}"
                                        title="Hapus Galeri"
                                        aria-label="Hapus Galeri {{ $konten->judul }}">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-2">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <p class="font-semibold text-slate-700 text-sm">Belum ada data galeri</p>
                                <p class="text-xs text-slate-400 mt-0.5">Klik tombol "Tambah Galeri" untuk mengunggah konten baru.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card List View (Accessible for Elderly & Small Screens) -->
    <div class="md:hidden divide-y divide-slate-100">
        @forelse ($data as $konten)
            @php
                $media = json_decode($konten->url_media, true);
                $firstMedia = (!empty($media) && isset($media[0])) ? $media[0] : null;
                $extension = $firstMedia ? strtolower(pathinfo($firstMedia, PATHINFO_EXTENSION)) : '';
                $isVideo = in_array($extension, ['mp4', 'mov', 'avi']);
                $divisiName = $konten->divisi->nama_divisi ?? 'Umum';
            @endphp
            <div class="p-4 bg-white hover:bg-slate-50/70 transition-colors flex flex-col gap-3 gallery-data-row"
                 data-judul="{{ strtolower($konten->judul) }}"
                 data-konten="{{ strtolower(strip_tags($konten->deskripsi)) }}"
                 data-divisi="{{ strtolower($divisiName) }}"
                 data-media-type="{{ $isVideo ? 'video' : 'foto' }}">
                
                <div class="flex items-start gap-3">
                    @if ($firstMedia)
                        <div class="w-20 aspect-[16/10] rounded-lg overflow-hidden border border-slate-200 shrink-0 bg-slate-100">
                            @if ($isVideo)
                                <video src="{{ $firstMedia }}" class="w-full h-full object-cover" muted></video>
                            @else
                                <img src="{{ $firstMedia }}" alt="{{ $konten->judul }}" class="w-full h-full object-cover">
                            @endif
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-600 border border-blue-200 mb-1">
                            {{ $divisiName }}
                        </span>
                        <h4 class="font-bold text-slate-900 text-sm leading-snug line-clamp-2">
                            {{ $konten->judul }}
                        </h4>
                    </div>
                </div>

                <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                    {{ strip_tags($konten->deskripsi) }}
                </p>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <span class="text-xs font-semibold text-slate-400">
                        #{{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}
                    </span>
                    <div class="flex items-center gap-2">
                        <button type="button"
                                class="btn-updateGallery w-9 h-9 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white inline-flex items-center justify-center shadow-2xs transition-colors cursor-pointer"
                                data-idGallery="{{ $konten->id }}"
                                title="Edit Galeri"
                                aria-label="Edit Galeri {{ $konten->judul }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <a href="{{ url('/gallery/' . ($konten->slug ?: $konten->id)) }}"
                           class="w-9 h-9 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white inline-flex items-center justify-center shadow-2xs transition-colors cursor-pointer"
                           title="Lihat Detail Galeri"
                           aria-label="Lihat Detail {{ $konten->judul }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        <button type="button"
                                class="btn-deleteGallery w-9 h-9 rounded-xl bg-red-600 hover:bg-red-700 active:bg-red-800 text-white inline-flex items-center justify-center shadow-2xs transition-colors cursor-pointer"
                                data-idGallery="{{ $konten->id }}"
                                data-namaGallery="{{ $konten->judul }}"
                                title="Hapus Galeri"
                                aria-label="Hapus Galeri {{ $konten->judul }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-slate-400 text-sm">
                Belum ada data galeri.
            </div>
        @endforelse
    </div>

    <!-- Redesigned Pagination Footer Matching Approved Reference -->
    @include('components.pagination-gallery', ['data' => $data])
</div>
