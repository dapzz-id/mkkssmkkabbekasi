<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\Konten;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate dynamic XML sitemap including static pages and all /konten/{slug} articles
     */
    public function index(): Response
    {
        $domain = rtrim(config('app.url', 'https://mkkssmkkabbekasi.or.id'), '/');

        $staticPages = [
            ['loc' => $domain . '/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => $domain . '/galeri', 'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => $domain . '/divisi', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => $domain . '/jadwal', 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['loc' => $domain . '/selengkapnya', 'priority' => '0.6', 'changefreq' => 'monthly'],
        ];

        // Add each division
        $divisions = Divisi::all();
        foreach ($divisions as $div) {
            $staticPages[] = [
                'loc' => $domain . '/divisi/' . $div->nama_divisi,
                'priority' => '0.7',
                'changefreq' => 'weekly'
            ];
        }

        // Add all active Konten articles with canonical slug URLs
        $articles = Konten::whereNotNull('slug')->where('slug', '!=', '')->orderBy('id', 'desc')->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($staticPages as $page) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($page['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= "    <changefreq>" . $page['changefreq'] . "</changefreq>\n";
            $xml .= "    <priority>" . $page['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        foreach ($articles as $article) {
            $lastmod = !empty($article->tanggal_upload) 
                ? \Carbon\Carbon::parse($article->tanggal_upload)->toIso8601String() 
                : now()->toIso8601String();
            $loc = $domain . '/konten/' . $article->slug;

            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($loc, ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= "    <lastmod>" . $lastmod . "</lastmod>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>0.8</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8'
        ]);
    }
}
