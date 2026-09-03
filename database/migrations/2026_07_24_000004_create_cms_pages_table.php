<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
<<<<<<< HEAD
use Illuminate\Support\Facades\Schema;
=======
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('status')->default('published')->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('template')->default('default');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });
<<<<<<< HEAD
=======

        $pages = [
            ['About Us', 'about-us', 'about_content', 'default'],
            ['Careers', 'careers', 'careers_content', 'default'],
            ['How It Works', 'how-it-works', 'how_it_works_content', 'default'],
            ['Safety Tips', 'safety-tips', 'safety_tips_content', 'default'],
            ['Owner Guidelines', 'owner-guidelines', 'owner_guidelines_content', 'default'],
            ['User Guidelines', 'user-guidelines', 'user_guidelines_content', 'default'],
            ['Terms & Conditions', 'terms-and-conditions', 'terms_content', 'default'],
            ['Privacy Policy', 'privacy-policy', 'privacy_content', 'default'],
            ['Refund & Cancellation Policy', 'condition-policy', 'condition_content', 'default'],
            ['Contact Us', 'contact-us', 'contact_content', 'contact'],
            ['Frequently Asked Questions', 'faq', 'faq_content', 'faq'],
        ];

        $defaults = config('cms.defaults', []);
        foreach ($pages as $index => [$title, $slug, $settingKey, $template]) {
            $settingValue = DB::table('settings')->where('key', $settingKey)->value('value');
            $content = $settingValue ?? $defaults[$settingKey] ?? '';
            if ($slug === 'condition-policy' && (!$content || str_contains((string) $content, 'Condition Policy content not set'))) {
                $content = '<h2>Refund & Cancellation Policy</h2><p>Refund and cancellation terms will be updated here.</p>';
            }

            DB::table('cms_pages')->updateOrInsert(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'content' => $content,
                    'seo_title' => $title,
                    'meta_description' => Str::limit(strip_tags((string) $content), 155, ''),
                    'status' => 'published',
                    'sort_order' => ($index + 1) * 10,
                    'template' => $template,
                    'is_system' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_pages');
    }
};
