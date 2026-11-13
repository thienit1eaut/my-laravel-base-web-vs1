<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\MediaProcessor;
use Illuminate\Console\Command;

class RegenerateMediaVariants extends Command
{
    protected $signature = 'media:regenerate {--all : Tạo lại cả những ảnh đã có thumbnail/WebP}';

    protected $description = 'Tạo thumbnail 400/800 và bản WebP cho các ảnh trong thư viện';

    public function handle(MediaProcessor $processor): int
    {
        $query = Media::query()->where('type', 'image');

        if (! $this->option('all')) {
            $query->whereNull('thumb_400')->whereNull('thumb_800')->whereNull('webp_path');
        }

        $done = 0;

        $query->chunkById(50, function ($medias) use ($processor, &$done) {
            foreach ($medias as $media) {
                $media->deleteVariantFiles();

                $media->update(array_merge(
                    ['thumb_400' => null, 'thumb_800' => null, 'webp_path' => null],
                    $processor->variants($media->path, $media->mime),
                ));

                $done++;
            }
        });

        $this->info("Đã xử lý {$done} ảnh.");

        return self::SUCCESS;
    }
}