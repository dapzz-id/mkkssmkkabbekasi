@extends('admin.layouts.main')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 space-y-6">
    <!-- PAGE HEADER & BREADCRUMB -->
    <div class="flex flex-col gap-4 pt-1 sm:pt-2">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight leading-tight">
                        Sponsor
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5 font-medium">
                        Detail data mitra sponsor #{{ $sponsor->id }}
                    </p>
                </div>
            </div>

            <!-- Action Buttons: Edit & Kembali -->
            <div class="flex items-center gap-2.5 shrink-0">
                <a href="/sponsor"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold transition-all shadow-2xs min-h-[44px]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali</span>
                </a>

                <a href="{{ url('/sponsor/edit/' . $sponsor->id) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold transition-all shadow-xs min-h-[44px]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Edit Sponsor</span>
                </a>
            </div>
        </div>

        <div class="pt-1.5">
            <x-admin-breadcrumb :items="[
                ['label' => 'Sponsor', 'url' => '/sponsor'],
                ['label' => $sponsor->id . '_view']
            ]" />
        </div>
    </div>

    <!-- MAIN DETAIL CARD -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 sm:p-8 flex flex-col sm:flex-row gap-8 items-start">
            <!-- Sponsor Logo Box -->
            <div class="w-full sm:w-64 shrink-0">
                <div class="w-full aspect-video sm:aspect-square rounded-2xl bg-slate-100 border border-slate-200 p-4 flex items-center justify-center shadow-2xs">
                    @if($sponsor->url_image)
                        <img src="{{ $sponsor->url_image }}"
                             alt="{{ $sponsor->nama }}"
                             class="max-h-full max-w-full object-contain">
                    @else
                        <span class="text-xs text-slate-400">Logo tidak tersedia</span>
                    @endif
                </div>
            </div>

            <!-- Details -->
            <div class="flex-1 w-full space-y-4">
                <div>
                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        Mitra Sponsor MKKS
                    </span>
                </div>

                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
                        {{ $sponsor->nama }}
                    </h2>
                </div>

                <div class="pt-4 border-t border-slate-100 text-sm">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 inline-block min-w-[200px]">
                        <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">ID Sistem</span>
                        <span class="font-bold text-slate-800 text-base">#{{ $sponsor->id }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
