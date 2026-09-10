@extends('admin.layouts.main')

@section('container')
    @if(Request::is('manage/user'))
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
                document.getElementById('btn-user-section').classList.remove('text-gray-600');
                document.getElementById('btn-user-section').classList.add('bg-blue-100', 'text-blue-800');

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

    <!-- Accounts Section -->
    <div id="accountSection" class="p-4 sm:p-6 lg:p-8 space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-sm shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Kelola Akun</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola akun pengguna, hak akses peran, dan penugasan divisi</p>
                </div>
            </div>

            <!-- Action Button: Tambah Akun -->
            <a href="{{ route('subadmin.create') }}" id="btn-addAcc"
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold text-sm rounded-xl shadow-xs transition-colors min-h-[44px]">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Akun</span>
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
            $hasAccountFilters = request()->filled('search') || (request()->filled('role') && request('role') !== 'all');
        @endphp
        <x-admin.search-filter
            action="/manage/user"
            suggestionsUrl="/manage/user/suggestions"
            placeholder="Cari nama, username, atau email..."
            searchName="search"
            :hasActiveFilters="$hasAccountFilters"
            resetUrl="/manage/user">

            <!-- Filter: Role -->
            <div class="relative w-full sm:w-44 shrink-0">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <select name="role"
                        id="filterUserRole"
                        onchange="this.form.submit()"
                        class="w-full pl-9 pr-8 py-2.5 bg-white border border-slate-200/90 rounded-xl text-xs sm:text-sm font-medium text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-2xs appearance-none cursor-pointer min-h-[44px]">
                    <option value="all" {{ request('role', 'all') === 'all' ? 'selected' : '' }}>Semua Role</option>
                    <option value="superadmin" {{ request('role') === 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>
        </x-admin.search-filter>

        <div id="accountContent">
            @include('admin.account-table', ['dataAkun' => $dataAkun])
        </div>
    </div>
@endsection