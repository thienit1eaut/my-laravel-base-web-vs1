<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use SoftDeletes;

    // Laravel sẽ đoán tên bảng là "media", nên khai báo rõ
    protected $table = 'medias';

    protected $fillable = [
        'name', 'type', 'extension', 'title', 'alt',
        'path', 'webp_path', 'thumb_400', 'thumb_800',
        'mime', 'size', 'width', 'height', 'uploaded_by',
    ];

    protected $casts = [
        'type' => MediaType::class,
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    protected static function booted(): void
    {
        // Chỉ khi xóa vĩnh viễn mới xóa tệp trên đĩa (xóa mềm thì giữ lại để khôi phục)
        static::forceDeleted(fn (Media $media) => $media->deleteFiles());
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // URL công khai của tệp gốc (cùng cách với User::image_url, tránh lệch APP_URL)
    protected function url(): Attribute
    {
        return Attribute::get(fn () => asset('storage/'.$this->path));
    }

    // URL của một biến thể: 'webp_path' | 'thumb_400' | 'thumb_800'. Chưa có thì trả về null.
    public function variantUrl(string $column): ?string
    {
        return $this->{$column} ? asset('storage/'.$this->{$column}) : null;
    }

    public function deleteVariantFiles(): void
    {
        $files = array_filter([$this->webp_path, $this->thumb_400, $this->thumb_800]);

        if ($files) {
            Storage::disk('public')->delete($files);
        }
    }

    public function deleteFiles(): void
    {
        $this->deleteVariantFiles();
        Storage::disk('public')->delete($this->path);
    }
}