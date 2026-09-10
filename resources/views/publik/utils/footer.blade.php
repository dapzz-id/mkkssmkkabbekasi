<!-- Footer -->
<footer class="footer">
    <div class="py-2 running-text">
        <div class="running-text-container">
            @php
                $recentContent = DB::table('konten')
                    ->orderBy('tanggal_upload', 'desc')
                    ->take(3)
                    ->select('judul')
                    ->get();
            @endphp

            <div class="running-text-content">
                @if($recentContent->count() > 0)
                    @foreach($recentContent as $content)
                        <span class="running-text-item">{{ $content->judul }} &bull; </span>
                    @endforeach
                @else
                    <span class="running-text-item">Selamat datang di website MKKS SMK Kab Bekasi</span>
                @endif
            </div>
        </div>
    </div>

    <div class="py-4 footer-content">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                @php
                    $sponsor = DB::table('sponsor')->get();
                @endphp

                <!-- Sponsor Images -->
                <div class="flex-wrap gap-4 mb-4 sponsor-wrapper d-flex justify-content-center align-items-center">
                    @foreach($sponsor as $item)
                        <div class="sponsor-item">
                            <img data-src="{{ $item->url_image }}" height="60" title="{{$item->nama}}" alt="{{ $item->nama }}" class="lazyload sponsor-image">
                        </div>
                    @endforeach
                </div>

                <!-- Copyright -->
                <div class="text-center col-12">
                    <p class="mb-0 copyright">&copy; 2024 - MKKS SMK KAB BEKASI | All Right Reserved</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Bulletproof Footer Isolation (Protects from Tailwind / Bootstrap Preflight Resets) */
        .footer {
            margin-top: 60px;
            width: 100%;
            position: relative;
            clear: both;
        }

        .footer .running-text {
            overflow: hidden !important;
            background-color: #5B5E61 !important;
            border-top: 1px solid rgba(255,255,255,0.1) !important;
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
        }

        .footer .running-text-container {
            width: 100% !important;
            overflow: hidden !important;
            white-space: nowrap !important;
        }

        .footer .running-text-content {
            display: inline-block !important;
            white-space: nowrap !important;
            padding-left: 100% !important;
            animation: running-text 30s linear infinite !important;
        }

        .footer .running-text-item {
            color: white !important;
            font-size: 0.9rem !important;
            margin-right: 20px !important;
            display: inline-block !important;
            font-family: inherit !important;
        }

        .footer .footer-content {
            background-color: #5B5E61 !important;
            padding: 1.5rem 0 !important;
        }

        .footer .sponsor-wrapper {
            display: flex !important;
            flex-wrap: wrap !important;
            justify-content: center !important;
            align-items: center !important;
            gap: 1.25rem !important;
            margin: 1rem 0 !important;
        }

        .footer .sponsor-item {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex-shrink: 0 !important;
        }

        .footer .sponsor-image {
            height: 60px !important;
            max-height: 60px !important;
            width: auto !important;
            max-width: 140px !important;
            object-fit: contain !important;
            display: inline-block !important;
            transition: transform 0.3s ease !important;
        }

        .footer .sponsor-image:hover {
            transform: scale(1.08) !important;
        }

        .footer .copyright {
            color: white !important;
            font-size: 0.9rem !important;
            text-align: center !important;
            margin-bottom: 0 !important;
        }

        @media (max-width: 576px) {
            .footer .sponsor-image {
                height: 40px !important;
                max-height: 40px !important;
                max-width: 100px !important;
            }
            .footer .copyright {
                font-size: 0.8rem !important;
            }
        }
    </style>
</footer>
