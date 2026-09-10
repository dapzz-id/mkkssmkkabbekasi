@php
    $current = $data->currentPage();
    $last    = $data->lastPage();
    $total   = $data->total();
    $firstItem = $data->firstItem() ?? 0;
    $lastItem  = $data->lastItem() ?? 0;
    $pageName  = $data->getPageName() ?? 'page';

    // Narrower range on mobile via PHP — but we'll control this with CSS
    $range = 2;
    $start = max(1, $current - $range);
    $end   = min($last, $current + $range);

    $allowedSizes    = [2, 5, 10, 20, 50];
    $currentPerPage  = $data->perPage();
@endphp

{{-- ============================================================
     PAGINATION FOOTER — Responsive 3-zone layout
     Mobile  : stacked (page controls → info → per-page)
     sm+     : single row (info | controls | per-page)
     ============================================================ --}}
<div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 sm:px-6 py-3.5 border-t border-slate-100 bg-white rounded-b-xl">

    {{-- ① Info: "Menampilkan X - Y dari Z data" --}}
    <div class="text-xs text-slate-500 font-medium order-2 sm:order-1 text-center sm:text-left whitespace-nowrap">
        Menampilkan
        <span class="font-bold text-slate-700">{{ $firstItem }}</span>–<span class="font-bold text-slate-700">{{ $lastItem }}</span>
        dari <span class="font-bold text-slate-700">{{ $total }}</span> data
    </div>

    {{-- ② Page Controls (center) --}}
    <div class="flex items-center gap-0.5 sm:gap-1 order-1 sm:order-2 flex-wrap justify-center">

        {{-- Previous --}}
        @if ($data->onFirstPage())
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-300 cursor-not-allowed select-none" aria-disabled="true">
                {{-- Icon only (always) --}}
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </span>
        @else
            <a href="{{ $data->previousPageUrl() }}"
               class="page-link inline-flex items-center justify-center gap-1 w-8 h-8 sm:w-auto sm:px-2.5 rounded-lg text-xs font-semibold text-blue-600 hover:text-blue-800 hover:bg-blue-50 transition-colors"
               aria-label="Halaman Sebelumnya">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                <span class="hidden sm:inline">Sebelumnya</span>
            </a>
        @endif

        {{-- First page if out of range --}}
        @if ($start > 1)
            <a href="{{ $data->url(1) }}"
               class="page-link w-8 h-8 rounded-lg text-xs font-semibold flex items-center justify-center transition-colors text-slate-700 hover:bg-slate-100"
               aria-label="Halaman 1">1</a>
            @if ($start > 2)
                <span class="text-slate-400 text-xs px-0.5 select-none">…</span>
            @endif
        @endif

        {{-- Page Numbers --}}
        @for ($i = $start; $i <= $end; $i++)
            @if ($i == $current)
                <span class="w-8 h-8 rounded-lg text-xs font-bold flex items-center justify-center bg-blue-600 text-white shadow-sm select-none">
                    {{ $i }}
                </span>
            @else
                <a href="{{ $data->url($i) }}"
                   class="page-link w-8 h-8 rounded-lg text-xs font-semibold flex items-center justify-center transition-colors text-slate-700 hover:bg-slate-100"
                   aria-label="Halaman {{ $i }}">{{ $i }}</a>
            @endif
        @endfor

        {{-- Last page if out of range --}}
        @if ($end < $last)
            @if ($end < $last - 1)
                <span class="text-slate-400 text-xs px-0.5 select-none">…</span>
            @endif
            <a href="{{ $data->url($last) }}"
               class="page-link w-8 h-8 rounded-lg text-xs font-semibold flex items-center justify-center transition-colors text-slate-700 hover:bg-slate-100"
               aria-label="Halaman {{ $last }}">{{ $last }}</a>
        @endif

        {{-- Next --}}
        @if ($data->hasMorePages())
            <a href="{{ $data->nextPageUrl() }}"
               class="page-link inline-flex items-center justify-center gap-1 w-8 h-8 sm:w-auto sm:px-2.5 rounded-lg text-xs font-semibold text-blue-600 hover:text-blue-800 hover:bg-blue-50 transition-colors"
               aria-label="Halaman Berikutnya">
                <span class="hidden sm:inline">Berikutnya</span>
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        @else
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-300 cursor-not-allowed select-none" aria-disabled="true">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </span>
        @endif
    </div>

    {{-- ③ Per-page Selector (right) --}}
    <div class="flex items-center gap-1.5 text-xs text-slate-500 order-3 whitespace-nowrap">
        <label for="perPageSelect-{{ $pageName }}" class="sr-only">Jumlah baris per halaman</label>
        <div class="relative inline-block">
            <select id="perPageSelect-{{ $pageName }}"
                    onchange="handleGlobalPerPageChange(this.value, '{{ $pageName }}')"
                    class="pl-2.5 pr-7 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-700 shadow-sm appearance-none cursor-pointer focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    aria-label="Pilih jumlah data per halaman">
                @foreach ($allowedSizes as $size)
                    <option value="{{ $size }}" {{ $currentPerPage == $size ? 'selected' : '' }}>{{ $size }}</option>
                @endforeach
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2 text-slate-400">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>
        <span>per halaman</span>
    </div>
</div>

<script>
    if (typeof handleGlobalPerPageChange !== 'function') {
        function handleGlobalPerPageChange(size, pageName) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', size);
            url.searchParams.set(pageName || 'page', '1');
            window.location.href = url.toString();
        }
    }
</script>
