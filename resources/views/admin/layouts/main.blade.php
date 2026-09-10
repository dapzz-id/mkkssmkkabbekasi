@if (Auth::check())
    <!DOCTYPE html>
    <html lang="id" class="h-full bg-slate-50">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="keywords" content="MKKS, Musyawarah Kerja Kepala Sekolah, SMK, SMKN, SMKS, Organisasi, Bekasi, Kabupaten Bekasi">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Panel Admin | MKKS SMK KAB BEKASI</title>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js" async></script>
        <link rel="stylesheet" href="{{ asset('dist/css/main.css') }}">
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link rel="icon" href="{{ asset('img/ic_MKKS.png') }}" type="image/png">
        <link rel="icon" href="{{ asset('img/ic_MKKS.ico') }}" type="image/x-icon">
        <style>
            * { outline: none; }
            body { overflow-x: hidden; }
            .sidebar-nav-active {
                background-color: #eff6ff !important;
                color: #2563eb !important;
                font-weight: 600;
                border: 1px solid #dbeafe;
                box-shadow: 0 1px 2px rgba(37, 99, 235, 0.05);
            }
            .sidebar-nav-active svg {
                color: #2563eb !important;
            }
        </style>
        @stack('styles')
    </head>

    <body class="min-h-screen bg-slate-50 text-slate-800 font-sans flex flex-col antialiased">
        <!-- GLOBAL INSTITUTIONAL HEADER (Fixed Top) -->
        <header class="fixed top-0 inset-x-0 h-20 bg-white border-b border-slate-200/80 z-40 shadow-xs">
            <div class="h-full px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-3">
                <!-- Left: Branding with 2 Official Logos + Title -->
                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                    <!-- Mobile Hamburger Button -->
                    <button id="btnMobileSidebarToggle"
                            type="button"
                            class="lg:hidden p-2 rounded-xl text-slate-600 hover:text-blue-600 hover:bg-slate-100 transition-colors focus:ring-2 focus:ring-blue-400"
                            aria-label="Buka Menu Navigasi">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Official Logos (Disdik Jabar + MKKS) -->
                    <div class="flex items-center gap-2 lg:mr-[90px] sm:gap-2.5 shrink-0 cursor-pointer" onclick="window.location.href='/gallery'">
                        <img src="{{ asset('img/disdik_icon.png') }}"
                             alt="Logo Dinas Pendidikan Jawa Barat"
                             class="h-8 sm:h-9 lg:ml-10 w-auto object-contain"
                             title="Dinas Pendidikan Jawa Barat">
                        <img src="{{ asset('img/ic_MKKS.png') }}"
                             alt="Logo MKKS SMK Kabupaten Bekasi"
                             class="h-8 sm:h-9 w-auto object-contain"
                             title="MKKS SMK Kabupaten Bekasi">
                    </div>

                    <!-- Institutional Typography -->
                    <div class="hidden xs:block sm:block min-w-0">
                        <h1 class="text-sm sm:text-base lg:text-lg font-bold text-slate-900 tracking-tight truncate leading-tight">
                            MKKS SMK KAB BEKASI
                        </h1>
                        <p class="text-[10px] sm:text-xs text-slate-500 font-semibold tracking-wider uppercase leading-none mt-0.5">
                            MUSYAWARAH KERJA KEPALA SEKOLAH
                        </p>
                    </div>
                </div>

                <!-- Right: Account Profile (No Dropdown Arrow) & Red Logout Button -->
                <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                    <!-- User Profile Area: Name and Role below -->
                    <div class="hidden lg:flex items-center gap-2.5 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200/70 text-slate-700 select-none">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="hidden sm:flex flex-col text-left leading-tight">
                            <span class="text-xs sm:text-sm font-bold text-slate-800 tracking-tight">{{ Auth::user()->name }}</span>
                            <span class="text-[11px] font-semibold text-slate-500">
                                {{ Auth::user()->role === 'superadmin' ? 'Super Administrator' : 'Administrator' }}
                            </span>
                        </div>
                    </div>

                    <!-- Red Logout Button with Form POST + CSRF -->
                    <form id="form-logout" action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="button"
                                id="btn-logout"
                                class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-semibold text-xs sm:text-sm rounded-xl shadow-xs transition-all cursor-pointer focus:ring-2 focus:ring-rose-400 min-h-[40px]"
                                aria-label="Keluar dari akun">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span class="hidden sm:inline">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Mobile Off-Canvas Backdrop -->
        <div id="mobileSidebarBackdrop"
             class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-40 lg:hidden hidden transition-opacity duration-300 opacity-0"
             aria-hidden="true"></div>

        <!-- SIDEBAR NAVIGATION (Fixed Stationary on Desktop, Off-canvas on Mobile) -->
        <aside id="sidebar"
               class="fixed top-0 lg:top-20 inset-y-0 left-0 z-50 lg:z-30 w-64 sm:w-72 lg:w-64 bg-white border-r border-slate-200/80 p-5 flex flex-col justify-between shrink-0 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out h-screen lg:h-[calc(100vh-80px)] overflow-y-auto">
                <div>
                    <!-- Mobile Drawer Header with Close Button -->
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100 lg:hidden">
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('img/ic_MKKS.png') }}" alt="MKKS Logo" class="h-7 w-auto">
                            <span class="font-bold text-sm text-slate-800">Menu Admin</span>
                        </div>
                        <button id="btnMobileSidebarClose"
                                type="button"
                                class="p-1.5 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors"
                                aria-label="Tutup Menu">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Navigation Items -->
                    <nav>
                        <ul id="sidebarMenu" class="space-y-2">
                            <!-- 1. Gallery (Public & Superadmin) -->
                            <li>
                                <a href="/gallery"
                                   id="btn-gallery-section"
                                   title="Gallery"
                                   class="flex items-center gap-3.5 px-4 py-3 rounded-2xl text-sm font-medium transition-all {{ Request::is('gallery*') || Request::is('galeri-kelola*') ? 'sidebar-nav-active' : 'text-slate-700 hover:bg-slate-100/80 hover:text-blue-600' }}">
                                    <svg class="w-5 h-5 shrink-0 {{ Request::is('gallery*') || Request::is('galeri-kelola*') ? 'text-blue-600' : 'text-slate-600' }}"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="truncate">Gallery</span>
                                </a>
                            </li>

                            @if (Auth::user()->role == 'superadmin')
                                <!-- 2. Pimpinan MKKS -->
                                <li>
                                    <a href="/pimpinan"
                                       id="btn-pimpinan-section"
                                       title="Pimpinan MKKS"
                                       class="flex items-center gap-3.5 px-4 py-3 rounded-2xl text-sm font-medium transition-all {{ Request::is('pimpinan*') ? 'sidebar-nav-active' : 'text-slate-700 hover:bg-slate-100/80 hover:text-blue-600' }}">
                                        <svg class="w-5 h-5 shrink-0 {{ Request::is('pimpinan*') ? 'text-blue-600' : 'text-slate-600' }}"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <span class="truncate">Pimpinan MKKS</span>
                                    </a>
                                </li>

                                <!-- 3. Sponsor -->
                                <li>
                                    <a href="/sponsor"
                                       id="btn-sponsor-section"
                                       title="Sponsor"
                                       class="flex items-center gap-3.5 px-4 py-3 rounded-2xl text-sm font-medium transition-all {{ Request::is('sponsor*') ? 'sidebar-nav-active' : 'text-slate-700 hover:bg-slate-100/80 hover:text-blue-600' }}">
                                        <svg class="w-5 h-5 shrink-0 {{ Request::is('sponsor*') ? 'text-blue-600' : 'text-slate-600' }}"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <span class="truncate">Sponsor</span>
                                    </a>
                                </li>

                                <!-- 4. Manage Account -->
                                <li>
                                    <a href="/manage/user"
                                       id="btn-user-section"
                                       title="Manage Account"
                                       class="flex items-center gap-3.5 px-4 py-3 rounded-2xl text-sm font-medium transition-all {{ Request::is('manage/user*') || Request::is('subadmin*') ? 'sidebar-nav-active' : 'text-slate-700 hover:bg-slate-100/80 hover:text-blue-600' }}">
                                        <svg class="w-5 h-5 shrink-0 {{ Request::is('manage/user*') || Request::is('subadmin*') ? 'text-blue-600' : 'text-slate-600' }}"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M5.121 17.804A8.977 8.977 0 0112 16c2.397 0 4.577.936 6.121 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0zM12 2a10 10 0 100 20 10 10 0 000-20z" />
                                        </svg>
                                        <span class="truncate">Manage Account</span>
                                    </a>
                                </li>

                                <!-- 5. Event Schedule -->
                                <li>
                                    <a href="/manage/event"
                                       id="btn-calendar-section"
                                       title="Event Schedule"
                                       class="flex items-center gap-3.5 px-4 py-3 rounded-2xl text-sm font-medium transition-all {{ Request::is('manage/event*') || Request::is('calendar*') ? 'sidebar-nav-active' : 'text-slate-700 hover:bg-slate-100/80 hover:text-blue-600' }}">
                                        <svg class="w-5 h-5 shrink-0 {{ Request::is('manage/event*') || Request::is('calendar*') ? 'text-blue-600' : 'text-slate-600' }}"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="truncate">Event Schedule</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </nav>
                </div>

                <!-- Sidebar Footer Note -->
                <div class="pt-4 border-t border-slate-100 text-xs text-slate-400 flex items-center justify-between">
                    <img src="https://assets-pbc-neuracakrawirasolusi.s3.ap-northeast-1.amazonaws.com/ncs_logo+(1).png" height="40" onclick="window.open('https://neuracakrawira.asia', '_blank')" style="cursor: pointer;" width="40" alt="Logo 1" class="lazyload me-2 mb-1">
                    <span><span class="font-bold text-blue-400 cursor-pointer ml-[9px] hover:text-blue-500 transition-all" onclick="window.open('https://neuracakrawira.asia', '_blank')">Neura Cakrawira Solusi</span> <br> <span class="flex justify-end">Version 2.0.3 | <span class="font-bold text-blue-400 cursor-pointer ml-[5px] hover:text-blue-500 transition-all" onclick="window.open('https://alvinzz.my.id', '_blank')">Alvin Coderz</span></span></span>
                </div>
            </aside>

            <!-- MAIN CONTENT AREA (Offset by fixed header and fixed desktop sidebar) -->
            <main class="lg:ml-64 pt-20 min-h-screen bg-[#f8fafc] flex flex-col justify-between overflow-x-hidden">
                <div class="flex-1 w-full">
                    @hasSection('content')
                        @yield('content')
                    @else
                        @yield('container')
                    @endif
                </div>

                <!-- Centered RPanel Footer -->
                @include('admin.layouts.footer')
            </main>

        <!-- Global JS Logic: Logout Confirmation, Mobile Drawer, CRUD Listeners -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Mobile Sidebar Drawer
                const sidebar = document.getElementById('sidebar');
                const backdrop = document.getElementById('mobileSidebarBackdrop');
                const btnOpen = document.getElementById('btnMobileSidebarToggle');
                const btnClose = document.getElementById('btnMobileSidebarClose');

                function openSidebar() {
                    sidebar.classList.remove('-translate-x-full');
                    sidebar.classList.add('translate-x-0');
                    backdrop.classList.remove('hidden');
                    setTimeout(() => backdrop.classList.remove('opacity-0'), 10);
                    document.body.classList.add('overflow-hidden', 'lg:overflow-auto');
                }

                function closeSidebar() {
                    sidebar.classList.remove('translate-x-0');
                    sidebar.classList.add('-translate-x-full');
                    backdrop.classList.add('opacity-0');
                    setTimeout(() => backdrop.classList.add('hidden'), 300);
                    document.body.classList.remove('overflow-hidden', 'lg:overflow-auto');
                }

                if (btnOpen) btnOpen.addEventListener('click', openSidebar);
                if (btnClose) btnClose.addEventListener('click', closeSidebar);
                if (backdrop) backdrop.addEventListener('click', closeSidebar);

                // Logout Confirmation SweetAlert2
                const btnLogout = document.getElementById('btn-logout');
                if (btnLogout) {
                    btnLogout.addEventListener('click', function(e) {
                        e.preventDefault();
                        Swal.fire({
                            title: "Konfirmasi Logout",
                            text: "Apakah Anda yakin ingin keluar dari akun {{ Auth::user()->name }}?",
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#dc2626",
                            cancelButtonColor: "#64748b",
                            confirmButtonText: "Ya, Keluar",
                            cancelButtonText: "Batal",
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                document.getElementById('form-logout').submit();
                            }
                        });
                    });
                }

                // Global event handlers for Subadmin, Calendar, Sponsor CRUD
                attachAccountEventListeners();
                attachCalendarEventListeners();
                attachSponsorEventListeners();

                function attachAccountEventListeners() {
                    document.querySelectorAll('.btn-updateAkun').forEach(function(button) {
                        let cloned = button.cloneNode(true);
                        button.parentNode.replaceChild(cloned, button);
                        cloned.addEventListener('click', function() {
                            let idAkun = this.getAttribute('data-idAkun');
                            window.location.href = '/subadmin/edit/' + idAkun;
                        });
                    });

                    document.querySelectorAll('.btn-deleteAkun').forEach(function(button) {
                        let cloned = button.cloneNode(true);
                        button.parentNode.replaceChild(cloned, button);
                        cloned.addEventListener('click', function(e) {
                            e.preventDefault();
                            let idAkun = this.getAttribute('data-idAkun');
                            let namaAkun = this.getAttribute('data-namaAkun') || 'Akun';

                            Swal.fire({
                                title: "Hapus Akun?",
                                text: "Apakah Anda yakin ingin menghapus akun " + namaAkun + "? Tindakan ini tidak dapat dibatalkan.",
                                icon: "warning",
                                showCancelButton: true,
                                confirmButtonColor: "#dc2626",
                                cancelButtonColor: "#64748b",
                                confirmButtonText: "Hapus",
                                cancelButtonText: "Batal"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    fetch('/subadmin/' + idAkun, {
                                        method: 'DELETE',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                            'Accept': 'application/json'
                                        }
                                    }).then(res => res.json())
                                    .then(data => {
                                        if (data.status === 'success') {
                                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Akun berhasil dihapus.', timer: 1200, showConfirmButton: false })
                                                .then(() => location.reload());
                                        } else {
                                            Swal.fire('Gagal!', data.message || 'Data gagal dihapus.', 'error');
                                        }
                                    });
                                }
                            });
                        });
                    });
                }

                function attachCalendarEventListeners() {
                    document.querySelectorAll('.btn-updateCalendar').forEach(function(button) {
                        let cloned = button.cloneNode(true);
                        button.parentNode.replaceChild(cloned, button);
                        cloned.addEventListener('click', function() {
                            let id = this.getAttribute('data-idCalendar');
                            window.location.href = '/calendar/edit/' + id;
                        });
                    });

                    document.querySelectorAll('.btn-deleteCalendar').forEach(function(button) {
                        let cloned = button.cloneNode(true);
                        button.parentNode.replaceChild(cloned, button);
                        cloned.addEventListener('click', function(e) {
                            e.preventDefault();
                            let id = this.getAttribute('data-idCalendar');
                            let nama = this.getAttribute('data-namaCalendar') || 'Acara';

                            Swal.fire({
                                title: "Hapus Acara?",
                                text: "Apakah Anda yakin ingin menghapus agenda " + nama + "?",
                                icon: "warning",
                                showCancelButton: true,
                                confirmButtonColor: "#dc2626",
                                cancelButtonColor: "#64748b",
                                confirmButtonText: "Hapus",
                                cancelButtonText: "Batal"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    fetch('/calendar/' + id, {
                                        method: 'DELETE',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                            'Accept': 'application/json'
                                        }
                                    }).then(res => res.json())
                                    .then(data => {
                                        if (data.status === 'success') {
                                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Agenda berhasil dihapus.', timer: 1200, showConfirmButton: false })
                                                .then(() => location.reload());
                                        } else {
                                            Swal.fire('Gagal!', data.message || 'Agenda gagal dihapus.', 'error');
                                        }
                                    });
                                }
                            });
                        });
                    });
                }

                function attachSponsorEventListeners() {
                    document.querySelectorAll('.btn-updateSponsor').forEach(function(button) {
                        let cloned = button.cloneNode(true);
                        button.parentNode.replaceChild(cloned, button);
                        cloned.addEventListener('click', function() {
                            let id = this.getAttribute('data-idSponsor');
                            window.location.href = '/sponsor/edit/' + id;
                        });
                    });

                    document.querySelectorAll('.btn-deleteSponsor').forEach(function(button) {
                        let cloned = button.cloneNode(true);
                        button.parentNode.replaceChild(cloned, button);
                        cloned.addEventListener('click', function(e) {
                            e.preventDefault();
                            let id = this.getAttribute('data-idSponsor');
                            let nama = this.getAttribute('data-namaSponsor') || 'Sponsor';

                            Swal.fire({
                                title: "Hapus Sponsor?",
                                text: "Apakah Anda yakin ingin menghapus sponsor " + nama + "?",
                                icon: "warning",
                                showCancelButton: true,
                                confirmButtonColor: "#dc2626",
                                cancelButtonColor: "#64748b",
                                confirmButtonText: "Hapus",
                                cancelButtonText: "Batal"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    fetch('/sponsor/' + id, {
                                        method: 'DELETE',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                            'Accept': 'application/json'
                                        }
                                    }).then(res => res.json())
                                    .then(data => {
                                        if (data.status === 'success') {
                                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Sponsor berhasil dihapus.', timer: 1200, showConfirmButton: false })
                                                .then(() => location.reload());
                                        } else {
                                            Swal.fire('Gagal!', data.message || 'Sponsor gagal dihapus.', 'error');
                                        }
                                    });
                                }
                            });
                        });
                    });
                }
            });
        </script>
        @stack('scripts')
    </body>
    </html>
@else
    @php
        header("Location: /login");
        exit();
    @endphp
@endif
