<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacBootstrapTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_permission_seed_creates_expected_roles_and_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Scholarship Secretary');

        $this->assertTrue($user->hasRole('Scholarship Secretary'));
        $this->assertTrue($user->can('create students'));
        $this->assertTrue($user->can('send sms'));
        $this->assertFalse($user->can('manage users'));
    }

    public function test_only_active_authorized_users_can_access_filament_panel(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $panel = Filament::getPanel('admin');
        $authorized = User::factory()->create(['is_active' => true]);
        $inactive = User::factory()->create(['is_active' => false]);
        $unassigned = User::factory()->create(['is_active' => true]);

        $authorized->assignRole('Super Administrator');
        $inactive->assignRole('Super Administrator');

        $this->assertTrue($authorized->canAccessPanel($panel));
        $this->assertFalse($inactive->canAccessPanel($panel));
        $this->assertFalse($unassigned->canAccessPanel($panel));
    }
}
