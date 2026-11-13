<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            // Ảnh lấy từ Thư viện ảnh; ảnh bị xóa vĩnh viễn thì tự về null
            $table->foreignId('image_id')->nullable()->after('description')
                ->constrained('medias')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropConstrainedForeignId('image_id');
        });
    }
};