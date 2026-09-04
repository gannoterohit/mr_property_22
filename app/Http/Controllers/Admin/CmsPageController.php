<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Services\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CmsPageController extends Controller
{
    public function index(Request $request)
    {
        $pages = CmsPage::query()
            ->where('slug', '!=', 'how-it-works')
            ->when($request->filled('search'), fn ($query) => $query->where(fn ($q) => $q
                ->where('title', 'like', '%'.$request->search.'%')
                ->orWhere('slug', 'like', '%'.$request->search.'%')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        return view('admin.cms-pages.index', compact('pages'));
    }

    public function create()
    {
        $page = new CmsPage(['status' => 'draft', 'template' => 'default', 'is_system' => false]);
        return view('admin.cms-pages.form', compact('page'));
    }

    public function store(Request $request)
    {
        $page = CmsPage::create($this->validated($request));
        return redirect()->route('admin.cms-pages.edit', $page)->with('success', 'CMS page created.');
    }

    public function edit(CmsPage $cmsPage)
    {
        if ($cmsPage->slug === 'how-it-works') {
            return redirect()->route('admin.how-it-works.index');
        }

        $page = $cmsPage;
        return view('admin.cms-pages.form', compact('page'));
    }

    public function update(Request $request, CmsPage $cmsPage)
    {
        $cmsPage->update($this->validated($request, $cmsPage));
        return back()->with('success', 'CMS page updated.');
    }

    public function destroy(CmsPage $cmsPage)
    {
        if ($cmsPage->is_system) {
            return back()->withErrors(['page' => 'System pages cannot be deleted. You can save them as draft.']);
        }

        $cmsPage->delete();
        return redirect()->route('admin.cms-pages.index')->with('success', 'CMS page deleted.');
    }

    public function toggleStatus(CmsPage $cmsPage)
    {
        $cmsPage->update([
            'status' => $cmsPage->isPublished() ? 'draft' : 'published',
        ]);

        return back()->with('success', "{$cmsPage->title} is now {$cmsPage->status}.");
    }

    public function legacy(string $key)
    {
        $slug = [
            'about' => 'about-us',
            'careers' => 'careers',
            'how-it-works' => 'how-it-works',
            'safety-tips' => 'safety-tips',
            'owner-guidelines' => 'owner-guidelines',
            'user-guidelines' => 'user-guidelines',
            'terms' => 'terms-and-conditions',
            'condition' => 'condition-policy',
            'privacy' => 'privacy-policy',
            'contact' => 'contact-us',
            'faq' => 'faq',
        ][$key] ?? $key;

        $page = CmsPage::where('slug', $slug)->firstOrFail();
        return redirect()->route('admin.cms-pages.edit', $page);
    }

    private function validated(Request $request, ?CmsPage $page = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('cms_pages', 'slug')->ignore($page?->id)],
            'content' => ['nullable', 'string'],
            'faqs' => ['nullable', 'array'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:draft,published'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'template' => ['required', 'in:default,faq,contact'],
        ]);

        if ($data['template'] === 'faq') {
            $faqs = collect($data['faqs'] ?? [])
                ->filter(fn ($faq) => !empty($faq['question']))
                ->map(fn ($faq) => [
                    'question' => $faq['question'],
                    'answer' => app(HtmlSanitizer::class)->clean($faq['answer'] ?? ''),
                ])
                ->values()
                ->all();
            $data['content'] = json_encode($faqs);
        } else {
            $data['content'] = app(HtmlSanitizer::class)->clean($data['content'] ?? '');
        }

        unset($data['faqs']);
        
        // Ensure new pages are not marked as system pages
        if (!$page) {
            $data['is_system'] = false;
        }
        
        return $data;
    }
}
