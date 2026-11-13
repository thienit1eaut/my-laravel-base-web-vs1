<?php

namespace App\Admin\Http\Requests;

use App\Admin\Services\PermissionChecker;
use App\Models\Role;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows($this->route('account') ? 'accounts.update' : 'accounts.create');
    }

    public function rules(): array
    {
        // Khi sửa: là User đang sửa (để bỏ qua chính nó khi kiểm tra trùng). Khi thêm: null.
        $account = $this->route('account');

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required', 'string', 'min:3', 'max:50',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('users', 'username')->ignore($account),
            ],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($account),
            ],
            'id_role' => [
                'required', 'integer', 'exists:roles,id',
                // Chống leo thang quyền: chỉ gán vai trò có quyền không vượt quá quyền của mình
                function (string $attribute, mixed $value, Closure $fail) use ($account) {
                    // Giữ nguyên vai trò hiện tại thì không cần kiểm tra lại
                    if ($account && (int) $account->id_role === (int) $value) {
                        return;
                    }

                    $role = Role::with('permissions')->find($value);

                    if ($role && ! app(PermissionChecker::class)->canAssignRole($this->user(), $role)) {
                        $fail('Bạn không thể gán vai trò có quyền vượt quá quyền của mình.');
                    }
                },
            ],
            // Thêm mới: bắt buộc. Sửa: để trống nghĩa là giữ mật khẩu cũ.
            'password' => [$account ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['sometimes', 'boolean'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'Tên đăng nhập chỉ gồm chữ, số, dấu chấm, gạch dưới và gạch ngang.',
        ];
    }
}