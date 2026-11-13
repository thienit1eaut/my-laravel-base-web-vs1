<?php

namespace App\Admin\Http\Controllers;

use App\Admin\Http\Requests\AccountRequest;
use App\Admin\Services\PermissionChecker;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AccountController implements HasMiddleware
{
    public function __construct(private PermissionChecker $permissions)
    {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('can:accounts.view', only: ['index']),
            new Middleware('can:accounts.create', only: ['create', 'store']),
            new Middleware('can:accounts.update', only: ['edit', 'update']),
            new Middleware('can:accounts.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $role = (string) $request->query('role', '');
        $status = (string) $request->query('status', '');

        $accounts = User::query()
            ->with('role:id,name')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role !== '', fn ($query) => $query->where('id_role', $role))
            ->when(in_array($status, ['0', '1'], true), fn ($query) => $query->where('is_active', $status === '1'))
            ->latest('id')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role_name' => $user->role?->name,
                'is_active' => $user->is_active,
                'image_url' => $user->image_url,
            ]);

        return Inertia::render('Accounts/Index', [
            'accounts' => $accounts,
            'roles' => Role::orderBy('name')->get(['id', 'name']),
            'filters' => ['search' => $search, 'role' => $role, 'status' => $status],
            'title' => 'Tài khoản',
            'breadcrumbs' => [['label' => 'Người dùng'], ['label' => 'Tài khoản']],
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Accounts/Create', [
            'roles' => $this->assignableRoles($request->user()),
            'title' => 'Thêm tài khoản',
            'breadcrumbs' => [['label' => 'Người dùng'], ['label' => 'Tài khoản', 'href' => '/admin/accounts'], ['label' => 'Thêm mới']],
        ]);
    }

    public function store(AccountRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'remove_image']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('users', 'public');
        }

        User::create($data);

        return redirect()->route('admin.accounts.index')
            ->with('success', 'Đã thêm tài khoản.');
    }

    public function edit(Request $request, User $account): Response
    {
        $this->ensureManageable($request, $account);

        return Inertia::render('Accounts/Edit', [
            'account' => [
                'id' => $account->id,
                'name' => $account->name,
                'username' => $account->username,
                'email' => $account->email,
                'id_role' => $account->id_role,
                'is_active' => $account->is_active,
                'note' => $account->note,
                'image_url' => $account->image_url,
            ],
            'roles' => $this->assignableRoles($request->user(), $account->id_role),
            'title' => 'Sửa tài khoản',
            'breadcrumbs' => [['label' => 'Người dùng'], ['label' => 'Tài khoản', 'href' => '/admin/accounts'], ['label' => 'Sửa']],
        ]);
    }

    public function update(AccountRequest $request, User $account): RedirectResponse
    {
        $this->ensureManageable($request, $account);

        // Không cho tự khóa tài khoản đang đăng nhập
        if ($account->is($request->user()) && ! $request->boolean('is_active')) {
            return back()->withErrors(['is_active' => 'Bạn không thể tự khóa tài khoản đang đăng nhập.']);
        }

        $data = $request->safe()->except(['image', 'remove_image']);

        // Mật khẩu để trống = giữ mật khẩu cũ
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        if ($request->hasFile('image')) {
            $this->deleteImage($account->image);
            $data['image'] = $request->file('image')->store('users', 'public');
        } elseif ($request->boolean('remove_image')) {
            $this->deleteImage($account->image);
            $data['image'] = null;
        }

        $account->update($data);

        return redirect()->route('admin.accounts.index')
            ->with('success', 'Đã cập nhật tài khoản.');
    }

    public function destroy(Request $request, User $account): RedirectResponse
    {
        $this->ensureManageable($request, $account);

        if ($account->is($request->user())) {
            return back()->with('error', 'Bạn không thể xóa tài khoản đang đăng nhập.');
        }

        $this->deleteImage($account->image);
        $account->delete();

        return redirect()->route('admin.accounts.index')
            ->with('success', 'Đã xóa tài khoản.');
    }

    /**
     * Chỉ quản lý được tài khoản thuộc vai trò có quyền không vượt quá quyền của mình
     * (nếu không, người có quyền "sửa tài khoản" có thể đổi mật khẩu của quản trị viên rồi chiếm quyền).
     */
    private function ensureManageable(Request $request, User $account): void
    {
        abort_unless(
            $this->permissions->canAssignRole($request->user(), $account->role),
            403,
            'Bạn không có quyền quản lý tài khoản thuộc vai trò này.'
        );
    }

    /**
     * Các vai trò được phép gán (cộng thêm vai trò hiện tại của tài khoản đang sửa).
     */
    private function assignableRoles(User $actor, ?int $currentRoleId = null)
    {
        return Role::with('permissions')
            ->orderBy('name')
            ->get()
            ->filter(fn (Role $role) => $role->id === $currentRoleId || $this->permissions->canAssignRole($actor, $role))
            ->map(fn (Role $role) => ['id' => $role->id, 'name' => $role->name])
            ->values();
    }

    private function deleteImage(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}