<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_function', function (Blueprint $table) {
            $table->id('menuFunctionId')->comment('Id');
            $table->string('menuFunctionName')->comment('選單名稱');
            $table->string('menuFunctionAlias')->comment('選單別名');
            $table->string('pathOfMenuFunction')->comment('選單路徑');
            $table->integer('menuFunctionOfParentId')->default(0)->comment('父選單Id');

            $table->tinyInteger('isCategory')->default(0)->comment('大類');
            $table->tinyInteger('isOperation')->default(0)->comment('頁面');
            $table->tinyInteger('isFunction')->default(0)->comment('功能');

            $table->string('icon')->default('setting')->comment('icon');
            $table->tinyInteger('isChildren')->default(0)->comment('子選單(pure admin)');
            $table->integer('orderOfMenuFunction')->default(0)->comment('排序');
            $table->tinyInteger('isShowLink')->default(1)->comment('顯示選單(pure admin)');
            $table->tinyInteger('isHiddenTag')->default(0)->comment('顯示標籤(pure admin)');

            //狀態
            $table->tinyInteger('isEnabled')->default(0)->comment('啟用停用');

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

        DB::statement("ALTER TABLE `menu_function` comment '後台選單'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menu_function');
    }
}
