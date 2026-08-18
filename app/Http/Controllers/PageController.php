<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CmsPage;
use App\Models\HowItWorksItem;
use App\Models\Setting;

class PageController extends Controller
{
    public function about()
    {
        return $this->render('about-us', 'About Us', 'about_content');
    }

    public function careers()
    {
        return $this->render('careers', 'Careers', 'careers_content');
    }

    public function howItWorks()
    {
        $page = $this->page('how-it-works');
        if ($page && !$page->isPublished()) {
            abort(404);
        }

        $items = HowItWorksItem::active()
            ->orderBy('group')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('group');

        $title = $page?->seo_title ?: 'How It Works';
        $pageTitle = $page?->title ?: 'How It Works';
        $metaDescription = $page?->meta_description ?: 'Learn how users find properties and unlock owner contacts, and how property owners list and manage properties.';
        $updatedAt = $page?->updated_at;

        return view('pages.how-it-works', compact('items', 'title', 'pageTitle', 'metaDescription', 'updatedAt'));
    }

    public function safetyTips()
    {
        return $this->render('safety-tips', 'Safety Tips', 'safety_tips_content');
    }

    public function ownerGuidelines()
    {
        return $this->render('owner-guidelines', 'Owner Guidelines', 'owner_guidelines_content');
    }

    public function userGuidelines()
    {
        return $this->render('user-guidelines', 'User Guidelines', 'user_guidelines_content');
    }

    public function terms()
    {
        return $this->render('terms-and-conditions', 'Terms & Conditions', 'terms_content');
    }

    public function privacy()
    {
        return $this->render('privacy-policy', 'Privacy Policy', 'privacy_content');
    }

    public function condition()
    {
        return $this->render('condition-policy', 'Refund & Cancellation Policy', 'condition_content');
    }

    public function contact()
    {
        return $this->render('contact-us', 'Contact Us', 'contact_content', 'pages.contact');
    }

    public function faq()
    {
        $json = Setting::get('faq_content', '[]');
        $faqs = json_decode($json, true);
        if (!is_array($faqs)) {
            $faqs = [];
        }
        
        $title = 'Frequently Asked Questions';
        return view('pages.faq', compact('faqs', 'title'));
    }

    public function show(string $slug)
    {
        $page = CmsPage::where('slug', $slug)->published()->firstOrFail();
        return $this->renderPage($page);
    }

    private function render(string $slug, string $fallbackTitle, string $settingKey, string $view = 'pages.show')
    {
        $page = $this->page($slug);
        if ($page) {
            return $this->renderPage($page, $view);
        }

        $content = Setting::get($settingKey, config("cms.defaults.{$settingKey}", ''));
        $content = $this->replaceContentTokens((string) $content);
        $title = $fallbackTitle;
        $pageTitle = $fallbackTitle;
        $metaDescription = '';
        $updatedAt = null;
        return view($view, compact('content', 'title', 'pageTitle', 'metaDescription', 'updatedAt'));
    }

    private function renderPage(CmsPage $page, ?string $forcedView = null)
    {
        if (!$page->isPublished()) abort(404);

        $content = $this->replaceContentTokens((string) $page->content);
        $title = $page->seo_title ?: $page->title;
        $pageTitle = $page->title;
        $metaDescription = $page->meta_description ?: '';
        $updatedAt = $page->updated_at;
        if (($forcedView ?: $page->template) === 'contact' || $page->template === 'contact') {
            return view('pages.contact', compact('content', 'title', 'pageTitle', 'metaDescription', 'updatedAt'));
        }
        $view = $forcedView ?: 'pages.show';
        return view($view, compact('content', 'title', 'pageTitle', 'metaDescription', 'updatedAt'));
    }

    private function replaceContentTokens(string $content): string
    {
        return str_replace([
            '{{site_name}}',
            '{{rooms_url}}',
            '{{owner_register_url}}',
            '{{plans_url}}',
            '{{safety_tips_url}}',
        ], [
            Setting::get('website_name', 'ApnaNest'),
            route('rooms.index'),
            route('register', ['role' => 'owner']),
            route('plans'),
            route('pages.safety-tips'),
        ], $content);
    }

    private function page(string $slug): ?CmsPage
    {
        return CmsPage::where('slug', $slug)->first();
    }
}
