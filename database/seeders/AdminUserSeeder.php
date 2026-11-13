<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Tạo (hoặc cập nhật) tài khoản quản trị từ biến trong file .env
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command->error('Thiếu ADMIN_EMAIL hoặc ADMIN_PASSWORD trong file .env');

            return;
        }

        $role = Role::where('name', 'Quản trị viên')->first();

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'Quản trị viên'),
                'username' => env('ADMIN_USERNAME', 'admin'),
                'id_role' => $role?->id,
                'is_active' => true,
                // Model User có cast 'hashed' nên mật khẩu tự được mã hóa
                'password' => $password,
            ]
        );

        $this->command->info("Đã tạo/cập nhật tài khoản quản trị: {$email}");
    }
}