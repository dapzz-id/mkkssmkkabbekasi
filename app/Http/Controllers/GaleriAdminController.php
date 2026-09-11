<?php

namespace App\Http\Controllers;

use App\Models\Konten;
use App\Models\Divisi;
use App\Services\SecureUploadValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GaleriAdminController extends Controller
{
    protected SecureUploadValidator $uploadValidator;

    public function __construct(SecureUploadValidator $uploadValidator)
    {
        $this->uploadValidator = $uploadValidator;
    }

    public function index()
    {
        return redirect('/gallery');
    }

    public function dashboard()
    {
        $data = Konten::all();
        if (Auth::user()->role == 'admin') {
            $data = Konten::where('divisi_uuid', Auth::user()->divisi_uuid)->get();
        }
        return view('admin.dashboard', compact('data'));
    }

    public function create()
    {
        $divisi = Divisi::all();
        return view('admin.galeri.tambahgaleri', compact('divisi'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'divisi_uuid' => 'nullable|string',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required',
            'media' => 'required|array|min:1',
            'media.*' => 'file',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:1000',
            'slug' => ['nullable', 'string', 'max:191', 'regex:/^[a-zA-Z0-9\-_]+$/'],
        ], [
            'judul.required' => 'Masukkan judul',
            'deskripsi.required' => 'Masukkan deskripsi',
            'media.required' => 'Unggah setidaknya satu foto atau video',
            'media.min' => 'Unggah setidaknya satu foto atau video',
            'media.*.file' => 'File yang diunggah harus berupa file yang valid',
            'seo_title.max' => 'SEO Title maksimal 255 karakter',
            'seo_description.max' => 'Meta Description maksimal 1000 karakter',
            'slug.regex' => 'Slug hanya boleh berisi huruf, angka, tanda hubung (-), dan garis bawah (_).',
            'slug.max' => 'Slug maksimal 191 karakter',
        ]);

        if (Auth::user()->role === 'superadmin') {
            $divisiInput = $request->input('divisi_uuid');
            $divisi = Divisi::where('uuid', $divisiInput)
                ->orWhere('nama_divisi', $divisiInput)
                ->first();
            if (!$divisi) {
                throw ValidationException::withMessages(['divisi_uuid' => 'Pilih divisi yang valid.']);
            }
            $validatedData['divisi_uuid'] = $divisi->uuid;
        } else {
            $validatedData['divisi_uuid'] = Auth::user()->divisi_uuid;
        }

        // SEO Field Processing (Auto-fallback & Deterministic Collision Resolution)
        $rawSlug = !empty($validatedData['slug']) ? $validatedData['slug'] : $validatedData['judul'];
        $validatedData['slug'] = $this->generateUniqueSlug($rawSlug);

        $validatedData['seo_title'] = !empty($validatedData['seo_title']) 
            ? trim($validatedData['seo_title']) 
            : trim($validatedData['judul']);

        $validatedData['seo_description'] = !empty($validatedData['seo_description']) 
            ? trim($validatedData['seo_description']) 
            : Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($validatedData['deskripsi']))), 160);

        // 1. Stage and validate all media files using SecureUploadValidator (Defense-in-Depth)
        $urls = [];
        $savedPaths = [];

        try {
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $mediaInfo = $this->uploadValidator->validateGalleryMedia($file);
                    $safeFilename = $mediaInfo['safe_filename'];

                    // Store using cryptographically safe random filename in staging
                    $path = $file->storeAs('media', $safeFilename, 'public');
                    $savedPaths[] = $path;
                    $urls[] = '/storage/' . $path;
                }
            }

            if (empty($urls)) {
                throw ValidationException::withMessages([
                    'media' => 'Unggah setidaknya satu media yang valid.'
                ]);
            }

            $validatedData['url_media'] = json_encode($urls);
            $validatedData['user_uuid'] = Auth::user()->uuid;
            $validatedData['tanggal_upload'] = now();

            // 2. Database ACID Transaction
            DB::beginTransaction();
            Konten::create($validatedData);
            DB::commit();

        } catch (\Throwable $e) {
            // Rollback database if inside transaction
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            // Compensating cleanup: Delete newly staged files on failure
            foreach ($savedPaths as $p) {
                if (Storage::disk('public')->exists($p)) {
                    Storage::disk('public')->delete($p);
                }
            }

            if ($e instanceof ValidationException) {
                throw $e;
            }

            Log::error('Gagal menyimpan galeri: ' . $e->getMessage());
            return back()->withErrors(['media' => 'Terjadi kesalahan sistem saat menyimpan data galeri.'])->withInput();
        }

        return redirect('/gallery')->with('success', 'Data galeri berhasil ditambahkan.');
    }

    public function show($identifier)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        // 1. Legacy ID compatibility: If numeric ID is requested, redirect 301 to slug URL
        if (ctype_digit((string) $identifier)) {
            $galeri = Konten::with(['divisi', 'user'])->whereIdentifier($identifier)->firstOrFail();
            if (!empty($galeri->slug)) {
                return redirect('/gallery/' . $galeri->slug, 301);
            }
        } elseif (preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', (string) $identifier)) {
            // UUID compatibility: If canonical UUID is requested, redirect 301 to slug URL
            $galeri = Konten::with(['divisi', 'user'])->where('uuid', $identifier)->firstOrFail();
            if (!empty($galeri->slug)) {
                return redirect('/gallery/' . $galeri->slug, 301);
            }
        } else {
            // 2. Canonical Slug Lookup
            $galeri = Konten::with(['divisi', 'user'])->where('slug', $identifier)->firstOrFail();
        }

        $isMismatch = ($galeri->divisi_uuid !== Auth::user()->divisi_uuid);

        if (Auth::user()->role === 'admin' && $isMismatch) {
            abort(403, 'Akses ditolak. Anda hanya dapat melihat galeri divisi Anda.');
        }

        return view('admin.galeri.show', compact('galeri'));
    }

    public function edit($id)
    {
        $galeri = Konten::findByIdentifierOrFail($id);

        $isMismatch = ($galeri->divisi_uuid !== Auth::user()->divisi_uuid);

        if (Auth::user()->role === 'admin' && $isMismatch) {
            abort(403, 'Akses ditolak. Anda hanya dapat mengelola galeri divisi Anda.');
        }

        $divisi = Divisi::all();
        return view('admin.galeri.editgaleri', compact('galeri', 'divisi'));
    }

    public function update(Request $request, $id)
    {
        $galeri = Konten::findByIdentifierOrFail($id);

        $isMismatch = ($galeri->divisi_uuid !== Auth::user()->divisi_uuid);

        if (Auth::user()->role === 'admin' && $isMismatch) {
            abort(403, 'Akses ditolak. Anda hanya dapat mengelola galeri divisi Anda.');
        }

        $validatedData = $request->validate([
            'divisi_uuid' => 'nullable|string',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required',
            'media' => 'sometimes|array',
            'media.*' => 'file',
            'keep_media' => 'sometimes|array',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:1000',
            'slug' => ['nullable', 'string', 'max:191', 'regex:/^[a-zA-Z0-9\-_]+$/'],
        ], [
            'judul.required' => 'Masukkan judul',
            'deskripsi.required' => 'Masukkan deskripsi',
            'media.*.file' => 'File yang diunggah harus berupa file yang valid',
            'seo_title.max' => 'SEO Title maksimal 255 karakter',
            'seo_description.max' => 'Meta Description maksimal 1000 karakter',
            'slug.regex' => 'Slug hanya boleh berisi huruf, angka, tanda hubung (-), dan garis bawah (_)',
        ]);

        if (Auth::user()->role == 'admin') {
            $validatedData['divisi_uuid'] = Auth::user()->divisi_uuid;
        } else {
            $divisiInput = $request->input('divisi_uuid');
            if ($divisiInput) {
                $divisi = Divisi::where('uuid', $divisiInput)
                    ->orWhere('nama_divisi', $divisiInput)
                    ->first();
                if ($divisi) {
                    $validatedData['divisi_uuid'] = $divisi->uuid;
                }
            }
        }

        // SEO Field Processing for Edit
        if ($request->has('slug')) {
            $rawSlug = !empty($validatedData['slug']) ? $validatedData['slug'] : ($galeri->slug ?: $validatedData['judul']);
            $validatedData['slug'] = $this->generateUniqueSlug($rawSlug, $galeri->uuid);
        }

        if ($request->has('seo_title')) {
            $validatedData['seo_title'] = !empty($validatedData['seo_title']) 
                ? trim($validatedData['seo_title']) 
                : ($galeri->seo_title ?: trim($validatedData['judul']));
        }

        if ($request->has('seo_description')) {
            $validatedData['seo_description'] = !empty($validatedData['seo_description']) 
                ? trim($validatedData['seo_description']) 
                : ($galeri->seo_description ?: Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($validatedData['deskripsi']))), 160));
        }

        // 1. keep_media Ownership Validation
        // Retrieve current authoritative DB media state for THIS gallery
        $existingMedia = json_decode($galeri->url_media, true) ?? [];

        // Normalize function for consistent path comparison
        $normalizeUrl = function ($url) {
            return str_replace(['\/public\/storage\/', '/public/storage/'], '/storage/', $url);
        };

        $normalizedExisting = array_map($normalizeUrl, $existingMedia);

        $submittedKeepMedia = $request->input('keep_media', []);
        $keptUrls = [];

        if (is_array($submittedKeepMedia)) {
            foreach ($submittedKeepMedia as $subUrl) {
                $cleanSubUrl = $normalizeUrl($subUrl);
                // Strict ownership check: Only keep if URL actually belongs to this Gallery!
                if (in_array($cleanSubUrl, $normalizedExisting, true)) {
                    $keptUrls[] = $cleanSubUrl;
                }
            }
        }
        $keptUrls = array_values(array_unique($keptUrls));

        // Determine which old media were explicitly removed
        $toDeleteUrls = array_diff($normalizedExisting, $keptUrls);

        // 2. Validate and Stage New Uploads (outside DB transaction to prevent holding locks during transfer)
        $newUploadedUrls = [];
        $newStagedPaths = [];

        try {
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $mediaInfo = $this->uploadValidator->validateGalleryMedia($file);
                    $safeFilename = $mediaInfo['safe_filename'];

                    $path = $file->storeAs('media', $safeFilename, 'public');
                    $newStagedPaths[] = $path;
                    $newUploadedUrls[] = '/storage/' . $path;
                }
            }

            // Append mode: kept existing media + new validated uploads
            $finalUrls = array_values(array_merge($keptUrls, $newUploadedUrls));

            if (empty($finalUrls)) {
                throw ValidationException::withMessages([
                    'media' => 'Galeri harus memiliki setidaknya satu foto atau video.'
                ]);
            }

            $validatedData['url_media'] = json_encode($finalUrls);

            // 3. Short Database ACID Critical Section with lockForUpdate()
            DB::beginTransaction();

            $lockedGaleri = Konten::whereIdentifier($id)->lockForUpdate()->firstOrFail();

            $isMismatch = ($lockedGaleri->divisi_uuid !== Auth::user()->divisi_uuid);

            if (Auth::user()->role === 'admin' && $isMismatch) {
                abort(403, 'Akses ditolak.');
            }

            $lockedGaleri->update($validatedData);

            DB::commit();

        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            // Compensating cleanup: Delete newly staged files, keep old media intact
            foreach ($newStagedPaths as $p) {
                if (Storage::disk('public')->exists($p)) {
                    Storage::disk('public')->delete($p);
                }
            }

            if ($e instanceof ValidationException) {
                throw $e;
            }

            Log::error('Gagal memperbarui galeri: ' . $e->getMessage());
            return back()->withErrors(['media' => 'Terjadi kesalahan sistem saat memperbarui data galeri.'])->withInput();
        }

        // 4. Post-Commit Cleanup: Delete explicitly removed old media only after successful commit
        foreach ($toDeleteUrls as $oldUrl) {
            $cleanPath = str_replace(['/public/storage/', '/storage/'], '', $oldUrl);
            if (!empty($cleanPath) && Storage::disk('public')->exists($cleanPath)) {
                try {
                    Storage::disk('public')->delete($cleanPath);
                } catch (\Throwable $err) {
                    Log::warning("Gagal menghapus file media lama [{$cleanPath}]: " . $err->getMessage());
                }
            }
        }

        return redirect('/gallery')->with('success', 'Data galeri berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $oldMedia = [];

        DB::beginTransaction();
        try {
            $galeri = Konten::whereIdentifier($id)->lockForUpdate()->first();

            if (!$galeri) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
            }

            $isMismatch = ($galeri->divisi_uuid !== Auth::user()->divisi_uuid);

            if (Auth::user()->role === 'admin' && $isMismatch) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
            }

            $oldMedia = json_decode($galeri->url_media, true) ?? [];

            // Delete database record first inside transaction
            $galeri->delete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal menghapus rekaman galeri: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus data galeri dari basis data.'], 500);
        }

        // Post-Commit Physical File Deletion: files only deleted after DB commit succeeds
        foreach ($oldMedia as $oldUrl) {
            $path = str_replace(['/public/storage/', '/storage/'], '', $oldUrl);
            if (!empty($path) && Storage::disk('public')->exists($path)) {
                try {
                    Storage::disk('public')->delete($path);
                } catch (\Throwable $err) {
                    Log::warning("Gagal membersihkan media fisik galeri id {$id} [{$path}]: " . $err->getMessage());
                }
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Generate deterministic, collision-free URL-safe slug
     */
    protected function generateUniqueSlug(string $rawSlug, ?string $ignoreUuid = null): string
    {
        $base = Str::slug($rawSlug);
        if ($base === '' || ctype_digit($base)) {
            $base = 'konten-' . ($ignoreUuid ?: time());
        }

        $slug = $base;
        $counter = 2;

        while (Konten::where('slug', $slug)->when($ignoreUuid, fn($q) => $q->where('uuid', '!=', $ignoreUuid))->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}