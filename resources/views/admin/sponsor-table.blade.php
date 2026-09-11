<!-- Sponsor Table Card Container -->
<div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
    <!-- Desktop Table (>= 768px) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase tracking-wider">
                    <th class="py-3.5 px-4 text-center w-14 font-bold">No</th>
                    <th class="py-3.5 px-4 text-center w-28 font-bold">Logo Sponsor</th>
                    <th class="py-3.5 px-4 font-bold">Nama Sponsor</th>
                    <th class="py-3.5 px-4 text-center w-36 font-bold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse ($dataSponsor as $kontenSponsor)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3.5 px-4 text-center text-slate-500 font-semibold text-xs">
                            {{ ($dataSponsor->currentPage() - 1) * $dataSponsor->perPage() + $loop->iteration }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <div class="w-16 h-12 rounded-lg border border-slate-200 shadow-2xs mx-auto bg-white p-1 flex items-center justify-center cursor-pointer hover:ring-2 hover:ring-blue-400 transition-all"
                                 onclick="confirmDownload('{{ $kontenSponsor['url_image'] }}')"
                                 title="Klik untuk unduh gambar sponsor">
                                <img src="{{ $kontenSponsor['url_image'] }}"
                                     alt="{{ $kontenSponsor['nama'] }}"
                                     class="max-h-full max-w-full object-contain">
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-slate-900">
                            {{ $kontenSponsor['nama'] }}
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                {{-- View Button (Green) --}}
                                {{-- <a href="{{ url('/sponsor/' . $kontenSponsor['uuid']) }}"
                                   class="inline-flex items-center justify-center w-9 h-9 text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 rounded-xl transition-colors shadow-2xs focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                                   aria-label="Lihat Sponsor {{ $kontenSponsor['nama'] }}"
                                   title="Lihat Sponsor">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a> --}}
                                {{-- Edit Button (Blue) --}}
                                <button type="button"
                                        class="btn-updateSponsor inline-flex items-center justify-center w-9 h-9 text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors shadow-2xs focus:ring-2 focus:ring-blue-400 focus:outline-none"
                                        data-idSponsor="{{ $kontenSponsor['uuid'] }}"
                                        aria-label="Edit Sponsor {{ $kontenSponsor['nama'] }}"
                                        title="Edit Sponsor">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                {{-- Delete Button (Red) --}}
                                <button type="button"
                                        class="btn-deleteSponsor inline-flex items-center justify-center w-9 h-9 text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors shadow-2xs focus:ring-2 focus:ring-rose-400 focus:outline-none"
                                        data-idSponsor="{{ $kontenSponsor['uuid'] }}"
                                        data-namaSponsor="{{ $kontenSponsor['nama'] }}"
                                        aria-label="Hapus Sponsor {{ $kontenSponsor['nama'] }}"
                                        title="Hapus Sponsor">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-12 px-4 text-center text-slate-400">
                            Belum ada data sponsor. Silakan tambah sponsor baru.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View (< 768px) -->
    <div class="block md:hidden divide-y divide-slate-100">
        @forelse ($dataSponsor as $kontenSponsor)
            <div class="p-4 flex flex-col gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-16 h-12 rounded-lg border border-slate-200 shadow-2xs bg-white p-1 flex items-center justify-center shrink-0 cursor-pointer"
                         onclick="confirmDownload('{{ $kontenSponsor['url_image'] }}')">
                        <img src="{{ $kontenSponsor['url_image'] }}" alt="{{ $kontenSponsor['nama'] }}" class="max-h-full max-w-full object-contain">
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-slate-900 text-sm truncate">{{ $kontenSponsor['nama'] }}</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Ketuk logo untuk mengunduh</p>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <a href="{{ url('/sponsor/' . $kontenSponsor['uuid']) }}"
                       class="w-9 h-9 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white inline-flex items-center justify-center shadow-2xs transition-colors cursor-pointer"
                       title="Lihat Sponsor"
                       aria-label="Lihat Sponsor {{ $kontenSponsor['nama'] }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </a>
                    <button type="button"
                            class="btn-updateSponsor w-9 h-9 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white inline-flex items-center justify-center shadow-2xs transition-colors cursor-pointer"
                            data-idSponsor="{{ $kontenSponsor['uuid'] }}"
                            title="Edit Sponsor"
                            aria-label="Edit Sponsor {{ $kontenSponsor['nama'] }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <button type="button"
                            class="btn-deleteSponsor w-9 h-9 rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white inline-flex items-center justify-center shadow-2xs transition-colors cursor-pointer"
                            data-idSponsor="{{ $kontenSponsor['uuid'] }}"
                            data-namaSponsor="{{ $kontenSponsor['nama'] }}"
                            title="Hapus Sponsor"
                            aria-label="Hapus Sponsor {{ $kontenSponsor['nama'] }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-slate-400">
                Belum ada data sponsor.
            </div>
        @endforelse
    </div>

    @include('components.pagination-sponsor', ['data' => $dataSponsor])
</div>