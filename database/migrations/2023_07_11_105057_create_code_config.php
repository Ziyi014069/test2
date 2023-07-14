<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCodeConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('code_config', function (Blueprint $table) {
            $table->id('id')->comment('Id');
            $table->string('kind')->comment('分類代號');
            $table->string('kindName')->comment('分類名稱');
            $table->string('name')->comment('名稱');
            $table->string('code')->comment('代碼');
            $table->string('value')->default(0)->comment('數值');

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

        DB::statement("ALTER TABLE `code_config` comment '參數或狀態碼'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('code_config');
    }
}
