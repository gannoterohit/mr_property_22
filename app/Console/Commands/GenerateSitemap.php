<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CmsPage;
use App\Models\Room;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate static sitemap.xml in public/';

    public function handle()
    {
        $baseUrl = rtrim(config('app.url') ?: url('/'), '/');

        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Homepage
        $sitemap .= "  <url>\n";
        $sitemap .= '    <loc>' . htmlspecialchars($baseUrl) . '</loc>' . "\n";
        $sitemap .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
        $sitemap .= "    <changefreq>daily</changefreq>\n";
        $sitemap .= "    <priority>1.0</priority>\n";
        $sitemap .= "  </url>\n";

        // Rooms listing
        $sitemap .= "  <url>\n";
        $sitemap .= '    <loc>' . htmlspecialchars($baseUrl . '/rooms') . '</loc>' . "\n";
        $sitemap .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
        $sitemap .= "    <changefreq>daily</changefreq>\n";
        $sitemap .= "    <priority>0.9</priority>\n";
        $sitemap .= "  </url>\n";

        // Plans
        $sitemap .= "  <url>\n";
        $sitemap .= '    <loc>' . htmlspecialchars($baseUrl . '/plans') . '</loc>' . "\n";
        $sitemap .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
        $sitemap .= "    <changefreq>weekly</changefreq>\n";
        $sitemap .= "    <priority>0.7</priority>\n";
        $sitemap .= "  </url>\n";

        foreach (CmsPage::where('status', 'published')->orderBy('sort_order')->get() as $page) {
            $lastmod = optional($page->updated_at)->format('Y-m-d') ?? date('Y-m-d');

            $sitemap .= "  <url>\n";
            $sitemap .= '    <loc>' . htmlspecialchars($page->public_url) . '</loc>' . "\n";
            $sitemap .= '    <lastmod>' . $lastmod . '</lastmod>' . "\n";
            $sitemap .= "    <changefreq>monthly</changefreq>\n";
            $sitemap .= "    <priority>0.5</priority>\n";
            $sitemap .= "  </url>\n";
        }

        $rooms = Room::where('status','active')->orderBy('updated_at','desc')->get();
        foreach ($rooms as $room) {
            $sitemap .= "  <url>\n";
            $sitemap .= '    <loc>' . htmlspecialchars(route('rooms.show', $room->id)) . '</loc>' . "\n";
            $sitemap .= '    <lastmod>' . $room->updated_at->format('Y-m-d') . '</lastmod>' . "\n";
            $sitemap .= "    <changefreq>weekly</changefreq>\n";
            $sitemap .= "    <priority>0.8</priority>\n";
            $sitemap .= "  </url>\n";
        }

        $sitemap .= '</urlset>';

        $path = public_path('sitemap.xml');
        file_put_contents($path, $sitemap);

        $this->info('Sitemap generated at: ' . $path);
        return 0;
    }
}
