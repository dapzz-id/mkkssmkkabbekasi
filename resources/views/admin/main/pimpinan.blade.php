@extends('admin.layouts.main')

@section('container')
    @if(Request::is('pimpinan*'))
        <script>
            document.addEventListener("DOMContentLoaded", function(){
                document.querySelectorAll('#sidebarMenu a').forEach(btn => {
                    btn.classList.remove('bg-blue-100', 'text-blue-800');
                    btn.classList.add('text-gray-600');
                    let svg = btn.querySelector('svg');
                    if (svg) {
                        svg.classList.remove('text-blue-800');
                        svg.classList.add('text-gray-600');
                    }
                });

                let pimpinanBtn = document.getElementById('btn-pimpinan-section');
                if (pimpinanBtn) {
                    pimpinanBtn.classList.remove('text-gray-600');
                    pimpinanBtn.classList.add('bg-blue-100', 'text-blue-800');
                    let svg = pimpinanBtn.querySelector('svg');
                    if (svg) {
                        svg.classList.remove('text-gray-600');
                        svg.classList.add('text-blue-800');
                    }
                }
            });
        </script>
    @endif

    <!-- Pimpinan Section -->
    <div id="pimpinanSection" class="p-4 sm:p-6 lg:p-8 space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-sm flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Pimpinan MKKS</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola data pimpinan MKKS SMK Kabupaten Bekasi, urutan, foto 4:3, dan status tampil</p>
                </div>
            </div>

            <!-- Action Button -->
            <a href="/pimpinan/tambah" id="btn-addPimpinan"
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-xs transition-colors min-h-[44px]">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Pimpinan</span>
            </a>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium rounded-xl flex items-center gap-2 shadow-2xs">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Search & Filter Toolbar with Server-Side Autocomplete and Debounce -->
        @php
            $hasPimpinanFilters = request()->filled('search') || (request()->filled('status') && request('status') !== 'all');
        @endphp
        <x-admin.search-filter
            action="/pimpinan"
            suggestionsUrl="/pimpinan/suggestions"
            placeholder="Cari nama atau jabatan..."
            searchName="search"
            :hasActiveFilters="$hasPimpinanFilters"
            resetUrl="/pimpinan">

            <!-- Filter: Status -->
            <div class="relative w-full sm:w-44 shrink-0">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <select name="status"
                        id="filterPimpinanStatus"
                        onchange="this.form.submit()"
                        class="w-full pl-9 pr-8 py-2.5 bg-white border border-slate-200/90 rounded-xl text-xs sm:text-sm font-medium text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-2xs appearance-none cursor-pointer min-h-[44px]">
                    <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>
        </x-admin.search-filter>

        <div id="pimpinanContent">
            @include('admin.pimpinan-table', ['dataPimpinan' => $dataPimpinan])
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            attachPimpinanEventListeners();

            // Handle AJAX pagination
            $(document).on('click', '.pagination-pimpinan a', function(event) {
                event.preventDefault();
                let href = $(this).attr('href');
                let page = href ? href.split('pimpinan-page=')[1] : null;
                if (page) {
                    fetchPimpinanContent(page);
                }
            });

            function renderTableSkeleton() {
                return `
                    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden animate-pulse">
                        <div class="hidden md:block overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-400 uppercase">
                                        <th class="py-3.5 px-4 text-center w-14">No</th>
                                        <th class="py-3.5 px-4 text-center w-28">Foto</th>
                                        <th class="py-3.5 px-4">Nama Pimpinan</th>
                                        <th class="py-3.5 px-4">Jabatan</th>
                                        <th class="py-3.5 px-4 text-center w-20">Urutan</th>
                                        <th class="py-3.5 px-4 text-center w-28">Status</th>
                                        <th class="py-3.5 px-4 text-center w-36">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm">
                                    ${Array(5).fill(0).map(() => `
                                        <tr>
                                            <td class="py-4 px-4 text-center"><div class="h-4 w-6 bg-slate-200 rounded mx-auto"></div></td>
                                            <td class="py-4 px-4 text-center"><div class="w-16 h-12 bg-slate-200 rounded-lg mx-auto"></div></td>
                                            <td class="py-4 px-4"><div class="h-4 w-40 bg-slate-200 rounded"></div></td>
                                            <td class="py-4 px-4"><div class="h-4 w-32 bg-slate-200 rounded"></div></td>
                                            <td class="py-4 px-4 text-center"><div class="h-6 w-8 bg-slate-200 rounded-lg mx-auto"></div></td>
                                            <td class="py-4 px-4 text-center"><div class="h-6 w-16 bg-slate-200 rounded-full mx-auto"></div></td>
                                            <td class="py-4 px-4 text-center"><div class="h-8 w-20 bg-slate-200 rounded-xl mx-auto"></div></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                        <div class="md:hidden p-4 space-y-3">
                            ${Array(3).fill(0).map(() => `
                                <div class="p-3 rounded-xl border border-slate-200 flex items-center gap-3">
                                    <div class="w-16 h-12 bg-slate-200 rounded-lg flex-shrink-0"></div>
                                    <div class="flex-1 space-y-2">
                                        <div class="h-4 w-3/4 bg-slate-200 rounded"></div>
                                        <div class="h-3 w-1/2 bg-slate-200 rounded"></div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }

            function fetchPimpinanContent(page) {
                // UI State 1: Data fetching uses Skeleton Loading
                $('#pimpinanContent').html(renderTableSkeleton());

                $.ajax({
                    url: "/pimpinan?pimpinan-page=" + page,
                    success: function(data) {
                        $('#pimpinanContent').html(data);
                        history.pushState(null, null, "/pimpinan");
                        attachPimpinanEventListeners();
                    },
                    error: function() {
                        $('#pimpinanContent').html(`
                            <div class="p-6 text-center text-red-600 bg-red-50 rounded-xl border border-red-200">
                                <p class="text-sm font-semibold">Gagal memuat data pimpinan.</p>
                                <button onclick="location.reload()" class="mt-2 text-xs px-3 py-1.5 bg-red-600 text-white rounded-lg">Muat Ulang</button>
                            </div>
                        `);
                    }
                });
            }
        });

        function attachPimpinanEventListeners() {
            // Edit button handler
            document.querySelectorAll('.btn-updatePimpinan').forEach(button => {
                let cloned = button.cloneNode(true);
                button.parentNode.replaceChild(cloned, button);
                cloned.addEventListener('click', function() {
                    let id = this.getAttribute('data-idPimpinan');
                    window.location.href = '/pimpinan/edit/' + id;
                });
            });

            // Toggle active status handler
            document.querySelectorAll('.btn-toggleStatus').forEach(button => {
                let cloned = button.cloneNode(true);
                button.parentNode.replaceChild(cloned, button);
                cloned.addEventListener('click', function() {
                    let id = this.getAttribute('data-idPimpinan');
                    let btn = this;
                    btn.disabled = true;

                    fetch('/pimpinan/' + id + '/toggle-status', {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        btn.disabled = false;
                        if (data.status === 'success') {
                            if (data.is_active) {
                                btn.className = 'btn-toggleStatus px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 hover:bg-green-200 cursor-pointer';
                                btn.textContent = 'Aktif';
                            } else {
                                btn.className = 'btn-toggleStatus px-3 py-1 text-xs font-semibold rounded-full bg-gray-200 text-gray-700 hover:bg-gray-300 cursor-pointer';
                                btn.textContent = 'Nonaktif';
                            }
                        }
                    })
                    .catch(() => {
                        btn.disabled = false;
                    });
                });
            });

            // Delete button handler
            document.querySelectorAll('.btn-deletePimpinan').forEach(button => {
                let cloned = button.cloneNode(true);
                button.parentNode.replaceChild(cloned, button);
                cloned.addEventListener('click', function(e) {
                    e.preventDefault();
                    let id = this.getAttribute('data-idPimpinan');
                    let nama = this.getAttribute('data-namaPimpinan');

                    Swal.fire({
                        title: "Hapus Pimpinan?",
                        text: "Apakah Anda yakin ingin menghapus data " + nama + "? Data dan foto yang dihapus tidak bisa dikembalikan!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#64748b",
                        confirmButtonText: "Ya, Hapus!",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('/pimpinan/' + id, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json'
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    Swal.fire({
                                        title: "Berhasil!",
                                        text: "Data pimpinan berhasil dihapus.",
                                        icon: "success",
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire("Gagal!", data.message || "Gagal menghapus data.", "error");
                                }
                            })
                            .catch(() => {
                                Swal.fire("Gagal!", "Terjadi kesalahan sistem saat menghapus.", "error");
                            });
                        }
                    });
                });
            });
        }
    </script>
@endsection
