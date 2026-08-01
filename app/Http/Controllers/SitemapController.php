<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\CmsPage;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    public function index()
    {
        $rooms = Room::where('status', 'active')->orderBy('updated_at', 'desc')->get();

        $urls = [];
        $urls[] = [
            'loc' => url('/'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '1.0'
        ];

        foreach (CmsPage::where('status', 'published')->orderBy('sort_order')->get() as $page) {
            $urls[] = [
                'loc' => $page->public_url,
                'lastmod' => optional($page->updated_at)->toAtomString() ?? now()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ];
        }

        $urls[] = [
            'loc' => route('rooms.index'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.9'
        ];

        $urls[] = [
            'loc' => route('plans'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'weekly',
            'priority' => '0.7'
        ];

        foreach ($rooms as $room) {
            $urls[] = [
                'loc' => route('rooms.show', $room->id),
                'lastmod' => optional($room->updated_at)->toAtomString() ?? now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7'
            ];
        }

        return response()->view('sitemap.index', compact('urls'))
            ->header('Content-Type', 'application/xml');
    }

    public function robots()
    {
        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /owner/\n";
        $content .= "Disallow: /profile/\n";
        $content .= "Disallow: /complaints\n";
        $content .= "Disallow: /api/\n\n";
        $content .= "Sitemap: " . route('sitemap') . "\n";

        return response($content)->header('Content-Type', 'text/plain');
    }
}






