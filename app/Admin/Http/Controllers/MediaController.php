<?php

namespace App\Admin\Http\Controllers;

use App\Admin\Http\Requests\MediaUpdateRequest;
use App\Admin\Http\Requests\MediaUploadRequest;
use App\Admin\Services\MediaPresenter;
use App\Enums\MediaType;
use App\Models\Media;
use App\Services\MediaProcessor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class MediaController implements HasMiddleware
{
    // Tầng 2: kiểm tra quyền theo từng hành động
    public static function middleware(): array
    {
        return [
            new Middleware('can:medias.view', only: ['index']),
            new Middleware('can:medias.create', only: ['store']),
            new Middleware('can:medias.update', only: ['update']),
            new Middleware('can:medias.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): Response|JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $type = MediaType::tryFrom((string) $request->query('type', ''));

        $medias = Media::query()
            ->with('uploader:id,name')
            ->when($type, fn ($query) => $query->where('type', $type->value))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(24)
            ->withQueryString()
            ->through(fn (Media $media) => MediaPresenter::make($media));

        // Cửa sổ chọn ảnh (MediaPicker) gọi bằng fetch và nhận JSON
        if ($request->boolean('picker')) {
            return response()->json($medias);
        }

        // Số lượng theo từng loại (không tính tệp trong thùng rác)
        $byType = Media::query()->selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type');

        $counts = ['all' => (int) $byType->sum()];
        foreach (MediaType::cases() as $case) {
            $counts[$case->value] = (int) ($byType[$case->value] ?? 0);
        }

        return Inertia::render('Medias/Index', [
            'medias' => $medias,
            'filters' => ['search' => $search, 'type' => $type?->value ?? ''],
            'counts' => $counts,
            'trashCount' => Media::onlyTrashed()->count(),
            'accept' => implode(',', MediaType::allMimes()),
            'types' => collect(MediaType::cases())->map(fn (MediaType $case) => [
                'value' => $case->value,
                'label' => $case->label(),
                'max_mb' => round($case->maxKb() / 1024),
            ])->all(),
            'title' => 'Thư viện ảnh',
            'breadcrumbs' => [['label' => 'Thư viện ảnh']],
        ]);
    }

    public function store(MediaUploadRequest $request, MediaProcessor $processor): RedirectResponse
    {
        $folder = 'medias/'.now()->format('Y/m');
        $count = 0;

        foreach ($request->file('files') as $file) {
            // Loại tệp và đuôi tệp lấy từ nội dung thật + danh sách cho phép
            $mime = $file->getMimeType();
            $type = MediaType::fromMime($mime);
            $extension = MediaType::extensionFor($mime);
            $size = $file->getSize();

            $attributes = [];

            if ($type === MediaType::Image) {
                [$width, $height] = @getimagesize($file->getRealPath()) ?: [null, null];
                $attributes = ['width' => $width, 'height' => $height];
            }

            // Tên tệp trên đĩa ngẫu nhiên, đuôi từ danh sách cho phép
            $path = $file->storeAs($folder, Str::random(40).'.'.$extension, 'public');

            // Thumbnail 400/800 và bản WebP (chỉ với ảnh)
            if ($type === MediaType::Image) {
                $attributes += $processor->variants($path, $mime);
            }

            Media::create([
                'name' => Str::limit($file->getClientOriginalName(), 200, ''),
                'type' => $type,
                'extension' => $extension,
                'path' => $path,
                'mime' => $mime,
                'size' => $size,
                'uploaded_by' => $request->user()->id,
            ] + $attributes);

            $count++;
        }

        return back()->with('success', "Đã tải lên {$count} tệp.");
    }

    public function update(MediaUpdateRequest $request, Media $media): RedirectResponse
    {
        $media->update($request->validated());

        return back()->with('success', 'Đã cập nhật thông tin tệp.');
    }

    // Chuyển vào thùng rác (xóa mềm), dùng cho cả một tệp và nhiều tệp
    public function destroy(Request $request): RedirectResponse
    {
        Gate::authorize('medias.delete');

        $ids = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer'],
        ])['ids'];

        $count = 0;

        foreach (Media::whereIn('id', $ids)->get() as $media) {
            $media->delete();
            $count++;
        }

        return back()->with('success', "Đã chuyển {$count} tệp vào thùng rác.");
    }
}