<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); // PK
            $table->string('category_name', 50);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::disableForeignKeyConstraints(); // 追加
        Schema::dropIfExists('categories');
        Schema::enableForeignKeyConstraints(); // 追加
    }
};
