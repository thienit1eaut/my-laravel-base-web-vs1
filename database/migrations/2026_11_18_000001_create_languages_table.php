<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();     // mã ngôn ngữ (vi, en, ja...): lưu trong bản dịch, dùng cho thẻ lang/hreflang. Không đổi sau khi tạo.
            $table->string('prefix', 10)->unique();   // tiền tố trên đường dẫn web (vn, en, jp...)
            $table->string('name', 60);               // tên hiển thị (Tiếng Việt)
            $table->string('flag', 5)->nullable();    // mã quốc gia của lá cờ (vn, gb, jp...)
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_default')->default(false); // ngôn ngữ chính (chỉ một ngôn ngữ)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();

        DB::table('languages')->insert([
            ['code' => 'vi', 'prefix' => 'vn', 'name' => 'Tiếng Việt', 'flag' => 'vn', 'sort_order' => 1, 'is_default' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'en', 'prefix' => 'en', 'name' => 'English', 'flag' => 'gb', 'sort_order' => 2, 'is_default' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
