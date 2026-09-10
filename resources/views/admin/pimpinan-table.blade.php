<!-- Table Card Container -->
<div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
    <!-- Desktop Table View (>= 768px) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase tracking-wider">
                    <th class="py-3.5 px-4 text-center w-14 font-bold">No</th>
                    <th class="py-3.5 px-4 text-center w-28 font-bold">Foto</th>
                    <th class="py-3.5 px-4 font-bold">Nama Pimpinan</th>
                    <th class="py-3.5 px-4 font-bold">Jabatan</th>
                    <th class="py-3.5 px-4 text-center w-20 font-bold">Urutan</th>
                    <th class="py-3.5 px-4 text-center w-28 font-bold">Status</th>
                    <th class="py-3.5 px-4 text-center w-36 font-bold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse ($dataPimpinan as $item)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3.5 px-4 text-center text-slate-500 font-semibold text-xs">
                            {{ ($dataPimpinan->currentPage() - 1) * $dataPimpinan->perPage() + $loop->iteration }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <div class="w-16 h-12 rounded-lg overflow-hidden border border-slate-200 shadow-2xs mx-auto bg-slate-100">
                                <img src="{{ $item->foto_url }}" alt="{{ $item->nama }}" class="w-full h-full object-cover">
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-slate-900">
                            {{ $item->nama }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 font-medium">
                            {{ $item->jabatan }}
                        </td>
                        <td class="py-3.5 px-4 text-center font-bold text-slate-700">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 text-slate-800 text-xs font-bold">
                                {{ $item->urutan }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <button type="button" 
                                    class="btn-toggleStatus px-3 py-1.5 text-xs font-bold rounded-full transition-colors cursor-pointer {{ $item->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200' }}" 
                                    data-idPimpinan="{{ $item->id }}"
                                    title="Klik untuk ubah status aktif">
                                {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                {{-- Edit Button (Blue) --}}
                                <button type="button" 
                                        class="btn-updatePimpinan inline-flex items-center justify-center w-9 h-9 text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors shadow-2xs focus:ring-2 focus:ring-blue-400 focus:outline-none" 
                                        data-idPimpinan="{{ $item->id }}" 
                                        aria-label="Edit Pimpinan {{ $item->nama }}"
                                        title="Edit Pimpinan">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                {{-- View Button (Green) --}}
                                <a href="{{ url('/pimpinan/' . $item->id) }}"
                                   class="inline-flex items-center justify-center w-9 h-9 text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 rounded-xl transition-colors shadow-2xs focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                                   aria-label="Lihat Pimpinan {{ $item->nama }}"
                                   title="Lihat Pimpinan">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                {{-- Delete Button (Red) --}}
                                <button type="button" 
                                        class="btn-deletePimpinan inline-flex items-center justify-center w-9 h-9 text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors shadow-2xs focus:ring-2 focus:ring-rose-400 focus:outline-none" 
                                        data-idPimpinan="{{ $item->id }}" 
                                        data-namaPimpinan="{{ $item->nama }}" 
                                        aria-label="Hapus Pimpinan {{ $item->nama }}"
                                        title="Hapus Pimpinan">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-12 px-4 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p class="text-sm font-semibold text-slate-600">Belum ada data pimpinan MKKS</p>
                                <p class="text-xs text-slate-400 mt-1">Silakan klik tombol Tambah Pimpinan di atas.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View (< 768px) -->
    <div class="block md:hidden divide-y divide-slate-100">
        @forelse ($dataPimpinan as $item)
            <div class="p-4 flex flex-col gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-16 h-12 rounded-lg overflow-hidden border border-slate-200 shadow-2xs flex-shrink-0 bg-slate-100">
                        <img src="{{ $item->foto_url }}" alt="{{ $item->nama }}" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-slate-900 text-sm truncate">{{ $item->nama }}</h3>
                        <p class="text-xs text-slate-600 truncate">{{ $item->jabatan }}</p>
                        <div class="flex items-center gap-2 mt-1.5">
                            <span class="text-xs text-slate-500 font-medium">Urutan: <strong>{{ $item->urutan }}</strong></span>
                            <button type="button" 
                                    class="btn-toggleStatus px-2.5 py-0.5 text-xs font-bold rounded-full {{ $item->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}" 
                                    data-idPimpinan="{{ $item->id }}">
                                {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <a href="{{ url('/pimpinan/' . $item->id) }}"
                       class="w-9 h-9 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white inline-flex items-center justify-center shadow-2xs transition-colors cursor-pointer"
                       title="Lihat Pimpinan"
                       aria-label="Lihat Pimpinan {{ $item->nama }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </a>
                    <button type="button" 
                            class="btn-updatePimpinan w-9 h-9 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white inline-flex items-center justify-center shadow-2xs transition-colors cursor-pointer" 
                            data-idPimpinan="{{ $item->id }}"
                            title="Edit Pimpinan"
                            aria-label="Edit Pimpinan {{ $item->nama }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <button type="button" 
                            class="btn-deletePimpinan w-9 h-9 rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white inline-flex items-center justify-center shadow-2xs transition-colors cursor-pointer" 
                            data-idPimpinan="{{ $item->id }}" 
                            data-namaPimpinan="{{ $item->nama }}"
                            title="Hapus Pimpinan"
                            aria-label="Hapus Pimpinan {{ $item->nama }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-slate-400">
                Belum ada data pimpinan MKKS.
            </div>
        @endforelse
    </div>

    @include('components.pagination-pimpinan', ['data' => $dataPimpinan])
</div>
