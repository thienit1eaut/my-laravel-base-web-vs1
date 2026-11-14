<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bảng trung gian bài viết - thẻ. Xóa bài hoặc xóa thẻ thì các liên kết tự mất theo.
        Schema::create('article_tag', function (Blueprint $table) {
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();

            $table->primary(['article_id', 'tag_id']); // không trùng cặp, tra theo bài viết nhanh
            $table->index('tag_id');                    // tra theo thẻ nhanh
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_tag');
    }
};
