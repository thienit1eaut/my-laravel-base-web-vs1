<?php

namespace App\Enums;

enum MediaType: string
{
    case Image = 'image';
    case Document = 'document';
    case Audio = 'audio';
    case Video = 'video';

    public function label(): string
    {
        return config("media.types.{$this->value}.label");
    }

    /** Danh sách MIME của loại này. */
    public function mimes(): array
    {
        return array_keys(config("media.types.{$this->value}.mimes"));
    }

    public function maxKb(): int
    {
        return (int) config("media.types.{$this->value}.max_kb");
    }

    /** Xác định loại từ MIME (MIME do máy chủ đọc từ nội dung tệp). */
    public static function fromMime(?string $mime): ?self
    {
        foreach (self::cases() as $type) {
            if (in_array($mime, $type->mimes(), true)) {
                return $type;
            }
        }

        return null;
    }

    /** Đuôi tệp an toàn tương ứng với MIME (lấy từ danh sách cho phép, không lấy từ người dùng). */
    public static function extensionFor(?string $mime): ?string
    {
        foreach (self::cases() as $type) {
            $map = config("media.types.{$type->value}.mimes");

            if (isset($map[$mime])) {
                return $map[$mime];
            }
        }

        return null;
    }

    public static function allMimes(): array
    {
        return array_merge(...array_map(fn (self $type) => $type->mimes(), self::cases()));
    }
}