@extends('admin.layouts.main')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 space-y-6">
    <!-- PAGE HEADER & BREADCRUMB -->
    <div class="flex flex-col gap-4 pt-1 sm:pt-2">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h18a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight leading-tight">
                        Manage Account
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5 font-medium">
                        Detail data akun pengurus #{{ $user->id }}
                    </p>
                </div>
            </div>

            <!-- Action Buttons: Edit & Kembali -->
            <div class="flex items-center gap-2.5 shrink-0">
                <a href="/manage/user"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold transition-all shadow-2xs min-h-[44px]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali</span>
                </a>

                @if(Auth::user()->role === 'superadmin')
                    <a href="{{ url('/manage/user/' . $user->id . '/edit') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold transition-all shadow-xs min-h-[44px]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Edit Akun</span>
                    </a>
                @endif
            </div>
        </div>

        <div class="pt-1.5">
            <x-admin-breadcrumb :items="[
                ['label' => 'Manage Account', 'url' => '/manage/user'],
                ['label' => $user->id . '_view']
            ]" />
        </div>
    </div>

    <!-- MAIN DETAIL CARD -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 sm:p-8 space-y-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-2xl shadow-xs shrink-0">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">
                        {{ $user->name }}
                    </h2>
                    <p class="text-sm font-semibold text-slate-500 mt-0.5">
                        {{ '@' . $user->username }} &bull; {{ $user->email }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-slate-100">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Peran (Role)</span>
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold {{ $user->role === 'superadmin' ? 'bg-blue-100 text-blue-800' : 'bg-slate-200 text-slate-800' }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Divisi Ditugaskan</span>
                    <span class="font-bold text-slate-800 text-sm">
                        {{ $user->divisi->nama_divisi ?? 'Tanpa Divisi (Superadmin)' }}
                    </span>
                </div>

                <div class="sm:col-span-2 p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Alamat / Instansi</span>
                    <p class="text-sm text-slate-700 leading-relaxed font-medium">
                        {{ $user->alamat ?? '-' }}
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">ID Pengguna</span>
                    <span class="font-bold text-slate-800 text-sm">#{{ $user->id }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
