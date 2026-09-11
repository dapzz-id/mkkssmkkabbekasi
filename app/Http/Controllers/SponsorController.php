<?php

namespace App\Http\Controllers;

use App\Models\Sponsor;
use App\Services\SecureUploadValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SponsorController extends Controller
{
    protected SecureUploadValidator $uploadValidator;

    public function __construct(SecureUploadValidator $uploadValidator)
    {
        $this->uploadValidator = $uploadValidator;
    }

    public function index()
    {
        $data = Sponsor::paginate(6);
        return view('admin.sponsor.datasponsor', [
            "data" => $data
        ]);
    }

    public function create()
    {
        return view('admin.sponsor.tambahsponsor');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'url_image' => 'required|file',
        ], [
            'nama.required' => 'Masukkan nama sponsor',
            'url_image.required' => 'Unggah gambar sponsor',
            'url_image.file' => 'File yang diunggah harus berupa berkas yang valid',
        ]);

        $image = $request->file('url_image');
        $imageInfo = $this->uploadValidator->validateSponsorLogo($image);

        // Build optimized data URI with auto-downscaling to prevent bloated HTML payload
        $dataUri = $this->optimizeLogoToBase64($image, $imageInfo['mime']);

        DB::beginTransaction();
        try {
            Sponsor::create([
                'nama' => trim($validated['nama']),
                'url_image' => $dataUri,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan sponsor: ' . $e->getMessage());
            return back()->withErrors(['url_image' => 'Terjadi kesalahan basis data saat menyimpan sponsor.'])->withInput();
        }

        return redirect('/sponsor')->with('success', 'Sponsor berhasil ditambahkan.');
    }

    public function show($id)
    {
        $sponsor = Sponsor::findByIdentifierOrFail($id);
        return view('admin.sponsor.show', compact('sponsor'));
    }

    public function edit($id)
    {
        $sponsor = Sponsor::findByIdentifierOrFail($id);
        return view('admin.sponsor.editsponsor', [
            'sponsor' => $sponsor
        ]);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'url_image' => 'nullable|file',
        ], [
            'nama.required' => 'Masukkan nama sponsor',
            'url_image.file' => 'File yang diunggah harus berupa berkas yang valid',
        ]);

        $newImageUri = null;

        // If replacement file is provided, validate via SecureUploadValidator first
        if ($request->hasFile('url_image')) {
            $image = $request->file('url_image');
            $imageInfo = $this->uploadValidator->validateSponsorLogo($image);

            $newImageUri = $this->optimizeLogoToBase64($image, $imageInfo['mime']);
        }

        // Database ACID critical section
        DB::beginTransaction();
        try {
            $sponsor = Sponsor::whereIdentifier($id)->lockForUpdate()->firstOrFail();
            $sponsor->nama = trim($validatedData['nama']);

            // Only update logo if a replacement was validated; otherwise preserve old logo
            if ($newImageUri !== null) {
                $sponsor->url_image = $newImageUri;
            }

            $sponsor->save();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui sponsor: ' . $e->getMessage());
            return back()->withErrors(['url_image' => 'Terjadi kesalahan basis data saat memperbarui sponsor.'])->withInput();
        }

        return redirect('/sponsor')->with('success', 'Sponsor berhasil diperbarui.');
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $sponsor = Sponsor::whereIdentifier($id)->lockForUpdate()->first();

            if ($sponsor) {
                $sponsor->delete();
                DB::commit();
                return response()->json(['status' => 'success']);
            } else {
                $sponsorNotFound = true;
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Sponsor tidak ditemukan'], 404);
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal menghapus sponsor: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus data sponsor'], 500);
        }
    }

    /**
     * Downscale and optimize sponsor logo to max height 140px preserving transparency.
     */
    protected function optimizeLogoToBase64(\Illuminate\Http\UploadedFile $file, string $mime): string
    {
        $realPath = $file->getRealPath();

        // If SVG, keep raw SVG as data URI (SVGs are vector and already small)
        if (str_contains($mime, 'svg')) {
            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($realPath));
        }

        $binary = file_get_contents($realPath);
        $img = @imagecreatefromstring($binary);
        if (!$img) {
            return 'data:' . $mime . ';base64,' . base64_encode($binary);
        }

        $origW = imagesx($img);
        $origH = imagesy($img);
        $maxH = 140;

        if ($origH > $maxH) {
            $ratio = $maxH / $origH;
            $newH = $maxH;
            $newW = (int) round($origW * $ratio);

            $resized = imagecreatetruecolor($newW, $newH);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
            imagefilledrectangle($resized, 0, 0, $newW, $newH, $transparent);

            imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

            ob_start();
            if (str_contains($mime, 'png')) {
                imagepng($resized, null, 8);
            } elseif (str_contains($mime, 'jpeg') || str_contains($mime, 'jpg')) {
                imagejpeg($resized, null, 85);
            } elseif (str_contains($mime, 'webp')) {
                imagewebp($resized, null, 85);
            } else {
                imagepng($resized);
            }
            $optimizedBinary = ob_get_clean();
            imagedestroy($resized);
            imagedestroy($img);

            return 'data:' . $mime . ';base64,' . base64_encode($optimizedBinary);
        }

        imagedestroy($img);
        return 'data:' . $mime . ';base64,' . base64_encode($binary);
    }
}
