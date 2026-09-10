@php
    $isEdit = isset($galeri);
    $defaultTitle = old('judul', $isEdit ? $galeri->judul : '');
    $defaultDeskripsi = old('deskripsi', $isEdit ? $galeri->deskripsi : '');
    $cleanDefaultDeskripsi = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($defaultDeskripsi))), 160);

    $defaultSeoTitle = old('seo_title', $isEdit ? ($galeri->seo_title ?: $galeri->judul) : '');
    $defaultSeoDesc = old('seo_description', $isEdit ? ($galeri->seo_description ?: $cleanDefaultDeskripsi) : '');
    $defaultSlug = old('slug', $isEdit ? ($galeri->slug ?: \Illuminate\Support\Str::slug($galeri->judul)) : '');

    $domainUrl = rtrim(config('app.url', 'https://mkkssmkkabbekasi.or.id'), '/');
@endphp

<!-- SEO SETTINGS MODAL DIALOG -->
<div id="seoSettingsModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="seoModalTitle" role="dialog" aria-modal="true">
    <!-- Backdrop with blur -->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-300" id="seoModalBackdrop"></div>

    <!-- Modal Center Wrapper -->
    <div class="min-h-screen px-4 py-6 sm:py-10 flex items-center justify-center">
        <div class="relative w-full max-w-3xl bg-white rounded-3xl shadow-2xl border border-slate-200 text-left overflow-hidden transform transition-all z-10 flex flex-col max-h-[92vh]">

            <!-- MODAL HEADER -->
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200/60 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 id="seoModalTitle" class="text-lg font-bold text-slate-900 leading-snug">
                            SEO Settings (Google Search)
                        </h3>
                        <p class="text-xs text-slate-500 font-medium">
                            Kelola tampilan judul, slug URL, dan deskripsi galeri di hasil pencarian Google
                        </p>
                    </div>
                </div>
                <button type="button" id="btnCloseSeoModalTop" class="w-9 h-9 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-500 hover:text-slate-800 flex items-center justify-center transition-colors cursor-pointer" aria-label="Tutup Panel SEO">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- MODAL BODY (SCROLLABLE) -->
            <div class="p-6 sm:p-7 space-y-6 overflow-y-auto flex-1">

                <!-- 1. GOOGLE SEARCH PREVIEW CARD -->
                <div class="bg-slate-50/70 rounded-2xl border border-slate-200/80 p-4 sm:p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-600 text-white text-[10px] font-bold">G</span>
                            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Preview Hasil Pencarian Google</h4>
                        </div>
                        <span class="text-[11px] text-slate-400 font-medium hidden sm:inline">Tampilan Real-time</span>
                    </div>

                    <!-- Google SERP Style Result Box -->
                    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs space-y-1.5 transition-all">
                        <!-- URL / Breadcrumb line -->
                        <div class="flex items-center gap-2 text-xs text-slate-700">
                            <div class="w-4 h-4 rounded-full bg-blue-100 flex items-center justify-center text-[9px] font-bold text-blue-700 shrink-0">
                                M
                            </div>
                            <div class="truncate text-[12px] leading-tight font-normal text-slate-600">
                                <span class="font-medium text-slate-800">MKKS SMK Kab Bekasi</span>
                                <span class="text-slate-400 mx-1">›</span>
                                <span class="text-slate-500 font-mono text-[11px]">{{ $domainUrl }}/konten/<span id="previewGoogleSlug" class="font-semibold text-slate-700">{{ $defaultSlug ?: 'judul-galeri' }}</span></span>
                            </div>
                        </div>

                        <!-- Title line (Google Blue) -->
                        <div class="pt-0.5">
                            <h5 id="previewGoogleTitle" class="text-base sm:text-lg font-medium text-[#1a0dab] hover:underline cursor-pointer leading-snug break-words">
                                {{ $defaultSeoTitle ?: ($defaultTitle ?: 'Judul Galeri Kegiatan - MKKS SMK Kab Bekasi') }}
                            </h5>
                        </div>

                        <!-- Snippet description line (Google Gray) -->
                        <p id="previewGoogleSnippet" class="text-xs sm:text-sm text-[#4d5156] leading-relaxed break-words">
                            {{ $defaultSeoDesc ?: ($cleanDefaultDeskripsi ?: 'Deskripsi rangkuman galeri kegiatan MKKS SMK Kabupaten Bekasi akan tampil di sini pada hasil pencarian Google.') }}
                        </p>
                    </div>
                </div>

                <!-- 2. SEO TITLE INPUT -->
                <div class="space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-1.5">
                        <label for="inputSeoTitle" class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                            <span>SEO Title</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <button type="button" id="btnSyncTitleFromGallery" class="text-xs text-blue-600 hover:text-blue-800 hover:underline font-semibold flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span>Gunakan judul Galeri</span>
                            </button>
                            <span class="text-slate-300">|</span>
                            <span id="seoTitleCounter" class="text-xs font-mono text-slate-500 font-medium">0 / 60</span>
                        </div>
                    </div>
                    <input type="text" id="inputSeoTitle" maxlength="255" value="{{ $defaultSeoTitle }}"
                        placeholder="Judul SEO untuk hasil pencarian Google"
                        class="w-full px-4 py-2.5 bg-white border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 rounded-xl text-sm font-medium text-slate-800 transition-all">
                    <p id="seoTitleHint" class="text-xs text-slate-400 font-normal">
                        Rekomendasi panjang: 50-60 karakter agar tidak terpotong di hasil pencarian Google.
                    </p>
                </div>

                <!-- 3. SLUG URL INPUT -->
                <div class="space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-1.5">
                        <label for="inputSlug" class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                            <span>Slug URL</span>
                        </label>
                        <button type="button" id="btnSyncSlugFromTitle" class="text-xs text-blue-600 hover:text-blue-800 hover:underline font-semibold flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.172 13.828a4 4 0 015.656 0l4-4a4 4 0 00-5.656-5.656l-1.102 1.101" />
                            </svg>
                            <span>Generate dari judul</span>
                        </button>
                    </div>
                    <div class="flex rounded-xl shadow-2xs border border-slate-300 overflow-hidden focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-500/20 transition-all">
                        <span class="inline-flex items-center px-3.5 bg-slate-100 text-slate-500 text-xs font-mono border-r border-slate-300 select-none">
                            /konten/
                        </span>
                        <input type="text" id="inputSlug" maxlength="191" value="{{ $defaultSlug }}"
                            placeholder="slug-url-halaman"
                            class="flex-1 px-3.5 py-2.5 bg-white text-sm font-mono text-slate-800 border-0 focus:ring-0 focus:outline-hidden">
                    </div>
                    <p class="text-xs text-slate-400 font-normal">
                        Hanya huruf kecil, angka, dan tanda hubung (-). Contoh: <code class="text-slate-600">rapat-koordinasi-mkks-2026</code>
                    </p>
                </div>

                <!-- 4. META DESCRIPTION INPUT -->
                <div class="space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-1.5">
                        <label for="inputSeoDescription" class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                            <span>Meta Description</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <button type="button" id="btnSyncDescFromGallery" class="text-xs text-blue-600 hover:text-blue-800 hover:underline font-semibold flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span>Gunakan deskripsi Galeri</span>
                            </button>
                            <span class="text-slate-300">|</span>
                            <span id="seoDescCounter" class="text-xs font-mono text-slate-500 font-medium">0 / 160</span>
                        </div>
                    </div>
                    <textarea id="inputSeoDescription" rows="3" maxlength="1000"
                        placeholder="Deskripsi ringkas yang menarik untuk hasil pencarian Google"
                        class="w-full px-4 py-2.5 bg-white border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 rounded-xl text-sm font-medium text-slate-800 transition-all leading-relaxed">{{ $defaultSeoDesc }}</textarea>
                    <p id="seoDescHint" class="text-xs text-slate-400 font-normal">
                        Rekomendasi panjang: 120-160 karakter untuk ringkasan optimal di hasil pencarian.
                    </p>
                </div>

            </div>

            <!-- MODAL FOOTER -->
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/70 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
                <button type="button" id="btnResetAllSeo" class="text-xs font-semibold text-slate-600 hover:text-blue-600 flex items-center gap-1.5 transition-colors py-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Gunakan otomatis semua dari Galeri</span>
                </button>

                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <button type="button" id="btnCancelSeo"
                        class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm transition-all min-h-[44px] cursor-pointer shadow-2xs">
                        Batalkan
                    </button>
                    <button type="button" id="btnApplySeo"
                        class="flex-1 sm:flex-none px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold text-sm transition-all min-h-[44px] cursor-pointer shadow-xs">
                        Terapkan / Selesai
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal DOM Elements
        const modal = document.getElementById('seoSettingsModal');
        const backdrop = document.getElementById('seoModalBackdrop');
        const btnOpenModal = document.getElementById('btnOpenSeoModal');
        const btnCloseTop = document.getElementById('btnCloseSeoModalTop');
        const btnCancel = document.getElementById('btnCancelSeo');
        const btnApply = document.getElementById('btnApplySeo');

        // Main Form & Hidden Inputs
        const mainTitleInput = document.getElementById('judul');
        const mainDescInput = document.getElementById('deskripsi');
        const hiddenSeoTitle = document.getElementById('hiddenSeoTitle');
        const hiddenSeoDesc = document.getElementById('hiddenSeoDescription');
        const hiddenSlug = document.getElementById('hiddenSlug');
        const seoBadge = document.getElementById('seoBadge');

        // Modal Input Elements
        const inputSeoTitle = document.getElementById('inputSeoTitle');
        const inputSlug = document.getElementById('inputSlug');
        const inputSeoDesc = document.getElementById('inputSeoDescription');

        // Character Counters & Hints
        const titleCounter = document.getElementById('seoTitleCounter');
        const descCounter = document.getElementById('seoDescCounter');
        const titleHint = document.getElementById('seoTitleHint');
        const descHint = document.getElementById('seoDescHint');

        // SERP Preview Elements
        const previewTitle = document.getElementById('previewGoogleTitle');
        const previewSlug = document.getElementById('previewGoogleSlug');
        const previewSnippet = document.getElementById('previewGoogleSnippet');

        // Helper Buttons
        const btnSyncTitle = document.getElementById('btnSyncTitleFromGallery');
        const btnSyncSlug = document.getElementById('btnSyncSlugFromTitle');
        const btnSyncDesc = document.getElementById('btnSyncDescFromGallery');
        const btnResetAll = document.getElementById('btnResetAllSeo');

        // State Tracking (Auto-fill intelligence)
        const isEditMode = {{ $isEdit ? 'true' : 'false' }};
        let seoTitleTouched = isEditMode || Boolean("{{ old('seo_title') }}");
        let seoDescTouched = isEditMode || Boolean("{{ old('seo_description') }}");
        let slugTouched = isEditMode || Boolean("{{ old('slug') }}");

        // Temporary Snapshot on modal open for Cancel/Revert behavior
        let snapshot = {
            title: hiddenSeoTitle ? hiddenSeoTitle.value : '',
            desc: hiddenSeoDesc ? hiddenSeoDesc.value : '',
            slug: hiddenSlug ? hiddenSlug.value : '',
            titleTouched: seoTitleTouched,
            descTouched: seoDescTouched,
            slugTouched: slugTouched
        };

        // Utility: URL-Safe Slug Generator
        function generateSlug(text) {
            if (!text) return '';
            return text
                .toString()
                .toLowerCase()
                .trim()
                .replace(/\s+/g, '-')           // Replace spaces with -
                .replace(/[^\w\-]+/g, '')       // Remove all non-word chars except -
                .replace(/\-\-+/g, '-')         // Replace multiple - with single -
                .replace(/^-+/, '')             // Trim - from start of text
                .replace(/-+$/, '');            // Trim - from end of text
        }

        // Utility: Strip HTML tags for clean description
        function stripHtml(html) {
            if (!html) return '';
            const tmp = document.createElement('DIV');
            tmp.innerHTML = html;
            return (tmp.textContent || tmp.innerText || '').trim().replace(/\s+/g, ' ');
        }

        // Utility: Auto-fill SEO description formatted and truncated to optimal Google snippet length (max 160 chars)
        function formatSeoDescription(text) {
            if (!text) return '';
            const clean = stripHtml(text);
            return clean.length > 160 ? clean.slice(0, 160).trim() : clean;
        }

        // Update Real-Time Character Counters
        function updateCounters() {
            const titleLen = (inputSeoTitle.value || '').length;
            titleCounter.textContent = `${titleLen} / 60`;
            if (titleLen > 60) {
                titleCounter.classList.remove('text-slate-500');
                titleCounter.classList.add('text-amber-600', 'font-bold');
                titleHint.textContent = 'Peringatan: Judul melebihi 60 karakter dan mungkin terpotong di Google SERP.';
                titleHint.classList.add('text-amber-600');
                titleHint.classList.remove('text-slate-400');
            } else {
                titleCounter.classList.add('text-slate-500');
                titleCounter.classList.remove('text-amber-600', 'font-bold');
                titleHint.textContent = 'Rekomendasi panjang: 50-60 karakter agar tidak terpotong di hasil pencarian Google.';
                titleHint.classList.remove('text-amber-600');
                titleHint.classList.add('text-slate-400');
            }

            const descLen = (inputSeoDesc.value || '').length;
            descCounter.textContent = `${descLen} / 160`;
            if (descLen > 160) {
                descCounter.classList.remove('text-slate-500');
                descCounter.classList.add('text-amber-600', 'font-bold');
                descHint.textContent = 'Peringatan: Deskripsi melebihi 160 karakter dan mungkin terpotong di Google SERP.';
                descHint.classList.add('text-amber-600');
                descHint.classList.remove('text-slate-400');
            } else {
                descCounter.classList.add('text-slate-500');
                descCounter.classList.remove('text-amber-600', 'font-bold');
                descHint.textContent = 'Rekomendasi panjang: 120-160 karakter untuk ringkasan optimal di hasil pencarian.';
                descHint.classList.remove('text-amber-600');
                descHint.classList.add('text-slate-400');
            }
        }

        // Update Google SERP Preview Box
        function updateGooglePreview() {
            const currentTitle = inputSeoTitle.value.trim() || (mainTitleInput ? mainTitleInput.value.trim() : '') || 'Judul Galeri Kegiatan - MKKS SMK Kab Bekasi';
            const currentSlug = inputSlug.value.trim() || generateSlug(currentTitle) || 'judul-galeri';
            const currentDesc = inputSeoDesc.value.trim() || (mainDescInput ? formatSeoDescription(mainDescInput.value) : '') || 'Deskripsi rangkuman galeri kegiatan MKKS SMK Kabupaten Bekasi akan tampil di sini pada hasil pencarian Google.';

            previewTitle.textContent = currentTitle;
            previewSlug.textContent = currentSlug;
            previewSnippet.textContent = currentDesc;

            updateCounters();
        }

        // Check & update the "Terkonfigurasi" badge on main SEO button
        function updateBadge() {
            if (!seoBadge) return;
            const hasCustomSeo = (hiddenSeoTitle && hiddenSeoTitle.value.trim()) ||
                                 (hiddenSlug && hiddenSlug.value.trim()) ||
                                 (hiddenSeoDesc && hiddenSeoDesc.value.trim());
            if (hasCustomSeo) {
                seoBadge.classList.remove('hidden');
            } else {
                seoBadge.classList.add('hidden');
            }
        }

        // Modal Open / Close Logic
        function openModal() {
            // Take snapshot of current applied state
            snapshot = {
                title: hiddenSeoTitle ? hiddenSeoTitle.value : '',
                desc: hiddenSeoDesc ? hiddenSeoDesc.value : '',
                slug: hiddenSlug ? hiddenSlug.value : '',
                titleTouched: seoTitleTouched,
                descTouched: seoDescTouched,
                slugTouched: slugTouched
            };

            // Populate modal inputs from applied hidden inputs if available, otherwise from main form
            if (hiddenSeoTitle && hiddenSeoTitle.value) {
                inputSeoTitle.value = hiddenSeoTitle.value;
            } else if (!seoTitleTouched && mainTitleInput && mainTitleInput.value) {
                inputSeoTitle.value = mainTitleInput.value;
            }

            if (hiddenSlug && hiddenSlug.value) {
                inputSlug.value = hiddenSlug.value;
            } else if (!slugTouched && mainTitleInput && mainTitleInput.value) {
                inputSlug.value = generateSlug(mainTitleInput.value);
            }

            if (hiddenSeoDesc && hiddenSeoDesc.value) {
                inputSeoDesc.value = hiddenSeoDesc.value;
            } else if (!seoDescTouched && mainDescInput && mainDescInput.value) {
                inputSeoDesc.value = formatSeoDescription(mainDescInput.value);
            }

            updateGooglePreview();
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        function cancelModal() {
            // Discard unapplied edits and revert to snapshot
            if (hiddenSeoTitle) hiddenSeoTitle.value = snapshot.title;
            if (hiddenSeoDesc) hiddenSeoDesc.value = snapshot.desc;
            if (hiddenSlug) hiddenSlug.value = snapshot.slug;

            inputSeoTitle.value = snapshot.title;
            inputSeoDesc.value = snapshot.desc;
            inputSlug.value = snapshot.slug;

            seoTitleTouched = snapshot.titleTouched;
            seoDescTouched = snapshot.descTouched;
            slugTouched = snapshot.slugTouched;

            updateGooglePreview();
            closeModal();
        }

        function applyModal() {
            // Apply modal temporary inputs to hidden form inputs
            if (hiddenSeoTitle) hiddenSeoTitle.value = inputSeoTitle.value.trim();
            if (hiddenSeoDesc) hiddenSeoDesc.value = inputSeoDesc.value.trim();
            if (hiddenSlug) hiddenSlug.value = generateSlug(inputSlug.value) || inputSlug.value.trim();

            updateBadge();
            closeModal();
        }

        // Open Modal Trigger
        if (btnOpenModal) {
            btnOpenModal.addEventListener('click', openModal);
        }

        // Close/Cancel Triggers
        if (btnCloseTop) btnCloseTop.addEventListener('click', cancelModal);
        if (btnCancel) btnCancel.addEventListener('click', cancelModal);
        if (backdrop) backdrop.addEventListener('click', cancelModal);

        // Apply Trigger
        if (btnApply) btnApply.addEventListener('click', applyModal);

        // Escape Key Listener
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                cancelModal();
            }
        });

        // Real-Time Input Listeners on Modal Inputs
        inputSeoTitle.addEventListener('input', function() {
            seoTitleTouched = true;
            updateGooglePreview();
        });

        inputSlug.addEventListener('input', function() {
            slugTouched = true;
            // Sanitized preview
            const clean = generateSlug(this.value);
            previewSlug.textContent = clean || 'judul-galeri';
            updateCounters();
        });

        inputSlug.addEventListener('blur', function() {
            this.value = generateSlug(this.value);
            updateGooglePreview();
        });

        inputSeoDesc.addEventListener('input', function() {
            seoDescTouched = true;
            updateGooglePreview();
        });

        // Helper Button: Sync SEO Title from Gallery
        if (btnSyncTitle) {
            btnSyncTitle.addEventListener('click', function() {
                const mainVal = mainTitleInput ? mainTitleInput.value.trim() : '';
                inputSeoTitle.value = mainVal;
                seoTitleTouched = false;
                updateGooglePreview();
            });
        }

        // Helper Button: Sync Slug from Title
        if (btnSyncSlug) {
            btnSyncSlug.addEventListener('click', function() {
                const baseText = inputSeoTitle.value.trim() || (mainTitleInput ? mainTitleInput.value.trim() : '');
                inputSlug.value = generateSlug(baseText);
                slugTouched = false;
                updateGooglePreview();
            });
        }

        // Helper Button: Sync Meta Description from Gallery
        if (btnSyncDesc) {
            btnSyncDesc.addEventListener('click', function() {
                const clean = formatSeoDescription(mainDescInput ? mainDescInput.value : '');
                inputSeoDesc.value = clean;
                seoDescTouched = false;
                updateGooglePreview();
            });
        }

        // Helper Button: Reset All to Gallery Defaults
        if (btnResetAll) {
            btnResetAll.addEventListener('click', function() {
                const mainTitle = mainTitleInput ? mainTitleInput.value.trim() : '';
                const mainDesc = formatSeoDescription(mainDescInput ? mainDescInput.value : '');

                inputSeoTitle.value = mainTitle;
                inputSlug.value = generateSlug(mainTitle);
                inputSeoDesc.value = mainDesc;

                seoTitleTouched = false;
                slugTouched = false;
                seoDescTouched = false;

                updateGooglePreview();
            });
        }

        // Auto-fill Synchronization from Main Form (only if NOT touched)
        if (mainTitleInput) {
            mainTitleInput.addEventListener('input', function() {
                const val = this.value;
                if (!seoTitleTouched) {
                    inputSeoTitle.value = val;
                    if (hiddenSeoTitle) hiddenSeoTitle.value = val;
                }
                if (!slugTouched) {
                    const slugVal = generateSlug(val);
                    inputSlug.value = slugVal;
                    if (hiddenSlug) hiddenSlug.value = slugVal;
                }
                updateGooglePreview();
                updateBadge();
            });
        }

        if (mainDescInput) {
            mainDescInput.addEventListener('input', function() {
                if (!seoDescTouched) {
                    const clean = formatSeoDescription(this.value);
                    inputSeoDesc.value = clean;
                    if (hiddenSeoDesc) hiddenSeoDesc.value = clean;
                }
                updateGooglePreview();
                updateBadge();
            });
        }

        // Initial setup on page load
        updateGooglePreview();
        updateBadge();
    });
</script>
@endpush
