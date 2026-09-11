@extends('admin.layouts.main')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8 space-y-6">
        <!-- PAGE HEADER & BREADCRUMB -->
        <div class="flex flex-col gap-4 pt-1 sm:pt-2">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div
                        class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight leading-tight">
                            Gallery
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-500 mt-0.5 font-medium">
                            Tambah data galeri kegiatan MKKS SMK Kab Bekasi
                        </p>
                    </div>
                </div>

                <!-- Action Buttons: SEO Settings & Kembali -->
                <div class="flex items-center gap-3 shrink-0">
                    <button type="button" id="btnOpenSeoModal"
                        class="inline-flex items-center gap-2.5 px-4 py-2.5 rounded-xl border border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-sm transition-all shadow-xs min-h-[44px] cursor-pointer">
                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>SEO Settings</span>
                        <span id="seoBadge" class="hidden text-[11px] bg-blue-600 text-white px-2 py-0.5 rounded-full font-bold uppercase tracking-wider">Terkonfigurasi</span>
                    </button>
                    <a href="/gallery"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm transition-all shadow-xs min-h-[44px]">
                        <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Kembali</span>
                    </a>
                </div>
            </div>

            <!-- Breadcrumb with lowered spacing -->
            <div class="pt-1.5">
                <x-admin-breadcrumb :items="[['label' => 'Gallery', 'url' => '/gallery'], ['label' => 'Tambah Gallery']]" />
            </div>
        </div>

        <!-- MAIN FORM CARD -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-900">Form Tambah Galeri</h2>
                <span class="text-xs text-slate-500 font-medium">* Wajib diisi</span>
            </div>

            <form action="{{ route('galeri.store') }}" id="formGalleryCreate" method="POST" enctype="multipart/form-data"
                class="p-6 sm:p-8 space-y-6">
                @csrf
                <!-- SEO Hidden Inputs (persisted together with Gallery in the same request) -->
                <input type="hidden" name="seo_title" id="hiddenSeoTitle" value="{{ old('seo_title') }}">
                <input type="hidden" name="seo_description" id="hiddenSeoDescription" value="{{ old('seo_description') }}">
                <input type="hidden" name="slug" id="hiddenSlug" value="{{ old('slug') }}">

                @if ($errors->any())
                    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                        <p class="font-bold mb-1">Terjadi kesalahan input:</p>
                        <ul class="list-disc list-inside space-y-0.5 text-xs">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Judul -->
                <div>
                    <label for="judul" class="block text-sm font-semibold text-slate-800 mb-2">
                        Judul Galeri <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="judul" id="judul" value="{{ old('judul') }}" required
                        placeholder="Contoh: Rapat Koordinasi MKKS SMK Kab Bekasi 2026"
                        class="w-full px-4 py-2.5 bg-white border @error('judul') border-rose-400 focus:ring-rose-400 @else border-slate-300 focus:ring-blue-500 focus:border-blue-500 @enderror rounded-xl text-sm font-medium text-slate-800 focus:ring-2 shadow-2xs transition-all">
                    @error('judul')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Divisi -->
                <div>
                    <label for="divisi_uuid" class="block text-sm font-semibold text-slate-800 mb-2">
                        Divisi Terkait <span class="text-rose-500">*</span>
                    </label>
                    @php
                        if (Auth::user()->role == 'superadmin') {
                            $divisiList = \App\Models\Divisi::all();
                        } else {
                            $divisiList = \App\Models\Divisi::where('uuid', Auth::user()->divisi_uuid)->get();
                        }
                    @endphp
                    <div class="relative">
                        <select name="divisi_uuid" id="divisi_uuid" required
                            class="w-full pl-4 pr-10 py-2.5 border @error('divisi_uuid') border-rose-400 @else border-slate-300 focus:ring-blue-500 focus:border-blue-500 @enderror rounded-xl text-sm font-medium text-slate-800 focus:ring-2 shadow-2xs appearance-none transition-all cursor-pointer {{ Auth::user()->role !== 'superadmin' ? 'bg-slate-100 pointer-events-none' : 'bg-white' }}"
                            {{ Auth::user()->role !== 'superadmin' ? 'tabindex="-1"' : '' }}>
                            
                            @if(Auth::user()->role == 'superadmin')
                                <option value="" disabled {{ !old('divisi_uuid') && !Auth::user()->divisi_uuid ? 'selected' : '' }}>Pilih Divisi</option>
                            @endif
                            
                            @foreach ($divisiList as $item)
                                <option value="{{ $item->uuid }}"
                                    {{ old('divisi_uuid', Auth::user()->divisi_uuid) == $item->uuid ? 'selected' : '' }}>
                                    {{ $item->nama_divisi }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                    @error('divisi_uuid')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deskripsi -->
                <div>
                    <label for="deskripsi" class="block text-sm font-semibold text-slate-800 mb-2">
                        Deskripsi Kegiatan <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="deskripsi" id="deskripsi" rows="5" required
                        placeholder="Tuliskan deskripsi lengkap kegiatan di sini..."
                        class="w-full px-4 py-2.5 bg-white border @error('deskripsi') border-rose-400 focus:ring-rose-400 @else border-slate-300 focus:ring-blue-500 focus:border-blue-500 @enderror rounded-xl text-sm font-medium text-slate-800 focus:ring-2 shadow-2xs transition-all leading-relaxed">{{ old('deskripsi') }}</textarea>
                    <p class="text-xs text-slate-400 mt-1 font-medium">* Kolom deskripsi mendukung format tag HTML dasar
                        (seperti &lt;b&gt;, &lt;i&gt;, &lt;p&gt;).</p>
                    @error('deskripsi')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Unggah Media Multi-file dengan Drag & Drop -->
                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-1">
                        Unggah Media (Foto / Video) <span class="text-rose-500">*</span>
                    </label>
                    <p class="text-xs text-slate-500 mb-2.5">
                        Tarik & lepas banyak file sekaligus (batch) atau klik area di bawah. Format didukung: JPG, JPEG, PNG
                        (Maks. 10MB), MP4 (Maks. 100 MiB = 104,857,600 bytes).
                    </p>

                    <!-- Modern Multi-File Drag & Drop Zone -->
                    <div id="dropzoneGallery"
                        class="relative border-2 border-dashed border-slate-300 hover:border-blue-500 bg-slate-50/60 hover:bg-blue-50/20 rounded-2xl p-6 sm:p-8 text-center transition-all cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-400 group"
                        tabindex="0" role="button"
                        aria-label="Upload media galeri. Tarik dan lepas beberapa file atau klik untuk memilih">

                        <input type="file" id="mediaInput" accept="image/jpeg,image/png,image/jpg,video/mp4" multiple
                            class="sr-only" aria-hidden="true">

                        <!-- Default State -->
                        <div id="dropzoneDefaultState" class="flex flex-col items-center">
                            <div
                                class="w-14 h-14 mb-3 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-2xs">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-slate-800">
                                <span class="text-blue-600 hover:underline">Tarik & Lepas Foto / Video di sini</span> atau
                                Klik untuk Memilih
                            </p>
                            <p class="text-xs text-slate-400 mt-1">Bisa memilih lebih dari 1 file sekaligus (Video MP4 maks.
                                100 MiB / 104,857,600 bytes, Foto maks. 10MB)</p>
                        </div>

                        <!-- Dragging State -->
                        <div id="dropzoneDragState" class="hidden flex flex-col items-center py-2">
                            <div
                                class="w-14 h-14 mb-3 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center animate-bounce shadow-md">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-blue-700">Lepaskan file untuk menambahkan ke daftar media</p>
                        </div>
                    </div>

                    <!-- Client Error Alert -->
                    <p id="uploadErrorMsg" class="hidden text-xs font-semibold text-rose-600 mt-2 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                        <span id="uploadErrorText"></span>
                    </p>

                    <!-- Queued Media Preview Section -->
                    <div id="queuedSection" class="hidden mt-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <span
                                class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                                <span>Media Terpilih:</span>
                                <span id="queuedCountBadge"
                                    class="px-2 py-0.5 rounded-full text-2xs font-bold bg-blue-100 text-blue-800">0
                                    file</span>
                            </span>
                            <button type="button" id="btnClearAllQueue"
                                class="text-xs text-rose-600 hover:text-rose-800 font-semibold cursor-pointer transition-colors">
                                Hapus Semua
                            </button>
                        </div>

                        <div id="mediaPreviewGrid"
                            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 pt-1"></div>
                    </div>

                    <!-- REAL UPLOAD PROGRESS BAR -->
                    <div id="uploadProgressSection"
                        class="hidden mt-4 p-4 bg-white border border-blue-100 rounded-2xl shadow-xs space-y-2">
                        <div class="flex items-center justify-between text-xs font-semibold">
                            <span id="uploadStatusText" class="text-slate-700 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                Mengunggah media kegiatan...
                            </span>
                            <span id="uploadPercent" class="font-mono text-blue-600 font-bold">0%</span>
                        </div>
                        <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                            <div id="uploadProgressBar"
                                class="h-full bg-blue-600 rounded-full transition-all duration-150" style="width: 0%">
                            </div>
                        </div>
                        <p id="uploadBytesText" class="text-[11px] text-slate-400 font-mono text-right">0 MB / 0 MB</p>
                    </div>

                    @error('media')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                    @error('media.*')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- ACTION BUTTONS -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="/gallery"
                        class="px-5 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-semibold transition-all cursor-pointer">
                        Batal
                    </a>
                    <button type="button" id="btnSubmitGallery"
                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold rounded-xl shadow-xs transition-all cursor-pointer focus:ring-2 focus:ring-blue-400 min-w-[120px]">
                        <span id="btnSubmitText">Simpan Galeri</span>
                        <svg id="btnSubmitSpinner" class="w-4 h-4 animate-spin hidden" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @include('admin.galeri.partials.seo-modal')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropzone = document.getElementById('dropzoneGallery');
            const mediaInput = document.getElementById('mediaInput');
            const defaultState = document.getElementById('dropzoneDefaultState');
            const dragState = document.getElementById('dropzoneDragState');
            const queuedSection = document.getElementById('queuedSection');
            const previewGrid = document.getElementById('mediaPreviewGrid');
            const queuedCountBadge = document.getElementById('queuedCountBadge');
            const btnClearAll = document.getElementById('btnClearAllQueue');

            const errorMsg = document.getElementById('uploadErrorMsg');
            const errorText = document.getElementById('uploadErrorText');

            const progressSection = document.getElementById('uploadProgressSection');
            const progressBar = document.getElementById('uploadProgressBar');
            const progressPercent = document.getElementById('uploadPercent');
            const statusText = document.getElementById('uploadStatusText');
            const bytesText = document.getElementById('uploadBytesText');

            const btnSubmit = document.getElementById('btnSubmitGallery');
            const btnText = document.getElementById('btnSubmitText');
            const btnSpinner = document.getElementById('btnSubmitSpinner');
            const form = document.getElementById('formGalleryCreate');

            // Multi-file queue array
            let queuedFiles = [];

            function formatBytes(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            function showError(msg) {
                errorText.textContent = msg;
                errorMsg.classList.remove('hidden');
            }

            function clearError() {
                errorMsg.classList.add('hidden');
                errorText.textContent = '';
            }

            function addFiles(files) {
                clearError();
                if (!files || !files.length) return;

                const maxVideoBytes = 104857600; // 100 MiB (104,857,600 bytes)
                const maxImageBytes = 10485760; // 10 MB
                const validExts = ['jpg', 'jpeg', 'png', 'mp4'];

                let rejectedCount = 0;
                let rejectedMsg = '';

                Array.from(files).forEach(file => {
                    const ext = file.name.split('.').pop().toLowerCase();
                    if (!validExts.includes(ext)) {
                        rejectedCount++;
                        rejectedMsg = 'Format file tidak didukung: ' + file.name +
                            ' (Hanya JPG, JPEG, PNG, MP4).';
                        return;
                    }

                    if (ext === 'mp4' && file.size > maxVideoBytes) {
                        rejectedCount++;
                        rejectedMsg = 'Video melebihi batas 100 MiB (104,857,600 bytes): ' + file.name;
                        return;
                    }

                    if (ext !== 'mp4' && file.size > maxImageBytes) {
                        rejectedCount++;
                        rejectedMsg = 'Foto melebihi batas 10MB: ' + file.name;
                        return;
                    }

                    // Check duplicate by name and size
                    const isDuplicate = queuedFiles.some(f => f.name === file.name && f.size === file.size);
                    if (isDuplicate) return;

                    queuedFiles.push(file);
                });

                if (rejectedCount > 0) {
                    showError(rejectedMsg);
                }

                renderQueue();
            }

            function renderQueue() {
                previewGrid.innerHTML = '';

                if (queuedFiles.length === 0) {
                    queuedSection.classList.add('hidden');
                    return;
                }

                queuedSection.classList.remove('hidden');
                queuedCountBadge.textContent = `${queuedFiles.length} file`;

                queuedFiles.forEach((file, index) => {
                    const isVideo = file.type.startsWith('video/') || file.name.endsWith('.mp4');
                    const card = document.createElement('div');
                    card.className =
                        'relative group aspect-square rounded-2xl overflow-hidden bg-slate-100 border border-slate-200/90 shadow-2xs flex items-center justify-center';

                    const previewUrl = URL.createObjectURL(file);

                    if (isVideo) {
                        const vid = document.createElement('video');
                        vid.src = previewUrl;
                        vid.className = 'w-full h-full object-contain p-1 rounded-xl';
                        vid.controls = false;
                        vid.muted = true;
                        card.appendChild(vid);

                        const badge = document.createElement('span');
                        badge.className =
                            'absolute bottom-1.5 left-1.5 px-2 py-0.5 rounded-md text-xs #  font-bold text-white uppercase bg-indigo-600/90 shadow-xs pointer-events-none';
                        badge.textContent = 'Video';
                        card.appendChild(badge);
                    } else {
                        const img = document.createElement('img');
                        img.src = previewUrl;
                        img.alt = file.name;
                        img.className = 'w-full h-full object-contain p-1 rounded-xl';
                        card.appendChild(img);

                        const badge = document.createElement('span');
                        badge.className =
                            'absolute bottom-1.5 left-1.5 px-2 py-0.5 rounded-md text-xs #  font-bold text-white uppercase bg-slate-800/80 shadow-xs pointer-events-none';
                        badge.textContent = 'Foto';
                        card.appendChild(badge);
                    }

                    // File size indicator
                    const sizeSpan = document.createElement('span');
                    sizeSpan.className =
                        'absolute bottom-1.5 right-1.5 px-1.5 py-0.5 rounded text-xs #  font-semibold text-slate-700 bg-white/90 shadow-2xs pointer-events-none';
                    sizeSpan.textContent = formatBytes(file.size);
                    card.appendChild(sizeSpan);

                    // Delete button
                    const btnDel = document.createElement('button');
                    btnDel.type = 'button';
                    btnDel.className =
                        'absolute top-1.5 right-1.5 w-7 h-7 rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white flex items-center justify-center shadow-md transition-transform hover:scale-105 cursor-pointer';
                    btnDel.title = 'Hapus file ini';
                    btnDel.innerHTML =
                        '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                    btnDel.addEventListener('click', function(e) {
                        e.stopPropagation();
                        URL.revokeObjectURL(previewUrl);
                        queuedFiles.splice(index, 1);
                        renderQueue();
                    });
                    card.appendChild(btnDel);

                    previewGrid.appendChild(card);
                });
            }

            // Dropzone interaction
            dropzone.addEventListener('click', function() {
                mediaInput.click();
            });

            dropzone.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    mediaInput.click();
                }
            });

            mediaInput.addEventListener('change', function() {
                addFiles(this.files);
                this.value = ''; // Reset so the same file can be re-selected if removed
            });

            // Drag & Drop
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('border-blue-500', 'bg-blue-50/40');
                    defaultState.classList.add('hidden');
                    dragState.classList.remove('hidden');
                });
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('border-blue-500', 'bg-blue-50/40');
                    dragState.classList.add('hidden');
                    defaultState.classList.remove('hidden');
                });
            });

            dropzone.addEventListener('drop', function(e) {
                const dt = e.dataTransfer;
                if (dt && dt.files && dt.files.length) {
                    addFiles(dt.files);
                }
            });

            // Clear all
            btnClearAll.addEventListener('click', function() {
                queuedFiles = [];
                renderQueue();
                clearError();
            });

            // XHR Submit with Real Progress Bar
            if (btnSubmit && form) {
                btnSubmit.addEventListener('click', function() {
                    clearError();

                    const judulInput = document.getElementById('judul');
                    const deskripsiInput = document.getElementById('deskripsi');
                    const divisiSelect = document.getElementById('divisi_uuid');

                    if (!judulInput.value.trim()) {
                        judulInput.focus();
                        showError('Judul galeri wajib diisi.');
                        return;
                    }

                    if (divisiSelect && !divisiSelect.value) {
                        divisiSelect.focus();
                        showError('Silakan pilih divisi terkait.');
                        return;
                    }

                    if (queuedFiles.length === 0) {
                        showError('Silakan unggah setidaknya 1 foto atau video untuk kegiatan ini.');
                        return;
                    }

                    if (!deskripsiInput.value.trim()) {
                        deskripsiInput.focus();
                        showError('Deskripsi kegiatan wajib diisi.');
                        return;
                    }

                    Swal.fire({
                        title: "Simpan Galeri?",
                        text: `Anda akan mengunggah ${queuedFiles.length} file media untuk kegiatan ini.`,
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonColor: "#2563eb",
                        cancelButtonColor: "#64748b",
                        confirmButtonText: "Ya, Simpan",
                        cancelButtonText: "Batal",
                        reverseButtons: true
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        // Lock UI
                        btnSubmit.disabled = true;
                        btnSubmit.classList.add('opacity-75', 'cursor-not-allowed');
                        btnText.textContent = "Mengunggah...";
                        btnSpinner.classList.remove('hidden');

                        progressSection.classList.remove('hidden');
                        progressBar.style.width = '0%';
                        progressPercent.textContent = '0%';

                        const formData = new FormData(form);
                        // Remove any empty media[] fields
                        formData.delete('media[]');
                        // Append all queued files
                        queuedFiles.forEach(file => {
                            formData.append('media[]', file);
                        });

                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', form.action || '/gallery', true);
                        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                const percent = Math.round((e.loaded / e.total) * 100);
                                progressBar.style.width = percent + '%';
                                progressPercent.textContent = percent + '%';
                                bytesText.textContent =
                                    `${formatBytes(e.loaded)} / ${formatBytes(e.total)}`;
                                if (percent >= 100) {
                                    btnText.textContent = "Memproses...";
                                    statusText.innerHTML =
                                        '<svg class="w-3.5 h-3.5 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Memproses dan menyimpan berkas ke server...';
                                }
                            }
                        });

                        xhr.onload = function() {
                            if (xhr.status >= 200 && xhr.status < 400) {
                                progressBar.style.width = '100%';
                                progressPercent.textContent = '100%';
                                window.location.href = '/gallery';
                            } else {
                                btnSubmit.disabled = false;
                                btnSubmit.classList.remove('opacity-75', 'cursor-not-allowed');
                                btnText.textContent = "Simpan Galeri";
                                btnSpinner.classList.add('hidden');
                                progressSection.classList.add('hidden');

                                try {
                                    const resp = JSON.parse(xhr.responseText);
                                    if (resp.errors) {
                                        const firstKey = Object.keys(resp.errors)[0];
                                        showError(resp.errors[firstKey][0]);
                                    } else {
                                        showError(
                                            'Gagal menyimpan galeri. Silakan periksa formulir.'
                                            );
                                    }
                                } catch (err) {
                                    showError('Terjadi kesalahan server (' + xhr.status + ').');
                                }
                            }
                        };

                        xhr.onerror = function() {
                            btnSubmit.disabled = false;
                            btnSubmit.classList.remove('opacity-75', 'cursor-not-allowed');
                            btnText.textContent = "Simpan Galeri";
                            btnSpinner.classList.add('hidden');
                            progressSection.classList.add('hidden');
                            showError(
                                'Gagal menghubungi server. Periksa koneksi internet Anda.');
                        };

                        xhr.send(formData);
                    });
                });
            }
        });
    </script>
@endpush
