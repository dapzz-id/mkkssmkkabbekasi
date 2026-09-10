<?php

namespace App\Http\Controllers;

use App\Models\Pimpinan;
use App\Services\SecureUploadValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PimpinanAdminController extends Controller
{
    protected SecureUploadValidator $uploadValidator;

    public function __construct(SecureUploadValidator $uploadValidator)
    {
        $this->uploadValidator = $uploadValidator;
    }

    /**
     * Ensure only Superadmin can access Pimpinan management.
     */
    protected function authorizeSuperAdmin(): void
    {
        if (!Auth::check() || Auth::user()->role !== 'superadmin') {
            abort(403, 'Akses ditolak. Halaman ini hanya untuk Superadmin.');
        }
    }

    /**
     * Display a listing of Pimpinan MKKS.
     */
    public function index(Request $request)
    {
        $this->authorizeSuperAdmin();

        $query = Pimpinan::ordered();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $cleanSearch = trim(preg_replace('/^(nama|jabatan|pimpinan)\s*[:\-]?\s*/i', '', $search));
            $effective = $cleanSearch !== '' ? $cleanSearch : $search;

            $query->where(function ($q) use ($search, $effective) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%");
                if ($effective !== $search) {
                    $q->orWhere('nama', 'like', "%{$effective}%")
                      ->orWhere('jabatan', 'like', "%{$effective}%");
                }
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $page = $request->input('pimpinan-page', 1);
        $allowedPerPage = [2, 5, 10, 20, 50];
        $requestedPerPage = (int) $request->input('per_page');
        $perPage = in_array($requestedPerPage, $allowedPerPage) ? $requestedPerPage : 5;
        $dataPimpinan = $query->paginate($perPage, ['*'], 'pimpinan-page', $page)->withQueryString();

        if ($request->ajax()) {
            if ($request->has('pimpinan-page') || $request->has('per_page')) {
                return view('admin.pimpinan-table', compact('dataPimpinan'))->render();
            }
        }

        return view('admin.main.pimpinan', compact('dataPimpinan'));
    }

    /**
     * Return lightweight suggestions for Pimpinan search.
     */
    public function suggestions(Request $request)
    {
        $this->authorizeSuperAdmin();

        $term = trim($request->input('term', $request->input('q', '')));
        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $cleanTerm = trim(preg_replace('/^(nama|jabatan|pimpinan)\s*[:\-]?\s*/i', '', $term));
        $effective = $cleanTerm !== '' ? $cleanTerm : $term;

        $results = Pimpinan::where(function($q) use ($term, $effective) {
                $q->where('nama', 'like', "%{$term}%")
                  ->orWhere('jabatan', 'like', "%{$term}%");
                if ($effective !== $term) {
                    $q->orWhere('nama', 'like', "%{$effective}%")
                      ->orWhere('jabatan', 'like', "%{$effective}%");
                }
            })
            ->limit(8)
            ->get(['id', 'nama', 'jabatan'])
            ->map(function ($item) {
                return [
                    'label' => $item->nama,
                    'sub' => $item->jabatan,
                    'value' => $item->nama,
                ];
            });

        return response()->json($results);
    }

    /**
     * Show the form for creating a new Pimpinan.
     */
    public function create()
    {
        $this->authorizeSuperAdmin();

        $nextOrder = (Pimpinan::max('urutan') ?? 0) + 1;

        return view('admin.pimpinan.tambahpimpinan', compact('nextOrder'));
    }

    /**
     * Store a newly created Pimpinan in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'foto' => 'required|file',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|in:0,1',
        ], [
            'nama.required' => 'Masukkan nama pimpinan',
            'jabatan.required' => 'Masukkan jabatan pimpinan',
            'foto.required' => 'Unggah foto pimpinan',
            'foto.file' => 'File harus berupa berkas yang valid',
        ]);

        // 1. Validate photo through SecureUploadValidator (magic bytes, MIME, getimagesize structure)
        $photo = $request->file('foto');
        $photoInfo = $this->uploadValidator->validatePimpinanPhoto($photo);
        $safeFilename = $photoInfo['safe_filename'];

        // 2. Stage photo to public disk
        $path = $photo->storeAs('pimpinan', $safeFilename, 'public');

        // 3. Database ACID transaction
        DB::beginTransaction();
        try {
            Pimpinan::create([
                'nama' => trim($validated['nama']),
                'jabatan' => trim($validated['jabatan']),
                'foto' => '/storage/' . $path,
                'urutan' => $validated['urutan'] ?? ((Pimpinan::max('urutan') ?? 0) + 1),
                'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : true,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            // Compensating cleanup on DB failure
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            Log::error('Gagal menyimpan pimpinan ke basis data: ' . $e->getMessage());
            return back()->withErrors(['foto' => 'Terjadi kesalahan sistem saat menyimpan data pimpinan.'])->withInput();
        }

        session()->flash('success', 'Data pimpinan berhasil ditambahkan.');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data pimpinan berhasil ditambahkan.',
                'redirect' => url('/pimpinan')
            ]);
        }

        return redirect('/pimpinan');
    }

    /**
     * Display the specified Pimpinan (Read-Only).
     */
    public function show($id)
    {
        $this->authorizeSuperAdmin();

        $pimpinan = Pimpinan::findOrFail($id);

        return view('admin.pimpinan.show', compact('pimpinan'));
    }

    /**
     * Show the form for editing the specified Pimpinan.
     */
    public function edit($id)
    {
        $this->authorizeSuperAdmin();

        $pimpinan = Pimpinan::findOrFail($id);

        return view('admin.pimpinan.editpimpinan', compact('pimpinan'));
    }

    /**
     * Update the specified Pimpinan in storage.
     */
    public function update(Request $request, $id)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'foto' => 'nullable|file',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|in:0,1',
        ], [
            'nama.required' => 'Masukkan nama pimpinan',
            'jabatan.required' => 'Masukkan jabatan pimpinan',
            'foto.file' => 'File harus berupa berkas yang valid',
        ]);

        $dataToUpdate = [
            'nama' => trim($validated['nama']),
            'jabatan' => trim($validated['jabatan']),
            'urutan' => $validated['urutan'] ?? null,
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : false,
        ];

        $newPath = null;
        $oldPhotoToDelete = null;

        // 1. Stage new photo if provided
        if ($request->hasFile('foto')) {
            $photo = $request->file('foto');
            $photoInfo = $this->uploadValidator->validatePimpinanPhoto($photo);
            $safeFilename = $photoInfo['safe_filename'];

            $newPath = $photo->storeAs('pimpinan', $safeFilename, 'public');
            $dataToUpdate['foto'] = '/storage/' . $newPath;
        }

        // 2. Database ACID critical section with lockForUpdate()
        DB::beginTransaction();
        try {
            $pimpinan = Pimpinan::where('id', $id)->lockForUpdate()->firstOrFail();

            if ($dataToUpdate['urutan'] === null) {
                $dataToUpdate['urutan'] = $pimpinan->urutan;
            }

            if ($newPath !== null) {
                $oldPhotoToDelete = str_replace(['/public/storage/', '/storage/'], '', $pimpinan->foto);
            }

            $pimpinan->update($dataToUpdate);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            // Compensating cleanup: Delete newly staged file on failure, old photo preserved
            if ($newPath && Storage::disk('public')->exists($newPath)) {
                Storage::disk('public')->delete($newPath);
            }
            Log::error('Gagal memperbarui pimpinan di basis data: ' . $e->getMessage());
            return back()->withErrors(['foto' => 'Terjadi kesalahan sistem saat memperbarui pimpinan.'])->withInput();
        }

        // 3. Post-commit cleanup: only delete old photo after commit succeeds
        if ($oldPhotoToDelete && !empty($oldPhotoToDelete) && Storage::disk('public')->exists($oldPhotoToDelete)) {
            try {
                Storage::disk('public')->delete($oldPhotoToDelete);
            } catch (\Throwable $err) {
                Log::warning("Gagal membersihkan foto lama pimpinan [{$oldPhotoToDelete}]: " . $err->getMessage());
            }
        }

        session()->flash('success', 'Data pimpinan berhasil diperbarui.');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data pimpinan berhasil diperbarui.',
                'redirect' => url('/pimpinan')
            ]);
        }

        return redirect('/pimpinan');
    }

    /**
     * Remove the specified Pimpinan from storage.
     */
    public function destroy($id)
    {
        $this->authorizeSuperAdmin();

        $photoToDelete = null;

        DB::beginTransaction();
        try {
            $pimpinan = Pimpinan::where('id', $id)->lockForUpdate()->first();

            if (!$pimpinan) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Pimpinan tidak ditemukan'], 404);
            }

            $photoToDelete = str_replace(['/public/storage/', '/storage/'], '', $pimpinan->foto);

            // Delete database record first
            $pimpinan->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal menghapus data pimpinan: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus data pimpinan dari basis data'], 500);
        }

        // Post-commit cleanup: delete file only after DB commit
        if (!empty($photoToDelete) && Storage::disk('public')->exists($photoToDelete)) {
            try {
                Storage::disk('public')->delete($photoToDelete);
            } catch (\Throwable $err) {
                Log::warning("Gagal membersihkan foto pimpinan saat destroy [{$photoToDelete}]: " . $err->getMessage());
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Toggle the active status of a Pimpinan.
     */
    public function toggleStatus($id)
    {
        $this->authorizeSuperAdmin();

        DB::beginTransaction();
        try {
            $pimpinan = Pimpinan::where('id', $id)->lockForUpdate()->firstOrFail();
            $pimpinan->is_active = !$pimpinan->is_active;
            $pimpinan->save();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'is_active' => $pimpinan->is_active,
                'message' => 'Status pimpinan berhasil diubah.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal mengubah status pimpinan'], 500);
        }
    }
}
