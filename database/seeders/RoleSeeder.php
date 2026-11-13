<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Vai trò hệ thống: toàn quyền, không xóa được
        Role::updateOrCreate(
            ['name' => 'Quản trị viên'],
            ['description' => 'Toàn quyền quản trị hệ thống', 'is_system' => true]
        );

        Role::firstOrCreate(
            ['name' => 'Quản lý'],
            ['description' => 'Quản lý vận hành hệ thống']
        );

        Role::firstOrCreate(
            ['name' => 'Nhân viên'],
            ['description' => 'Nhân viên vận hành']
        );
    }
}