<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'expense_category_id',
        'amount',
        'quantity',
        'unit_rate',
        'category',
        'date',
        'description',
        'status',
        'remarks',
        'upload_file',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function categoryRelation()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function getUploadFileUrlAttribute(): ?string
    {
        if (empty($this->upload_file)) return null;
        if (str_starts_with($this->upload_file, 'http')) {
            return $this->upload_file;
        }
        return route('partner.hrms.expenses.file', $this->id);
    }

    public function getIsImageAttribute(): bool
    {
        if (empty($this->upload_file)) return false;
        $ext = strtolower(pathinfo($this->upload_file, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'jfif']);
    }

    public function getIsPdfAttribute(): bool
    {
        if (empty($this->upload_file)) return false;
        $ext = strtolower(pathinfo($this->upload_file, PATHINFO_EXTENSION));
        return $ext === 'pdf';
    }
}
