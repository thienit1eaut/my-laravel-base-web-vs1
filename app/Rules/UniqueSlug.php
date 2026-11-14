<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

/**
 * Kiểm tra slug chưa bị dùng, kèm gợi ý slug còn trống (vd: "ten-bai-2").
 *
 * Hiện tại chỉ kiểm tra trong bảng của chính module. Mọi form request đều dùng lớp này nên
 * sau này nếu làm bảng "routes" dùng chung cho nhiều module, chỉ cần sửa ở đây.
 */
class UniqueSlug implements ValidationRule
{
    public function __construct(
        private readonly string $table,
        private readonly mixed $ignoreId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->exists($value)) {
            return;
        }

        $i = 2;
        while ($this->exists("{$value}-{$i}")) {
            $i++;
        }

        $fail("Đường dẫn này đã được sử dụng. Gợi ý: {$value}-{$i}");
    }

    private function exists(string $slug): bool
    {
        return DB::table($this->table)
            ->where('slug', $slug)
            ->when($this->ignoreId, fn ($query) => $query->where('id', '!=', $this->ignoreId))
            ->exists();
    }
}
