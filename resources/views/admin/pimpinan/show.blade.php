@extends('admin.layouts.main')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 space-y-6">
    <!-- PAGE HEADER & BREADCRUMB -->
    <div class="flex flex-col gap-4 pt-1 sm:pt-2">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight leading-tight">
                        Pimpinan MKKS
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5 font-medium">
                        Detail data pimpinan #{{ $pimpinan->id }}
                    </p>
                </div>
            </div>

            <!-- Action Buttons: Edit (if Superadmin) & Kembali -->
            <div class="flex items-center gap-2.5 shrink-0">
                <a href="/pimpinan"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm transition-all shadow-xs min-h-[44px]">
                    <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali</span>
                </a>

                @if(Auth::user()->role === 'superadmin')
                    <a href="{{ url('/pimpinan/edit/' . $pimpinan->id) }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm transition-all shadow-xs min-h-[44px]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Edit Pimpinan</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Breadcrumb with lowered spacing -->
        <div class="pt-1.5">
            <x-admin-breadcrumb :items="[
                ['label' => 'Pimpinan MKKS', 'url' => '/pimpinan'],
                ['label' => $pimpinan->id . '_view']
            ]" />
        </div>
    </div>

    <!-- MAIN DETAIL CARD -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 sm:p-8 flex flex-col md:flex-row gap-8 items-start">
            <!-- 4:3 Photo Box -->
            <div class="w-full md:w-80 shrink-0">
                <div class="w-full aspect-[4/3] rounded-2xl bg-slate-100 border border-slate-200 overflow-hidden shadow-2xs flex items-center justify-center">
                    @if($pimpinan->foto)
                        <img src="{{ $pimpinan->foto }}"
                             alt="{{ $pimpinan->nama }}"
                             class="w-full h-full object-contain p-1">
                    @else
                        <div class="flex flex-col items-center justify-center text-slate-400">
                            <svg class="w-12 h-12 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="text-xs">Foto tidak tersedia</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Identitas Information Details -->
            <div class="flex-1 w-full space-y-4">
                <div>
                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold {{ $pimpinan->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                        {{ $pimpinan->is_active ? 'Status: Aktif Ditampilkan' : 'Status: Nonaktif' }}
                    </span>
                </div>

                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
                        {{ $pimpinan->nama }}
                    </h2>
                    <p class="text-base sm:text-lg font-semibold text-blue-600 mt-1">
                        {{ $pimpinan->jabatan }}
                    </p>
                </div>

                <div class="pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Urutan Tampil</span>
                        <span class="font-bold text-slate-800 text-base">Nomor Urut: {{ $pimpinan->urutan }}</span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">ID Sistem</span>
                        <span class="font-bold text-slate-800 text-base">#{{ $pimpinan->id }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
