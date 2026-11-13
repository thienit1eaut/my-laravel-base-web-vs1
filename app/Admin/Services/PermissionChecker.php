<?php

namespace App\Admin\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

class PermissionChecker
{
    /** @var array<string,int>|null  [mã quyền => id] của mọi quyền đã khai báo */
    private ?array $declared = null;

    /** @var array<int,array<string,int>>  [id vai trò => [mã quyền => id]] */
    private array $roleCodes = [];

    /**
     * Mã quyền này đã được khai báo (có trong database) chưa?
     */
    public function declared(string $code): bool
    {
        return isset($this->declaredCodes()[$code]);
    }

    /**
     * Vai trò hệ thống có toàn quyền ngầm định.
     */
    public function isSuper(?User $user): bool
    {
        return (bool) $user?->role?->is_system;
    }

    /**
     * Người dùng có quyền này không? Mã chưa khai báo thì luôn bị từ chối.
     */
    public function allows(?User $user, string $code): bool
    {
        if (! $user || ! $user->is_active || ! $this->declared($code)) {
            return false;
        }

        if ($this->isSuper($user)) {
            return true;
        }

        return isset($this->codesOfRole($user->role)[$code]);
    }

    /**
     * Danh sách mã quyền của người dùng (gửi cho giao diện để ẩn/hiện).
     *
     * @return list<string>
     */
    public function codesFor(?User $user): array
    {
        if (! $user) {
            return [];
        }

        return array_keys($this->isSuper($user) ? $this->declaredCodes() : $this->codesOfRole($user->role));
    }

    /**
     * Id các quyền mà người này được phép bật/tắt cho vai trò khác (chính các quyền họ đang có).
     *
     * @return list<int>
     */
    public function grantableIds(User $user): array
    {
        return array_values($this->isSuper($user) ? $this->declaredCodes() : $this->codesOfRole($user->role));
    }

    /**
     * Có được gán hoặc quản lý vai trò này không? Quyền của vai trò đó không được vượt quá quyền của mình.
     */
    public function canAssignRole(User $actor, ?Role $role): bool
    {
        if ($role === null || $this->isSuper($actor)) {
            return true;
        }

        if ($role->is_system) {
            return false;
        }

        return array_diff($role->permissions->pluck('id')->all(), $this->grantableIds($actor)) === [];
    }

    /** @return array<string,int> */
    private function declaredCodes(): array
    {
        return $this->declared ??= Permission::pluck('id', 'code')->all();
    }

    /** @return array<string,int> */
    private function codesOfRole(?Role $role): array
    {
        if (! $role) {
            return [];
        }

        return $this->roleCodes[$role->id] ??= $role->permissions()
            ->pluck('permissions.id', 'permissions.code')
            ->all();
    }
}