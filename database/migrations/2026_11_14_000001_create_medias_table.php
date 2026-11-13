<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medias', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // tên tệp gốc, dùng để hiển thị
            $table->string('title')->nullable();          // tiêu đề do người dùng nhập
            $table->string('alt')->nullable();            // văn bản thay thế (SEO, trợ năng)
            $table->string('path');                       // đường dẫn trong disk public
            $table->string('mime', 100);
            $table->unsignedBigInteger('size');           // byte
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medias');
    }
};