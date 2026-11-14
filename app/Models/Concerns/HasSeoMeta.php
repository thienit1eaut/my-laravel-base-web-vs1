<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Các trường SEO và người đăng dùng chung: meta_key (từ khóa), no_index, no_follow, public_by (người đăng).
 * public_by tự gán người đang đăng nhập khi tạo mới (không nhận từ form để không giả mạo được).
 */
trait HasSeoMeta
{
    public function initializeHasSeoMeta(): void
    {
        $this->mergeFillable(['meta_key', 'no_index', 'no_follow']);
        $this->mergeCasts(['no_index' => 'boolean', 'no_follow' => 'boolean']);
    }

    protected static function bootHasSeoMeta(): void
    {
        static::creating(function ($model) {
            if ($model->public_by === null && Auth::check()) {
                $model->public_by = Auth::id();
            }
        });
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'public_by');
    }

    /** Giá trị thẻ meta robots, vd "index,follow" hoặc "noindex,nofollow". */
    public function robots(): string
    {
        return ($this->no_index ? 'noindex' : 'index').','.($this->no_follow ? 'nofollow' : 'follow');
    }
}
