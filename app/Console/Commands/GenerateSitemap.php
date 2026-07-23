<?php

namespace App\Console\Commands;

use App\Models\Ad;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate sitemap.xml for SEO';

    public function handle(): void
    {
        $urls = [];
        $baseUrl = config('app.url');

        // Static pages
        $urls[] = ['loc' => $baseUrl, 'priority' => '1.0', 'changefreq' => 'daily'];

        // Categories
        foreach (Category::roots()->get() as $category) {
            $urls[] = [
                'loc' => $baseUrl . '/?category_id=' . $category->id,
                'priority' => '0.8',
                'changefreq' => 'weekly',
            ];
        }

        // Active listings
        foreach (Ad::active()->select('id', 'slug', 'updated_at')->cursor() as $ad) {
            $urls[] = [
                'loc' => $baseUrl . '/listing/' . $ad->slug,
                'priority' => '0.6',
                'changefreq' => 'weekly',
                'lastmod' => $ad->updated_at?->toDateString(),
            ];
        }

        $xml = $this->buildXml($urls);

        File::put(public_path('sitemap.xml'), $xml);
        $this->info('Sitemap generated: ' . count($urls) . ' URLs');
    }

    private function buildXml(array $urls): string
    {
        $lines = ['<?xml version="1.0" encoding="UTF-8"?>'];
        $lines[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>' . htmlspecialchars($url['loc']) . '</loc>';
            $lines[] = '    <priority>' . $url['priority'] . '</priority>';
            $lines[] = '    <changefreq>' . $url['changefreq'] . '</changefreq>';
            if (isset($url['lastmod'])) {
                $lines[] = '    <lastmod>' . $url['lastmod'] . '</lastmod>';
            }
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines);
    }
}