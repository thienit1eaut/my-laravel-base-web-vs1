<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Ngôn ngữ của website. Danh sách nhỏ và được đọc ở mọi trang web nên lưu cache, tự xóa khi có thay đổi.
 */
class Language extends Model
{
    private const CACHE_KEY = 'languages.all';

    /** Lá cờ có thể chọn: mã quốc gia => tên. */
    public const FLAGS = [
        'vn' => 'Việt Nam',
        'gb' => 'Anh',
        'us' => 'Mỹ',
        'jp' => 'Nhật Bản',
        'kr' => 'Hàn Quốc',
        'cn' => 'Trung Quốc',
        'fr' => 'Pháp',
        'de' => 'Đức',
        'th' => 'Thái Lan',
    ];

    /** Tiền tố không được dùng vì trùng đường dẫn có sẵn của hệ thống. */
    public const RESERVED_PREFIXES = ['admin', 'api', 'storage', 'build', 'up', 'login', 'logout'];

    protected $fillable = ['code', 'prefix', 'name', 'flag', 'sort_order', 'is_default', 'is_active'];

    protected function casts(): array
    {
        return ['is_default' => 'boolean', 'is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::forgetCache());
        static::deleted(fn () => static::forgetCache());
    }

    /**
     * Mọi ngôn ngữ, theo thứ tự hiển thị (có cache).
     *
     * @return Collection<int, Language>
     */
    public static function allCached(): Collection
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->orderBy('sort_order')->orderBy('id')->get());
    }

    /** Các ngôn ngữ đang bật. */
    public static function active(): Collection
    {
        return static::allCached()->where('is_active', true)->values();
    }

    /** Ngôn ngữ chính (mặc định). */
    public static function default(): ?self
    {
        return static::allCached()->firstWhere('is_default', true);
    }

    /** Tìm ngôn ngữ đang bật theo tiền tố trên đường dẫn. */
    public static function findActiveByPrefix(string $prefix): ?self
    {
        return static::active()->firstWhere('prefix', $prefix);
    }

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
