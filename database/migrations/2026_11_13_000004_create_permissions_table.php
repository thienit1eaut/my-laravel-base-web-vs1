<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->string('action', 100);          // view, create, update, delete, images.create...
            $table->string('code', 150)->unique();  // module.action, ví dụ brands.create
            $table->string('name');                 // nhãn hiển thị
            $table->timestamps();

            $table->unique(['module_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};