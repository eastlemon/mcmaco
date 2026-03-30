<?php

namespace App\Console\Commands;

use App\Models\Ad;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate sitemap.xml for active ads';

    public function handle(): int
    {
        $urls = [];

        $urls[] = [
            'loc' => url('/'),
            'lastmod' => now()->toAtomString(),
        ];

        Ad::query()
            ->where('status', 'active')
            ->orderByDesc('updated_at')
            ->chunk(500, function ($ads) use (&$urls) {
                foreach ($ads as $ad) {
                    $urls[] = [
                        'loc' => route('ads.show', $ad),
                        'lastmod' => $ad->updated_at->toAtomString(),
                    ];
                }
            });

        $xml = $this->buildXml($urls);
        Storage::disk('public')->put('sitemap.xml', $xml);

        $this->info('Sitemap generated.');

        return self::SUCCESS;
    }

    private function buildXml(array $urls): string
    {
        $items = array_map(function ($item) {
            return "    <url>\n".
                "        <loc>{$item['loc']}</loc>\n".
                "        <lastmod>{$item['lastmod']}</lastmod>\n".
                "    </url>";
        }, $urls);

        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
            "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n".
            implode("\n", $items).
            "\n</urlset>\n";
    }
}
