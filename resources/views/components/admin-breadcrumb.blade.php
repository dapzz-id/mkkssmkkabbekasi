@props(['items' => []])

@if(!empty($items))
    <nav aria-label="Breadcrumb" class="w-full">
        <ol class="flex items-center flex-wrap gap-1.5 text-xs sm:text-sm font-medium text-slate-500 list-none p-0 m-0 leading-relaxed">
            @foreach($items as $index => $item)
                @php
                    $isLast = $loop->last;
                    $hasUrl = !empty($item['url']) && !$isLast;
                @endphp

                <li class="inline-flex items-center gap-1.5">
                    @if($hasUrl)
                        <a href="{{ $item['url'] }}"
                           class="text-blue-600 hover:text-blue-700 hover:underline transition-colors font-semibold rounded focus:ring-2 focus:ring-blue-400 focus:outline-none">
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span class="text-slate-900 font-bold tracking-tight" aria-current="{{ $isLast ? 'page' : 'false' }}">
                            {{ $item['label'] }}
                        </span>
                    @endif

                    @if(!$isLast)
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 select-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
