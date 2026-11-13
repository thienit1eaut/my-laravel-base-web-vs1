<?php

namespace App\Admin\Http\Controllers;

use App\Admin\Services\MediaPresenter;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MediaTrashController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:medias.trash.view', only: ['index']),
            new Middleware('can:medias.trash.restore', only: ['restore']),
            new Middleware('can:medias.trash.delete', only: ['destroy']),
            new Middleware('can:medias.trash.empty', only: ['empty']),
        ];
    }

    public function index(): Response
    {
        $medias = Media::onlyTrashed()
            ->with('uploader:id,name')
            ->latest('deleted_at')
            ->paginate(24)
            ->through(fn (Media $media) => MediaPresenter::make($media));

        return Inertia::render('Medias/Trash', [
            'medias' => $medias,
            'title' => 'Thùng rác',
            'breadcrumbs' => [
                ['label' => 'Thư viện ảnh', 'href' => '/admin/medias'],
                ['label' => 'Thùng rác'],
            ],
        ]);
    }

    public function restore(Request $request): RedirectResponse
    {
        Gate::authorize('medias.trash.restore');

        $count = 0;

        foreach (Media::onlyTrashed()->whereIn('id', $this->ids($request))->get() as $media) {
            $media->restore();
            $count++;
        }

        return back()->with('success', "Đã khôi phục {$count} tệp.");
    }

    // Xóa vĩnh viễn: xóa bản ghi và toàn bộ tệp trên đĩa (gốc, WebP, thumbnail)
    public function destroy(Request $request): RedirectResponse
    {
        Gate::authorize('medias.trash.delete');

        $count = 0;

        foreach (Media::onlyTrashed()->whereIn('id', $this->ids($request))->get() as $media) {
            $media->forceDelete();
            $count++;
        }

        return back()->with('success', "Đã xóa vĩnh viễn {$count} tệp.");
    }

    public function empty(): RedirectResponse
    {
        Gate::authorize('medias.trash.empty');

        $count = 0;

        Media::onlyTrashed()->chunkById(100, function ($medias) use (&$count) {
            foreach ($medias as $media) {
                $media->forceDelete();
                $count++;
            }
        });

        return back()->with('success', "Đã dọn sạch thùng rác ({$count} tệp).");
    }

    private function ids(Request $request): array
    {
        return $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer'],
        ])['ids'];
    }
}