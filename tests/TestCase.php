<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    protected function authenticateAsBoss(): User
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $role = Role::firstOrCreate(['name' => 'boss', 'guard_name' => 'web']);
        $user = User::factory()->create(['role' => 'boss']);
        $user->assignRole($role);
        $this->actingAs($user);
        return $user;
    }

    protected function authenticateAsEmployee(): User
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $role = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        $user = User::factory()->create(['role' => 'employee']);
        $user->assignRole($role);
        $this->actingAs($user);
        return $user;
    }
}
