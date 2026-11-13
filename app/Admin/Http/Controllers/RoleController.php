<?php

namespace App\Admin\Http\Controllers;

use App\Admin\Http\Requests\RoleRequest;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class RoleController implements HasMiddleware
{
    /**
     * Tầng 2: mỗi hàm yêu cầu đúng quyền của nó.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:roles.view', only: ['index']),
            new Middleware('can:roles.create', only: ['create', 'store']),
            new Middleware('can:roles.update', only: ['edit', 'update']),
            new Middleware('can:roles.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        $roles = Role::query()
            ->withCount(['users', 'permissions'])
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
                'is_system' => $role->is_system,
                'users_count' => $role->users_count,
                'permissions_count' => $role->permissions_count,
            ]);

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'filters' => ['search' => $search],
            'title' => 'Vai trò',
            'breadcrumbs' => [['label' => 'Người dùng'], ['label' => 'Vai trò']],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Roles/Create', [
            'title' => 'Thêm vai trò',
            'breadcrumbs' => [['label' => 'Người dùng'], ['label' => 'Vai trò', 'href' => '/admin/roles'], ['label' => 'Thêm mới']],
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        Role::create($request->validated());

        return redirect()->route('admin.roles.index')
            ->with('success', 'Đã thêm vai trò. Hãy gán quyền cho vai trò này.');
    }

    public function edit(Role $role): Response
    {
        return Inertia::render('Roles/Edit', [
            'role' => $role->only(['id', 'name', 'description', 'is_system']),
            'title' => 'Sửa vai trò',
            'breadcrumbs' => [['label' => 'Người dùng'], ['label' => 'Vai trò', 'href' => '/admin/roles'], ['label' => 'Sửa']],
        ]);
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        $data = $request->validated();

        // Vai trò hệ thống không được đổi tên (tên được dùng để nhận diện)
        if ($role->is_system) {
            unset($data['name']);
        }

        $role->update($data);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Đã cập nhật vai trò.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return back()->with('error', 'Không thể xóa vai trò hệ thống.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Vai trò đang được gán cho tài khoản. Hãy chuyển các tài khoản sang vai trò khác trước khi xóa.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Đã xóa vai trò.');
    }
}