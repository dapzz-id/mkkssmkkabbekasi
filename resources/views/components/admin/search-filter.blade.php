@props([
    'action' => request()->url(),
    'autocompleteUrl' => '',
    'suggestionsUrl' => '',
    'placeholder' => 'Cari kata kunci...',
    'searchName' => 'search',
    'hasActiveFilters' => false,
    'resetUrl' => request()->url(),
    'id' => 'sf_' . uniqid(),
])

@php
    $effectiveAutocompleteUrl = $autocompleteUrl ?: $suggestionsUrl;
@endphp

<form method="GET" action="{{ $action }}" id="{{ $id }}_form" class="search-filter-toolbar mb-6 flex flex-col gap-3 lg:flex-row lg:items-center">
    {{-- Preserve per_page parameter if set in request --}}
    @if(request()->filled('per_page'))
        <input type="hidden" name="per_page" value="{{ request('per_page') }}">
    @endif

    <!-- Search Box (Flex-1 Left) with Autocomplete Dropdown -->
    <div class="flex-1 relative" id="{{ $id }}_container">
        <!-- Search Input Group -->
        <div class="relative flex items-center">
            <!-- Search Button / Submit Icon (Left) -->
            <button type="submit"
                    class="absolute inset-y-0 left-0 pl-3.5 pr-2 flex items-center text-slate-400 hover:text-blue-600 transition-colors focus:outline-none z-10 cursor-pointer"
                    title="Cari"
                    aria-label="Cari">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>

            <!-- Input Field -->
            <input type="text"
                   name="{{ $searchName }}"
                   value="{{ request($searchName) }}"
                   id="{{ $id }}_input"
                   placeholder="{{ $placeholder }}"
                   autocomplete="off"
                   class="w-full pl-10 pr-24 py-2.5 bg-white border border-slate-200/90 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-800 placeholder-slate-400 shadow-2xs transition-all min-h-[44px]"
                   aria-label="{{ $placeholder }}">

            <!-- Right-side Actions: Loading Spinner, Clear Button, Submit Pill -->
            <div class="absolute inset-y-0 right-0 flex items-center pr-2 gap-1 z-10">
                <!-- Loading Spinner Indicator (Only visible during fetch) -->
                <div id="{{ $id }}_spinner" class="hidden px-1.5 text-blue-600">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <!-- Clear Search Button (Always in DOM, shown/hidden by JS) -->
                <button type="button"
                        id="{{ $id }}_clear"
                        class="p-1.5 text-slate-400 hover:text-rose-500 transition-colors rounded-lg hover:bg-rose-50 {{ request()->filled($searchName) ? '' : 'hidden' }}"
                        title="Hapus pencarian"
                        aria-label="Hapus pencarian">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Explicit Search Submit Button -->
                <button type="submit"
                        class="hidden sm:inline-flex items-center gap-1 px-3 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition-colors border border-blue-200/60 shadow-2xs"
                        title="Klik untuk mencari">
                    <span>Cari</span>
                </button>
            </div>
        </div>

        <!-- Autocomplete Suggestions Dropdown (High z-index, max 8 items) -->
        <div id="{{ $id }}_dropdown"
             class="absolute left-0 right-0 top-full mt-1.5 z-50 bg-white rounded-xl shadow-xl border border-slate-200/90 overflow-hidden hidden max-h-80 overflow-y-auto">
            <ul id="{{ $id }}_list" class="divide-y divide-slate-100 text-sm" role="listbox"></ul>
        </div>
    </div>

    <!-- Filter Controls & Reset (Aligned Side-by-Side Right) -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3 shrink-0">
        {{ $slot }}

        <!-- Reset Button (Only rendered when search or filter is active) -->
        @if($hasActiveFilters)
            <a href="{{ $resetUrl }}"
               class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-rose-50 hover:bg-rose-100 active:bg-rose-200 text-rose-700 font-semibold text-xs rounded-xl border border-rose-200/80 transition-all min-h-[44px] shadow-2xs"
               title="Hapus semua filter dan pencarian">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Reset Filter</span>
            </a>
        @endif
    </div>
</form>

<script>
(function() {
    const form = document.getElementById('{{ $id }}_form');
    const input = document.getElementById('{{ $id }}_input');
    const dropdown = document.getElementById('{{ $id }}_dropdown');
    const list = document.getElementById('{{ $id }}_list');
    const spinner = document.getElementById('{{ $id }}_spinner');
    const clearBtn = document.getElementById('{{ $id }}_clear');
    const autocompleteUrl = @json($effectiveAutocompleteUrl);

    let debounceTimer = null;
    let abortController = null;
    let selectedIndex = -1;
    let suggestionsData = [];
    const queryCache = {};

    if (!input || !form) return;

    // --- Clear Button Logic ---
    function updateClearBtn() {
        if (!clearBtn) return;
        if (input.value.trim().length > 0) {
            clearBtn.classList.remove('hidden');
        } else {
            clearBtn.classList.add('hidden');
        }
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            input.value = '';
            updateClearBtn();
            closeDropdown();
            input.focus();
        });
    }

    // Run once on load in case value is pre-filled (e.g. back navigation)
    updateClearBtn();

    // Show suggestions on focus if input has >= 2 characters
    input.addEventListener('focus', function() {
        const query = this.value.trim();
        if (query.length >= 2) {
            if (queryCache[query]) {
                if (queryCache[query].length > 0) {
                    renderSuggestions(queryCache[query]);
                } else {
                    renderEmpty();
                }
            } else {
                fetchSuggestions(query);
            }
        }
    });

    // RULE: Debounce 500ms is strictly for fetching autocomplete suggestions.
    // It NEVER submits the form or triggers page reload!
    input.addEventListener('input', function() {
        updateClearBtn();
        const query = this.value.trim();

        // 1. Clear previous timer
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }

        // 2. Abort previous running fetch request if any
        if (abortController) {
            abortController.abort();
            abortController = null;
        }

        // 3. If query length < 2, close dropdown and do not request
        if (query.length < 2) {
            closeDropdown();
            return;
        }

        // Instant response from client cache without hitting server
        if (queryCache[query]) {
            if (queryCache[query].length > 0) {
                renderSuggestions(queryCache[query]);
            } else {
                renderEmpty();
            }
            return;
        }

        // 4. Set debounce 500ms strictly for autocomplete request
        debounceTimer = setTimeout(() => {
            fetchSuggestions(query);
        }, 500);
    });

    function fetchSuggestions(query) {
        if (!autocompleteUrl) {
            return;
        }

        if (queryCache[query]) {
            if (queryCache[query].length > 0) {
                renderSuggestions(queryCache[query]);
            } else {
                renderEmpty();
            }
            return;
        }

        // Show spinner
        if (spinner) spinner.classList.remove('hidden');

        abortController = new AbortController();

        const separator = autocompleteUrl.includes('?') ? '&' : '?';
        const requestUrl = `${autocompleteUrl}${separator}term=${encodeURIComponent(query)}`;

        fetch(requestUrl, {
            signal: abortController.signal,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            if (spinner) spinner.classList.add('hidden');
            const items = Array.isArray(data) ? data : [];
            queryCache[query] = items;
            if (items.length > 0) {
                renderSuggestions(items);
            } else {
                renderEmpty();
            }
        })
        .catch(err => {
            // Silently ignore AbortError
            if (err.name !== 'AbortError') {
                if (spinner) spinner.classList.add('hidden');
                closeDropdown();
            }
        });
    }

    // Render suggestions (Structured: label + sub + value, max 8 items)
    function renderSuggestions(items) {
        suggestionsData = items.slice(0, 8);
        selectedIndex = -1;
        list.innerHTML = '';

        suggestionsData.forEach((item, index) => {
            // Support both structured {label, sub, value} and simple string items
            const label = (typeof item === 'object' && item !== null && item.label) ? item.label : String(item);
            const sub = (typeof item === 'object' && item !== null && item.sub) ? item.sub : '';
            const value = (typeof item === 'object' && item !== null && item.value) ? item.value : label;

            const li = document.createElement('li');
            li.className = 'px-4 py-2.5 cursor-pointer hover:bg-blue-50/80 text-slate-800 transition-colors text-sm min-h-[44px] flex items-center justify-between gap-3';
            li.setAttribute('role', 'option');
            li.setAttribute('data-index', index);

            li.innerHTML = `
                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <div class="min-w-0 flex-1">
                        <div class="font-semibold text-slate-900 truncate leading-snug">${escapeHtml(label)}</div>
                        ${sub ? `<div class="text-xs text-slate-500 font-medium truncate mt-0.5">${escapeHtml(sub)}</div>` : ''}
                    </div>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            `;

            li.addEventListener('click', function(e) {
                e.preventDefault();
                selectSuggestion(value);
            });

            list.appendChild(li);
        });

        dropdown.classList.remove('hidden');
    }

    function renderEmpty() {
        suggestionsData = [];
        selectedIndex = -1;
        list.innerHTML = `
            <li class="px-4 py-3 text-xs text-slate-400 text-center select-none">
                Tidak ada saran ditemukan
            </li>
        `;
        dropdown.classList.remove('hidden');
    }

    // PROCESS 2: ACTUAL SEARCH via native GET when selecting a suggestion
    function selectSuggestion(val) {
        input.value = val;
        closeDropdown();
        // Native GET server-side submit
        form.submit();
    }

    function closeDropdown() {
        if (dropdown) dropdown.classList.add('hidden');
        if (spinner) spinner.classList.add('hidden');
        selectedIndex = -1;
    }

    // Keyboard navigation: ArrowDown, ArrowUp, Enter, Escape
    input.addEventListener('keydown', function(e) {
        const items = list.querySelectorAll('li[data-index]');

        if (e.key === 'Escape') {
            closeDropdown();
            return;
        }

        // If dropdown is open and has options
        if (!dropdown.classList.contains('hidden') && items.length > 0) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = (selectedIndex + 1) % items.length;
                highlightItem(items);
                return;
            }

            if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = (selectedIndex - 1 + items.length) % items.length;
                highlightItem(items);
                return;
            }
        }

        if (e.key === 'Enter') {
            const hasSelectableOptions = !dropdown.classList.contains('hidden') && items.length > 0;
            if (hasSelectableOptions && selectedIndex >= 0 && selectedIndex < suggestionsData.length) {
                e.preventDefault();
                const item = suggestionsData[selectedIndex];
                const val = (typeof item === 'object' && item !== null && item.value) ? item.value : ((typeof item === 'object' && item !== null && item.label) ? item.label : String(item));
                selectSuggestion(val);
            } else {
                // Enter without selecting a specific dropdown item: submit native GET with current query
                e.preventDefault();
                closeDropdown();
                form.submit();
            }
            return;
        }
    });

    function highlightItem(items) {
        items.forEach((item, idx) => {
            if (idx === selectedIndex) {
                item.classList.add('bg-blue-50', 'text-blue-900');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('bg-blue-50', 'text-blue-900');
            }
        });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const container = document.getElementById('{{ $id }}_container');
        if (container && !container.contains(e.target)) {
            closeDropdown();
        }
    });

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>'"]/g, tag => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            "'": '&#39;',
            '"': '&quot;'
        }[tag] || tag));
    }
})();
</script>
