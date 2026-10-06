<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = ['parent_id','name','slug','icon','color','is_active','sort_order','has_shifts','has_trainers','has_rooms','has_packages','has_attendance'];

    protected $appends = ['icon_url'];

    protected $casts = [
        'is_active'      => 'boolean',
        'has_shifts'     => 'boolean',
        'has_trainers'   => 'boolean',
        'has_rooms'      => 'boolean',
        'has_packages'   => 'boolean',
        'has_attendance' => 'boolean',
    ];

    public function parent()       { return $this->belongsTo(Category::class, 'parent_id'); }
    public function children()     { return $this->hasMany(Category::class, 'parent_id'); }
    public function listings()     { return $this->hasMany(Listing::class); }
    public function customFields() { return $this->hasMany(CustomField::class)->orderBy('sort_order'); }

    public function scopeActive($q) { return $q->where('is_active', true)->orderBy('sort_order'); }
    public function scopeRoot($q)   { return $q->whereNull('parent_id'); }

    public function getIconUrlAttribute(): ?string
    {
        if (empty($this->attributes['icon'])) return null;
        $val = $this->attributes['icon'];
        if (\Illuminate\Support\Str::startsWith($val, 'bi-')) return null; // Handle legacy font icons
        return str_starts_with($val, 'http') ? $val : asset('storage/' . $val);
    }
}
