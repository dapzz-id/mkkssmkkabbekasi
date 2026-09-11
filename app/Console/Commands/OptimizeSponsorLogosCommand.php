<?php

namespace App\Console\Commands;

use App\Models\Sponsor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OptimizeSponsorLogosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sponsor:optimize {--max-height=140 : Maximum height in pixels for sponsor logos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downscale and compress giant Base64 sponsor logos in the database to optimize page load speed';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $maxH = (int) $this->option('max-height');
        $sponsors = Sponsor::all();

        $this->components->info("Starting sponsor logo optimization (Max height: {$maxH}px)...");

        $totalBefore = 0;
        $totalAfter = 0;
        $optimizedCount = 0;

        foreach ($sponsors as $s) {
            $raw = (string) $s->url_image;
            $origLen = strlen($raw);
            $totalBefore += $origLen;

            if (!str_starts_with($raw, 'data:image') || str_contains($raw, 'image/svg')) {
                $totalAfter += $origLen;
                $this->components->twoColumnDetail($s->nama, '<fg=gray>Skipped (SVG/URL)</>');
                continue;
            }

            [$meta, $b64] = explode(',', $raw, 2);
            $binary = @base64_decode($b64);
            if (!$binary) {
                $totalAfter += $origLen;
                continue;
            }

            $img = @imagecreatefromstring($binary);
            if (!$img) {
                $totalAfter += $origLen;
                continue;
            }

            $origW = imagesx($img);
            $origH = imagesy($img);

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
                imagepng($resized, null, 8);
                $newBinary = ob_get_clean();
                imagedestroy($resized);

                $newB64 = 'data:image/png;base64,' . base64_encode($newBinary);
                $newLen = strlen($newB64);
                $totalAfter += $newLen;

                DB::table('sponsor')->where('uuid', $s->uuid)->update(['url_image' => $newB64]);
                $optimizedCount++;

                $savings = round((1 - ($newLen / $origLen)) * 100, 1);
                $this->components->twoColumnDetail($s->nama, "<fg=green>Optimized {$origW}x{$origH} ({$origLen}B) -> {$newW}x{$newH} ({$newLen}B) [-{$savings}%]</>");
            } else {
                $totalAfter += $origLen;
                $this->components->twoColumnDetail($s->nama, "<fg=gray>Already optimal ({$origW}x{$origH})</>");
            }

            imagedestroy($img);
        }

        $mbBefore = round($totalBefore / (1024 * 1024), 2);
        $mbAfter = round($totalAfter / (1024 * 1024), 2);
        $savedMb = round(($totalBefore - $totalAfter) / (1024 * 1024), 2);

        $this->components->info("Summary: {$optimizedCount} logos optimized. Total payload reduced from {$mbBefore} MB to {$mbAfter} MB (Saved {$savedMb} MB).");

        return Command::SUCCESS;
    }
}
