<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medias', function (Blueprint $table) {
            // Các dòng cũ đều là ảnh nên mặc định 'image'
            $table->string('type', 20)->default('image')->after('name')->index();
            $table->string('extension', 10)->nullable()->after('type');
            $table->string('webp_path')->nullable()->after('path');
            $table->string('thumb_400')->nullable()->after('webp_path');
            $table->string('thumb_800')->nullable()->after('thumb_400');
            $table->softDeletes(); // xóa mềm: thùng rác
        });
    }

    public function down(): void
    {
        Schema::table('medias', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['type', 'extension', 'webp_path', 'thumb_400', 'thumb_800']);
        });
    }
};