@extends('admin.layouts.main')

@push('styles')
    <!-- Cropper.js 1.6.1 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <style>
        .cropper-container {
            max-width: 100% !important;
            border-radius: 0.75rem;
            overflow: hidden;
        }
        .cropper-view-box {
            outline: 2px solid #2563eb;
            outline-color: rgba(37, 99, 235, 0.9);
            border-radius: 8px;
        }
        .cropper-line, .cropper-point {
            display: none !important;
        }
        .dropzone-active {
            border-color: #2563eb !important;
            background-color: #eff6ff !important;
            transform: scale(1.008);
        }
        .preview-landing-card {
            width: 100%;
            max-width: 220px;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.08);
            margin: 0 auto;
            display: flex;
            flex-direction: column;
        }
        .preview-landing-image-wrapper {
            width: 100%;
            aspect-ratio: 4 / 3;
            overflow: hidden;
            background: #f1f5f9;
            position: relative;
        }
        .preview-landing-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
    </style>
@endpush

@section('content')
<div class="p-4 sm:p-6 lg:p-8 space-y-6">
    <!-- PAGE HEADER & BREADCRUMB -->
    <div class="flex flex-col gap-4 pt-1 sm:pt-2">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight leading-tight">
                        Pimpinan MKKS
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5 font-medium">
                        Edit data pimpinan
                    </p>
                </div>
            </div>

            <!-- Action Buttons: Kembali -->
            <div class="flex items-center gap-3 shrink-0">
                <a href="/pimpinan"
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
                ['label' => 'Pimpinan MKKS', 'url' => '/pimpinan'],
                ['label' => 'Edit Pimpinan']
            ]" />
        </div>
    </div>

    <!-- MAIN FORM CARD -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 sm:p-8">
            <div class="mb-6 pb-4 border-b border-gray-100">
                <h2 class="text-xl font-bold text-gray-800">Form Edit Pimpinan</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Ubah identitas atau ganti foto pimpinan. Foto lama akan tetap aman jika proses update tidak diselesaikan.
                </p>
            </div>

            <!-- Error Notification Banner -->
            <div id="errorBanner" class="hidden mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg">
                <p class="text-sm font-semibold text-red-800 mb-1" id="errorBannerTitle">Terdapat kesalahan pengisian form:</p>
                <ul class="list-disc list-inside text-xs text-red-700" id="errorBannerList"></ul>
            </div>

            <form id="formEditPimpinan" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')

                <!-- Section: FOTO PIMPINAN (CURRENT + REPLACE OPTION) -->
                <div class="mb-7 bg-slate-50/70 p-4 sm:p-5 rounded-xl border border-slate-200/80">
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-bold text-gray-800">
                            Foto Pimpinan
                        </label>
                        <span class="text-xs text-gray-500 font-medium">Rasio Frame: <strong>4:3</strong></span>
                    </div>

                    <!-- Current Photo Card Preview -->
                    <div id="currentPhotoSection" class="p-4 bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col sm:flex-row items-center sm:items-start gap-4">
                        <div class="w-28 h-21 flex-shrink-0 aspect-[4/3] rounded-lg overflow-hidden border border-gray-300 shadow-sm bg-gray-100">
                            <img id="currentPhotoImg" src="{{ $pimpinan->foto_url }}" alt="{{ $pimpinan->nama }}" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 text-center sm:text-left">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 mb-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                Foto Aktif Saat Ini
                            </div>
                            <p class="text-xs text-gray-500 mb-3">
                                Foto ini saat ini tampil di halaman publik. Klik "Ganti Foto" di bawah untuk mengunggah dan menyesuaikan foto baru.
                            </p>
                            <button type="button"
                                    id="btnTriggerReplace"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Ganti Foto Ini
                            </button>
                        </div>
                    </div>

                    <!-- Replacement Upload & Crop Section (Hidden initially) -->
                    <div id="replacementSection" class="hidden mt-4 pt-4 border-t border-gray-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-gray-700">Unggah & Crop Foto Baru (4:3)</span>
                            <button type="button" id="btnCancelReplace" class="text-xs text-red-600 hover:text-red-800 font-semibold underline">
                                Batalkan Penggantian
                            </button>
                        </div>

                        <!-- Dropzone -->
                        <div id="dropzone"
                             class="relative border-2 border-dashed border-gray-300 hover:border-blue-400 bg-white rounded-xl p-5 text-center transition-all cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-400"
                             tabindex="0"
                             role="button"
                             aria-label="Upload foto baru pimpinan">
                            
                            <input type="file"
                                   id="fotoInput"
                                   name="foto_raw"
                                   accept="image/jpeg,image/png,image/jpg,image/webp"
                                   class="sr-only"
                                   aria-hidden="true">

                            <div id="dropzoneDefaultState" class="flex flex-col items-center">
                                <div class="w-12 h-12 mb-2 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>
                                <p class="text-xs sm:text-sm font-semibold text-gray-700">
                                    <span class="text-blue-600 hover:underline">Tarik & Lepas Foto Baru</span> atau Klik untuk Memilih
                                </p>
                                <p class="text-[11px] text-gray-400 mt-0.5">JPG, JPEG, PNG, WEBP (Maks. 2MB)</p>
                            </div>

                            <div id="dropzoneDragState" class="hidden flex flex-col items-center py-1">
                                <p class="text-xs font-bold text-blue-700">Lepaskan file foto untuk memulai crop</p>
                            </div>
                        </div>

                        <!-- Error Message -->
                        <p id="uploadErrorMsg" class="hidden text-xs font-semibold text-red-600 mt-2 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            <span id="uploadErrorText"></span>
                        </p>

                        <!-- Real Upload Progress Bar -->
                        <div id="uploadProgressSection" class="hidden mt-3 p-3 bg-white border border-blue-100 rounded-xl shadow-sm">
                            <div class="flex justify-between items-center text-xs font-semibold text-gray-700 mb-1">
                                <span class="flex items-center gap-1.5 text-blue-700">
                                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span id="progressStatusText">Mengunggah file...</span>
                                </span>
                                <span id="progressPercentText" class="font-bold text-blue-700">0%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                <div id="progressBarFill" class="bg-blue-600 h-2 rounded-full transition-all duration-150 ease-out" style="width: 0%"></div>
                            </div>
                            <p id="progressByteDetails" class="text-[10px] text-gray-400 mt-1 text-right">0 KB / 0 KB</p>
                        </div>

                        <!-- Crop Editor Container -->
                        <div id="cropEditorContainer" class="hidden mt-4 pt-3 border-t border-gray-200">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-xs font-bold text-gray-800 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                    Sesuaikan Posisi Wajah (Frame 4:3 Tetap)
                                </h3>
                                <button type="button" id="btnResetCrop" class="text-xs text-blue-600 hover:text-blue-800 font-medium px-2 py-0.5 rounded bg-blue-50">
                                    Reset Posisi
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">
                                <!-- Cropper Canvas -->
                                <div class="md:col-span-7 bg-gray-900 rounded-xl overflow-hidden p-1 shadow-inner">
                                    <div class="max-h-[340px] flex items-center justify-center overflow-hidden">
                                        <img id="cropperImage" src="" alt="Foto pengganti" class="max-w-full block">
                                    </div>
                                    <div class="p-2.5 bg-gray-800 text-white rounded-b-lg flex flex-col gap-2">
                                        <div class="flex items-center justify-between text-xs text-gray-300">
                                            <span>Zoom:</span>
                                            <div class="flex items-center gap-1.5">
                                                <button type="button" id="btnZoomOut" class="w-6 h-6 flex items-center justify-center bg-gray-700 rounded text-sm font-bold">-</button>
                                                <input type="range" id="zoomSlider" min="0.2" max="3" step="0.02" value="1" class="w-24 sm:w-32 accent-blue-500 cursor-pointer">
                                                <button type="button" id="btnZoomIn" class="w-6 h-6 flex items-center justify-center bg-gray-700 rounded text-sm font-bold">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Live Landing Card Preview -->
                                <div class="md:col-span-5 flex flex-col items-center bg-white p-3.5 rounded-xl border border-gray-200 shadow-sm">
                                    <div class="w-full flex items-center justify-between mb-2">
                                        <span class="text-[11px] font-bold text-gray-700">Preview Landing Page</span>
                                        <span class="text-[9px] bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-full">4:3 Tetap</span>
                                    </div>

                                    <div class="preview-landing-card">
                                        <div class="preview-landing-image-wrapper">
                                            <canvas id="cropPreviewCanvas" class="preview-landing-image"></canvas>
                                        </div>
                                        <div class="p-2.5 text-center">
                                            <p id="previewCardName" class="text-xs font-bold text-gray-800 truncate">{{ $pimpinan->nama }}</p>
                                            <p id="previewCardPosition" class="text-[11px] text-gray-500 truncate mt-0.5">{{ $pimpinan->jabatan }}</p>
                                        </div>
                                    </div>

                                    <button type="button" id="btnApplyCrop" class="w-full mt-3 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors flex items-center justify-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Gunakan Hasil Crop Ini
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Cropped Output Summary Badge -->
                        <div id="cropSuccessNotice" class="hidden mt-3 p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-9 rounded overflow-hidden border border-emerald-300 shadow-sm">
                                    <img id="croppedThumbnail" src="" class="w-full h-full object-cover" alt="Thumbnail Pengganti">
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-emerald-800 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        Foto Pengganti Siap (4:3)
                                    </p>
                                    <p class="text-[11px] text-emerald-600">Resolusi optimal 800 × 600 px</p>
                                </div>
                            </div>
                            <button type="button" id="btnRecrop" class="text-xs text-blue-700 hover:text-blue-900 font-semibold underline px-2 py-1">
                                Ubah Crop
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Input: Nama -->
                <div class="mb-5">
                    <label for="nama" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="nama"
                           name="nama"
                           value="{{ old('nama', $pimpinan->nama) }}"
                           placeholder="Contoh: Dr. H. Budi Santoso, M.Pd."
                           required
                           class="w-full px-3.5 py-2.5 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm border-gray-300">
                    <p class="text-red-500 text-xs mt-1 hidden" id="error_nama"></p>
                </div>

                <!-- Input: Jabatan -->
                <div class="mb-5">
                    <label for="jabatan" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Jabatan <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="jabatan"
                           name="jabatan"
                           value="{{ old('jabatan', $pimpinan->jabatan) }}"
                           placeholder="Contoh: Ketua MKKS SMK Kabupaten Bekasi"
                           required
                           class="w-full px-3.5 py-2.5 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm border-gray-300">
                    <p class="text-red-500 text-xs mt-1 hidden" id="error_jabatan"></p>
                </div>

                <!-- Urutan & Status Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="urutan" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Urutan Tampilan
                        </label>
                        <input type="number"
                               id="urutan"
                               name="urutan"
                               value="{{ old('urutan', $pimpinan->urutan) }}"
                               min="1"
                               class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 text-sm">
                        <p class="text-xs text-gray-400 mt-1">Nomor urut tampilan di halaman publik.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Status Tampil
                        </label>
                        <label class="flex items-center gap-2.5 mt-2.5 cursor-pointer">
                            <input type="checkbox"
                                   id="is_active"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', $pimpinan->is_active ? '1' : '0') == '1' ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer">
                            <span class="text-sm text-gray-700 font-medium">Aktifkan di landing page</span>
                        </label>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-100">
                    <button type="submit"
                            id="btnSubmit"
                            class="flex-1 inline-flex items-center justify-center px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-lg shadow-sm focus:ring-4 focus:ring-blue-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="btnSubmitSpinner" class="hidden mr-2">
                            <svg class="animate-spin h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </span>
                        <span id="btnSubmitText">Perbarui Data</span>
                    </button>
                    <a href="/pimpinan"
                       class="px-5 py-2.5 bg-gray-100 text-gray-700 hover:bg-gray-200 font-semibold text-sm rounded-lg transition-colors text-center">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <!-- Script Logic -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('formEditPimpinan');
            const currentPhotoSection = document.getElementById('currentPhotoSection');
            const btnTriggerReplace = document.getElementById('btnTriggerReplace');
            const replacementSection = document.getElementById('replacementSection');
            const btnCancelReplace = document.getElementById('btnCancelReplace');
            
            const dropzone = document.getElementById('dropzone');
            const fotoInput = document.getElementById('fotoInput');
            const dropzoneDefaultState = document.getElementById('dropzoneDefaultState');
            const dropzoneDragState = document.getElementById('dropzoneDragState');
            const uploadErrorMsg = document.getElementById('uploadErrorMsg');
            const uploadErrorText = document.getElementById('uploadErrorText');
            
            const cropEditorContainer = document.getElementById('cropEditorContainer');
            const cropperImage = document.getElementById('cropperImage');
            const btnResetCrop = document.getElementById('btnResetCrop');
            const btnZoomIn = document.getElementById('btnZoomIn');
            const btnZoomOut = document.getElementById('btnZoomOut');
            const zoomSlider = document.getElementById('zoomSlider');
            const btnApplyCrop = document.getElementById('btnApplyCrop');
            const cropPreviewCanvas = document.getElementById('cropPreviewCanvas');
            
            const cropSuccessNotice = document.getElementById('cropSuccessNotice');
            const croppedThumbnail = document.getElementById('croppedThumbnail');
            const btnRecrop = document.getElementById('btnRecrop');
            
            const uploadProgressSection = document.getElementById('uploadProgressSection');
            const progressBarFill = document.getElementById('progressBarFill');
            const progressPercentText = document.getElementById('progressPercentText');
            const progressStatusText = document.getElementById('progressStatusText');
            const progressByteDetails = document.getElementById('progressByteDetails');
            
            const btnSubmit = document.getElementById('btnSubmit');
            const btnSubmitText = document.getElementById('btnSubmitText');
            const btnSubmitSpinner = document.getElementById('btnSubmitSpinner');
            
            const inputNama = document.getElementById('nama');
            const inputJabatan = document.getElementById('jabatan');
            const previewCardName = document.getElementById('previewCardName');
            const previewCardPosition = document.getElementById('previewCardPosition');

            let cropper = null;
            let croppedBlob = null; // New replacement photo blob if user crops one

            // Sync name & title to live card preview
            inputNama.addEventListener('input', () => {
                previewCardName.textContent = inputNama.value.trim() || 'Nama Pimpinan';
            });
            inputJabatan.addEventListener('input', () => {
                previewCardPosition.textContent = inputJabatan.value.trim() || 'Jabatan Pimpinan';
            });

            // Toggle Replace Photo
            btnTriggerReplace.addEventListener('click', () => {
                replacementSection.classList.remove('hidden');
                btnTriggerReplace.classList.add('hidden');
            });

            btnCancelReplace.addEventListener('click', () => {
                replacementSection.classList.add('hidden');
                btnTriggerReplace.classList.remove('hidden');
                // Revert any staged photo replacement
                croppedBlob = null;
                cropSuccessNotice.classList.add('hidden');
                cropEditorContainer.classList.add('hidden');
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
                clearUploadError();
            });

            // Accessibility: Dropzone keyboard
            dropzone.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    fotoInput.click();
                }
            });

            dropzone.addEventListener('click', () => fotoInput.click());

            // Drag & Drop handlers
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('dropzone-active');
                    dropzoneDefaultState.classList.add('hidden');
                    dropzoneDragState.classList.remove('hidden');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('dropzone-active');
                    dropzoneDefaultState.classList.remove('hidden');
                    dropzoneDragState.classList.add('hidden');
                }, false);
            });

            dropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                if (dt && dt.files && dt.files.length > 0) {
                    handleFileSelected(dt.files[0]);
                }
            });

            fotoInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    handleFileSelected(this.files[0]);
                }
            });

            function showUploadError(msg) {
                uploadErrorText.textContent = msg;
                uploadErrorMsg.classList.remove('hidden');
            }

            function clearUploadError() {
                uploadErrorText.textContent = '';
                uploadErrorMsg.classList.add('hidden');
            }

            function handleFileSelected(file) {
                clearUploadError();

                const validMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
                if (!validMimes.includes(file.type.toLowerCase())) {
                    showUploadError('Format file tidak didukung. Silakan gunakan JPG, PNG, atau WEBP.');
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    showUploadError('Ukuran file terlalu besar (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB). Maksimal 2MB.');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    initCropper(e.target.result);
                };
                reader.readAsDataURL(file);
            }

            function initCropper(imageSrc) {
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }

                cropperImage.src = imageSrc;
                cropEditorContainer.classList.remove('hidden');
                cropSuccessNotice.classList.add('hidden');

                cropperImage.onload = function() {
                    cropper = new Cropper(cropperImage, {
                        aspectRatio: 4 / 3, // STRICTLY FIXED 4:3
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 0.9,
                        responsive: true,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: false,
                        cropBoxResizable: false,
                        toggleDragModeOnDblclick: false,
                        ready: function() {
                            zoomSlider.value = 1;
                            updatePreview();
                        },
                        crop: function() {
                            updatePreview();
                        }
                    });
                };

                cropEditorContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            function updatePreview() {
                if (!cropper) return;
                const canvas = cropper.getCroppedCanvas({
                    width: 320,
                    height: 240,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high'
                });

                if (canvas) {
                    cropPreviewCanvas.width = canvas.width;
                    cropPreviewCanvas.height = canvas.height;
                    const ctx = cropPreviewCanvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(canvas, 0, 0);
                }
            }

            zoomSlider.addEventListener('input', function() {
                if (cropper) {
                    cropper.zoomTo(parseFloat(this.value));
                }
            });

            btnZoomIn.addEventListener('click', function() {
                if (cropper) {
                    cropper.zoom(0.1);
                    zoomSlider.value = (parseFloat(zoomSlider.value) + 0.1).toFixed(2);
                }
            });

            btnZoomOut.addEventListener('click', function() {
                if (cropper) {
                    cropper.zoom(-0.1);
                    zoomSlider.value = (parseFloat(zoomSlider.value) - 0.1).toFixed(2);
                }
            });

            btnResetCrop.addEventListener('click', function() {
                if (cropper) {
                    cropper.reset();
                    zoomSlider.value = 1;
                }
            });

            btnApplyCrop.addEventListener('click', function() {
                applyCropResult();
            });

            function applyCropResult(callback) {
                if (!cropper) {
                    if (callback) callback(null);
                    return;
                }

                const outputCanvas = cropper.getCroppedCanvas({
                    width: 800,
                    height: 600,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high'
                });

                outputCanvas.toBlob(function(blob) {
                    if (!blob) {
                        showUploadError('Gagal memproses crop foto.');
                        if (callback) callback(null);
                        return;
                    }

                    croppedBlob = blob;
                    croppedThumbnail.src = URL.createObjectURL(blob);
                    cropSuccessNotice.classList.remove('hidden');
                    cropEditorContainer.classList.add('hidden');
                    clearUploadError();

                    if (callback) callback(blob);
                }, 'image/jpeg', 0.92);
            }

            btnRecrop.addEventListener('click', function() {
                cropSuccessNotice.classList.add('hidden');
                cropEditorContainer.classList.remove('hidden');
                if (cropper) {
                    cropper.resize();
                }
            });

            // Form Submit Logic
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                // If cropper is open but user didn't click "Gunakan Hasil Crop", apply it automatically
                if (cropper && !croppedBlob) {
                    applyCropResult(function(blob) {
                        if (blob) {
                            executeSubmission();
                        }
                    });
                    return;
                }

                executeSubmission();
            });

            function executeSubmission() {
                // UI State 4: Form submitting (disabled button + spinner)
                btnSubmit.disabled = true;
                btnSubmitSpinner.classList.remove('hidden');
                btnSubmitText.textContent = 'Menyimpan...';

                // Prepare FormData
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                formData.append('_method', 'PUT');
                formData.append('nama', inputNama.value.trim());
                formData.append('jabatan', inputJabatan.value.trim());
                formData.append('urutan', document.getElementById('urutan').value);
                formData.append('is_active', document.getElementById('is_active').checked ? '1' : '0');

                const hasNewPhoto = croppedBlob !== null;
                if (hasNewPhoto) {
                    formData.append('foto', croppedBlob, 'pimpinan_edit_' + Date.now() + '.jpg');

                    // UI State 2: Real File Upload Progress
                    uploadProgressSection.classList.remove('hidden');
                    progressBarFill.style.width = '0%';
                    progressPercentText.textContent = '0%';
                    progressStatusText.textContent = 'Mengunggah file...';
                    progressByteDetails.textContent = '0 KB / ' + Math.round(croppedBlob.size / 1024) + ' KB';
                }

                const xhr = new XMLHttpRequest();
                xhr.open('POST', '{{ route("pimpinan.update", $pimpinan->uuid) }}', true);
                xhr.setRequestHeader('Accept', 'application/json');

                if (hasNewPhoto) {
                    xhr.upload.addEventListener('progress', function(event) {
                        if (event.lengthComputable) {
                            const percent = Math.round((event.loaded / event.total) * 100);
                            progressBarFill.style.width = percent + '%';
                            progressPercentText.textContent = percent + '%';
                            
                            const loadedKb = Math.round(event.loaded / 1024);
                            const totalKb = Math.round(event.total / 1024);
                            progressByteDetails.textContent = loadedKb + ' KB / ' + totalKb + ' KB';

                            if (percent === 100) {
                                progressStatusText.textContent = 'Memproses data pimpinan...';
                            }
                        }
                    });
                }

                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        let res;
                        try {
                            res = JSON.parse(xhr.responseText);
                        } catch(e) {
                            res = { redirect: '/pimpinan' };
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message || 'Data pimpinan berhasil diperbarui.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = res.redirect || '/pimpinan';
                        });
                    } else {
                        btnSubmit.disabled = false;
                        btnSubmitSpinner.classList.add('hidden');
                        btnSubmitText.textContent = 'Perbarui Data';
                        uploadProgressSection.classList.add('hidden');

                        let errorData = {};
                        try {
                            errorData = JSON.parse(xhr.responseText);
                        } catch(e) {}

                        const errorBanner = document.getElementById('errorBanner');
                        const errorBannerList = document.getElementById('errorBannerList');
                        errorBannerList.innerHTML = '';

                        if (errorData.errors) {
                            Object.keys(errorData.errors).forEach(key => {
                                errorData.errors[key].forEach(msg => {
                                    const li = document.createElement('li');
                                    li.textContent = msg;
                                    errorBannerList.appendChild(li);
                                });
                            });
                            errorBanner.classList.remove('hidden');
                            errorBanner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        } else {
                            Swal.fire('Gagal Menyimpan', errorData.message || 'Terjadi kesalahan sistem saat memperbarui data pimpinan.', 'error');
                        }
                    }
                };

                xhr.onerror = function() {
                    btnSubmit.disabled = false;
                    btnSubmitSpinner.classList.add('hidden');
                    btnSubmitText.textContent = 'Perbarui Data';
                    uploadProgressSection.classList.add('hidden');
                    Swal.fire('Kesalahan Jaringan', 'Gagal menghubungi server. Periksa koneksi internet Anda.', 'error');
                };

                xhr.send(formData);
            }
        });
    </script>
@endpush
