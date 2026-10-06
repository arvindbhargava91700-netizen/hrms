<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KycRequirement extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'label',
        'key',
        'field_type',
        'input_type',
        'has_value_field',
        'document_mode',
        'value_label',
        'placeholder',
        'help_text',
        'is_required',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'has_value_field' => 'boolean',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function (self $requirement): void {
            $requirement->id = $requirement->id ?: (string) Str::uuid();
        });
    }

    public function isDocument(): bool
    {
        return $this->field_type === 'document';
    }

    public function isText(): bool
    {
        return $this->field_type === 'text';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
