<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use WithFileUploads;
    public string $name = '';
    public string $email = '';
    public string $mobile = '';
    public string $role = '';
    public string $status = '';
    public ?string $current_password = null;
    public ?string $password = null;
    public ?string $password_confirmation = null;
    public $profile_image;

    public function mount(): void
    {
        $user = auth()->user();

        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->mobile = (string) ($user->mobile ?? '');
        $this->role = (string) $user->role;
        $this->status = (string) $user->status;
    }

    public function save(): void
    {
        $user = auth()->user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'mobile' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'mobile')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'current_password' => ['required_with:password', 'nullable', 'string'],
            'profile_image' => ['nullable', 'image', 'max:2048'], // 2MB max
        ]);

        if (! empty($validated['password'] ?? null)) {
            if (! Hash::check($validated['current_password'], $user->password)) {
                $this->addError('current_password', 'The current password is incorrect.');
                return;
            }
        }

        $updates = [];

        if (in_array($user->role, ['partner', 'admin'])) {
            $updates['name'] = $validated['name'];
            $updates['email'] = $validated['email'];
            $updates['mobile'] = $validated['mobile'] ?? null;

            if (($user->email !== $validated['email']) || (($user->mobile ?? null) !== ($validated['mobile'] ?? null))) {
                $updates['email_verified_at'] = $user->email !== $validated['email'] ? null : $user->email_verified_at;
                $updates['mobile_verified_at'] = $user->mobile !== ($validated['mobile'] ?? null) ? null : $user->mobile_verified_at;
            }
        }

        if (! empty($validated['password'] ?? null)) {
            $updates['password'] = Hash::make($validated['password']);
        }

        if ($this->profile_image) {
            $path = $this->profile_image->store('profile_images', 'public');
            $updates['profile_image'] = $path;
        }

        $user->update($updates);

        $this->password = null;
        $this->password_confirmation = null;
        $this->current_password = null;

        session()->flash('success', 'Profile updated successfully.');
        $this->redirectRoute('partner.profile', navigate: false);
    }

    public function render()
    {
        $user = auth()->user();
        $salaryStructure = \App\Models\EmployeeSalaryStructure::where('employee_id', $user->id)->first();

        $sidebarView = ($user && $user->role === 'employee') ? 'partials.sidebar-employee' : 'partials.sidebar-partner';

        return view('livewire.profile', compact('salaryStructure'))
            ->layout('layouts.app', [
                'panelName' => ($user && $user->isAdmin()) ? 'Admin Panel' : 'HRMS Panel',
                'pageTitle' => 'Profile',
                'pageSubtitle' => 'View and update your account details',
                'sidebarLinks' => view($sidebarView),
            ]);
    }
}
