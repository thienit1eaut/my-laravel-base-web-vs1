<?php

namespace App\Admin\Http\Controllers;

use App\Admin\Http\Requests\ProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController
{
    public function edit(Request $request): Response
    {
        $user = $request->user()->load('role:id,name');

        return Inertia::render('Profile/Edit', [
            'profile' => [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role_name' => $user->role?->name,
                'image_url' => $user->image_url,
            ],
            'title' => 'Thông tin hồ sơ',
            'breadcrumbs' => [['label' => 'Thông tin hồ sơ']],
        ]);
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Chỉ cho sửa các trường cá nhân, không bao giờ cho đổi vai trò hay trạng thái
        $data = $request->safe()->only(['name', 'username', 'email']);

        if ($request->filled('password')) {
            $data['password'] = $request->input('password');
        }

        if ($request->hasFile('image')) {
            $this->deleteImage($user->image);
            $data['image'] = $request->file('image')->store('users', 'public');
        } elseif ($request->boolean('remove_image')) {
            $this->deleteImage($user->image);
            $data['image'] = null;
        }

        $user->update($data);

        return redirect()->route('admin.profile.edit')
            ->with('success', 'Đã cập nhật hồ sơ.');
    }

    private function deleteImage(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}