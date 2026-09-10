@extends('admin.layouts.main')

@section('container')
    <!-- Dashboard Section -->
    <div id="dashboardSection" class="p-4 sm:p-6 lg:p-8 space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Dashboard</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Ringkasan dan selamat datang di panel administrasi MKKS SMK Kab Bekasi</p>
        </div>

        <div class="p-6 sm:p-8 bg-white rounded-2xl border border-slate-200/90 shadow-sm">
            <h2 class="text-xl font-bold text-slate-900">Selamat datang, {{ Auth::user()->name }} 👋</h2>
            <p class="mt-2 text-slate-600 leading-relaxed">Kerja keras, kolaborasi, dan komitmen adalah kunci sukses kita!</p>
            <p class="text-slate-600 leading-relaxed">Bersama kita wujudkan SMK yang berdaya saing tinggi!</p>
        </div>
    </div>
@endsection
