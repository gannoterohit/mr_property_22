<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class HomePageController extends Controller
{
    public function index()
    {
        return view('admin.content.home', ['sections' => $this->sections()]);
    }

    public function update(Request $request)
    {
        $allowed = collect($this->sections())->flatMap(fn ($fields) => array_keys($fields))->all();
        $rules = collect($allowed)->mapWithKeys(fn ($key) => [
            $key => ['nullable', 'string', 'max:2000'],
        ])->all();
        $data = $request->validate($rules);

        foreach ($allowed as $key) {
            Setting::set($key, $data[$key] ?? '');
        }

        return back()->with('success', 'Home page content updated successfully.');
    }

    private function sections(): array
    {
        return [
            'Hero Section' => [
                'home_hero_title' => ['label' => 'Main heading', 'default' => 'Find a place that'],
                'home_hero_highlight' => ['label' => 'Highlighted heading', 'default' => 'feels like home'],
                'home_hero_description' => ['label' => 'Description', 'default' => 'Discover verified rooms, PGs and flats for rent. Connect directly with genuine property owners.', 'textarea' => true],
                'home_search_button' => ['label' => 'Search button', 'default' => 'Search Rooms'],
            ],
            'Current Landing Section Headings' => [
                'home_category_eyebrow' => ['label' => 'Categories small label', 'default' => 'Explore your options'],
                'home_category_title' => ['label' => 'Categories heading', 'default' => 'Find the right kind of home'],
                'home_category_description' => ['label' => 'Categories description', 'default' => 'Start with a property type that matches your lifestyle and budget.'],
                'home_latest_title' => ['label' => 'Latest listings heading', 'default' => 'Latest verified rooms'],
                'home_latest_description' => ['label' => 'Latest listings description', 'default' => 'Genuine listings with clear rent, photos and property details.'],
                'home_areas_eyebrow' => ['label' => 'Areas small label', 'default' => 'Popular neighbourhoods'],
                'home_areas_title' => ['label' => 'Areas heading', 'default' => 'Explore places renters search most'],
                'home_areas_description' => ['label' => 'Areas description', 'default' => 'Compare local options before choosing your next area.'],
                'home_steps_title' => ['label' => 'How it works heading', 'default' => 'How ApnaNest works'],
                'home_why_title' => ['label' => 'Trust panel heading', 'default' => 'More clarity before you connect'],
                'home_why_description' => ['label' => 'Trust panel description', 'default' => 'ApnaNest helps users compare useful property information and reach owners without unnecessary middlemen.', 'textarea' => true],
                'home_blog_title' => ['label' => 'Guides heading', 'default' => 'Helpful guides and updates'],
                'home_testimonials_title' => ['label' => 'Testimonials heading', 'default' => 'What users value'],
            ],
            'Benefits & Trust Points' => $this->cardFields('why', [
                ['Verified Listings', 'Listings reviewed for authenticity.', 'fa-circle-check'],
                ['Zero Brokerage', 'Connect directly with property owners.', 'fa-wallet'],
                ['Direct Owner Contact', 'Speak directly with genuine owners.', 'fa-user-check'],
                ['Report and Support', 'Use built-in reporting and support tools.', 'fa-headset'],
            ], true, false),
            'How It Works' => $this->cardFields('step', [
                ['Search', 'Find rooms by city, budget and preference.', ''],
                ['Compare', 'Review photos, rent, amenities and owner information.', ''],
                ['Connect', 'Unlock contact and speak directly with the property owner.', ''],
            ], false),
            'Testimonials' => $this->testimonialFields(),
            'Owner Call To Action' => [
                'home_owner_title' => ['label' => 'Heading', 'default' => 'Have a room or property to rent?'],
                'home_owner_description' => ['label' => 'Description', 'default' => 'Create a clear listing and connect with people actively searching in your city.', 'textarea' => true],
                'home_owner_button' => ['label' => 'Button text', 'default' => 'List your property'],
            ],
        ];
    }

    private function cardFields(
        string $prefix,
        array $cards,
        bool $includeIcons = true,
        bool $includeDescriptions = true
    ): array
    {
        $fields = [];
        foreach ($cards as $index => [$title, $description, $icon]) {
            $number = $index + 1;
            $fields["home_{$prefix}_{$number}_title"] = ['label' => "Card {$number} title", 'default' => $title];
            if ($includeDescriptions) {
                $fields["home_{$prefix}_{$number}_description"] = ['label' => "Card {$number} description", 'default' => $description, 'textarea' => true];
            }
            if ($includeIcons) {
                $fields["home_{$prefix}_{$number}_icon"] = ['label' => "Card {$number} icon class", 'default' => $icon];
            }
        }

        return $fields;
    }

    private function testimonialFields(): array
    {
        $fields = [];
        foreach ([1, 2] as $number) {
            $fields["home_testimonial_{$number}_name"] = ['label' => "Testimonial {$number} name", 'default' => $number === 1 ? 'Rahul Sharma' : 'Neha Verma'];
            $fields["home_testimonial_{$number}_role"] = ['label' => "Testimonial {$number} role", 'default' => $number === 1 ? 'Student' : 'Working Professional'];
            $fields["home_testimonial_{$number}_text"] = ['label' => "Testimonial {$number} review", 'default' => 'A simple and reliable room-finding experience.', 'textarea' => true];
        }

        return $fields;
    }
}
