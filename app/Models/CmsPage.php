<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CmsPage extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'seo_title',
        'meta_description',
        'status',
        'sort_order',
        'template',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (CmsPage $page) {
            if (!$page->slug) {
                $page->slug = Str::slug($page->title);
            }
            $page->slug = trim(Str::slug($page->slug), '-');
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getPublicUrlAttribute(): string
    {
        return url('/' . $this->slug);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return !$this->isPublished();
    }
}
