<?php

namespace App\Livewire\Partner;

use App\Livewire\Partner\Hrms\HasPartnerId;
use App\Models\User;

trait HasPartnerWorkspaceScope
{
    use HasPartnerId {
        getPartnerId as private getLegacyPartnerId;
    }

    public ?string $selectedPartnerId = null;

    public function getPartnerId()
    {
        return auth()->user()->isSuperAdmin() ? null : $this->getLegacyPartnerId();
    }

    protected function selectedWorkspacePartnerId(): ?string
    {
        if (!filled($this->selectedPartnerId) && filled(request()->query('partner_id'))) {
            $this->selectedPartnerId = (string) request()->query('partner_id');
        }

        if (auth()->user()->isSuperAdmin() && filled($this->selectedPartnerId)) {
            $this->validateSelectedPartner($this->selectedPartnerId);
        }

        return filled($this->selectedPartnerId) ? $this->selectedPartnerId : null;
    }

    protected function scopePartnerRecords($query, string $column = 'partner_id')
    {
        if (auth()->user()->isSuperAdmin()) {
            $partnerId = $this->selectedWorkspacePartnerId();
            if ($partnerId !== null) {
                $query->where($column, $partnerId);
            }

            return $query;
        }

        return $query->where($column, $this->getLegacyPartnerId());
    }

    protected function requirePartnerIdForWrite(): string
    {
        $partnerId = auth()->user()->isSuperAdmin()
            ? $this->selectedWorkspacePartnerId()
            : $this->getLegacyPartnerId();

        abort_unless(filled($partnerId), 422, 'Select a partner to continue.');
        $this->validateSelectedPartner($partnerId);

        return (string) $partnerId;
    }

    protected function validateSelectedPartner(string $partnerId): void
    {
        abort_unless(
            User::whereKey($partnerId)->where('role', 'partner')->exists(),
            422,
            'Select a valid partner.'
        );
    }
}
