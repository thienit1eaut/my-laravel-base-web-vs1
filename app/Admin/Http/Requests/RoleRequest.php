<?php

namespace App\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Tầng 3: chặn lại ngay trước khi xử lý dữ liệu
        return Gate::allows($this->route('role') ? 'roles.update' : 'roles.create');
    }

    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($role)],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}