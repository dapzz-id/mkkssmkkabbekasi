<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="MKKS, Musyawarah Kerja Kepala Sekolah, MKKS SMK, MKKS SMK Bekasi, SMK Bekasi, SMK, SMKN, SMKS, Organisasi, Organization, SMK Swasta, SMK Negeri, Swasta, Sekolah, SMK Telkom, SMK Telekomunikasi, SMK Telesandi, SMK Telekomunikasi Telesandi, Bekasi, Kab Bekasi, Kabupaten Bekasi, Kota Bekasi">
    @hasSection('seo_tags')
        @yield('seo_tags')
    @else
        <meta name="description" content="MKKS SMK Kab Bekasi adalah organisasi yang beranggotakan seluruh Kepala Sekolah SMK Negeri dan Swasta di Kabupaten Bekasi.">
        <meta name="robots" content="index, follow">
        <link rel="canonical" href="https://mkkssmkkabbekasi.or.id/">
        <meta property="og:title" content="MKKS SMK Kab Bekasi">
        <meta property="og:description" content="MKKS SMK Kab Bekasi berkolaborasi untuk meningkatkan pendidikan vokasi, mempersiapkan generasi muda menghadapi dunia kerja, dan mendorong inovasi berbasis industri. Bergabunglah bersama kami untuk menciptakan perubahan nyata di dunia pendidikan kejuruan!">
        <meta property="og:image" content="{{asset('img/kegiatan/kegiatan1.jpg')}}">
        <meta property="og:url" content="https://www.instagram.com/mkks.smkkab.bekasi/">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="MKKS SMK Kab Bekasi">
        <meta name="author" content="MKKS SMK Kab Bekasi">
        <meta http-equiv="Content-Language" content="id">
        <meta name="geo.placename" content="Bekasi">
        <title>MKKS SMK KAB BEKASI</title>
    @endif

    <style>
        @media (min-width: 768px){
            .btn-prev, .btn-next {
                padding: 0.625rem !important /* 10px */;
                background: #c3c5c7 !important;
            }
        }
        @media not all and (min-width: 768px) {
            .btn-prev, .btn-next {
                padding: 0.25rem !important /* 4px */;
                background: black !important;
            }
        }
    </style>

    @hasSection('json_ld')
        @yield('json_ld')
    @else
        <script type="application/ld+json">
            {
            "@context": "https://schema.org",
            "@type": "Article",
            "headline": "MKKS SMK Bekasi",
            "description": "MKKS SMK Bekasi berkolaborasi untuk meningkatkan pendidikan vokasi, mempersiapkan generasi muda menghadapi dunia kerja, dan mendorong inovasi berbasis industri. Bergabunglah bersama kami untuk menciptakan perubahan nyata di dunia pendidikan kejuruan!",
            "image": "{{asset('img/kegiatan/kegiatan1.jpg')}}",
            "author": {
                "@type": "Person",
                "name": "MKKS SMK Bekasi"
            },
            "datePublished": "2024-11-29"
            }
        </script>
    @endif    

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- <link rel="stylesheet" href="{{asset('dist/css/main.css')}}"> --}}
    <link rel="stylesheet" href="{{ asset('css/beranda.css') }}" />

    @if(Request::is('selengkapnya'))
        <link rel="stylesheet" href="{{ asset('css/selengkapnya.css') }}" />
    @endif

    @if(Request::is('jadwal'))
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
    <link rel="stylesheet" href="{{ asset('css/jadwal.css') }}">
    @endif

    @if(Request::is('divisi*'))
    <link rel="stylesheet" href="{{ asset('css/jadwal.css') }}">
    @endif

    @if(Request::is('konten*'))
        <link rel="stylesheet" href="{{ asset('dist/css/main.css') }}">
    @endif

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js" async></script>

    <style>
        .pointer {
            cursor: pointer;
        }
        *{
            caret-color: transparent;
        }
        ::-webkit-scrollbar{
            width: 0;
        }

        @supports (-ms-ime-align: auto){
            html{
                -ms-overflow-style: -ms-autohiding-scrollbar;
            }
        }
    </style>

    {{-- link rel stylesheet ikon --}}
    <link rel="icon" href="{{ asset('img/ic_MKKS.png') }}" type="image/png">
    <link rel="icon" href="{{asset('img/ic_MKKS.ico')}}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet">
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">
    @include('publik.utils.navbar')

    <main class="flex-grow-1">
        @yield('container')
    </main>

    @include('publik.utils.footer')

    <!-- Bootstrap JS -->
    @if(Request::is('jadwal'))
    <script src="{{ asset('js/jadwal.js') }}"></script>
    @endif
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
