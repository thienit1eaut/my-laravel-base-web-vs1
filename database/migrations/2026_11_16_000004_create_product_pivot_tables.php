<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mọi danh mục của sản phẩm (gồm danh mục chính và danh mục cha), để lọc theo danh mục chỉ cần đọc bảng này
        Schema::create('product_category_product', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('product_categories')->cascadeOnDelete();

            $table->primary(['product_id', 'category_id']);
            $table->index('category_id');
        });

        Schema::create('product_product_tag', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('product_tags')->cascadeOnDelete();

            $table->primary(['product_id', 'tag_id']);
            $table->index('tag_id');
        });

        // Thư viện ảnh của sản phẩm (lấy từ Thư viện ảnh), sort_order = thứ tự hiển thị
        Schema::create('product_media', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('medias')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->primary(['product_id', 'media_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_media');
        Schema::dropIfExists('product_product_tag');
        Schema::dropIfExists('product_category_product');
    }
};
