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
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight leading-tight">
                            Sponsor
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-500 mt-0.5 font-medium">
                            Edit data mitra sponsor #{{ $sponsor->id }}
                        </p>
                    </div>
                </div>

                <a href="/sponsor"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold transition-all shadow-2xs shrink-0 self-start sm:self-auto min-h-[44px]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali</span>
                </a>
            </div>

            <div class="pt-1.5">
                <x-admin-breadcrumb :items="[['label' => 'Sponsor', 'url' => '/sponsor'], ['label' => $sponsor->id . '_edit']]" />
            </div>
        </div>

        <!-- MAIN FORM CARD -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-900">Form Edit Sponsor #{{ $sponsor->id }}</h2>
                <span class="text-xs text-slate-500 font-medium">* Wajib diisi</span>
            </div>

            <form action="{{ route('sponsor.update', $sponsor->id) }}" id="formEditSponsor" method="POST"
                enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
                @csrf
                @method('PUT')

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
                    <input type="text" name="nama" id="nama" value="{{ old('nama', $sponsor->nama) }}" required
                        placeholder="Masukkan nama sponsor"
                        class="w-full px-4 py-2.5 bg-white border @error('nama') border-rose-400 focus:ring-rose-400 @else border-slate-300 focus:ring-blue-500 focus:border-blue-500 @enderror rounded-xl text-sm font-medium text-slate-800 focus:ring-2 shadow-2xs transition-all">
                    @error('nama')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Logo Saat Ini -->
                <!-- Logo Sponsor Saat Ini -->
                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">
                        Logo Sponsor Saat Ini
                    </label>
                    <div
                        class="p-4 bg-slate-50 rounded-2xl border border-slate-200 w-fit flex items-center gap-4 shadow-2xs">
                        <div
                            class="w-20 h-16 bg-white rounded-xl border border-slate-200/80 p-1.5 flex items-center justify-center overflow-hidden">
                            <img src="{{ $sponsor->url_image }}" alt="{{ $sponsor->nama }}"
                                class="max-h-full max-w-full object-contain">
                        </div>
                        <div>
                            <span
                                class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 mb-1">
                                Logo Aktif
                            </span>
                            <p class="text-xs text-slate-400">Logo ini tetap digunakan jika Anda tidak mengunggah logo baru.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Ganti Logo dengan Drag & Drop (Opsional) -->
                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-1">
                        Ganti Logo / Gambar Sponsor (Opsional)
                    </label>
                    <p class="text-xs text-slate-500 mb-2.5">Biarkan kosong jika tidak ingin mengubah logo yang sudah ada.
                    </p>

                    <!-- Modern Drag & Drop Zone -->
                    <div id="dropzoneSponsorEdit"
                        class="relative border-2 border-dashed border-slate-300 hover:border-blue-500 bg-slate-50/60 hover:bg-blue-50/20 rounded-2xl p-6 sm:p-8 text-center transition-all cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-400 group"
                        tabindex="0" role="button"
                        aria-label="Upload logo baru sponsor. Tarik dan lepas atau klik untuk memilih file">

                        <input type="file" name="url_image" id="url_image"
                            accept="image/jpeg,image/png,image/jpg,image/svg+xml,image/gif" class="sr-only"
                            aria-hidden="true">

                        <!-- Default Empty State -->
                        <div id="dropzoneDefaultState" class="flex flex-col items-center">
                            <div
                                class="w-12 h-12 mb-2.5 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-2xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-slate-800">
                                <span class="text-blue-600 hover:underline">Tarik & Lepas Logo Baru di sini</span> atau Klik
                                untuk Memilih
                            </p>
                            <p class="text-xs text-slate-400 mt-1">Mendukung format: JPEG, PNG, JPG, SVG, GIF (Maks. 2MB)
                            </p>
                        </div>

                        <!-- Dragging Active State -->
                        <div id="dropzoneDragState" class="hidden flex flex-col items-center py-2">
                            <div
                                class="w-12 h-12 mb-2.5 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center animate-bounce shadow-md">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-blue-700">Lepaskan file logo untuk mengganti</p>
                        </div>
                    </div>

                    <!-- Preview Card untuk Logo Baru -->
                    <div id="newPreviewWrapper"
                        class="hidden mt-3 p-4 bg-blue-50/40 rounded-2xl border border-blue-200 shadow-2xs flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div
                                class="w-16 h-14 rounded-xl bg-white border border-blue-200 p-1 flex items-center justify-center shrink-0 overflow-hidden">
                                <img id="newImagePreview" src="" alt="Preview Logo Baru"
                                    class="max-h-full max-w-full object-contain">
                            </div>
                            <div class="min-w-0 flex-1">
                                <span
                                    class="inline-block px-2 py-0.5 rounded text-xs #  font-bold bg-blue-600 text-white uppercase tracking-wider mb-0.5">Logo
                                    Baru Terpilih</span>
                                <h4 id="newPreviewFileName" class="text-xs sm:text-sm font-bold text-slate-900 truncate">
                                    logo.png</h4>
                                <p id="newPreviewFileSize" class="text-xs text-slate-500 mt-0.5">0 KB</p>
                            </div>
                        </div>
                        <button type="button" id="btnCancelNewFile"
                            class="px-3 py-1.5 text-xs font-semibold text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition-colors shrink-0 cursor-pointer"
                            title="Batalkan penggantian logo">
                            Batalkan Logo Baru
                        </button>
                    </div>

                    <!-- Client Error Message -->
                    <p id="uploadErrorMsg" class="hidden text-xs font-semibold text-rose-600 mt-2 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                        <span id="uploadErrorText"></span>
                    </p>

                    <!-- REAL UPLOAD PROGRESS BAR -->
                    <div id="uploadProgressSection"
                        class="hidden mt-3 p-4 bg-white border border-blue-100 rounded-2xl shadow-xs space-y-2">
                        <div class="flex items-center justify-between text-xs font-semibold">
                            <span id="uploadStatusText" class="text-slate-700 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                Menyimpan perubahan sponsor...
                            </span>
                            <span id="uploadPercent" class="font-mono text-blue-600 font-bold">0%</span>
                        </div>
                        <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                            <div id="uploadProgressBar"
                                class="h-full bg-blue-600 rounded-full transition-all duration-150" style="width: 0%">
                            </div>
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
                    <button type="button" id="btnSubmitSponsorEdit"
                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold rounded-xl shadow-xs transition-all cursor-pointer focus:ring-2 focus:ring-blue-400 min-w-[140px]">
                        <span id="btnSubmitText">Simpan Perubahan</span>
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
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropzone = document.getElementById('dropzoneSponsorEdit');
            const fileInput = document.getElementById('url_image');
            const defaultState = document.getElementById('dropzoneDefaultState');
            const dragState = document.getElementById('dropzoneDragState');
            const previewWrapper = document.getElementById('newPreviewWrapper');
            const previewImg = document.getElementById('newImagePreview');
            const previewFileName = document.getElementById('newPreviewFileName');
            const previewFileSize = document.getElementById('newPreviewFileSize');
            const btnCancel = document.getElementById('btnCancelNewFile');
            const errorMsg = document.getElementById('uploadErrorMsg');
            const errorText = document.getElementById('uploadErrorText');

            const progressSection = document.getElementById('uploadProgressSection');
            const progressBar = document.getElementById('uploadProgressBar');
            const progressPercent = document.getElementById('uploadPercent');
            const statusText = document.getElementById('uploadStatusText');

            const btnSubmit = document.getElementById('btnSubmitSponsorEdit');
            const btnText = document.getElementById('btnSubmitText');
            const btnSpinner = document.getElementById('btnSubmitSpinner');
            const form = document.getElementById('formEditSponsor');

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

            // Cancel new file (keep existing logo)
            btnCancel.addEventListener('click', function(e) {
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

                    Swal.fire({
                        title: "Simpan Perubahan?",
                        text: "Apakah Anda yakin ingin memperbarui data sponsor ini?",
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
                        btnText.textContent = "Menyimpan...";
                        btnSpinner.classList.remove('hidden');

                        // Show progress bar
                        progressSection.classList.remove('hidden');
                        progressBar.style.width = '0%';
                        progressPercent.textContent = '0%';

                        const formData = new FormData(form);
                        formData.set('_method', 'PUT');

                        // If new file chosen, ensure it's in FormData
                        if (currentFile) {
                            formData.set('url_image', currentFile);
                        } else {
                            formData.delete('url_image');
                        }

                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', form.action, true);
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
                                btnText.textContent = "Simpan Perubahan";
                                btnSpinner.classList.add('hidden');
                                progressSection.classList.add('hidden');

                                try {
                                    const resp = JSON.parse(xhr.responseText);
                                    if (resp.errors) {
                                        const firstKey = Object.keys(resp.errors)[0];
                                        showError(resp.errors[firstKey][0]);
                                    } else {
                                        showError(
                                            'Gagal menyimpan perubahan. Silakan periksa formulir.'
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
                            btnText.textContent = "Simpan Perubahan";
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
