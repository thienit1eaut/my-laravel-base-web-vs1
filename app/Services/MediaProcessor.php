<?php

namespace App\Services;

use GdImage;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Tạo thumbnail 400/800 và bản WebP bằng GD.
 * Lỗi xử lý ảnh không làm hỏng việc tải lên: trả về những gì tạo được (có thể rỗng).
 */
class MediaProcessor
{
    // chiều rộng (px) => tên cột trong bảng medias
    private const THUMBNAILS = [400 => 'thumb_400', 800 => 'thumb_800'];

    /**
     * @return array<string, string> các cột đường dẫn đã tạo: thumb_400, thumb_800, webp_path
     */
    public function variants(string $path, string $mime): array
    {
        $result = [];

        if (! extension_loaded('gd') || ! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return $result;
        }

        $disk = Storage::disk('public');
        $absolute = $disk->path($path);

        try {
            $info = @getimagesize($absolute);

            if (! $info || $info[0] * $info[1] > config('media.max_pixels')) {
                return $result;
            }

            $image = match ($mime) {
                'image/jpeg' => @imagecreatefromjpeg($absolute),
                'image/png' => @imagecreatefrompng($absolute),
                'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($absolute) : false,
            };

            if (! $image) {
                return $result;
            }

            $image = $this->orient($image, $absolute, $mime);

            $dir = dirname($path);
            $name = pathinfo($path, PATHINFO_FILENAME);
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $width = imagesx($image);

            foreach (self::THUMBNAILS as $target => $column) {
                // Ảnh gốc không lớn hơn kích thước thumbnail thì không cần tạo (không phóng to)
                if ($width <= $target) {
                    continue;
                }

                $relative = "{$dir}/{$name}-{$target}.{$ext}";

                if ($this->write($this->resize($image, $target), $disk->path($relative), $mime)) {
                    $result[$column] = $relative;
                }
            }

            // Bản WebP đầy đủ kích thước (ảnh gốc đã là webp thì không cần)
            if ($mime !== 'image/webp' && function_exists('imagewebp')) {
                $relative = "{$dir}/{$name}.webp";

                if ($this->write($image, $disk->path($relative), 'image/webp')) {
                    $result['webp_path'] = $relative;
                }
            }
        } catch (Throwable $e) {
            report($e);
        }

        return $result;
    }

    // Xoay ảnh JPEG theo EXIF (ảnh chụp từ điện thoại)
    private function orient(GdImage $image, string $absolute, string $mime): GdImage
    {
        if ($mime !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($absolute);

        $angle = match ($exif['Orientation'] ?? 1) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        return $angle === 0 ? $image : (imagerotate($image, $angle, 0) ?: $image);
    }

    private function resize(GdImage $image, int $targetWidth): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $targetHeight = max(1, (int) round($height * $targetWidth / $width));

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));

        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        return $canvas;
    }

    private function write(GdImage $image, string $absolute, string $mime): bool
    {
        return match ($mime) {
            'image/jpeg' => imagejpeg($image, $absolute, config('media.jpeg_quality')),
            'image/png' => imagepng($image, $absolute, 6),
            'image/webp' => $this->writeWebp($image, $absolute),
            default => false,
        };
    }

    private function writeWebp(GdImage $image, string $absolute): bool
    {
        // imagewebp không nhận ảnh dạng bảng màu (PNG 8 bit)
        if (! imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);

        return imagewebp($image, $absolute, config('media.webp_quality'));
    }
}