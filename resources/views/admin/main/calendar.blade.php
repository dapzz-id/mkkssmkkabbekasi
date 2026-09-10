@extends('admin.layouts.main')

@section('container')
    <!-- Calendar Events Section -->
    <div id="calendarSection" class="p-4 sm:p-6 lg:p-8 space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-sm shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Agenda Kegiatan</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola data jadwal kegiatan dan agenda MKKS SMK Kab Bekasi</p>
                </div>
            </div>

            <!-- Action Button: Tambah Agenda -->
            <a href="{{ route('calendar.create') }}" id="btn-addEvents"
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold text-sm rounded-xl shadow-xs transition-colors min-h-[44px]">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Agenda</span>
            </a>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium rounded-xl flex items-center gap-2 shadow-2xs">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Search & Filter Toolbar with Server-Side Autocomplete and Debounce -->
        @php
            $hasCalendarFilters = request()->filled('search') || (request()->filled('date_order') && request('date_order') !== 'all');
        @endphp
        <x-admin.search-filter
            action="/manage/event"
            suggestionsUrl="/manage/event/suggestions"
            placeholder="Cari nama acara atau kegiatan..."
            searchName="search"
            :hasActiveFilters="$hasCalendarFilters"
            resetUrl="/manage/event">

            <!-- Filter: Urutan Tanggal -->
            <div class="relative w-full sm:w-44 shrink-0">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <select name="date_order"
                        id="filterCalendarOrder"
                        onchange="this.form.submit()"
                        class="w-full pl-9 pr-8 py-2.5 bg-white border border-slate-200/90 rounded-xl text-xs sm:text-sm font-medium text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-2xs appearance-none cursor-pointer min-h-[44px]">
                    <option value="all" {{ request('date_order', 'all') === 'all' ? 'selected' : '' }}>Semua Urutan</option>
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

        <div id="calendarContent">
            @include('admin.calendar-table', ['dataCalendar' => $dataCalendar])
        </div>
    </div>
@endsection