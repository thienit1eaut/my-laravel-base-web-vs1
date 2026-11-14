<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = [
        'brands', 'article_categories', 'articles', 'tags',
        'product_categories', 'products', 'product_tags',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->string('meta_key', 255)->nullable();          // từ khóa SEO
                $table->boolean('no_index')->default(false);          // true = báo công cụ tìm kiếm không lập chỉ mục
                $table->boolean('no_follow')->default(false);         // true = báo công cụ tìm kiếm không theo link
                $table->foreignId('public_by')->nullable()->constrained('users')->nullOnDelete(); // người đăng
            });
        }

        // Bài viết trước đây lưu người viết ở author_id: chuyển sang public_by cho thống nhất
        DB::table('articles')->update(['public_by' => DB::raw('author_id')]);

        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['author_id']);
            $table->dropColumn('author_id');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
        });

        DB::table('articles')->update(['author_id' => DB::raw('public_by')]);

        foreach (self::TABLES as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropForeign(['public_by']);
                $table->dropColumn(['meta_key', 'no_index', 'no_follow', 'public_by']);
            });
        }
    }
};
