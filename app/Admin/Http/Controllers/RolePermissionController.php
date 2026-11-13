<?php

namespace App\Admin\Http\Controllers;

use App\Admin\Services\PermissionChecker;
use App\Models\Module;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class RolePermissionController implements HasMiddleware
{
    public function __construct(private PermissionChecker $permissions)
    {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('can:roles.permissions.update'),
        ];
    }

    public function edit(Request $request, Role $role): Response|RedirectResponse
    {
        if ($role->is_system) {
            return $this->systemRoleRedirect();
        }

        $modules = Module::with('permissions')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Module $module) => [
                'id' => $module->id,
                'code' => $module->code,
                'name' => $module->name,
                'permissions' => $module->permissions
                    ->map(fn ($permission) => $permission->only(['id', 'action', 'name']))
                    ->values(),
            ])
            ->values();

        return Inertia::render('Roles/Permissions', [
            'role' => $role->only(['id', 'name']),
            'modules' => $modules,
            // Quyền vai trò đang có
            'granted' => $role->permissions()->pluck('permissions.id')->all(),
            // Quyền mà người đang thao tác được phép bật/tắt (chính các quyền họ đang có)
            'grantable' => $this->permissions->grantableIds($request->user()),
            'title' => 'Phân quyền: '.$role->name,
            'breadcrumbs' => [['label' => 'Người dùng'], ['label' => 'Vai trò', 'href' => '/admin/roles'], ['label' => 'Phân quyền']],
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return $this->systemRoleRedirect();
        }

        $data = $request->validate([
            'permissions' => ['present', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $allowed = $this->permissions->grantableIds($request->user());
        $current = $role->permissions()->pluck('permissions.id')->all();
        $requested = array_map('intval', $data['permissions']);

        // Chống leo thang quyền: chỉ bật/tắt được quyền mà chính mình đang có,
        // các quyền còn lại của vai trò được giữ nguyên.
        $final = array_values(array_unique(array_merge(
            array_intersect($requested, $allowed),
            array_diff($current, $allowed),
        )));

        $role->permissions()->sync($final);

        return redirect()->route('admin.roles.index')
            ->with('success', "Đã cập nhật phân quyền cho vai trò \"{$role->name}\".");
    }

    private function systemRoleRedirect(): RedirectResponse
    {
        return redirect()->route('admin.roles.index')
            ->with('error', 'Vai trò hệ thống có toàn quyền nên không cần phân quyền.');
    }
}