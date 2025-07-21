<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id(); // PK
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('item_name', 100);
            $table->text('description')->nullable();
            $table->string('brand', 50)->nullable();
            $table->string('condition', 20)->nullable();
            $table->integer('price');
            $table->string('image_path', 255)->nullable();
            $table->string('category_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
}
