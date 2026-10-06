<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    protected $fillable = ['category_id','label','field_type','is_required','sort_order'];
    protected $casts = ['is_required' => 'boolean'];

    public function category() { return $this->belongsTo(Category::class); }

    public function scopeForCategory($q, $categoryId) { return $q->where('category_id', $categoryId)->orderBy('sort_order'); }
}
