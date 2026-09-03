<?php

use App\Services\HtmlSanitizer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $sanitizer = app(HtmlSanitizer::class);

        if (Schema::hasTable('blogs')) {
            DB::table('blogs')->select('id', 'content')->orderBy('id')->each(function ($blog) use ($sanitizer): void {
                DB::table('blogs')->where('id', $blog->id)->update([
                    'content' => $sanitizer->clean($blog->content),
                ]);
            });
        }

        if (Schema::hasTable('cms_pages')) {
            DB::table('cms_pages')->select('id', 'template', 'content')->orderBy('id')->each(function ($page) use ($sanitizer): void {
                $content = $page->template === 'faq'
                    ? $this->sanitizeFaqJson($page->content, $sanitizer)
                    : $sanitizer->clean($page->content);
                DB::table('cms_pages')->where('id', $page->id)->update(['content' => $content]);
            });
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->where('key', 'like', '%\_content')->select('id', 'key', 'value')->orderBy('id')->each(function ($setting) use ($sanitizer): void {
                $value = $setting->key === 'faq_content'
                    ? $this->sanitizeFaqJson($setting->value, $sanitizer)
                    : $sanitizer->clean($setting->value);
                DB::table('settings')->where('id', $setting->id)->update(['value' => $value]);
            });
        }
    }

    public function down(): void
    {
        // Removed executable markup cannot be restored safely.
    }

    private function sanitizeFaqJson(?string $json, HtmlSanitizer $sanitizer): string
    {
        $items = json_decode((string) $json, true);
        if (! is_array($items)) {
            return '[]';
        }

        return json_encode(array_values(array_map(fn ($item) => [
            'question' => mb_substr((string) ($item['question'] ?? ''), 0, 500),
            'answer' => $sanitizer->clean((string) ($item['answer'] ?? '')),
        ], $items)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
};
