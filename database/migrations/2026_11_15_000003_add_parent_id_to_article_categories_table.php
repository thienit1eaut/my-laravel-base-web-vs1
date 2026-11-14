<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('article_categories', function (Blueprint $table) {
            // Danh mục cha (null = danh mục gốc). Không cho xóa danh mục cha còn danh mục con.
            $table->foreignId('parent_id')->nullable()->after('id')
                ->constrained('article_categories')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('article_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};
