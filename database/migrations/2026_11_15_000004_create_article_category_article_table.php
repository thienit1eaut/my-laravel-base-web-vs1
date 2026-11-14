<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bảng trung gian: mỗi dòng là một cặp (bài viết, danh mục). Chứa MỌI danh mục của bài,
        // gồm cả danh mục chính (articles.category_id) để truy vấn theo danh mục chỉ cần đọc bảng này.
        Schema::create('article_category_article', function (Blueprint $table) {
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('article_categories')->cascadeOnDelete();

            $table->primary(['article_id', 'category_id']); // không trùng cặp, tra theo bài viết nhanh
            $table->index('category_id');                    // tra theo danh mục nhanh
        });

        // Đưa danh mục chính của các bài đã có vào bảng trung gian
        DB::statement(
            'INSERT INTO article_category_article (article_id, category_id) SELECT id, category_id FROM articles'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('article_category_article');
    }
};
