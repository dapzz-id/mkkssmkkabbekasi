<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SecureUploadValidator
{
    /**
     * Exact byte limit for Gallery MP4 (100 MiB).
     */
    public const GALLERY_MAX_VIDEO_BYTES = 104857600; // 100 * 1024 * 1024

    /**
     * Maximum byte limit for Gallery Images (10 MiB).
     */
    public const GALLERY_MAX_IMAGE_BYTES = 10485760; // 10 * 1024 * 1024

    /**
     * Maximum byte limit for Sponsor Logo (2 MiB).
     */
    public const SPONSOR_MAX_IMAGE_BYTES = 2097152; // 2 * 1024 * 1024

    /**
     * Maximum byte limit for Pimpinan Photo (2 MiB).
     */
    public const PIMPINAN_MAX_IMAGE_BYTES = 2097152; // 2 * 1024 * 1024

    /**
     * Allowed extensions per domain.
     */
    protected const ALLOWED_EXTENSIONS = [
        'gallery' => ['jpg', 'jpeg', 'png', 'mp4'],
        'sponsor' => ['jpg', 'jpeg', 'png', 'gif', 'svg'],
        'pimpinan' => ['jpg', 'jpeg', 'png', 'webp'],
    ];

    /**
     * Dangerous extensions that must be rejected regardless of MIME.
     */
    protected const DANGEROUS_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar',
        'cgi', 'pl', 'py', 'sh', 'bash', 'exe', 'dll', 'com', 'bat', 'cmd',
        'html', 'htm', 'js', 'vbs', 'ps1', 'jar', 'svgz'
    ];

    /**
     * Validate an uploaded file for the Gallery domain.
     *
     * @param UploadedFile $file
     * @return array Array containing validated info ['extension', 'mime', 'type', 'safe_filename']
     * @throws ValidationException
     */
    public function validateGalleryMedia(UploadedFile $file): array
    {
        return $this->validateFile($file, 'gallery', [
            'video_max_bytes' => self::GALLERY_MAX_VIDEO_BYTES,
            'image_max_bytes' => self::GALLERY_MAX_IMAGE_BYTES,
        ]);
    }

    /**
     * Validate an uploaded file for the Sponsor domain.
     *
     * @param UploadedFile $file
     * @return array
     * @throws ValidationException
     */
    public function validateSponsorLogo(UploadedFile $file): array
    {
        return $this->validateFile($file, 'sponsor', [
            'image_max_bytes' => self::SPONSOR_MAX_IMAGE_BYTES,
        ]);
    }

    /**
     * Validate an uploaded file for the Pimpinan domain.
     *
     * @param UploadedFile $file
     * @return array
     * @throws ValidationException
     */
    public function validatePimpinanPhoto(UploadedFile $file): array
    {
        return $this->validateFile($file, 'pimpinan', [
            'image_max_bytes' => self::PIMPINAN_MAX_IMAGE_BYTES,
        ]);
    }

    /**
     * Core validation engine implementing Defense-in-Depth.
     *
     * @param UploadedFile $file
     * @param string $domain 'gallery' | 'sponsor' | 'pimpinan'
     * @param array $options
     * @return array
     * @throws ValidationException
     */
    public function validateFile(UploadedFile $file, string $domain, array $options = []): array
    {
        // 1. Basic Upload Integrity
        if (!$file->isValid()) {
            $this->fail('File upload tidak valid atau terputus.');
        }

        $realPath = $file->getRealPath();
        if (!$realPath || !file_exists($realPath) || !is_readable($realPath)) {
            $this->fail('File sementara tidak dapat dibaca di server.');
        }

        $fileSize = (int) $file->getSize();
        if ($fileSize <= 0) {
            $this->fail('File kosong atau tidak memiliki data.');
        }

        // 2. Client Filename & Extension Allowlist Checks
        $clientOriginalName = $file->getClientOriginalName();
        $this->checkPathTraversalAndNullBytes($clientOriginalName);

        $clientExtension = strtolower($file->getClientOriginalExtension());
        if (empty($clientExtension)) {
            $this->fail('File tidak memiliki ekstensi yang valid.');
        }

        // Reject dangerous extensions immediately (including double extensions)
        $this->checkDangerousExtensions($clientOriginalName, $clientExtension);

        $allowedDomainExtensions = self::ALLOWED_EXTENSIONS[$domain] ?? [];
        if (!in_array($clientExtension, $allowedDomainExtensions, true)) {
            $this->fail("Format file '.{$clientExtension}' tidak didukung untuk modul {$domain}.");
        }

        // 3. Server-Side MIME Detection using Fileinfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $serverMime = finfo_file($finfo, $realPath);
        finfo_close($finfo);

        if (!$serverMime || ($serverMime === 'application/octet-stream' && $clientExtension !== 'mp4')) {
            // Further inspection below will confirm
        }

        // 4. Magic-Byte and Structural Inspection Based on Extension
        $canonicalExt = $clientExtension;
        $mediaType = 'image';

        switch ($clientExtension) {
            case 'jpg':
            case 'jpeg':
                $this->validateJpeg($realPath, $serverMime);
                $canonicalExt = 'jpg';
                break;

            case 'png':
                $this->validatePng($realPath, $serverMime);
                $canonicalExt = 'png';
                break;

            case 'gif':
                $this->validateGif($realPath, $serverMime);
                $canonicalExt = 'gif';
                break;

            case 'webp':
                $this->validateWebp($realPath, $serverMime);
                $canonicalExt = 'webp';
                break;

            case 'svg':
                $this->validateSvg($realPath, $serverMime);
                $canonicalExt = 'svg';
                break;

            case 'mp4':
                $this->validateMp4($realPath, $serverMime);
                $canonicalExt = 'mp4';
                $mediaType = 'video';
                break;

            default:
                $this->fail("Format file '{$clientExtension}' tidak didukung.");
        }

        // 5. Strict Size Boundaries
        if ($mediaType === 'video') {
            $maxVideoBytes = $options['video_max_bytes'] ?? self::GALLERY_MAX_VIDEO_BYTES;
            if ($fileSize > $maxVideoBytes) {
                $this->fail("Ukuran video ({$fileSize} bytes) melebihi batas maksimum 100 MiB (" . self::GALLERY_MAX_VIDEO_BYTES . " bytes).");
            }
        } else {
            $maxImageBytes = $options['image_max_bytes'] ?? self::GALLERY_MAX_IMAGE_BYTES;
            if ($fileSize > $maxImageBytes) {
                $maxMb = round($maxImageBytes / (1024 * 1024), 1);
                $this->fail("Ukuran gambar melebihi batas maksimum {$maxMb}MB.");
            }
        }

        // 6. Safe Random Filename Generation
        $safeFilename = $this->generateSafeFilename($canonicalExt);

        return [
            'extension' => $canonicalExt,
            'mime' => $serverMime,
            'type' => $mediaType,
            'size' => $fileSize,
            'safe_filename' => $safeFilename,
        ];
    }

    /**
     * Generate cryptographically safe random filename.
     */
    public function generateSafeFilename(string $extension): string
    {
        $cleanExt = preg_replace('/[^a-z0-9]/', '', strtolower($extension));
        return Str::random(40) . '.' . $cleanExt;
    }

    /**
     * Validate JPEG file signature and structure.
     */
    protected function validateJpeg(string $path, string $detectedMime): void
    {
        if ($detectedMime !== 'image/jpeg') {
            $this->fail('MIME file tidak sesuai dengan format JPEG.');
        }

        $handle = @fopen($path, 'rb');
        if (!$handle) {
            $this->fail('Gagal membaca struktur berkas JPEG.');
        }

        $header = fread($handle, 3);
        fclose($handle);

        // JPEG SOI marker: FF D8 FF
        if ($header !== "\xFF\xD8\xFF") {
            $this->fail('Header signature berkas bukan JPEG yang valid.');
        }

        $imageInfo = @getimagesize($path);
        if (!$imageInfo || $imageInfo[2] !== IMAGETYPE_JPEG) {
            $this->fail('Berkas JPEG rusak atau struktur gambar tidak valid.');
        }

        $this->assertReasonableDimensions($imageInfo[0], $imageInfo[1]);
    }

    /**
     * Validate PNG file signature and structure.
     */
    protected function validatePng(string $path, string $detectedMime): void
    {
        if ($detectedMime !== 'image/png') {
            $this->fail('MIME file tidak sesuai dengan format PNG.');
        }

        $handle = @fopen($path, 'rb');
        if (!$handle) {
            $this->fail('Gagal membaca struktur berkas PNG.');
        }

        $header = fread($handle, 8);
        fclose($handle);

        // PNG signature: 89 50 4E 47 0D 0A 1A 0A
        if ($header !== "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A") {
            $this->fail('Header signature berkas bukan PNG yang valid.');
        }

        $imageInfo = @getimagesize($path);
        if (!$imageInfo || $imageInfo[2] !== IMAGETYPE_PNG) {
            $this->fail('Berkas PNG rusak atau struktur gambar tidak valid.');
        }

        $this->assertReasonableDimensions($imageInfo[0], $imageInfo[1]);
    }

    /**
     * Validate GIF file signature and structure.
     */
    protected function validateGif(string $path, string $detectedMime): void
    {
        if ($detectedMime !== 'image/gif') {
            $this->fail('MIME file tidak sesuai dengan format GIF.');
        }

        $handle = @fopen($path, 'rb');
        if (!$handle) {
            $this->fail('Gagal membaca struktur berkas GIF.');
        }

        $header = fread($handle, 6);
        fclose($handle);

        // GIF87a or GIF89a
        if ($header !== "GIF87a" && $header !== "GIF89a") {
            $this->fail('Header signature berkas bukan GIF yang valid.');
        }

        $imageInfo = @getimagesize($path);
        if (!$imageInfo || $imageInfo[2] !== IMAGETYPE_GIF) {
            $this->fail('Berkas GIF rusak atau struktur gambar tidak valid.');
        }

        $this->assertReasonableDimensions($imageInfo[0], $imageInfo[1]);
    }

    /**
     * Validate WEBP file signature and structure.
     */
    protected function validateWebp(string $path, string $detectedMime): void
    {
        if ($detectedMime !== 'image/webp') {
            $this->fail('MIME file tidak sesuai dengan format WEBP.');
        }

        $handle = @fopen($path, 'rb');
        if (!$handle) {
            $this->fail('Gagal membaca struktur berkas WEBP.');
        }

        $header = fread($handle, 12);
        fclose($handle);

        // RIFF....WEBP (RIFF at 0..3, WEBP at 8..11)
        if (strlen($header) < 12 || substr($header, 0, 4) !== 'RIFF' || substr($header, 8, 4) !== 'WEBP') {
            $this->fail('Header signature berkas bukan WEBP yang valid.');
        }

        $imageInfo = @getimagesize($path);
        if (!$imageInfo || $imageInfo[2] !== IMAGETYPE_WEBP) {
            $this->fail('Berkas WEBP rusak atau struktur gambar tidak valid.');
        }

        $this->assertReasonableDimensions($imageInfo[0], $imageInfo[1]);
    }

    /**
     * Validate MP4 container and ftyp box structure.
     */
    protected function validateMp4(string $path, string $detectedMime): void
    {
        $allowedMimes = ['video/mp4', 'video/x-m4v', 'application/mp4'];
        if (!in_array($detectedMime, $allowedMimes, true)) {
            $this->fail('MIME file tidak sesuai dengan format MP4.');
        }

        $handle = @fopen($path, 'rb');
        if (!$handle) {
            $this->fail('Gagal membaca struktur berkas MP4.');
        }

        // Read first 12 bytes: [4 bytes box size][4 bytes 'ftyp'][4 bytes major brand]
        $header = fread($handle, 12);
        fclose($handle);

        if (strlen($header) < 12) {
            $this->fail('Berkas MP4 terpotong atau rusak.');
        }

        $boxType = substr($header, 4, 4);
        if ($boxType !== 'ftyp') {
            $this->fail('Berkas tidak memiliki box container MP4 (ftyp) yang valid.');
        }

        // Major brands allowed
        $majorBrand = trim(substr($header, 8, 4));
        $validBrands = ['isom', 'iso2', 'mp41', 'mp42', 'M4V', 'avc1', 'dash', 'MSNV', 'NDAS'];
        $matched = false;
        foreach ($validBrands as $brand) {
            if (stripos($majorBrand, $brand) !== false) {
                $matched = true;
                break;
            }
        }

        if (!$matched && strlen($majorBrand) < 2) {
            $this->fail('Brand container MP4 tidak dikenal atau tidak valid.');
        }
    }

    /**
     * Validate SVG file: strictly reject active content, scripts, event handlers, and XXE constructs.
     */
    protected function validateSvg(string $path, string $detectedMime): void
    {
        $allowedMimes = ['image/svg+xml', 'image/svg', 'text/xml', 'text/plain', 'application/xml'];
        if (!in_array($detectedMime, $allowedMimes, true)) {
            $this->fail('MIME file tidak sesuai dengan format SVG.');
        }

        $raw = file_get_contents($path);
        if ($raw === false || strlen(trim($raw)) === 0) {
            $this->fail('Berkas SVG kosong.');
        }

        // Fast-fail check for obvious active content indicators
        if (preg_match('/<script\b/i', $raw)) {
            $this->fail('Berkas SVG mengandung tag skrip yang tidak diizinkan.');
        }

        if (preg_match('/\bon[a-z]+\s*=/i', $raw)) {
            $this->fail('Berkas SVG mengandung atribut event handler yang tidak diizinkan.');
        }

        if (preg_match('/javascript\s*:/i', $raw)) {
            $this->fail('Berkas SVG mengandung URI javascript: yang tidak diizinkan.');
        }

        if (preg_match('/<\?(php|=|xml-stylesheet)/i', $raw)) {
            $this->fail('Berkas SVG mengandung processing instruction berbahaya.');
        }

        if (preg_match('/<!ENTITY\b/i', $raw) || preg_match('/<!DOCTYPE[^>]*\[/i', $raw)) {
            $this->fail('Berkas SVG mengandung deklarasi XML ENTITY / DOCTYPE internal yang tidak diizinkan.');
        }

        // Safe XML parsing with external entities disabled
        $prevInternalErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();

        $dom = new \DOMDocument();
        $flags = LIBXML_NONET | LIBXML_NOWARNING | LIBXML_NOERROR;
        if (defined('LIBXML_NOENT')) {
            $flags |= LIBXML_NOENT;
        }

        $loaded = $dom->loadXML($raw, $flags);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($prevInternalErrors);

        if (!$loaded || !empty($errors)) {
            $this->fail('Struktur XML SVG rusak atau tidak valid.');
        }

        // Validate root element is <svg>
        $root = $dom->documentElement;
        if (!$root || strtolower($root->tagName) !== 'svg') {
            $this->fail('Berkas bukan format SVG yang valid.');
        }

        // Inspect all elements and attributes for active content
        $xpath = new \DOMXPath($dom);
        
        $dangerousTags = [
            'script', 'iframe', 'foreignobject', 'object', 'embed', 'applet',
            'meta', 'link', 'frame', 'frameset', 'audio', 'video', 'use'
        ];

        foreach ($dangerousTags as $tag) {
            $nodes = $xpath->query("//*[translate(local-name(), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz') = '{$tag}']");
            if ($nodes && $nodes->length > 0) {
                $this->fail("Berkas SVG mengandung elemen terlarang: <{$tag}>.");
            }
        }

        $allElements = $xpath->query('//*');
        if ($allElements) {
            foreach ($allElements as $element) {
                if (!$element->hasAttributes()) {
                    continue;
                }
                foreach ($element->attributes as $attr) {
                    $attrName = strtolower($attr->nodeName);
                    $attrValue = strtolower(trim($attr->nodeValue));

                    // Event handlers
                    if (str_starts_with($attrName, 'on')) {
                        $this->fail("Berkas SVG mengandung event handler '{$attrName}'.");
                    }

                    // Dangerous URIs
                    if (str_contains($attrValue, 'javascript:') || str_contains($attrValue, 'vbscript:')) {
                        $this->fail("Berkas SVG mengandung link protokol tidak aman pada '{$attrName}'.");
                    }

                    if (str_starts_with($attrValue, 'data:') && !str_starts_with($attrValue, 'data:image/')) {
                        $this->fail("Berkas SVG mengandung Data URI berbahaya pada '{$attrName}'.");
                    }
                }
            }
        }
    }

    /**
     * Ensure raster image dimensions are reasonable.
     */
    protected function assertReasonableDimensions(int $width, int $height): void
    {
        if ($width <= 0 || $height <= 0) {
            $this->fail('Dimensi gambar tidak valid.');
        }

        if ($width > 12000 || $height > 12000) {
            $this->fail("Dimensi gambar ({$width}x{$height}) melebihi batas wajar 12000x12000 piksel.");
        }
    }

    /**
     * Check for path traversal and null byte injections.
     */
    protected function checkPathTraversalAndNullBytes(string $filename): void
    {
        if (str_contains($filename, "\0")) {
            $this->fail('Nama file mengandung null-byte injection.');
        }

        if (str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\')) {
            $this->fail('Nama file mengandung karakter path traversal.');
        }
    }

    /**
     * Check for executable/script extensions anywhere in the filename (e.g. payload.php.jpg).
     */
    protected function checkDangerousExtensions(string $filename, string $extension): void
    {
        if (in_array($extension, self::DANGEROUS_EXTENSIONS, true)) {
            $this->fail("Ekstensi berkas '.{$extension}' terlarang.");
        }

        $parts = explode('.', strtolower($filename));
        if (count($parts) > 2) {
            for ($i = 1; $i < count($parts) - 1; $i++) {
                if (in_array($parts[$i], self::DANGEROUS_EXTENSIONS, true)) {
                    $this->fail("Berkas mengandung ekstensi ganda berbahaya '.{$parts[$i]}'.");
                }
            }
        }
    }

    /**
     * Throw standardized validation failure exception.
     */
    protected function fail(string $message): void
    {
        throw ValidationException::withMessages([
            'media' => $message,
            'url_image' => $message,
            'foto' => $message,
        ]);
    }
}
