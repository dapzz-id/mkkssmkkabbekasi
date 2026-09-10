@extends('admin.layouts.main')

@section('container')
    @if(Request::is('sponsor'))
        <script>
            document.addEventListener("DOMContentLoaded", function(){
                document.querySelectorAll('#sidebarMenu a').forEach(button => {
                    button.addEventListener('click', function() {
                        document.querySelectorAll('#sidebarMenu a').forEach(btn => {
                            btn.classList.remove('bg-blue-100', 'text-blue-800');
                            btn.classList.add('text-gray-600');
                            btn.querySelector('svg').classList.remove('text-blue-800');
                            btn.querySelector('svg').classList.add('text-gray-600');
                        });
                        this.querySelector('svg').classList.remove('text-gray-600');
                        this.querySelector('svg').classList.add('text-blue-800');
                    });
                });
                document.getElementById('btn-sponsor-section').classList.remove('text-gray-600');
                document.getElementById('btn-sponsor-section').classList.add('bg-blue-100', 'text-blue-800');

                @if (Auth::user()->role == 'superadmin')
                    document.getElementById('btn-calendar-section').addEventListener('click', function() {
                        window.location.href = "/manage/event"
                    });

                    document.getElementById('btn-user-section').addEventListener('click', function() {
                        window.location.href = "/manage/user"
                    });

                    document.getElementById('btn-sponsor-section').addEventListener('click', function() {
                        window.location.href = "/sponsor"
                    });
                @endif

                document.getElementById('btn-gallery-section').addEventListener('click', function() {
                    window.location.href = "/gallery";
                });
            });
        </script>
    @endif

    <!-- Sponsor Section -->
    <div id="sponsorSection" class="p-4 sm:p-6 lg:p-8 space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-sm shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Sponsor</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola data mitra sponsor kegiatan MKKS SMK Kab Bekasi</p>
                </div>
            </div>

            <!-- Action Button: Tambah Sponsor -->
            <a href="/sponsor/tambah" id="btn-addSponsor"
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold text-sm rounded-xl shadow-xs transition-colors min-h-[44px]">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Sponsor</span>
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
            $hasSponsorFilters = request()->filled('search') || (request()->filled('date_order') && request('date_order') !== 'all');
        @endphp
        <x-admin.search-filter
            action="/sponsor"
            suggestionsUrl="/sponsor/suggestions"
            placeholder="Cari nama sponsor..."
            searchName="search"
            :hasActiveFilters="$hasSponsorFilters"
            resetUrl="/sponsor">

            <!-- Filter: Urutan -->
            <div class="relative w-full sm:w-44 shrink-0">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                    </svg>
                </div>
                <select name="date_order"
                        id="filterSponsorOrder"
                        onchange="this.form.submit()"
                        class="w-full pl-9 pr-8 py-2.5 bg-white border border-slate-200/90 rounded-xl text-xs sm:text-sm font-medium text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-2xs appearance-none cursor-pointer min-h-[44px]">
                    <option value="all" {{ request('date_order', 'all') === 'all' ? 'selected' : '' }}>Semua Urutan</option>
                    <option value="newest" {{ request('date_order') === 'newest' ? 'selected' : '' }}>Terbaru</option>
                    <option value="oldest" {{ request('date_order') === 'oldest' ? 'selected' : '' }}>Terlama</option>
                    <option value="name_asc" {{ request('date_order') === 'name_asc' ? 'selected' : '' }}>Nama (A - Z)</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>
        </x-admin.search-filter>

        <div id="sponsorContent">
            @include('admin.sponsor-table', ['dataSponsor' => $dataSponsor])
        </div>
    </div>

    <script>
        function confirmDownload(base64Image) {
            Swal.fire({
                title: "Unduh Gambar Sponsor?",
                text: "Apakah Anda ingin mengunduh logo sponsor ini ke perangkat Anda?",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#2563eb",
                cancelButtonColor: "#64748b",
                confirmButtonText: "Ya, Unduh",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    const link = document.createElement("a");
                    link.href = base64Image;
                    link.download = "sponsor_image.png";
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            });
        }
    </script>
@endsection