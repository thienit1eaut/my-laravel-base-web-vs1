<?php

namespace App\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Controller gốc của phần quản trị: mọi controller module (Brand, Article, Account...) kế thừa lớp này.
 * Các hàm dùng chung cho nhiều module đặt ở đây.
 *
 * Hiện có: sửa nhanh ngay trên trang danh sách (bật/tắt trạng thái, đổi thứ tự...).
 * Cách dùng trong controller của module:
 *
 *   1. Khai báo trường được sửa nhanh và luật kiểm tra:
 *        protected function quickRules(): array { return ['is_active' => ['required', 'boolean']]; }
 *   2. Viết hàm gọi lại hàm chung (route: PATCH /admin/<module>/{id}/quick):
 *        public function quick(Request $request, Brand $brand) { return $this->quickUpdate($request, $brand); }
 *   3. (Tùy chọn) chặn trường hợp đặc biệt bằng cách ghi đè quickDeny().
 */
abstract class AdminController extends Controller
{
    /**
     * Trường được phép sửa nhanh => luật kiểm tra dữ liệu. Mặc định không có trường nào.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function quickRules(): array
    {
        return [];
    }

    /**
     * Chặn trường hợp đặc biệt. Trả về thông báo lỗi để từ chối, hoặc null để cho phép.
     * Dữ liệu $data đã qua kiểm tra. Quyền (<module>.update) đã được CheckPermission kiểm tra từ trước.
     */
    protected function quickDeny(Request $request, Model $record, array $data): ?string
    {
        return null;
    }

    /**
     * Cập nhật nhanh một hoặc vài trường của bản ghi. Trường không khai báo trong quickRules() bị từ chối
     * (không âm thầm bỏ qua). Khi lỗi trả về errors kèm thông báo đỏ để giao diện hoàn lại giá trị cũ.
     */
    protected function quickUpdate(Request $request, Model $record): RedirectResponse
    {
        $rules = $this->quickRules();
        $input = $request->except(['_token', '_method']);

        if ($input === []) {
            return $this->quickFail('quick', 'Không có dữ liệu để cập nhật.');
        }

        $forbidden = array_diff(array_keys($input), array_keys($rules));

        if ($forbidden !== []) {
            return $this->quickFail(reset($forbidden), 'Trường này không được phép sửa nhanh.');
        }

        $validator = Validator::make($input, array_intersect_key($rules, $input));

        if ($validator->fails()) {
            return $this->quickFail($validator->errors()->keys()[0], $validator->errors()->first());
        }

        $data = $validator->validated();

        if ($message = $this->quickDeny($request, $record, $data)) {
            return $this->quickFail(array_key_first($data), $message);
        }

        // Chỉ ghi các trường đã qua danh sách cho phép ở trên
        $record->forceFill($data)->save();

        return back()->with('success', 'Đã cập nhật.');
    }

    private function quickFail(string $field, string $message): RedirectResponse
    {
        return back()->withErrors([$field => $message])->with('error', $message);
    }
}
