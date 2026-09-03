<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_type_id',
        'name',
        'slug',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function propertyType()
    {
        return $this->belongsTo(PropertyType::class, 'property_type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopePublicSelectable($query)
    {
        return $query->active()->whereHas('propertyType', fn ($type) => $type->active());
    }
}
