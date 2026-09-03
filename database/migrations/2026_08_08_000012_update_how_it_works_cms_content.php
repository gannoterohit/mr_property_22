<?php

use Illuminate\Database\Migrations\Migration;
<<<<<<< HEAD
=======
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a

return new class extends Migration
{
    public function up(): void
    {
<<<<<<< HEAD
        // Content is managed from the admin panel.
=======
        $content = <<<'HTML'
<section class="hiw-cms-hero">
    <div class="hiw-cms-wrap hiw-cms-hero-inner">
        <div>
            <span class="hiw-cms-eyebrow"><i class="fas fa-route"></i> Simple and transparent process</span>
            <h1>Find the right property.<br><span>Connect directly.</span></h1>
            <p>{{site_name}} helps users discover room, PG, shop, showroom and rental listings, then unlock verified owner contact details. Owners can publish and manage listings from one dashboard.</p>
            <div class="hiw-cms-actions">
                <a href="{{rooms_url}}"><i class="fas fa-search"></i> Browse Properties</a>
                <a href="{{owner_register_url}}"><i class="fas fa-plus"></i> List a Property</a>
            </div>
        </div>
        <div class="hiw-cms-feature-grid">
            <div class="hiw-cms-feature"><i class="fas fa-house"></i><strong>Room, PG and commercial listings</strong></div>
            <div class="hiw-cms-feature"><i class="fas fa-circle-check"></i><strong>Admin reviewed details</strong></div>
            <div class="hiw-cms-feature"><i class="fas fa-address-card"></i><strong>Contact unlock flow</strong></div>
            <div class="hiw-cms-feature"><i class="fas fa-comments"></i><strong>Direct owner conversation</strong></div>
        </div>
    </div>
</section>

<section class="hiw-cms-section">
    <div class="hiw-cms-wrap">
        <div class="hiw-cms-section-head">
            <span>For property seekers</span>
            <h2>From search to owner contact</h2>
            <p>No booking confusion. Choose a property, unlock the contact and speak directly with the owner.</p>
        </div>
        <div class="hiw-cms-grid">
            <article class="hiw-cms-card step"><small>Step 01</small><i class="fas fa-magnifying-glass"></i><h3>Search and compare</h3><p>Filter by city, rent, property type, category, area, furnishing, tenant preference and facilities.</p></article>
            <article class="hiw-cms-card step"><small>Step 02</small><i class="fas fa-lock-open"></i><h3>Unlock owner contact</h3><p>Use a contact-plan credit, wallet balance or single online payment to reveal the complete owner number.</p></article>
            <article class="hiw-cms-card step"><small>Step 03</small><i class="fas fa-phone"></i><h3>Call, visit and finalize</h3><p>Contact the owner directly, schedule a visit and independently confirm rent, deposit and rental terms.</p></article>
        </div>
    </div>
</section>

<section class="hiw-cms-section is-soft">
    <div class="hiw-cms-wrap hiw-cms-owner">
        <div class="hiw-cms-owner-copy">
            <span class="hiw-cms-kicker">For property owners</span>
            <h2>List and manage your properties</h2>
            <p>Use a listing plan or single listing payment. Your property becomes public after entitlement validation and admin approval.</p>
            <a href="{{plans_url}}">View listing plans <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="hiw-cms-grid">
            <article class="hiw-cms-card owner"><i class="fas fa-file-pen"></i><h3>Create listing</h3><p>Add pricing, property type, category, square feet, facilities, location, photos and preferred tenant.</p></article>
            <article class="hiw-cms-card owner"><i class="fas fa-credit-card"></i><h3>Activate listing</h3><p>Use an owner listing credit, wallet balance or online payment to submit the property for approval.</p></article>
            <article class="hiw-cms-card owner"><i class="fas fa-chart-line"></i><h3>Manage availability</h3><p>Mark a property booked to hide it, then reactivate it when it becomes available again.</p></article>
        </div>
    </div>
</section>

<section class="hiw-cms-section">
    <div class="hiw-cms-wrap">
        <div class="hiw-cms-alert">
            <i class="fas fa-shield-halved"></i>
            <div>
                <h2>Visit and verify before finalizing</h2>
                <p>{{site_name}} provides listing discovery and contact access. Always visit the property, verify owner identity/documents and agree on rent, deposit and terms before paying the owner.</p>
            </div>
            <a href="{{safety_tips_url}}">Safety Tips</a>
        </div>
    </div>
</section>
HTML;

        DB::table('cms_pages')->updateOrInsert(
            ['slug' => 'how-it-works'],
            [
                'title' => 'How It Works',
                'content' => $content,
                'seo_title' => 'How It Works',
                'meta_description' => Str::limit(strip_tags(str_replace(
                    ['{{site_name}}', '{{rooms_url}}', '{{owner_register_url}}', '{{plans_url}}', '{{safety_tips_url}}'],
                    ['ApnaNest', '', '', '', ''],
                    $content
                )), 155, ''),
                'status' => 'published',
                'template' => 'default',
                'is_system' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'how_it_works_content'],
            [
                'value' => $content,
                'type' => 'textarea',
                'group' => 'pages',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a
    }

    public function down(): void
    {
<<<<<<< HEAD
        // Content is managed from the admin panel.
=======
        // Keep the CMS page; admins may have edited it after migration.
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a
    }
};
