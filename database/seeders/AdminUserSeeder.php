<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(
            ['name' => 'مدير النظام'],
            ['description' => 'صلاحيات كاملة']
        );

        User::updateOrCreate(
            ['email' => 'abodyhilal@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('12345678'),
                'role_id' => $adminRole->id,
                'is_active' => true,
            ]
        );
    }
}
