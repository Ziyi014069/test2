<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id('booksId')->comment('Id');
            $table->string('booksName')->comment('書籍名稱');
            $table->string('booksCode')->comment('書籍代碼');
            $table->string('coverImage')->comment('封面圖');
            $table->string('description')->comment('說明');

            //狀態
            $table->tinyInteger('isEnabled')->default(0)->comment('啟用停用');
            $table->tinyInteger('isPublished')->default(0)->comment('公開非公開');
            $table->string('category')->default('學校')->comment('分類 ( 班級 學校)');

            //基本時間
            $table->timestamps();
            $table->string('creator', 255)->nullable()->comment('建立者');
            $table->string('ipOfCreator', 255)->nullable()->comment('建立者IP');
            $table->string('lastUpdater', 255)->nullable()->comment('最後更新者');
            $table->string('ipOfLastUpdater', 255)->nullable()->comment('最後更新者IP');
            $table->tinyInteger('isRemoved')->default(0)->comment('是否刪除');
            $table->dateTime('removeTime')->nullable()->comment('刪除時間');
            $table->string('remover', 255)->nullable()->comment('刪除者');
            $table->string('ipOfRemover', 255)->nullable()->comment('刪除者IP');
            //備註
            $table->string('memo')->nullable()->comment('備註');
        });


        DB::statement("ALTER TABLE `books` comment '書籍'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books');
    }
}
