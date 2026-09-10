@extends('admin.layouts.main')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 space-y-6">
    <!-- PAGE HEADER & BREADCRUMB -->
    <div class="flex flex-col gap-4 pt-1 sm:pt-2">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight leading-tight">
                        Manage Account
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5 font-medium">
                        Edit akun pengurus #{{ $subAdmin->id }}
                    </p>
                </div>
            </div>

            <a href="/manage/user"
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold transition-all shadow-2xs shrink-0 self-start sm:self-auto min-h-[44px]">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali</span>
            </a>
        </div>

        <div class="pt-1.5">
            <x-admin-breadcrumb :items="[
                ['label' => 'Manage Account', 'url' => '/manage/user'],
                ['label' => $subAdmin->id . '_edit']
            ]" />
        </div>
    </div>

    <!-- MAIN FORM CARD -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Form Edit Akun #{{ $subAdmin->id }}</h2>
            <span class="text-xs text-slate-500 font-medium">* Wajib diisi</span>
        </div>

        <form action="{{ route('subadmin.update', $subAdmin->id) }}" id="formEditUser" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                    <p class="font-bold mb-1">Terjadi kesalahan input:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Nama Lengkap -->
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-semibold text-slate-800 mb-2">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           id="name"
                           value="{{ old('name', $subAdmin->name) }}"
                           required
                           placeholder="Masukkan nama lengkap"
                           class="w-full px-4 py-2.5 bg-white border @error('name') border-rose-400 focus:ring-rose-400 @else border-slate-300 focus:ring-blue-500 focus:border-blue-500 @enderror rounded-xl text-sm font-medium text-slate-800 focus:ring-2 shadow-2xs transition-all">
                    @error('name')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-semibold text-slate-800 mb-2">
                        Username <span class="text-rose-500">*</span>
                    </label>
                    <input type="text"
                           name="username"
                           id="username"
                           value="{{ old('username', $subAdmin->username) }}"
                           required
                           placeholder="Masukkan username"
                           class="w-full px-4 py-2.5 bg-white border @error('username') border-rose-400 focus:ring-rose-400 @else border-slate-300 focus:ring-blue-500 focus:border-blue-500 @enderror rounded-xl text-sm font-medium text-slate-800 focus:ring-2 shadow-2xs transition-all">
                    @error('username')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-800 mb-2">
                        Email <span class="text-rose-500">*</span>
                    </label>
                    <input type="email"
                           name="email"
                           id="email"
                           value="{{ old('email', $subAdmin->email) }}"
                           required
                           placeholder="nama@smk.belajar.id"
                           class="w-full px-4 py-2.5 bg-white border @error('email') border-rose-400 focus:ring-rose-400 @else border-slate-300 focus:ring-blue-500 focus:border-blue-500 @enderror rounded-xl text-sm font-medium text-slate-800 focus:ring-2 shadow-2xs transition-all">
                    @error('email')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Divisi -->
                <div>
                    <label for="divisi" class="block text-sm font-semibold text-slate-800 mb-2">
                        Divisi Terkait <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="divisi"
                                id="divisi"
                                required
                                class="w-full pl-4 pr-10 py-2.5 bg-white border @error('divisi') border-rose-400 @else border-slate-300 focus:ring-blue-500 focus:border-blue-500 @enderror rounded-xl text-sm font-medium text-slate-800 focus:ring-2 shadow-2xs appearance-none transition-all cursor-pointer">
                            <option value="" disabled>Pilih Divisi</option>
                            @foreach($divisi as $item)
                                <option value="{{ $item->id }}" {{ old('divisi', $subAdmin->id_divisi) == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama_divisi }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                    @error('divisi')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-sm font-semibold text-slate-800 mb-2">
                        Peran (Role) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="role"
                                id="role"
                                required
                                class="w-full pl-4 pr-10 py-2.5 bg-white border @error('role') border-rose-400 @else border-slate-300 focus:ring-blue-500 focus:border-blue-500 @enderror rounded-xl text-sm font-medium text-slate-800 focus:ring-2 shadow-2xs appearance-none transition-all cursor-pointer">
                            <option value="" disabled>Pilih Role</option>
                            <option value="admin" {{ old('role', $subAdmin->role) === 'admin' ? 'selected' : '' }}>Admin (Pengurus Divisi)</option>
                            <option value="superadmin" {{ old('role', $subAdmin->role) === 'superadmin' ? 'selected' : '' }}>Super Administrator</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                    @error('role')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password (Optional on Edit) -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-800 mb-1">
                        Password Baru (Opsional)
                    </label>
                    <p class="text-xs text-slate-500 mb-2">Kosongkan jika tidak ingin mengubah password lama.</p>
                    <input type="password"
                           name="password"
                           id="password"
                           placeholder="Minimal 8 karakter"
                           class="w-full px-4 py-2.5 bg-white border @error('password') border-rose-400 focus:ring-rose-400 @else border-slate-300 focus:ring-blue-500 focus:border-blue-500 @enderror rounded-xl text-sm font-medium text-slate-800 focus:ring-2 shadow-2xs transition-all">
                    @error('password')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label for="confirm_password" class="block text-sm font-semibold text-slate-800 mb-1">
                        Konfirmasi Password Baru
                    </label>
                    <p class="text-xs text-slate-500 mb-2">Isi jika password baru di atas diisi.</p>
                    <input type="password"
                           name="confirm_password"
                           id="confirm_password"
                           placeholder="Ulangi password baru"
                           class="w-full px-4 py-2.5 bg-white border @error('confirm_password') border-rose-400 focus:ring-rose-400 @else border-slate-300 focus:ring-blue-500 focus:border-blue-500 @enderror rounded-xl text-sm font-medium text-slate-800 focus:ring-2 shadow-2xs transition-all">
                    @error('confirm_password')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Alamat -->
                <div class="sm:col-span-2">
                    <label for="alamat" class="block text-sm font-semibold text-slate-800 mb-2">
                        Alamat Lengkap / Asal Sekolah <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="alamat"
                              id="alamat"
                              rows="3"
                              required
                              placeholder="Masukkan alamat atau sekolah asal..."
                              class="w-full px-4 py-2.5 bg-white border @error('alamat') border-rose-400 focus:ring-rose-400 @else border-slate-300 focus:ring-blue-500 focus:border-blue-500 @enderror rounded-xl text-sm font-medium text-slate-800 focus:ring-2 shadow-2xs transition-all leading-relaxed">{{ old('alamat', $subAdmin->alamat) }}</textarea>
                    @error('alamat')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="/manage/user"
                   class="px-5 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-semibold transition-all cursor-pointer">
                    Batal
                </a>
                <button type="button"
                        id="btnSubmitUserEdit"
                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold rounded-xl shadow-xs transition-all cursor-pointer focus:ring-2 focus:ring-blue-400 min-w-[140px]">
                    <span id="btnSubmitText">Simpan Perubahan</span>
                    <svg id="btnSubmitSpinner" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnSubmit = document.getElementById('btnSubmitUserEdit');
    const form = document.getElementById('formEditUser');
    const btnText = document.getElementById('btnSubmitText');
    const btnSpinner = document.getElementById('btnSubmitSpinner');

    if (btnSubmit && form) {
        btnSubmit.addEventListener('click', function() {
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            Swal.fire({
                title: "Simpan Perubahan?",
                text: "Apakah Anda yakin ingin memperbarui data akun ini?",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#2563eb",
                cancelButtonColor: "#64748b",
                confirmButtonText: "Ya, Simpan",
                cancelButtonText: "Batal",
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    btnSubmit.disabled = true;
                    btnSubmit.classList.add('opacity-75', 'cursor-not-allowed');
                    btnText.textContent = "Menyimpan...";
                    btnSpinner.classList.remove('hidden');
                    form.submit();
                }
            });
        });
    }
});
</script>
@endpush