<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class KycDocument extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id','partner_id','aadhaar_number','pan_number','gst_number',
        'bank_details','submission_data','status','reviewed_at','submitted_at',
    ];
    protected $casts = ['bank_details' => 'array', 'submission_data' => 'array', 'reviewed_at' => 'datetime', 'submitted_at' => 'datetime'];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($m) => $m->id = $m->id ?: (string) Str::uuid());
    }

    public function partner() { return $this->belongsTo(User::class, 'partner_id'); }

    public function isPending()  { return $this->status === 'pending'; }
    public function isApproved() { return $this->status === 'approved'; }
    public function isRejected() { return $this->status === 'rejected'; }

    public function getSubmissionSummaryAttribute(): array
    {
        if (!empty($this->submission_data) && is_array($this->submission_data)) {
            return $this->submission_data;
        }

        $items = [];

        if ($this->aadhaar_number) {
            $items['aadhaar_number'] = [
                'label' => 'Aadhaar Number',
                'type' => 'text',
                'value' => $this->aadhaar_number,
            ];
        }

        if ($this->pan_number) {
            $items['pan_number'] = [
                'label' => 'PAN Number',
                'type' => 'text',
                'value' => $this->pan_number,
            ];
        }

        if ($this->gst_number) {
            $items['gst_number'] = [
                'label' => 'GST Number',
                'type' => 'text',
                'value' => $this->gst_number,
            ];
        }

        if (!empty($this->bank_details) && is_array($this->bank_details)) {
            $items['bank_details'] = [
                'label' => 'Bank Details',
                'type' => 'text',
                'value' => collect($this->bank_details)
                    ->map(fn ($value, $key) => ucfirst(str_replace('_', ' ', $key)) . ': ' . $value)
                    ->implode(', '),
            ];
        }

        return $items;
    }

    public function getStoredFileUrl(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }
}
