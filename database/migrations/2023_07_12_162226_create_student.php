<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student', function (Blueprint $table) {
            $table->id('studentId')->comment('Id');
            $table->integer('classId')->comment('班級Id');
            $table->string('studentName')->comment('名稱');
            $table->string('studentCode')->comment('學號 帳號');
            $table->string('password')->comment('密碼');
            $table->string('gender')->nullable()->comment('性別');
            $table->string('pathOfAvater')->nullable()->comment('頭像圖');
            $table->string('accessToken',255)->nullable()->comment('登入token');
            $table->string('pushToken',255)->nullable()->comment('推撥token');
            $table->integer('experienceTotal')->default(0)->comment('累計經驗');
            $table->integer('level')->default(0)->comment('等級');


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

        DB::statement("ALTER TABLE `student` comment '學生'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student');
    }
}
