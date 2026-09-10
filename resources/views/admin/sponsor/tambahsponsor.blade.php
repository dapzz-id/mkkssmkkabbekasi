@extends('admin.layouts.main')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 space-y-6">
    <!-- PAGE HEADER & BREADCRUMB -->
    <div class="flex flex-col gap-4 pt-1 sm:pt-2">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
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
                        Tambah data mitra sponsor MKKS SMK Kab Bekasi
                    </p>
                </div>
            </div>

            <!-- Action Buttons: Kembali -->
            <div class="flex items-center gap-3 shrink-0">
                <a href="/sponsor"
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
            <x-admin-breadcrumb :items="[
                ['label' => 'Sponsor', 'url' => '/sponsor'],
                ['label' => 'Tambah Sponsor']
            ]" />
        </div>
    </div>

    <!-- MAIN FORM CARD -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Form Tambah Sponsor</h2>
            <span class="text-xs text-slate-500 font-medium">* Wajib diisi</span>
        </div>

        <form action="{{ route('sponsor.store') }}" id="formTambahSponsor" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
            @csrf

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

            <!-- Nama Sponsor -->
            <div>
                <label for="nama" class="block text-sm font-semibold text-slate-800 mb-2">
                    Nama Sponsor / Perusahaan <span class="text-rose-500">*</span>
                </label>
                <input type="text"
                       name="nama"
                       id="nama"
                       value="{{ old('nama') }}"
                       required
                       placeholder="Contoh: PT Neura Cakrawira Solusi"
                       class="w-full px-4 py-2.5 bg-white border @error('nama') border-rose-400 focus:ring-rose-400 @else border-slate-300 focus:ring-blue-500 focus:border-blue-500 @enderror rounded-xl text-sm font-medium text-slate-800 focus:ring-2 shadow-2xs transition-all">
                @error('nama')
                    <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Upload Logo/Foto Sponsor dengan Drag & Drop -->
            <div>
                <label class="block text-sm font-semibold text-slate-800 mb-1">
                    Logo / Gambar Sponsor <span class="text-rose-500">*</span>
                </label>
                <p class="text-xs text-slate-500 mb-2.5">Tarik & lepas file gambar logo atau klik area di bawah untuk memilih.</p>

                <!-- Modern Drag & Drop Zone -->
                <div id="dropzoneSponsor"
                     class="relative border-2 border-dashed border-slate-300 hover:border-blue-500 bg-slate-50/60 hover:bg-blue-50/20 rounded-2xl p-6 sm:p-8 text-center transition-all cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-400 group"
                     tabindex="0"
                     role="button"
                     aria-label="Upload logo sponsor. Tarik dan lepas atau klik untuk memilih file">

                    <input type="file"
                           name="url_image"
                           id="url_image"
                           accept="image/jpeg,image/png,image/jpg,image/svg+xml,image/gif"
                           required
                           class="sr-only"
                           aria-hidden="true">

                    <!-- Default Empty State -->
                    <div id="dropzoneDefaultState" class="flex flex-col items-center">
                        <div class="w-14 h-14 mb-3 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-2xs">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-800">
                            <span class="text-blue-600 hover:underline">Tarik & Lepas Logo di sini</span> atau Klik untuk Memilih
                        </p>
                        <p class="text-xs text-slate-400 mt-1">Mendukung format: JPEG, PNG, JPG, SVG, GIF (Maks. 2MB)</p>
                    </div>

                    <!-- Dragging Active State -->
                    <div id="dropzoneDragState" class="hidden flex flex-col items-center py-2">
                        <div class="w-14 h-14 mb-3 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center animate-bounce shadow-md">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-blue-700">Lepaskan file logo sponsor untuk memilih</p>
                    </div>
                </div>

                <!-- Preview Card (After file selected) -->
                <div id="previewWrapper" class="hidden mt-3 p-4 bg-white rounded-2xl border border-slate-200 shadow-2xs flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-16 h-14 rounded-xl bg-slate-50 border border-slate-200 p-1 flex items-center justify-center shrink-0 overflow-hidden">
                            <img id="imagePreview" src="" alt="Preview Logo" class="max-h-full max-w-full object-contain">
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 id="previewFileName" class="text-xs sm:text-sm font-bold text-slate-900 truncate">logo.png</h4>
                            <p id="previewFileSize" class="text-xs text-slate-400 mt-0.5">0 KB</p>
                        </div>
                    </div>
                    <button type="button"
                            id="btnRemoveFile"
                            class="px-3 py-1.5 text-xs font-semibold text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition-colors shrink-0 cursor-pointer"
                            title="Hapus file terpilih">
                        Ganti File
                    </button>
                </div>

                <!-- Client Error Message -->
                <p id="uploadErrorMsg" class="hidden text-xs font-semibold text-rose-600 mt-2 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span id="uploadErrorText"></span>
                </p>

                <!-- REAL UPLOAD PROGRESS BAR -->
                <div id="uploadProgressSection" class="hidden mt-3 p-4 bg-white border border-blue-100 rounded-2xl shadow-xs space-y-2">
                    <div class="flex items-center justify-between text-xs font-semibold">
                        <span id="uploadStatusText" class="text-slate-700 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            Mengunggah logo sponsor...
                        </span>
                        <span id="uploadPercent" class="font-mono text-blue-600 font-bold">0%</span>
                    </div>
                    <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                        <div id="uploadProgressBar" class="h-full bg-blue-600 rounded-full transition-all duration-150" style="width: 0%"></div>
                    </div>
                </div>

                @error('url_image')
                    <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- ACTION BUTTONS -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="/sponsor"
                   class="px-5 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-semibold transition-all cursor-pointer">
                    Batal
                </a>
                <button type="button"
                        id="btnSubmitSponsor"
                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold rounded-xl shadow-xs transition-all cursor-pointer focus:ring-2 focus:ring-blue-400 min-w-[120px]">
                    <span id="btnSubmitText">Simpan Sponsor</span>
                    <svg id="btnSubmitSpinner" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropzone = document.getElementById('dropzoneSponsor');
    const fileInput = document.getElementById('url_image');
    const defaultState = document.getElementById('dropzoneDefaultState');
    const dragState = document.getElementById('dropzoneDragState');
    const previewWrapper = document.getElementById('previewWrapper');
    const previewImg = document.getElementById('imagePreview');
    const previewFileName = document.getElementById('previewFileName');
    const previewFileSize = document.getElementById('previewFileSize');
    const btnRemove = document.getElementById('btnRemoveFile');
    const errorMsg = document.getElementById('uploadErrorMsg');
    const errorText = document.getElementById('uploadErrorText');

    const progressSection = document.getElementById('uploadProgressSection');
    const progressBar = document.getElementById('uploadProgressBar');
    const progressPercent = document.getElementById('uploadPercent');
    const statusText = document.getElementById('uploadStatusText');

    const btnSubmit = document.getElementById('btnSubmitSponsor');
    const btnText = document.getElementById('btnSubmitText');
    const btnSpinner = document.getElementById('btnSubmitSpinner');
    const form = document.getElementById('formTambahSponsor');

    let currentFile = null;

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

    function handleFile(file) {
        clearError();
        if (!file) return;

        // Validation: 2MB max
        if (file.size > 2 * 1024 * 1024) {
            showError('Ukuran file tidak boleh lebih dari 2MB.');
            return;
        }

        // Validation: format
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/svg+xml'];
        if (!validTypes.includes(file.type)) {
            showError('Format gambar harus berupa JPEG, PNG, JPG, GIF, atau SVG.');
            return;
        }

        currentFile = file;

        // Preview
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewFileName.textContent = file.name;
            previewFileSize.textContent = formatBytes(file.size);
            previewWrapper.classList.remove('hidden');
            dropzone.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }

    // Click dropzone to browse
    dropzone.addEventListener('click', function() {
        fileInput.click();
    });

    // Keyboard access on dropzone
    dropzone.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            fileInput.click();
        }
    });

    // Native file input change
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            handleFile(this.files[0]);
        }
    });

    // Drag & Drop events
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
        if (dt && dt.files && dt.files[0]) {
            fileInput.files = dt.files;
            handleFile(dt.files[0]);
        }
    });

    // Remove file / choose again
    btnRemove.addEventListener('click', function(e) {
        e.stopPropagation();
        currentFile = null;
        fileInput.value = '';
        previewImg.src = '';
        previewWrapper.classList.add('hidden');
        dropzone.classList.remove('hidden');
        clearError();
    });

    // Submit with Real XHR Progress Bar
    if (btnSubmit && form) {
        btnSubmit.addEventListener('click', function() {
            clearError();

            const namaInput = document.getElementById('nama');
            if (!namaInput.value.trim()) {
                namaInput.focus();
                showError('Nama sponsor wajib diisi.');
                return;
            }

            if (!currentFile && (!fileInput.files || !fileInput.files[0])) {
                showError('Silakan pilih atau tarik file logo sponsor terlebih dahulu.');
                return;
            }

            Swal.fire({
                title: "Simpan Sponsor?",
                text: "Apakah Anda yakin ingin menambahkan data mitra sponsor ini?",
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

                // Show progress bar
                progressSection.classList.remove('hidden');
                progressBar.style.width = '0%';
                progressPercent.textContent = '0%';

                const formData = new FormData(form);
                // Ensure the selected file is appended
                if (currentFile) {
                    formData.set('url_image', currentFile);
                }

                const xhr = new XMLHttpRequest();
                xhr.open('POST', form.action || '/sponsor', true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percent = Math.round((e.loaded / e.total) * 100);
                        progressBar.style.width = percent + '%';
                        progressPercent.textContent = percent + '%';
                        if (percent >= 100) {
                            btnText.textContent = "Memproses...";
                        }
                    }
                });

                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 400) {
                        progressBar.style.width = '100%';
                        progressPercent.textContent = '100%';
                        window.location.href = '/sponsor';
                    } else {
                        btnSubmit.disabled = false;
                        btnSubmit.classList.remove('opacity-75', 'cursor-not-allowed');
                        btnText.textContent = "Simpan Sponsor";
                        btnSpinner.classList.add('hidden');
                        progressSection.classList.add('hidden');

                        try {
                            const resp = JSON.parse(xhr.responseText);
                            if (resp.errors) {
                                const firstKey = Object.keys(resp.errors)[0];
                                showError(resp.errors[firstKey][0]);
                            } else {
                                showError('Gagal menyimpan sponsor. Silakan periksa formulir.');
                            }
                        } catch (err) {
                            showError('Terjadi kesalahan server (' + xhr.status + ').');
                        }
                    }
                };

                xhr.onerror = function() {
                    btnSubmit.disabled = false;
                    btnSubmit.classList.remove('opacity-75', 'cursor-not-allowed');
                    btnText.textContent = "Simpan Sponsor";
                    btnSpinner.classList.add('hidden');
                    progressSection.classList.add('hidden');
                    showError('Gagal menghubungi server. Periksa koneksi internet Anda.');
                };

                xhr.send(formData);
            });
        });
    }
});
</script>
@endpush
