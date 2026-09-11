<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container-fluid navbar-container">
        <!-- Logo and Title Section -->
        <div class="navbar-brand-section d-flex flex-column flex-lg-row align-items-center">
        <div class="d-flex justify-content-center justify-content-md-start">
            <img src="{{ asset('img/ic_MKKS.png') }}" alt="Logo MKKS" class="navbar-logo">
            <img src="{{ asset('img/disdik_icon.png') }}" alt="Logo Disdik Jabar" class="navbar-logo ms-2">
            <img src="{{ asset('img/fkksmkks.png') }}" alt="Logo FKKS" class="navbar-logo ms-2">
        </div>
        <div class="mt-2 mt-md-0 d-none d-sm-block text-white text-center text-md-start ms-md-2">
            <h5 class="mb-0 site-title">MKKS SMK KAB BEKASI</h5>
            <h6 class="mb-0 site-subtitle">MUSYAWARAH KERJA KEPALA SEKOLAH</h6>
        </div>
    </div>
    
    <style>
        .navbar-logo {
          height: 50px !important;
          max-height: 50px !important;
          width: auto !important;
          object-fit: contain !important;
        }
    </style>


        <!-- Navbar Toggler untuk Mobile -->
        <button class="navbar-toggler" type="button" onclick="toggleSidebar()">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Desktop Navigation -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('/') ? 'active' : '' }}" href="/">PROFIL</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ Request::is('divisi/*') ? 'active' : '' }}" href="#" id="divisiDropdown" data-bs-toggle="dropdown">
                        DIVISI
                    </a>
                    <ul class="dropdown-menu">
                        @foreach($divisi as $item)
                        <li><a class="dropdown-item" href="/divisi/{{ $item->uuid }}">{{ $item->nama_divisi }}</a></li>
                        @endforeach
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('galeri') ? 'active' : '' }}" href="/galeri">GALERI</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('jadwal') ? 'active' : '' }}" href="/jadwal">JADWAL</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Mobile Sidebar -->
<div class="sidebar" id="mobileSidebar">
    <button class="sidebar-close" onclick="toggleSidebar()">&times;</button>
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link {{ Request::is('/') ? 'active' : '' }}" href="/">PROFIL</a>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle {{ Request::is('divisi/*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                DIVISI
            </a>
            <ul class="dropdown-menu">
                @foreach($divisi as $item)
                <li><a class="dropdown-item" href="/divisi/{{ $item->uuid }}">{{ $item->nama_divisi }}</a></li>
                @endforeach
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Request::is('galeri') ? 'active' : '' }}" href="/galeri">GALERI</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Request::is('jadwal') ? 'active' : '' }}" href="/jadwal">JADWAL</a>
        </li>
    </ul>
</div>

<!-- Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- Tambahkan script ini di bagian bawah file -->
<script>
function toggleSidebar() {
    const sidebar = document.getElementById('mobileSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');

    // Mengunci scroll body ketika sidebar terbuka
    if (sidebar.classList.contains('active')) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
}

// Menutup sidebar ketika ukuran layar berubah ke desktop
window.addEventListener('resize', function() {
    if (window.innerWidth > 991.98) {
        const sidebar = document.getElementById('mobileSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
});
</script>
