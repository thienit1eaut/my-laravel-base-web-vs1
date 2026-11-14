<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // Danh mục chính (dùng cho breadcrumb/link chuẩn). Không cho xóa danh mục còn sản phẩm.
            $table->foreignId('category_id')->constrained('product_categories')->restrictOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->string('sku', 60)->nullable()->unique();
            // Giá tính bằng đồng (số nguyên). sale_price = giá khuyến mãi, để trống nếu không giảm.
            $table->unsignedBigInteger('price')->default(0);
            $table->unsignedBigInteger('sale_price')->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->text('short_description')->nullable();
            $table->longText('content')->nullable();
            $table->json('specs')->nullable(); // thông số kỹ thuật: [{"name": "...", "value": "..."}]
            $table->foreignId('image_id')->nullable()->constrained('medias')->nullOnDelete(); // ảnh đại diện
            $table->string('meta_title', 160)->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
