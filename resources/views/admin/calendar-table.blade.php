<!-- Calendar Events Table Card Container -->
<div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
    <!-- Desktop Table (>= 768px) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase tracking-wider">
                    <th class="py-3.5 px-4 text-center w-14 font-bold">No</th>
                    <th class="py-3.5 px-4 font-bold">Nama Acara</th>
                    <th class="py-3.5 px-4 w-52 font-bold">Tanggal Pelaksanaan</th>
                    <th class="py-3.5 px-4 text-center w-36 font-bold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse ($dataCalendar as $kontenKalender)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3.5 px-4 text-center text-slate-500 font-semibold text-xs">
                            {{ ($dataCalendar->currentPage() - 1) * $dataCalendar->perPage() + $loop->iteration }}
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-slate-900">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <span class="truncate max-w-md">{{ $kontenKalender['event_name'] }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-slate-700">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 text-xs font-medium">
                                <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ \Carbon\Carbon::parse($kontenKalender['event_date'])->translatedFormat('d F Y') }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                {{-- View Button (Green) --}}
                                {{-- <a href="{{ url('/manage/event/' . $kontenKalender['id']) }}"
                                   class="inline-flex items-center justify-center w-9 h-9 text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 rounded-xl transition-colors shadow-2xs focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                                   aria-label="Lihat Agenda {{ $kontenKalender['event_name'] }}"
                                   title="Lihat Agenda">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a> --}}
                                {{-- Edit Button (Blue) --}}
                                <button type="button"
                                        class="btn-updateCalendar inline-flex items-center justify-center w-9 h-9 text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors shadow-2xs focus:ring-2 focus:ring-blue-400 focus:outline-none"
                                        data-idCalendar="{{ $kontenKalender['id'] }}"
                                        aria-label="Edit Agenda {{ $kontenKalender['event_name'] }}"
                                        title="Edit Agenda">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                {{-- Delete Button (Red) --}}
                                <button type="button"
                                        class="btn-deleteCalendar inline-flex items-center justify-center w-9 h-9 text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors shadow-2xs focus:ring-2 focus:ring-rose-400 focus:outline-none"
                                        data-idCalendar="{{ $kontenKalender['id'] }}"
                                        data-namaCalendar="{{ $kontenKalender['event_name'] }}"
                                        aria-label="Hapus Agenda {{ $kontenKalender['event_name'] }}"
                                        title="Hapus Agenda">
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
                            Belum ada agenda kegiatan. Silakan tambah agenda baru.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View (< 768px) -->
    <div class="block md:hidden divide-y divide-slate-100">
        @forelse ($dataCalendar as $kontenKalender)
            <div class="p-4 flex flex-col gap-3">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-slate-900 text-sm leading-snug">{{ $kontenKalender['event_name'] }}</h3>
                        <div class="inline-flex items-center gap-1.5 mt-1.5 px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-xs font-medium">
                            <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ \Carbon\Carbon::parse($kontenKalender['event_date'])->translatedFormat('d F Y') }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <a href="{{ url('/manage/event/' . $kontenKalender['id']) }}"
                       class="w-9 h-9 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white inline-flex items-center justify-center shadow-2xs transition-colors cursor-pointer"
                       title="Lihat Agenda"
                       aria-label="Lihat Agenda {{ $kontenKalender['event_name'] }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </a>
                    <button type="button"
                            class="btn-updateCalendar w-9 h-9 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white inline-flex items-center justify-center shadow-2xs transition-colors cursor-pointer"
                            data-idCalendar="{{ $kontenKalender['id'] }}"
                            title="Edit Agenda"
                            aria-label="Edit Agenda {{ $kontenKalender['event_name'] }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <button type="button"
                            class="btn-deleteCalendar w-9 h-9 rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white inline-flex items-center justify-center shadow-2xs transition-colors cursor-pointer"
                            data-idCalendar="{{ $kontenKalender['id'] }}"
                            data-namaCalendar="{{ $kontenKalender['event_name'] }}"
                            title="Hapus Agenda"
                            aria-label="Hapus Agenda {{ $kontenKalender['event_name'] }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-slate-400">
                Belum ada agenda kegiatan.
            </div>
        @endforelse
    </div>

    @include('components.pagination-calendar', ['data' => $dataCalendar])
</div>