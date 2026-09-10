<!-- Account Table Card Container -->
<div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
    <!-- Desktop Table View (>= 768px) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase tracking-wider">
                    <th class="py-3.5 px-4 text-center w-14 font-bold">No</th>
                    <th class="py-3.5 px-4 font-bold">Pengguna</th>
                    <th class="py-3.5 px-4 font-bold">Divisi</th>
                    <th class="py-3.5 px-4 font-bold">Email</th>
                    <th class="py-3.5 px-4 text-center w-28 font-bold">Role</th>
                    <th class="py-3.5 px-4 font-bold">Alamat</th>
                    <th class="py-3.5 px-4 text-center w-36 font-bold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse ($dataAkun as $kontenAkun)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3.5 px-4 text-center text-slate-500 font-semibold text-xs">
                            {{ ($dataAkun->currentPage() - 1) * $dataAkun->perPage() + $loop->iteration }}
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ strtoupper(substr($kontenAkun->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-semibold text-slate-900 truncate">{{ $kontenAkun->name }}</div>
                                    <div class="text-xs text-slate-500">@<span>{{ $kontenAkun->username }}</span></div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                {{ $kontenAkun->divisi->nama_divisi ?? '-' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 text-xs sm:text-sm">
                            {{ $kontenAkun->email }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($kontenAkun->role === 'superadmin')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                    Superadmin
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                                    Admin
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-500 text-xs max-w-xs truncate">
                            {{ $kontenAkun->alamat ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                {{-- View Button (Green) --}}
                                {{-- <a href="{{ url('/manage/user/' . $kontenAkun['id']) }}"
                                   class="inline-flex items-center justify-center w-9 h-9 text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 rounded-xl transition-colors shadow-2xs focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                                   aria-label="Lihat Akun {{ $kontenAkun['name'] }}"
                                   title="Lihat Akun">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a> --}}
                                {{-- Edit Button (Blue) --}}
                                <button type="button"
                                        class="btn-updateAkun inline-flex items-center justify-center w-9 h-9 text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors shadow-2xs focus:ring-2 focus:ring-blue-400 focus:outline-none"
                                        data-idAkun="{{ $kontenAkun['id'] }}"
                                        aria-label="Edit Akun {{ $kontenAkun['name'] }}"
                                        title="Edit Akun">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                {{-- Delete Button (Red) --}}
                                <button type="button"
                                        class="btn-deleteAkun inline-flex items-center justify-center w-9 h-9 text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors shadow-2xs focus:ring-2 focus:ring-rose-400 focus:outline-none"
                                        data-idAkun="{{ $kontenAkun['id'] }}"
                                        data-namaAkun="{{ $kontenAkun['name'] }}"
                                        aria-label="Hapus Akun {{ $kontenAkun['name'] }}"
                                        title="Hapus Akun">
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
                            Belum ada akun pengurus terdaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View (< 768px) -->
    <div class="block md:hidden divide-y divide-slate-100">
        @forelse ($dataAkun as $kontenAkun)
            <div class="p-4 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm shrink-0">
                            {{ strtoupper(substr($kontenAkun->name, 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">{{ $kontenAkun->name }}</h3>
                            <p class="text-xs text-slate-500">@<span>{{ $kontenAkun->username }}</span> &bull; {{ $kontenAkun->email }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-2xs font-bold {{ $kontenAkun->role === 'superadmin' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700' }}">
                        {{ $kontenAkun->role }}
                    </span>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <a href="{{ url('/manage/user/' . $kontenAkun['id']) }}"
                       class="w-9 h-9 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white inline-flex items-center justify-center shadow-2xs transition-colors cursor-pointer"
                       title="Lihat Akun"
                       aria-label="Lihat Akun {{ $kontenAkun['name'] }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </a>
                    <button type="button"
                            class="btn-updateAkun w-9 h-9 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white inline-flex items-center justify-center shadow-2xs transition-colors cursor-pointer"
                            data-idAkun="{{ $kontenAkun['id'] }}"
                            title="Edit Akun"
                            aria-label="Edit Akun {{ $kontenAkun['name'] }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <button type="button"
                            class="btn-deleteAkun w-9 h-9 rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white inline-flex items-center justify-center shadow-2xs transition-colors cursor-pointer"
                            data-idAkun="{{ $kontenAkun['id'] }}"
                            data-namaAkun="{{ $kontenAkun['name'] }}"
                            title="Hapus Akun"
                            aria-label="Hapus Akun {{ $kontenAkun['name'] }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-slate-400">
                Belum ada akun pengurus terdaftar.
            </div>
        @endforelse
    </div>

    @include('components.pagination-account', ['data' => $dataAkun])
</div>