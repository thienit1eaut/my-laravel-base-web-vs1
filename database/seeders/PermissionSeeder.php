<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Đồng bộ module và quyền từ config/permissions.php vào database.
     */
    public function run(): void
    {
        $standard = config('permissions.actions', []);
        $declared = [];
        $order = 0;

        foreach (config('permissions.modules', []) as $moduleCode => $definition) {
            $module = Module::updateOrCreate(
                ['code' => $moduleCode],
                ['name' => $definition['name'], 'sort_order' => $order++]
            );

            foreach ($definition['actions'] as $key => $value) {
                // 'view' => hành động chuẩn; 'assign' => 'Nhãn' => hành động riêng
                [$action, $label] = is_int($key)
                    ? [$value, $standard[$value] ?? $value]
                    : [$key, $value];

                $code = "{$moduleCode}.{$action}";
                $declared[] = $code;

                Permission::updateOrCreate(
                    ['code' => $code],
                    ['module_id' => $module->id, 'action' => $action, 'name' => $label]
                );
            }
        }

        // Không tự xóa để tránh mất dữ liệu phân quyền khi gõ nhầm trong file khai báo
        $orphans = Permission::whereNotIn('code', $declared)->pluck('code');

        if ($orphans->isNotEmpty()) {
            $this->command->warn('Quyền không còn được khai báo (cân nhắc xóa thủ công): '.$orphans->implode(', '));
        }

        $this->command->info('Đã đồng bộ '.count($declared).' quyền.');
    }
}