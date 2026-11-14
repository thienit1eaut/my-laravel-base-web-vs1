<?php

namespace App\Admin\Http\Controllers;

use App\Admin\Http\Requests\LanguageRequest;
use App\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Quản lý ngôn ngữ của website. Không có chức năng xóa: ngôn ngữ đã có bản dịch thì chỉ nên tắt đi.
 */
class LanguageController extends AdminController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:languages.view', only: ['index']),
            new Middleware('can:languages.create', only: ['create', 'store']),
            new Middleware('can:languages.update', only: ['edit', 'update', 'quick']),
            new Middleware('can:languages.default', only: ['makeDefault']),
        ];
    }

    protected function quickRules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
    }

    /** Ngôn ngữ chính luôn phải bật. */
    protected function quickDeny(Request $request, Model $record, array $data): ?string
    {
        if ($record->is_default && array_key_exists('is_active', $data) && ! $data['is_active']) {
            return 'Không thể tắt ngôn ngữ chính. Hãy chọn ngôn ngữ chính khác trước.';
        }

        return null;
    }

    public function quick(Request $request, Language $language): RedirectResponse
    {
        return $this->quickUpdate($request, $language);
    }

    public function index(): Response
    {
        return Inertia::render('Languages/Index', [
            'languages' => Language::allCached()->map(fn (Language $language) => $language->only([
                'id', 'code', 'prefix', 'name', 'flag', 'sort_order', 'is_default', 'is_active',
            ]))->values(),
            'title' => 'Ngôn ngữ',
            'breadcrumbs' => [['label' => 'Hệ thống'], ['label' => 'Ngôn ngữ']],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Languages/Create', [
            'flags' => $this->flags(),
            'title' => 'Thêm ngôn ngữ',
            'breadcrumbs' => [
                ['label' => 'Hệ thống'],
                ['label' => 'Ngôn ngữ', 'href' => '/admin/languages'],
                ['label' => 'Thêm mới'],
            ],
        ]);
    }

    public function store(LanguageRequest $request): RedirectResponse
    {
        Language::create($request->validated());

        return redirect()->route('admin.languages.index')->with('success', 'Đã thêm ngôn ngữ.');
    }

    public function edit(Language $language): Response
    {
        return Inertia::render('Languages/Edit', [
            'language' => $language->only(['id', 'code', 'prefix', 'name', 'flag', 'sort_order', 'is_default', 'is_active']),
            'flags' => $this->flags(),
            'title' => 'Sửa ngôn ngữ',
            'breadcrumbs' => [
                ['label' => 'Hệ thống'],
                ['label' => 'Ngôn ngữ', 'href' => '/admin/languages'],
                ['label' => 'Sửa'],
            ],
        ]);
    }

    public function update(LanguageRequest $request, Language $language): RedirectResponse
    {
        $data = $request->validated();

        if ($language->is_default && ! $data['is_active']) {
            return back()->withErrors(['is_active' => 'Không thể tắt ngôn ngữ chính. Hãy chọn ngôn ngữ chính khác trước.']);
        }

        $language->update($data);

        return redirect()->route('admin.languages.index')->with('success', 'Đã cập nhật ngôn ngữ.');
    }

    /** Đặt làm ngôn ngữ chính: chỉ một ngôn ngữ là chính và nó phải đang bật. */
    public function makeDefault(Language $language): RedirectResponse
    {
        DB::transaction(function () use ($language) {
            Language::query()->where('id', '!=', $language->id)->update(['is_default' => false]);
            $language->update(['is_default' => true, 'is_active' => true]);
        });

        Language::forgetCache(); // update hàng loạt ở trên không chạy sự kiện của model

        return back()->with('success', "Đã đặt {$language->name} làm ngôn ngữ chính.");
    }

    private function flags(): array
    {
        return collect(Language::FLAGS)->map(fn ($name, $code) => ['id' => $code, 'name' => "{$name} ({$code})"])->values()->all();
    }
}
