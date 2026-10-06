<?php

namespace Tests\Feature;

use App\Livewire\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_profile_page_loads_and_updates(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'mobile' => '9000000001',
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin);

        $component = app(Profile::class);
        $component->mount();

        $this->assertSame('Admin User', $component->name);
        $this->assertSame('admin@example.com', $component->email);

        $component->name = 'Admin Updated';
        $component->email = 'admin.updated@example.com';
        $component->mobile = '9000000009';
        $component->save();

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'Admin Updated',
            'email' => 'admin.updated@example.com',
            'mobile' => '9000000009',
        ]);
    }

    public function test_partner_profile_page_loads_and_updates(): void
    {
        $partner = User::factory()->create([
            'name' => 'Partner User',
            'email' => 'partner@example.com',
            'mobile' => '9000000002',
            'role' => 'partner',
            'status' => 'active',
        ]);

        $this->actingAs($partner);

        $component = app(Profile::class);
        $component->mount();

        $this->assertSame('Partner User', $component->name);
        $component->name = 'Partner Updated';
        $component->email = 'partner.updated@example.com';
        $component->save();

        $this->assertDatabaseHas('users', [
            'id' => $partner->id,
            'name' => 'Partner Updated',
            'email' => 'partner.updated@example.com',
        ]);
    }
}
